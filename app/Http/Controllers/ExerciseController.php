<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;
use App\Models\Listing;
use Illuminate\Support\Facades\Storage;
use Illuminate\Database\Eloquent\Builder;

class ExerciseController extends Controller
{
    //
    function index(Request $request){
        // dd('点击exercise按钮到了exerciseController的index方法显示列表页面');
        // return inertia::render('Exercise/dragDarkLight');
        // $listings = Listing::whereHas('user', function (Builder $query) {
        //     $query->where('role', '!=', 'suspended');
        // });
        // dd($request);
        $listings = Listing::whereHas('user', function (Builder $query) {
            $query->where('role', '!=', 'suspended');
        });
        return Inertia::render('exercise/list');
    }
    //
    function dragDarkLight(){
        // dd('dragDarkLight是第一个练习页面');
        return Inertia::render('exercise/dragDarkLight');
    }

    // 🔥 所有页面都走这一个方法！
    public function showPage($pageName,Request $request)
    {
        // 自动加载视图：resources/views/exercise/xxx.blade.php
        // dd("showPage方法");
        $listings = Listing::find(85);
        // dd($listings);
        return inertia::render("exercise/{$pageName}", ['listings' => $listings]);
    }

    public function storeImages(Request $request)
    {
        dd($request->all());
        dd("storeImages method called");
        $request->validate([
            'images' => 'required|array',
            'images.*' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $uploadedImages = [];
        foreach ($request->file('images') as $image) {
            $path = $image->store('public/images/exercises');
            $uploadedImages[] = $path;
        }

        return response()->json(['message' => 'Images uploaded successfully', 'paths' => $uploadedImages]);
    }
    public function showImage(Request $request)
    {
 

    }

}
