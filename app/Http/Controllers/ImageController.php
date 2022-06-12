<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Validator;

class ImageController extends Controller
{
    public function upload(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'image' => 'required|image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors()->toJson(), 400);
        }

        if($request->hasFile('image')) {
            $image = $request->file('image');
            $name = time() . $image->getClientOriginalName();
            $filePath = 'images/' . $name;
            Storage::disk('s3')->put($filePath, file_get_contents($image), 'public');
            $url = Storage::disk('s3')->url($filePath);
            return response()->json([
                'success' => true,
                'url' => $url
            ], 200);
        } else {
            return response()->json([
                'success' => false,
                'message' => 'No image found'
            ], 400);
        }
    }
}
