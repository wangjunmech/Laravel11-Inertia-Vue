<script setup>
import { computed } from 'vue';
import { Link,Head, usePage ,router, useForm } from "@inertiajs/vue3";
import Container from "../../Components/Container.vue";
import SessionMessages from "../../Components/SessionMessages.vue";
import { useAppStore } from '../../stores/app.js'// 直接从Store的 app.js 导入pinia

const appStore = useAppStore()
const props = defineProps({
    user: Object,
    listing: Object,
    canModify:Boolean,
    status: String
});
const deleteListing = () => {
    // console.log('delete listing method called');
    // alert('delete listing method called');
    // return confirm('Are you sure you want to delete this listing?');
    if (confirm('Are you sure you want to delete this listing?')) {
        router.delete(route('listing.destroy', props.listing.id));
    }
};
// // const approvedFlag = $page.props.auth.user.role === 'admin';
// const approvedFlag = computed(() => $page.props.auth.user.role === 'admin');
// // approvedFlag = 0;
// console.log(approvedFlag);
const approveFlag = props.listing.approved; //这样写不会触发更新
// const approvedFlag = 0;

//为了使approve标签动态显示，可以直接在模板中使用props.listing.approved作为值来判断，但为了代码简洁易读，先使用computed把这个值返回给一个变量，在模板中使用变量更简单。
const approvedFlag = computed(() => {
  return props.listing.approved;
});
const page = usePage();
// //点击批准或不批准Listing
const toggleApprove = () => {
    // alert(listing.id);
    let msg = props.listing.approved
        ? "Disapprove this listing?"
        : "Approve this listing?";
    if (confirm(msg)) {     
        // alert(props.listing.id);       
        router.put(route("admin.approveListing", props.listing.id));
    }
    appStore.setShowFlag(true);//由于SessionMessage组件中使用了Pinia全局状态控制来增加了点击消息显示标签时自动隐藏，这里每次操作后都要把全局属性值改为true才能显示消息框
 }
</script>
<template>
<Head title="- Listing Detail" />
    <div >
        <div>{{ listing.approved }}</div>
      <SessionMessages 
      :status="status" 
      v-if="appStore.showFlag"
    />
    </div>
<!-- Admin toggle approve function -->
 <div v-if="$page.props.auth.user.role === 'admin'" 
        class="bg-slate-800 text-white mb-2 p-1 pl-5 rounded-full font-medium flex items-center justify-between">
    <p><i
        class="fa-solid "
        :class="{ 'text-green-400 fa-circle-check': approvedFlag, 'text-red-500 fa-circle-xmark': !approvedFlag }"></i>
        This listing is {{ approvedFlag ? "Approved!" : "Not approved!" }}</p>
    <p>
        <button 
            @click="toggleApprove"
            class="bg-slate-300 text-red-600 rounded-full px-6 hover:bg-lime-300"
            >{{ props.listing.approved ? "DisApprove" : "Approve it" }}</button></p>
 </div>
 
    <Container class="flex gap-4">
        <div class="w-1/4 rounded-md overflow-hidden">
    
            
            <img
                :src="
                    listing.image
                        ? `/storage/${listing.image}`
                        : '/storage/images/listing/default.png'
                "
                class="w-full h-full object-cover object-center"
                alt=""
            />
        </div>
        <div class="w-3/4 p-4 bg-orange-200">
            <!-- Listing info -->
            <div class="mb-6">
                
                <div class="flex items-end justify-between mb-2">
                    <p class="text-slate-400 w-full border-b">Listing detail</p>

                    <!-- Edit buttons -->
                    <div v-if="canModify" class="pl-4 flex items-center gap-4">
                        <Link
                            :href="route('listing.edit', listing.id)"
                            class="bg-green-500 rounded-md text-white px-6 py-2 hover:outline outline-green-500 outline-offset-2"
                        >
                            Edit
                        </Link>
                    </div>

                    <!-- delete buttons -->
                    <div v-if="canModify"  class="pl-4 flex items-center gap-4">
                        <Link
                            :href="route('listing.destroy', listing.id)"
                            @click.prevent="deleteListing"
                            class="bg-red-500 rounded-md text-white px-6 py-2 hover:outline outline-red-500 outline-offset-2"
                        >
                            Delete
                        </Link>
                    </div>
                </div>

                <h3 class="font-bold text-2xl mb-4">{{ listing.title }}</h3>
                <p>{{ listing.desc }}</p>
            </div>
            
            <!-- Contact info -->
            <div class="mb-6">
                <p class="text-slate-400 w-full border-b mb-2">Contact info</p>
                <!-- {{ user }} -->

                <!-- Email -->
                <div v-if="listing.email" class="flex items-center mb-2 gap-2">
                    <i class="fa-solid fa-at"></i>
                    <p>Email:</p>
                    <a :href="`mailto:${listing.email}`" class="text-link">
                        {{ listing.email }}
                    </a>
                </div>

                <!-- Link -->
                <div v-if="listing.link" class="flex items-center mb-2 gap-2">
                    <i class="fa-solid fa-up-right-from-square"></i>
                    <p>External Link:</p>
                    <a :href="listing.link" target="_blank" class="text-link">
                        {{ listing.link }}
                    </a>
                </div>

                <!-- User -->
                <div class="flex items-center mb-2 gap-2">
                    <i class="fa-solid fa-user"></i>
                    <p>Listed by:</p>
                    <Link
                        :href="route('home', { user_id: user.id })"
                        class="text-link"
                    >
                        {{ user.name }}
                    </Link>
                </div>
                <!-- Tags -->
                <div v-if="listing.tags" class="mb-6">
                <p class="text-slate-400 w-full border-b mb-2">Tags</p>

                <div class="flex items-center gap-3">
                    <div v-for="tag in listing.tags.split(',')" :key="tag">
                        <Link
                            :href="route('home', { tag })"
                            class="bg-slate-500 text-white px-2 py-px rounded-full hover:bg-slate-700 dark:hover:bg-slate-900"
                        >
                            {{ tag }}
                        </Link>
                    </div>
                </div>
            </div>
            </div>
        </div>
    </Container>


</template>