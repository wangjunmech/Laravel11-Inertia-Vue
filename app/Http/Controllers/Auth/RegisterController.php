<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class RegisterController extends Controller
{
    public function create()
    {
        // echo 'RegisterController create method called';
        // exit();

        return Inertia::render('Auth/Register');
    }

    public function store(Request $request)
    {
        // dd($request);//打印整个请求对象
        // 使用$request->validate方法进行表单验证
        $credentials = $request->validate([
            'name' => 'required|min:5|max:255',
            'email' => 'required|lowercase|email|max:255',
            'password' => 'required|confirmed|min:3',
        ]);

        // dd($request->all()); // 打印全部提交数据的数组
        $user = User::create($credentials);

        // send verification email
        event(new Registered($user));

        Auth::login($user);

        return redirect()->route('home');
    }
}
