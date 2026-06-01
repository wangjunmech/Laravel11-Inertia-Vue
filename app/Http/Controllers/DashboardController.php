<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Inertia\Inertia;


class DashboardController extends Controller
{
    public function index(Request $request)
    {
        // dd($request->user());
        // $listings = $request->user()->listings()->get();//获取所有listing数据
        // $listings = $request->user()->listings()->latest()->paginate(); //获取latest排序的参数再使用分页方法
        // 下面是判断用户的role是否为suspended,根据条件决定是否获取参数然后再分页
        $listings =
            $request->user()->role !== 'suspended' ?
            $request->user()->listings()->latest()->paginate(5) :
            null;

        // dd($listings);
        return Inertia::render('Dashboard',[
            'listings' =>$listings, //把listings参数传递到前台
            'status'=>session('status')
        ]);
    }
}
