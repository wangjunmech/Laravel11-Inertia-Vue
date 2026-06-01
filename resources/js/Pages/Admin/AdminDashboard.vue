<script setup>
import { Head, usePage ,router, useForm, Link} from '@inertiajs/vue3'
import Title from '../../Components/Title.vue';
import RoleSelect from '../../Components/RoleSelect.vue';
import PaginationLinks from '../../Components/PaginationLinks.vue';
import SessionMessages from "../../Components/SessionMessages.vue";
import { useAppStore } from '../../stores/app.js'// 直接从Store的 app.js 导入pinia
import InputField from '../../Components/InputField.vue';
import { Input } from 'postcss';



const appStore = useAppStore()
defineProps({
    users: Object,
    status: String,
});
const params = route().params

const form = useForm({ search:params.search })
const search = () => {
    router.get(route('admin.index', {
        search: form.search,
        user_role:params.user_role
    }))
}

//复选框点击选择或取消suspended用户过滤
const toggleRole = (e) => {    
    if (e.target.checked) { 
        // alert("sssss0000")
        router.get(route('admin.index', {
            search: params.search,
            user_role:'suspended'
        }))       
    } else {
        // router.get(route('admin.index'), {
        //     search: params.search,
        //     user_role: null
        // })
        //前面的写法有小问题，根据复先框是否选择在地址栏中显示参数=值或参数=空，注意优化写法如下：要把对象放到route的参数中这样给ziggy处理这样当没有参数值时地址样不显示参数出来更简洁，仔细观察括号的位置。
        router.get(route('admin.index', {
            search: params.search,
            user_role: null
        }))  
    }
}

const page = usePage();

</script>
<template>
  <Head title="-Admin">User List</Head>
    <div >
      <SessionMessages 
      :status="status" 
      v-if="appStore.showFlag"
    />
    </div>
    <!-- <div>{{params}}</div> -->
    <div>

    </div>
    <div>
      <!-- 再判断对象是否为空，不为空再执行遍历 -->
      <div>
        <div class="flex justify-between  mb-2">
            <div name="Search-Input" class="flex items-center gap-6">
                <form @submit.prevent="search">
                    <InputField
                        type="search"
                        label=""
                        icon="magnifying-glass"
                        placeholder="Search..."
                        v-model="form.search"                        
                    />
                </form>
            </div>
            <div name="Search text button" class="block -ml-1">
                <Link v-if="params.search"            
                    class="text-link bg-blue-300 rounded-full p-1 border-spacing-1 m-1 no-underline justify-items-start hover:bg-red-200"
                    :href="route('admin.index',{...params,search:null,page:null })"
                    >
                        search={{ params.search }}
                        <span class="bg-red-600 text-white m-1 rounded-full w-5 h-5 inline-flex items-center justify-center">X</span>    
                        <i class="fa-solid fa-xmark text-red-500"></i>   
                </Link> 
            </div>
            <div  name="Show suspended User checkbox" class="flex items-center gap-1 text-xs hover:bg-slate-300 dark:hover:bg-slate-800 px-5 py-1 rounded-full">
                <input
                @input="toggleRole"
                :checked="params.user_role"
                    type="checkbox"
                    id="toggleRole"
                    class="rounded-md border-1 outline-0 text-indigo-500 ring-indigo-500 border-slate-700 cursor-pointer ml-15"/>
                <label
                    for="toggleRole"
                    class="block text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer"
                >
                    Show suspended users
                </label>
                
            </div>

        </div>
        <!-- Heading -->
         <div class="flex items-center justify-between">
          <Title>User List</Title>
            <div class="flex items-center text-xs gap-4">
            <p>Approved<i class="fa-solid fa-circle-check text-green-400"></i></p>
            <p>Disapproved<i class="fa-solid fa-circle-xmark text-red-500"></i></p>
            </div>
         </div>
        <!-- table -->
          <div>
            <table class="w-full table-fixed border-collapse overflow-hidden rounded-md text-sm ring-1 ring-slate-300 dark:ring-slate-600 bg-white shadow-lg"
                >
                    <thead
                        class="bg-slate-300 text-xs uppercase text-slate-600 dark:text-slate-400 dark:bg-slate-900"
                    >
                        <tr>
                            <th class="w-1/2 p-3 text-left">User</th>
                            <th class="w-1/5 py-3 pr-3 text-left">Role</th>
                            <th class="w-1/5 py-3 pr-3 text-left">Listings</th>
                            <th class="w-1/5 py-3 pr-3 text-left">View User's Listings</th>
                            <th class="w-1/5 py-3 pr-3 text-left">Details</th>
                        </tr>
                    </thead> 
                   <!-- {{ console.log(users.data)}} -->
                    <tbody class="divide-y text-blue-300 divide-slate-300 divide-dashed">
                        <tr
                            v-for="user in users.data"
                            :key="user.id"
                            class="border-b border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-600 dark:border-slate-600"
                        >
                          <td class="w-1/2 p-3 text-left">                                
                                <h3 class="font-bold">
                                    {{ user.name }}
                                </h3>
                                <p class="text-link no-underline">{{user.email }}</p>                                
                          </td>
                            <td
                                class="w-1/4 py-3 pr-3 text-left text-red-300"
                            >
                            <RoleSelect :user="user"/>
  
                            </td>

                            <td
                                class="w-1/5 py-3 pr-3 text-left text-red-300"
                            >
                            <div class="flex">
                                <div class="flex">
                                    <div>{{user.listings.filter(l=>l.approved).length}}                                    </div>
                                    <div><i class="fa-solid fa-circle-check text-green-400"></i>
                                    </div>
                                </div>
                                <div class="flex">
                                    <div>{{user.listings.filter(l=>!l.approved).length}}                                    </div>
                                    <div><i class="fa-solid fa-circle-xmark text-red-600"></i>
                                    </div>
                                </div>
                            </div>

                            </td>
                            <td class="w-1/5 py-3 pr-3 text-right text-red-500 flex">
                                <Link 
                                :href="route('user.show',user.id)"
                                class="fa-solid fa-up-right-from-square px-3 text-indigo-500">
                                    
                                </Link>
                            </td>
                            <td class="w-1/5 py-3 pr-3 text-right text-red-500 ">
                                <Link type="button" 
                                :href="route('admin.details',user.id)"
                                class="bg-red-200 p-2 px-4 rounded-full hover:bg-blue-200">
                                    details-{{ user.id }}
                                </Link>
                            </td>
                        </tr>
                    </tbody>
            </table>
          </div>
          <!-- pagination link -->
          <div>
            <PaginationLinks :paginator="users" />
          </div>

      </div>
  
    </div>


</template>
