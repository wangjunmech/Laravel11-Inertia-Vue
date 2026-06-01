<?php

namespace App\Http\Controllers;

use App\Http\Middleware\NotSuspended;
use App\Models\Listing;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Routing\Controllers\HasMiddleware;
use Illuminate\Routing\Controllers\Middleware;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;

class ListingController extends Controller implements HasMiddleware
{
    /**
     * Display a listing of the resource.
     */
    // Add middleware function to apply auth middleware to all methods except index and show, which are publicly accessible
    public static function middleware()
    {
        // return ['auth', 'verified']; // 这种写法会将auth和verified中间件应用到所有方法上，包括index和show方法，这样就无法公开访问这两个方法了
        return [
            new Middleware(
                ['auth','verified', NotSuspended::class],            
                except:['index', 'show']
            )
            // 这种写法会将Middleware(参数1，[])的中间件应用到Middleware([]，参数2)除了index和show方法以外的所有方法上，这样就可以公开访问index和show方法了
        ];
    }

    public function index(Request $request)
    {
        // $listings = Listing::all();
        // dd($request);
        // $listings = Listing::with('user')
        // ->where('title', 'like', '%' . $request->search . '%')// 使用%百分号进行模糊搜索标题，搜索关键词在标题的任意位置都可以匹配到
        // ->latest()// 按照最新的顺序排序，latest()方法会根据模型的created_at字段进行排序，最新的记录会排在前面
        // ->paginate(6)// 每页显示6条数据
        // ->withQueryString()// 保持查询字符串，这样在分页链接中会保留搜索关键词等查询参数，确保点击下一页的链接时搜索条件不会丢失
        // ;

        // return Inertia::render('Home', [
        //     'listings' => $listings,
        // ]);

        $listings = Listing::whereHas('user', function (Builder $query) {
            $query->where('role', '!=', 'suspended');
        })
        ->with('user')
            ->Filteruser(request(['search','user_id','tag']))// 通过Filteruser作用域方法来过滤数据，传入搜索关键词、用户ID和标签等参数
            ->latest()
            ->paginate(6)
            ->withQueryString();

        return Inertia::render('Home', [
            'listings' => $listings,//返回给前端的listings数据是经过过滤和分页处理后的结果，包含了与用户关联的数据
            'searchTerm' => $request->search,// 将搜索关键词也返回给前端，这样前端就可以在搜索输入框中显示当前的搜索关键词，提供更好的用户体验
        ]);

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        // dd('create');
        Gate::authorize('create', Listing::class); //第一个参数view为Policy中要控制的方法对就Policy中的view方法，
        return Inertia::render('Listing/Create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
        // dd('store');
        // dd($request);
        Gate::authorize('create', Listing::class);


        //create表单数据验证使用Request的Validate方法
        $fields = $request->validate([
            'title' => ['required', 'max:255'],
            'desc' => ['required'],
            'tags' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'link' => ['nullable', 'url'],
            'image' => ['nullable', 'file', 'max:3072', 'mimes:jpeg,jpg,png,webp']
        ]);
        //先看看是否有上传图片，如果有就把图片存储到public/images目录下，并将存储路径保存到数据库中
        // <!--保存图片方法1：$request->file->store():::： 这种写法是Laravel中常见的文件上传处理方式，使用了$request对象的hasFile方法来检查是否有上传的文件，如果有，就使用store方法将文件存储到指定的目录中，并返回存储路径。 -->
        // if ($request->hasFile('image')) {
        //     $fields['image'] = $request->file('image')->store('images/listing', 'public');
        // }

        // <!--保存图片方法2：Storage::disk->put()：：： 这种写法是使用了Storage facade来处理文件上传，首先检查是否有上传的文件，如果有，就使用Storage::disk('public')来指定存储磁盘为public，然后调用put方法将文件存储到指定的目录中，并返回存储路径。 -->
        if ($request->hasFile('image')) {
            //保存图片并把存储路径返回给$fileds数组保存到数据库中
            $fields['image'] = Storage::disk('public')->put('images/listing', $request->image);
        }

        //tags标签数据过滤处理
        // $filteredTags = explode(',', $request->tags);
        // $filteredTags = array_map('trim', $filteredTags);//去除空白字符
        // $filteredTags = array_filter($filteredTags); //去除空白数组元素,去除值为空的数组元素
        // $filteredTags = array_unique($filteredTags); //去除重复的数组元素
        // $filteredTags = implode(',', $filteredTags);//把数组转换为字符串，使用逗号分隔每个标签
        // dd($filteredTags);
        //前面的5条代码可以合并写为一条代码，反过来先用implode逆向逐一函数写回去
        $fields['tags'] = implode(',', array_unique(array_filter(array_map('trim', explode(',',  $request->tags)))));
        // dd($fields['tags']);

        //保存表单数据到数据库中，$request->user()获取当前登录的用户对象，listings()是用户模型中定义的与Listing模型的关系方法，create($fields)会在数据库中创建一条新的记录，并将$fields数组中的数据保存到对应的字段中，同时自动关联当前用户的ID到user_id字段中
        $request->user()->listings()->create($fields);
        //跳转到dashboard页面，并携带一个状态消息，告诉用户列表创建成功了
        return redirect()->route('dashboard')->with('status', 'Listing created successfully.');

    }

    /**
     * Display the specified resource.
     */
    public function show(Listing $listing)
    {
        Gate::authorize('view', $listing); //第一个参数view为Policy中要控制的方法对就Policy中的view方法，
        //
        return Inertia::render('Listing/Show', [
            'listing' => $listing,
            'user' => $listing->user,
            'canModify' => Auth::user() ? Auth::user()->can('modify', $listing) : false
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Listing $listing)
    {
        //
        Gate::authorize('modify', $listing);
        return Inertia::render('Listing/Edit', [
            'listing' => $listing,            
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Listing $listing)
    {
        // echo ('update88888888888888888');
        // dd($request);
        
        //修改更新表单数据验证使用Request的Validate方法
        $fields = $request->validate([
            'title' => ['required', 'max:255'],
            'desc' => ['required'],
            'tags' => ['nullable', 'string'],
            'email' => ['nullable', 'email'],
            'link' => ['nullable', 'url'],
            'image' => ['nullable', 'file', 'max:3072', 'mimes:jpeg,jpg,png,webp']
        ]);
        //如果有上传新图片，就先删除旧图片（如果有的话），然后保存新图片并把存储路径返回给$fileds数组保存到数据库中；如果没有上传新图片，就保持原来的图片路径不变
        if ($request->hasFile('image')) {
            //先删除旧图片，如果有的话，避免存储空间被占满
            if ($listing->image) {
                Storage::disk('public')->delete($listing->image);
                //保存图片并把存储路径返回给$fileds数组保存到数据库中
                $fields['image'] = Storage::disk('public')->put('images/listing', $request->image);
                } else {
                    //如果没有上传新图片，就保持原来的图片路径不变
                    $fields['image'] = $listing->image;
                }

            }
        //tags标签数据过滤处理，和store方法一样
        $fields['tags'] = implode(',', array_unique(array_filter(array_map('trim', explode(',',  $request->tags)))));

        //更新数据库中的记录，$listing->update($fields)会将$fields数组中的数据更新到对应的字段中，并保存到数据库中
        // $listing->update($fields);//
        $listing->update([...$fields, 'approved' => false]);//在有数据更新提交处理时加上'approved'参数并设置为假

        //
        return redirect()->route('dashboard')->with('status', 'Listing updated successfully.');
    }
    

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Listing $listing)
    {
        // dd('删除操作'. $listing->id);
        //删除列表记录之前，先检查是否有图片，如果有就删除图片文件，避免存储空间被占满
        if ($listing->image) {
            Storage::disk('public')->delete($listing->image);
        }
        //删除数据库中的记录，$listing->delete()会从数据库中删除对应的记录
        $listing->delete();
        //跳转到dashboard页面，并携带一个状态消息，告诉用户列表删除成功了
        return redirect()->route('dashboard')->with('status', 'Listing deleted successfully.');
    }
}
