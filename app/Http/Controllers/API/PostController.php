<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Post\StoreRequest;
use App\Http\Resources\Post\PostResource;
use App\Models\Image;
use App\Models\Post;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;
use Intervention\Image\Drivers\Gd\Driver;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $post = Post::latest()->first();
        return new PostResource($post);
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreRequest $request)
    {
        $data = $request->validated();
        $images = $data['images'];
        unset($data['images']);
        $post = Post::firstOrCreate($data);

        foreach ($images as $image) {
            $name = md5(Carbon::now() . '_' . $image->getClientOriginalName()) . '.' . $image->getClientOriginalExtension();
            $filePath = Storage::disk('public')->putFileAs('/images', $image, $name);
            $previewName = 'prev_' . $name;

            Image::create([
                'path' => $filePath,
                'url' => url('/storage/' . $filePath),
                'post_id' => $post->id,
                'preview_url' => url('/storage/images/' . $previewName)
            ]);

            $img = new ImageManager(new Driver());
            $img->read($image)
                ->resize(100, 100)
                ->save(storage_path('app/public/images/') . $previewName);
        }

        return response()->json(['message' => 'success']);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }

    public function getLastPost()
    {
        $post = Post::latest()->first();
        return new PostResource($post);
    }
}
