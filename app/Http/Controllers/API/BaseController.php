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
}
