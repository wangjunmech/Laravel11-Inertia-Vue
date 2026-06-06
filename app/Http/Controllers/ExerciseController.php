<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;

class ExerciseController extends Controller
{
    //
    function index(){
        // dd('点击exercise按钮到了exerciseController的index方法显示列表页面');
        // return inertia::render('Exercise/dragDarkLight');
        return Inertia::render('exercise/list');
    }
    //
    function dragDarkLight(){
        // dd('dragDarkLight是第一个练习页面');
        return Inertia::render('exercise/dragDarkLight');
    }

    // 🔥 所有页面都走这一个方法！
    public function showPage($pageName)
    {
        // 自动加载视图：resources/views/exercise/xxx.blade.php
        // dd("showPage方法");
        return inertia::render("exercise/{$pageName}");
    }

}
