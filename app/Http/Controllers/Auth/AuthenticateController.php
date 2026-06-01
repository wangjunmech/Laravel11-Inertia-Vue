<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;

class AuthenticateController extends Controller
{
    // 显示登录页面
    public function create()
    {
        return Inertia::render('Auth/Login',[
            //重置密码成功后会重定向回登录页面，并且在session中存储一个status消息，告诉用户重置密码成功了，这个status消息会在登录页面显示出来
            'status' => session('status')    
        ]);
    }

    // 处理登录请求
    public function store(Request $request)
    {
        $credentials = $request->validate([
            'email' => 'required|email|max:255',
            'password' => 'required|min:3',
        ]);

        // dd($request->all()); // 打印全部提交数据的数组
        // if (!auth()->attempt($credentials, $request->remember)) {
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            return redirect()->intended('dashboard');
        }

        return back()->withErrors([
            'email' => 'The provided credentials do not match our XXX records.',
        ])->onlyInput('email');
        // send verification email

    }

    // 退出登录或登出销毁session
    public function destroy(Request $request)
    {
        // dd('logout');
        // dd($request);
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}
