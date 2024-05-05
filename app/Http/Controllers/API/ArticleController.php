<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Models\Article;
use App\Models\ArticleTag;
use Validator;

class ArticleController extends BaseController
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        $articles = Article::query();
        if ($search = $request->input('search')) {
            $articles->where('title', 'like', '%' . $search . '%')
            ->orWhere('slug', 'like', '%' . $search . '%');
        }

        if ($sort = $request->input('sort') ?? 'id') {
            $articles->orderBy($sort);
        }

        if ($order = $request->input('order') ?? 'asc') {
            $articles->orderBy('id', $order);
        }

        if ($request->input('category_id')) {
            $articles->where('category_id', $request->input('category_id'));
        }

        if ($request->input('is_draft')) {
            $articles->where('is_draft', $request->input('is_draft'));
        }

        $perPage = $request->input('per_page') ?? 10;
        $page = $request->input('page', 1);

        $result = $articles->paginate($perPage, ['*'], 'page', $page);

        return $this->sendResponse($result, 'Articles retrieved successfully.');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'category_id' => 'required',
            'content' => 'required',
            'cover' => 'required'
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $input['slug'] = \Str::slug($input['title']);
        $input['is_draft'] = $request->input('is_draft') ?? 0;
        $input['user_id'] = auth()->user()->id;

        if (isset($input['cover'])) {
            $cover = $input['cover'];
            $originalName = $cover->getClientOriginalName();
            $ext = $cover->getClientOriginalExtension();
            $coverName = $input['slug'] . '-' . time() . '.' . $ext;
            $coverThumbnail = $input['slug'] . '-thumbnail-' . time() . '.' . $ext;

            $path = $request->file('cover')->storeAs('articles/cover', $coverName, 'public');
            $pathThumbnail = $request->file('cover')->storeAs('articles/cover/thumbnail', $coverThumbnail, 'public');

            $smallthumbnailpath = public_path('storage/articles/cover/thumbnail/'.$coverThumbnail);
            $this->createThumbnail($smallthumbnailpath, 150, 93);

            $input['cover'] = $path;
            $input['thumbnail'] = $pathThumbnail;
        }

        $article = Article::create($input);

        if ($request->tags) {
            foreach ($request->tags as $tag) {
                $tag['article_id'] = $article->id;
                $tag['tag_id'] = $tag;
                $articleTag = ArticleTag::create($tag);
            }
        }

        return $this->sendResponse($article, 'Article created successfully.');
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $article = Article::with(['category|id,name', 'user|id,name', 'tags|id,title'])->find($id);
        if (is_null($article)) {
            return $this->sendError('Article not found.');
        }

        return $this->sendResponse($article, 'Article retrieved successfully.');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|max:255',
            'category_id' => 'required',
            'content' => 'required',
        ]);

        if($validator->fails()){
            return $this->sendError('Validation Error.', $validator->errors());
        }

        $input = $request->all();
        $article = Article::find($id);
        if(is_null($article)) {
            return $this->sendError('Article not found.');
        }
        $input['slug'] = \Str::slug($input['title']);
        $input['is_draft'] = $request->input('is_draft') ?? 0;

        if (isset($input['cover'])) {
            if ($article->cover && \Storage::exists('public/'. $article->cover)) {
                \Storage::delete('public/'.$article->cover);
            }

            if ($article->thumbnail && \Storage::exists('public/'. $article->thumbnail)) {
                \Storage::delete('public/'.$article->thumbnail);
            }

            $cover = $input['cover'];
            $originalName = $cover->getClientOriginalName();
            $ext = $cover->getClientOriginalExtension();
            $coverName = $input['slug'] . '-' . time() . '.' . $ext;
            $coverThumbnail = $input['slug'] . '-thumbnail-' . time() . '.' . $ext;

            $path = $request->file('cover')->storeAs('articles/cover', $coverName, 'public');
            $pathThumbnail = $request->file('cover')->storeAs('articles/cover/thumbnail', $coverThumbnail, 'public');

            $smallthumbnailpath = public_path('storage/articles/cover/thumbnail/'.$coverThumbnail);
            $this->createThumbnail($smallthumbnailpath, 150, 93);

            $input['cover'] = $path;
            $input['thumbnail'] = $pathThumbnail;
        }

        $article->update($input);

        if ($request->tags) {
            foreach ($request->tags as $tag) {
                $tag['article_id'] = $article->id;
                $tag['tag_id'] = $tag;
                $articleTag = ArticleTag::create($tag);
            }
        }

        return $this->sendResponse($article, 'Article updated successfully.');

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $article = Article::find($id);
        if(is_null($article)) {
            return $this->sendError('Article not found.');
        }
        $article->delete();

        return $this->sendResponse($article, 'Article deleted successfully.');
    }

    /**
     * Retrieves all soft-deleted articles.
     *
     * @return \Illuminate\Http\JsonResponse The JSON response containing the retrieved articles and a success message.
     */
    public function trash()
    {
        $articles = Article::onlyTrashed()->get();
        return $this->sendResponse($articles, 'Articles retrieved successfully.');
    }

    /**
     * Restores all soft-deleted articles.
     *
     * @param void
     * @throws void
     * @return void
     */
    public function restoreAll()
    {
        $articles = Article::onlyTrashed()->restore();
        return $this->sendResponse($articles, 'Articles restored successfully.');
    }

    /**
     * Restores a soft-deleted article.
     *
     * @param int $id The ID of the article to restore.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the restored article and a success message.
     */
    public function restore($id)
    {
        $article = Article::onlyTrashed()->find($id);
        if(is_null($article)) {
            return $this->sendError('Article not found.');
        }
        $article->restore();
        return $this->sendResponse($article, 'Article restored successfully.');
    }

    /**
     * Permanently deletes an article and its associated cover and thumbnail images.
     *
     * @param int $id The ID of the article to delete.
     * @throws \Exception If an error occurs during the deletion process.
     * @return \Illuminate\Http\JsonResponse The JSON response containing the deleted article and a success message.
     */
    public function forceDelete($id)
    {
        $article = Article::onlyTrashed()->find($id);
        if(is_null($article)) {
            return $this->sendError('Article not found.');
        }

        if ($article->cover && \Storage::exists('public/'. $article->cover)) {
            \Storage::delete('public/'.$article->cover);
        }

        if ($article->thumbnail && \Storage::exists('public/'. $article->thumbnail)) {
            \Storage::delete('public/'.$article->thumbnail);
        }

        $article->forceDelete();
        return $this->sendResponse($article, 'Article deleted permanently successfully.');
    }
}
