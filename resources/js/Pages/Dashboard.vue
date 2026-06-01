<script setup>
import { Head, usePage ,router} from '@inertiajs/vue3'
import Title from '../Components/Title.vue';
import PaginationLinks from '../Components/PaginationLinks.vue';
import SessionMessages from "../Components/SessionMessages.vue";
// 组件里改成这样导入pinia
import { useAppStore } from '../stores/app.js'
const appStore = useAppStore()
defineProps({
    listings: Object,
    status: String
});

const page = usePage();
// console.log(page.props.flash.status);
// // const status = page.props.flash.status;

const deleteListing = (id) => {
  if (confirm("Are you sure to delete?")) {
    //跳转路由到Listing控制器的destory方法进行删除处理
    router.delete(route('listing.destroy', id));
  };
};
</script>

<template>
  <Head title="Listing Dashboard"></Head>
  <SessionMessages :status="status" v-if="appStore.showFlag"/>    
    <div v-if="listings">
      <!-- 再判断对象是否为空，不为空再执行遍历 -->
      <div v-if="Object.keys(listings.data).length">
        <!-- Heading -->
         <div class="flex items-center justify-between">
          <Title>Your latest listings</Title>
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
                            <th class="w-3/4 p-3 text-left">Listing Title</th>

                            <th class="w-1/5 py-3 pr-3 text-right">View</th>
                            <th class="w-1/5 py-3 pr-3 text-right">Edit</th>
                            <th class="w-1/5 py-3 pr-3 text-right">Delete</th>
                        </tr>
                    </thead> 
                    <tbody>
                        <tr
                            v-for="listing in listings.data"
                            :key="listing.id"
                            class="border-b border-slate-200 hover:bg-slate-100 dark:bg-slate-800 dark:hover:bg-slate-600 dark:border-slate-600"
                        >
                          <td class="w-3/4 p-3 text-left">
                                <div class="flex items-center gap-2">
                                    <img
                                        :src="
                                            listing.image
                                                ? `/storage/${listing.image}`
                                                : '/storage/images/listing/default.png'
                                        "
                                        alt=""
                                        class="h-6 w-6 rounded-full object-cover object-center"
                                    />
                                    <h4 class="font-bold">
                                        {{ listing.title }}
                                        <i
                                            :class="`fa-solid fa-${
                                                listing.approved
                                                    ? 'circle-check text-green-500'
                                                    : 'circle-xmark text-red-500'
                                            }`"
                                        ></i>
                                    </h4>
                                </div>
                          </td>
                            <td
                                class="w-1/4 py-3 pr-3 text-right text-indigo-500"
                            >
                                <Link
                                    v-if="listing.approved"
                                    :href="route('listing.show', listing.id)"
                                    >View</Link
                                >
                            </td>

                            <td
                                class="w-1/5 py-3 pr-3 text-right text-indigo-500"
                            >
                                <Link :href="route('listing.edit', listing.id)"
                                    >Edit</Link
                                >
                            </td>

                            <td class="w-1/5 py-3 pr-3 text-right text-red-500">
                                <button type="button" @click="deleteListing(listing.id)">
                                    Delete
                                </button>
                            </td>
                        </tr>
                    </tbody>
            </table>
          </div>
          <!-- pagination link -->
          <div>
            <PaginationLinks :paginator="listings" />
          </div>

      </div>
      <div v-else>
        You have no listings!!
      </div>   
    </div>
    <div v-else>
      Suspended user can not see the listings.
      Due to violation of our terms you account has been suspended.
      Please contact us at <span class="text-link">email@admin.com</span>
    </div>
  <!-- 从 flash.status 读取 -->


</template>