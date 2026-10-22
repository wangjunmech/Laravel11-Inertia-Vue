<script setup>
import { computed, onMounted, onUnmounted, watch } from 'vue'
import { Head, Link, router } from '@inertiajs/vue3'
import StatusBadge from '@/Components/Crawler/StatusBadge.vue'

const props = defineProps({
    task: { type: Object, required: true },
    sample: { type: Array, default: () => [] },
})

const active = computed(() => ['pending', 'running', 'cancelling'].includes(props.task.status))

// 进行中每 2 秒只刷新 task（不读结果文件）；结束时再完整刷新一次
let timer = null
const stopPolling = () => {
    if (timer) clearInterval(timer)
    timer = null
}
const startPolling = () => {
    stopPolling()
    timer = setInterval(() => router.reload({ only: ['task'], preserveScroll: true }), 2000)
}

onMounted(() => {
    if (active.value) startPolling()
})
onUnmounted(stopPolling)

watch(active, (now, before) => {
    if (before && !now) {
        stopPolling()
        router.reload({ only: ['task', 'sample'], preserveScroll: true })
    }
})

function cancel() {
    if (!confirm('确定要取消这个任务吗？已抓取的结果会保留。')) return
    router.post(`/crawler/${props.task.id}/cancel`, {}, { preserveScroll: true })
}

const typeLabel = { text: '文本', html: 'HTML', attr: '属性' }
</script>

<template>
    <Head :title="task.name" />

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-3">
                <h1 class="text-xl font-semibold text-gray-900">{{ task.name }}</h1>
                <StatusBadge :status="task.status" />
            </div>
            <Link href="/crawler" class="text-sm text-indigo-600 hover:underline">返回任务列表</Link>
        </div>

        <section class="space-y-3 rounded-lg border border-gray-200 bg-white p-5">
            <div class="h-2 w-full overflow-hidden rounded-full bg-gray-100">
                <div class="h-full bg-indigo-600 transition-all duration-500" :style="{ width: task.progress + '%' }" />
            </div>

            <div class="flex flex-wrap gap-x-6 gap-y-1 text-sm text-gray-700">
                <span>已提取 {{ task.crawled }} / 上限 {{ task.max_pages }}</span>
                <span>已请求 {{ task.fetched }} 页</span>
                <span :class="task.failed ? 'text-red-600' : ''">失败 {{ task.failed }}</span>
                <span v-if="task.started_at">开始：{{ task.started_at }}</span>
                <span v-if="task.finished_at">结束：{{ task.finished_at }}</span>
            </div>

            <p
                v-if="!task.follow_pattern && task.fetched >= 20 && task.crawled * 3 < task.fetched"
                class="text-xs text-amber-700"
            >
                请求的页面里大部分并不是目标页，建议新建任务时填写「跟进链接规则」，只抓需要的页面。
            </p>

            <p v-if="task.status === 'running' && task.current_url" class="break-all text-xs text-gray-500">
                正在处理：{{ task.current_url }}
            </p>

            <p
                v-if="task.error"
                :class="[
                    'rounded-md p-3 text-sm',
                    task.status === 'failed' ? 'bg-red-50 text-red-700' : 'bg-amber-50 text-amber-800',
                ]"
            >
                {{ task.error }}
            </p>

            <p v-if="task.status === 'cancelling'" class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                已发出取消请求，会在当前页面处理完后停止。如果队列 worker 已经停止运行，5 分钟后再点一次「再次取消」即可强制结束。
            </p>

            <div class="flex flex-wrap gap-3 pt-1">
                <button
                    v-if="active"
                    type="button"
                    class="rounded-md border border-red-300 px-3 py-1.5 text-sm text-red-700 hover:bg-red-50"
                    @click="cancel"
                >
                    {{ task.status === 'cancelling' ? '再次取消' : '取消任务' }}
                </button>
                <template v-if="task.can_download">
                    <a
                        :href="`/crawler/${task.id}/download`"
                        class="rounded-md bg-indigo-600 px-3 py-1.5 text-sm font-medium text-white hover:bg-indigo-500"
                    >
                        下载 JSON
                    </a>
                    <a
                        :href="`/crawler/${task.id}/download?format=jsonl`"
                        class="rounded-md border border-gray-300 px-3 py-1.5 text-sm text-gray-700 hover:bg-gray-50"
                    >
                        下载 JSONL
                    </a>
                </template>
            </div>
        </section>

        <section class="space-y-3 rounded-lg border border-gray-200 bg-white p-5 text-sm">
            <h2 class="font-medium text-gray-900">任务设置</h2>
            <dl class="grid grid-cols-1 gap-x-6 gap-y-2 sm:grid-cols-[8rem_1fr]">
                <dt class="text-gray-500">起始网址</dt>
                <dd class="break-all text-gray-800">
                    <p v-for="u in task.start_urls" :key="u">{{ u }}</p>
                </dd>

                <dt class="text-gray-500">URL 正则</dt>
                <dd class="break-all font-mono text-xs text-gray-800">{{ task.url_pattern }}</dd>

                <dt class="text-gray-500">跟进规则</dt>
                <dd class="break-all font-mono text-xs text-gray-800">
                    {{ task.follow_pattern || '未设置（跟进所有同域名链接）' }}
                </dd>

                <dt class="text-gray-500">抓取字段</dt>
                <dd class="space-y-1 text-gray-800">
                    <p v-for="f in task.fields" :key="f.name">
                        <span class="font-medium">{{ f.name }}</span>
                        ← <code class="text-xs">{{ f.selector }}</code>
                        （{{ typeLabel[f.type] }}<template v-if="f.type === 'attr'">：{{ f.attr }}</template
                        ><template v-if="f.multiple">，多个</template>）
                    </p>
                </dd>

                <dt class="text-gray-500">限制</dt>
                <dd class="text-gray-800">
                    最大深度 {{ task.max_depth }}，请求间隔 {{ task.delay_ms }} ms，
                    {{ task.respect_robots ? '遵守 robots.txt' : '未启用 robots.txt 检查' }}
                </dd>
            </dl>
        </section>

        <section v-if="sample.length" class="space-y-2 rounded-lg border border-gray-200 bg-white p-5">
            <h2 class="text-sm font-medium text-gray-900">结果预览（前 {{ sample.length }} 条）</h2>
            <pre class="max-h-[28rem] overflow-auto rounded-md bg-gray-50 p-3 text-xs text-gray-800">{{ JSON.stringify(sample, null, 2) }}</pre>
        </section>
    </div>
</template>
