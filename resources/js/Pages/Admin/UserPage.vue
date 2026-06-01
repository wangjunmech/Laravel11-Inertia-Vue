<script setup>
import { Link, Head, usePage , router, useForm, } from "@inertiajs/vue3";
import Title from "../../Components/Title.vue";
import InputField from "../../Components/InputField.vue";
import PaginationLinks from '../../Components/PaginationLinks.vue';
import SessionMessages from "../../Components/SessionMessages.vue";
import { useAppStore } from '../../stores/app.js'// 直接从Store的 app.js 导入pinia
import { Input } from 'postcss';

const appStore = useAppStore()
const props = defineProps({
    user: Object,
    listings: Object,
    status: String
});

const params = route().params
console.log(params);
const form = useForm({ search:params.search })
const search = () => {
    router.get(route('user.show', {
        search: form.search,
        user:props.user.id
    }))
}

// //点击批准或不批准Listing
const toggleShowDisApprove = (e) => {    
    if (e.target.checked) { 
        alert("sssss0000")
        router.get(route('user.show', {
            search: params.search,
            user: props.user.id,
            disapproved:true
        }))       
    } else {
        // router.get(route('admin.index'), {
        //     search: params.search,
        //     user_role: null
        // })
        //前面的写法有小问题，根据复先框是否选择在地址栏中显示参数=值或参数=空，注意优化写法如下：要把对象放到route的参数中这样给ziggy处理这样当没有参数值时地址样不显示参数出来更简洁，仔细观察括号的位置。
        router.get(route('user.show', {
            search: params.search,
            user: props.user.id,
            disapproved:null
        }))  
    }
}

const page = usePage();
// //点击批准或不批准Listing
const toggleApprove = (listing) => {
    // alert(listing.id);
    let msg = listing.approved
        ? "Disapprove this listing?"
        : "Approve this listing?";
    if (confirm(msg)) {            
        router.put(route("admin.approveListing",listing.id))
    }
    appStore.setShowFlag(true);//由于SessionMessage组件中使用了Pinia全局状态控制来增加了点击消息显示标签时自动隐藏，这里每次操作后都要把全局属性值改为true才能显示消息框
 }
</script>

<template>
<div>
<!-- **********Show UserPage -->
    <Head :title="`-${user.name}'s Listings`"></Head>
    <div >
      <SessionMessages 
      :status="status" 
      v-if="appStore.showFlag"
    />
    </div>
<!-- Heading -->
    <div class="mb-6">
        <Title>{{ `${user.name}'s Listings` }}</Title>
        <div class="flex items-end justify-between">
            <div>
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
                    <Link v-if="params.search"            
                        class="text-link bg-blue-300 rounded-full p-1 border-spacing-1 m-1 no-underline justify-items-start hover:bg-red-200"
                        :href="route('user.show',{...params,search:null,page:null,user:user.id })"
                        >
                            search={{ params.search }}  
                            <i class="fa-solid fa-xmark text-red-500"></i>   
                    </Link> 
                </div>

            </div>
            <div>
                <!-- Toggle Approved listing button -->
                <div  name="Show suspended User checkbox" class="flex items-center gap-1 text-xs hover:bg-slate-300 dark:hover:bg-slate-800 px-5 py-1 rounded-full">
                    <input
                    @input="toggleShowDisApprove"
                    :checked="params.disapproved"
                        type="checkbox"
                        id="toggleShowDisApprove"
                        class="rounded-md border-1 outline-0 text-indigo-500 ring-indigo-500 border-slate-700 cursor-pointer ml-15"/>
                    <label
                        for="toggleShowDisApprove"
                        class="block text-sm font-medium text-slate-700 dark:text-slate-300 cursor-pointer"
                    >
                        Toggle Approved Listings
                    </label>                
                </div>
            </div>
        </div>

    </div>
<!-- table      -->
    <table>
        <thead>
            <tr class="bg-slate-600 text-slate-300 uppercase text-xs text-left">
                <th class="w-4/6 p-3">Title</th>
                <th class="w-2/6 p-3 text-center">Approved</th>
                <th class="w-1/6 p-3 text-right">View</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-slate-300 divide-dashed">
            <tr v-for="listing in listings.data" :key="listing.id">
                <td>{{ listing.title }}</td>
                <td class="py-5 px-3 text-2xl text-center">
                    <button
                    @click="toggleApprove(listing)"
                    >
                        <i :class="`fa-solid fa-${
                            listing.approved
                            ? 'circle-check text-green-400'
                            : 'circle-xmark text-red-500'
                            }`"></i>

                    </button>
                </td>
                <td class="w-1/6 py-5 px-3 text-right">
                    <Link
                    :href="route('listing.show',listing.id)"
                    class="fa-solid fa-up-right-from-square text-indigo-400"
                    >
                </Link>
                </td>

            </tr>

        </tbody>

    </table>
              <!-- pagination link -->
          <div>
            <PaginationLinks :paginator="listings" />
          </div>

</div>
</template>