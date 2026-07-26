<script setup>
import Card from "../Components/Card.vue";
import PaginationLinks from "../Components/PaginationLinks.vue";
import InputField from "../Components/InputField.vue";
import { Head, useForm, router, Link, usePage } from "@inertiajs/vue3";
// console.log(route().params);//user_id: "XXX"
const params = route().params;//user_id: "XXX"
const props = defineProps({
    listings: Object,

});
const form = useForm({
    search:props.searchTerm || "",
});
const search = () => {
    router.get(route("home"), {
        search: form.search,
        user_id: params.user_id,
        tag: params.tag
    });

};

// //添加搜索高亮功能
const page = usePage()
</script>

<template>
    <div class="">
    <Head title="Latest Listings" />
    <!-- {{ console.log(listings) }} -->
    <!-- {{ console.log(params) }} -->
    <div class="flex items-center justify-between mb-4">
        <div>
            <span  v-if="params && Object.keys(params).length > 0" class="text-gray-600 dark:text-gray-400">
                Filters:
            </span>
            <Link v-if="params.tag"
                class="text-link bg-blue-300 rounded-full p-1 border-spacing-2 m-1 no-underline"
                :href="route('home', { ...params, tag: null })"   
            >
                tag={{ params.tag }}
<span class="bg-red-600 text-white m-1 rounded-full w-5 h-5 inline-flex items-center justify-center">X</span>       </Link>
            <Link v-if="params.user_id"
                class="text-link bg-blue-300 rounded-full p-1 border-spacing-1 m-1 no-underline"
                :href="route('home',{...params,user_id:null })"
            >
                user_id={{ params.user_id }}
<span class="bg-red-600 text-white m-1 rounded-full w-5 h-5 inline-flex items-center justify-center">X</span>       
            </Link>
            <Link v-if="params.search"            
                class="text-link bg-blue-300 rounded-full p-1 border-spacing-1 m-1 no-underline    "
                :href="route('home',{...params,search:null,page:null })"
            >
                search={{ params.search }}
<span class="bg-red-600 text-white m-1 rounded-full w-5 h-5 inline-flex items-center justify-center">X</span>       
            </Link>            
        </div>
        <div class="w-1/4">
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
    </div>
    <div v-if="Object.keys(listings.data).length">
        <div class="grid grid-cols-3 gap-4">
            <Card :listing="listing" v-for="listing in listings.data" :key="listing.id" />  
        </div>
        <div class="mt-6 flex justify-center">
        </div>
    </div>
    <!-- <div v-else class="text-center text-slate-600 dark:text-slate-400">
        No listings found.
    </div> -->
    <div v-else class="text-center text-slate-600 dark:text-slate-400"></div>
    <div class="flex justify-evenly">
        <PaginationLinks :paginator="listings" :show-res-num="false" />
    </div>
    </div>
</template>
