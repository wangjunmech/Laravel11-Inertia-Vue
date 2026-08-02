<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\Session;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // 全局把session闪存status注入所有页面props,解决BOM编辑保存后session()->flash（）不能把status返回到前台的问题
        Inertia::share([
            // 先用 get 测试能拿到数据，后续再优化一次性销毁
            'status' => fn() => Session::get('status'),
        ]);
    }
}
