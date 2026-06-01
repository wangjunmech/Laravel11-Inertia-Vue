<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class Admin
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // 第1种方法：dd($request->user()->name);//要想这里测试打印当前登录用户名要先把本类Admin:class先加入到路由中对应的middleware参数中进行控制
        if ($request->user()->name !== "admin"){
            return redirect()->route('home');
            //如果登录的用户名不为admin则跳转到home页面，到地址栏中手动访问/admin测试一下用其它用户登录
        }
        //第2种方法：也可以在User模型中追加isAdmin方法，在这里判断条件中使用方法来进行验证，如果不是管理员于再跳转到home页
        if (!$request->user()->isAdmin()) {
            return redirect()->route('home');
            //如果登录的用户名不为admin则跳转到home页面，到地址栏中手动访问/admin测试一下用其它用户登录
        }
        return $next($request);
    }
}
