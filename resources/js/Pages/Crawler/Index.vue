<script setup>
import { computed, onMounted, onUnmounted } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import StatusBadge from '@/Components/Crawler/StatusBadge.vue'

const props = defineProps({ tasks: { type: Object, required: true } })

const hasActive = computed(() =>
    props.tasks.data.some((t) => ['pending', 'running'].includes(t.status)),
)

// 有进行中的任务时，每 3 秒刷新一次列表
let timer = null
onMounted(() => {
    timer = setInterval(() => {
        if (hasActive.value) router.reload({ only: ['tasks'], preserveScroll: true })
    }, 3000)
})
onUnmounted(() => clearInterval(timer))

function remove(t) {
    if (!confirm(`确定删除任务「${t.name}」及其结果文件吗？`)) return
    router.delete(`/crawler/${t.id}`, { preserveScroll: true })
}
</script>

<template>
    <Head title="抓取任务" />

    <div class="mx-auto max-w-5xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">抓取任务</h1>
            <Link
                href="/crawler/create"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500"
            >
                新建任务
            </Link>
        </div>

        <div class="overflow-x-auto rounded-lg border border-gray-200 bg-white">
            <table class="min-w-full divide-y divide-gray-200 text-sm">
                <thead class="bg-gray-50 text-left text-gray-600">
                    <tr>
                        <th class="px-4 py-3 font-medium">名称</th>
                        <th class="px-4 py-3 font-medium">状态</th>
                        <th class="px-4 py-3 font-medium">进度</th>
                        <th class="px-4 py-3 font-medium">创建时间</th>
                        <th class="px-4 py-3 text-right font-medium">操作</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <tr v-for="t in tasks.data" :key="t.id">
                        <td class="px-4 py-3">
                            <Link :href="`/crawler/${t.id}`" class="font-medium text-indigo-600 hover:underline">
                                {{ t.name }}
                            </Link>
                        </td>
                        <td class="px-4 py-3"><StatusBadge :status="t.status" /></td>
                        <td class="px-4 py-3 text-gray-700">
                            {{ t.crawled }} / {{ t.max_pages }}
                            <span v-if="t.failed" class="ml-1 text-xs text-red-600">失败 {{ t.failed }}</span>
                        </td>
                        <td class="px-4 py-3 text-gray-500">{{ t.created_at }}</td>
                        <td class="space-x-3 px-4 py-3 text-right">
                            <a v-if="t.can_download" :href="`/crawler/${t.id}/download`" class="text-indigo-600 hover:underline">
                                下载
                            </a>
                            <button
                                v-if="['done', 'failed', 'cancelled'].includes(t.status)"
                                type="button"
                                class="text-red-600 hover:underline"
                                @click="remove(t)"
                            >
                                删除
                            </button>
                        </td>
                    </tr>
                    <tr v-if="!tasks.data.length">
                        <td colspan="5" class="px-4 py-10 text-center text-gray-500">
                            还没有任务，点击右上角"新建任务"开始。
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>

        <div v-if="tasks.prev_page_url || tasks.next_page_url" class="flex items-center justify-between text-sm">
            <Link v-if="tasks.prev_page_url" :href="tasks.prev_page_url" class="text-indigo-600 hover:underline">上一页</Link>
            <span v-else />
            <span class="text-gray-500">共 {{ tasks.total }} 个任务</span>
            <Link v-if="tasks.next_page_url" :href="tasks.next_page_url" class="text-indigo-600 hover:underline">下一页</Link>
            <span v-else />
        </div>
    </div>
</template>
