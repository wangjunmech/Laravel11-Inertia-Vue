<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Illuminate\Support\Facades\Gate;
use App\Models\Listing; 



class AdminController extends Controller
{
    public function index(Request $request){
        // dd('AdminController->index方法');
        // dd($request->user()->get());
        // $users = $request->user()->all();
        // dd($request);
        //request后面的参数对应AdminDashboard.vue前面中的user_role，当选择复选框时从地址栏传过来参数
        $users = User::with('listings')
        ->filter(request(['search','user_role']))//要在User模型中的scopeFilter方法中根据参数接收多少对多个参数进行逐一处理
        ->paginate(4)
        ->withQueryString();

        return Inertia::render('Admin/AdminDashboard', [
            'users' => $users,
            'status'=> Session('status')
        ]);
    }

    //
    public function role(Request $request, User $user)
    {
        // dd('管理员选择用户角色提交后写入数据到数据库');
        // dd($request->user()->role);//当前请求的用户的角色 
        // dd($request->role);//当前请求的用户提交上来的角色，需要先使用validate方法验证
        Gate::authorize('modifyRole',$user);
        $request->validate(['role' => 'string|required']);//验证是否合法
        $user->update(['role' => $request->role]); //写入到数据库,注意写入提交成功也成功返回消息，但刷新页面后发现数据没有写入到数据库，检查User Model中的$fillable，要操作的列名必须要包含到fillable数组中。
        //跳转到admin的index页并附件参数status
        return redirect()
            ->route('admin.index')
            ->with(['msgShow' => '1111', 'status'=> "User {$user->name}'s role changed to {$request->role} successfully.!!!", ]);
    }
//在admin用户面板中点击某用户时跳转到该用户的所有Listing列表
    public function show(User $user)
    {
        // dd(request(['search']));
        // dd($user->listings());
        $user_listings = $user
            ->listings()
            ->filterUser(request(['search', 'disapproved']))
            ->latest()
            ->paginate(10)
            ->withQueryString();

        return Inertia::render('Admin/UserPage', [
            'user' => $user,
            'listings' => $user_listings,
            'status' => Session('status')
        ]);
    }

    public function approveListing(Listing $listing){
        // dd($listing->approved);
        Gate::authorize('approve', $listing);
        $listing->update(['approved' => !$listing->approved]);
        $approveMark = $listing->approved ? "Approved" : "DisApproved";
        return back()->with('status',"Listing {$approveMark} successfully!"); //发送status到前台，如果不显示，检查show方法中是否传递了status参数 status'=> Session('status')，并检查前台页中是否在props中接收参数，并且//由于SessionMessage组件中使用了Pinia全局状态控制来增加了点击消息显示标签时自动隐藏，这里每次操作后都要把全局属性值改为true才能显示消息框
    }
    //
    public function details(Request $request, User $user)
        {

        // dd([$request,$user]);
        // dd('AdminController->details方法');
        // dd($request->user()->get());
        // $users = $request->user()->all();
        // $users = User::with('listings')->paginate(3);
        return Inertia::render('Admin/UserDetails', [
            'user' => $user
        ]);
    }
}
