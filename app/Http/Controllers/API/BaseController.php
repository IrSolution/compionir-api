<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class BaseController extends Controller
{
    /**
     * success response method.
     *
     */
    public function sendResponse($result, $message) {
        $response = [
            'success' => true,
            'data' => $result,
            'message' => $message
        ];

        return response()->json($response, 200);
    }

    /**
     * error response method.
     *
     */
    public function sendError($error, $errorMessage = [], $code = 404) {
        $response = [
            'success' => false,
            'message' => $error
        ];

        if (!empty($errorMessage)) {
            $response['data'] = $errorMessage;
        }

        return response()->json($response, $code);
    }

    /**
     * Create a thumbnail of specified size
     *
     * @param string $path path of thumbnail
     * @param int $width
     * @param int $height
     */
    public function createThumbnail($path, $width, $height)
    {
        $manager = new ImageManager(new Driver());
        $image = $manager->read($path);
        $image->scale(width: $width, height: $height);
        $image->save($path);
    }

    /**
     * Uploads an image with a thumbnail and returns the paths of the image and thumbnail.
     *
     * @param mixed $image The image to be uploaded.
     * @param string $title The title of the image.
     * @param string $path The path where the image will be stored.
     * @param int $width The width of the thumbnail.
     * @param int $height The height of the thumbnail.
     * @return array An array containing the paths of the image and thumbnail.
     */
    public function uploadImageWithThumbnail($image, $title, $path, $width, $height)
    {
        $input = array();
        $originalName = $image->getClientOriginalName();
        $ext = $image->getClientOriginalExtension();
        $imageName = $title . '-' . time() . '.' . $ext;
        $imageThumbnail = $title . '-thumbnail-' . time() . '.' . $ext;

        $pathImage = $image->storeAs($path, $imageName, 'public');
        $pathThumbnail = $image->storeAs($path.'/thumbnail', $imageThumbnail, 'public');

        $smallthumbnailpath = public_path('storage/'.$path.'/thumbnail/'.$imageThumbnail);
        $this->createThumbnail($smallthumbnailpath, $width, $height);

        $input['image'] = $pathImage;
        $input['thumbnail'] = $pathThumbnail;

        return $input;
    }

    /**
     * Remove files from the specified path.
     *
     * @param string $path The path of the file to be removed.
     * @return void
     */
    public function removeFiles($path)
    {
        if ($path && \Storage::exists('public/'. $path)) {
            \Storage::delete('public/'. $path);
        }
    }
}
