<script setup>
import { computed } from 'vue'
import { Head, useForm, router, Link, usePage, } from "@inertiajs/vue3";
const params = route().params;

const props = defineProps({
    listing: Object,
});
const selectUser = (id) => {
    router.get(route("home"), {
        user_id: id,
        search: params.search,
        tag: params.tag
    });
};

const selectTag = (tag) => {
    router.get(route("home"), {
        user_id: params.user_id,
        search: params.search,
        tag: tag
    });
};

const ctit=computed(() => {
    return props.listing.title.length > 40
        ? props.listing.title.substring(0, 40) + "..."
        : props.listing.title;
});

// //********************* */
    // const searchKey = computed(() => params.search || '')

    // 关键词转义，防止正则特殊符号报错
    const escapeReg = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

    // 高亮核心方法：原文+关键词 → 带高亮标签html
    const highlight = (text, keyword) => {
    if (!keyword) return text
    const reg = new RegExp(`(${escapeReg(keyword)})`, 'gi')
    // 红色背景高亮，和你搜索框底色统一
    return text.replace(reg, `<span class="bg-yellow-200">$1</span>`)
    }
// console.log(params.search);
</script>

<template>     

    <div
        class="bg-white rounded-lg shadow-lg overflow-hidden dark:bg-slate-800 h-full flex flex-col justify-between"
    >
        <div>
            <!-- Image -->
            <Link :href="route('listing.show', listing.id)">
                <img
                    :src="
                        listing.image
                            ? `/storage/${listing.image}`
                            : '/storage/images/listing/default.png'
                    "
                    class="w-full h-10 object-cover object-center bg-slate-300"
                    alt=""
                />
            </Link>

            <!-- Title & user -->
            <div class="p-4">
                <h3 class="font-bold text-xl mb-2">
                    <a :href="route('listing.show', listing.id)">                    
                    <h2 v-html="highlight(ctit, params.search)" class="text-xl font-bold"></h2>
                    </a>
                </h3>

                <p>
                    Listed on
                    {{ new Date(listing.created_at).toLocaleDateString() }} by
                    <button
                        class="text-link"
                        @click="selectUser(listing.user.id)"
                    >
                        {{ listing.user.name }}
                    </button>
                </p>
            </div>
        </div>
        <!-- Tags -->
        <div v-if="listing.tags" class="flex items-center gap-3 px-4 pb-4">
            <div v-for="tag in listing.tags.split(',')" :key="tag">
                <button
                 @click="selectTag(tag)"
                    class="bg-slate-500 text-white px-2 py-px rounded-full hover:bg-slate-700 dark:hover:bg-slate-900"
                >
                    {{ tag }}
                </button>
            </div>
        </div>
    </div>
</template>
