<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import FieldRow from '@/Components/Crawler/FieldRow.vue'

// 如果你的项目有统一布局（如 AuthenticatedLayout），把下面模板最外层包进去即可

const newField = () => ({ name: '', selector: '', type: 'text', attr: '', multiple: false })

const form = useForm({
    name: '',
    start_urls_text: '',
    url_pattern: '',
    fields: [newField()],
    max_pages: 100,
    max_depth: 2,
    delay_ms: 800,
    respect_robots: true,
})

const startUrls = computed(() =>
    form.start_urls_text.split('\n').map((s) => s.trim()).filter(Boolean),
)
const errorList = computed(() => Object.values(form.errors))

const addField = () => form.fields.push(newField())
const removeField = (i) => form.fields.splice(i, 1)

const cleanFields = (fields) =>
    fields.map((f) => ({ ...f, attr: f.type === 'attr' ? f.attr : null }))

function submit() {
    form.transform((data) => {
        const { start_urls_text, ...rest } = data
        return { ...rest, start_urls: startUrls.value, fields: cleanFields(data.fields) }
    }).post('/crawler')
}

// ---- 单页测试（使用第一个起始网址）----
const testing = ref(false)
const result = ref(null)
const previewErrors = ref([])

async function runPreview() {
    testing.value = true
    result.value = null
    previewErrors.value = []

    try {
        const { data } = await axios.post('/crawler/preview', {
            url: startUrls.value[0],
            url_pattern: form.url_pattern || null,
            fields: cleanFields(form.fields),
        })
        result.value = data
    } catch (e) {
        const body = e.response?.data
        previewErrors.value = body?.errors ? Object.values(body.errors).flat() : [body?.message ?? '请求失败']
    } finally {
        testing.value = false
    }
}

const input = 'w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500'
</script>

<template>
    <Head title="新建抓取任务" />

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">新建抓取任务</h1>
            <Link href="/crawler" class="text-sm text-indigo-600 hover:underline">返回任务列表</Link>
        </div>

        <section class="space-y-4 rounded-lg border border-gray-200 bg-white p-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">任务名称</label>
                <input v-model="form.name" :class="input" placeholder="例如：某博客文章" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">起始网址（每行一个）</label>
                <textarea v-model="form.start_urls_text" rows="3" :class="input" placeholder="https://example.com/blog/" />
                <p class="mt-1 text-xs text-gray-500">从这些页面出发，只会跟进同域名下的链接。</p>
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">URL 匹配正则</label>
                <input v-model="form.url_pattern" :class="input" placeholder="^https://example\.com/blog/\d+" />
                <p class="mt-1 text-xs text-gray-500">只有网址符合这个规则的页面才会提取内容；不用写 / 分隔符。</p>
            </div>
        </section>

        <section class="space-y-2 rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex items-center justify-between">
                <label class="text-sm font-medium text-gray-700">抓取字段</label>
                <button type="button" class="text-sm text-indigo-600 hover:underline" @click="addField">添加字段</button>
            </div>
            <FieldRow
                v-for="(f, i) in form.fields"
                :key="i"
                :field="f"
                :can-remove="form.fields.length > 1"
                @remove="removeField(i)"
            />
        </section>

        <section class="rounded-lg border border-gray-200 bg-white p-5">
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-3">
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">最大页数</label>
                    <input v-model.number="form.max_pages" type="number" min="1" max="1000" :class="input" />
                    <p class="mt-1 text-xs text-gray-500">最多提取多少个匹配页面（1–1000）</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">最大深度</label>
                    <input v-model.number="form.max_depth" type="number" min="0" max="5" :class="input" />
                    <p class="mt-1 text-xs text-gray-500">起始页为 0，0 表示只抓起始页</p>
                </div>
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">请求间隔（毫秒）</label>
                    <input v-model.number="form.delay_ms" type="number" min="200" max="10000" step="100" :class="input" />
                    <p class="mt-1 text-xs text-gray-500">最小 200，间隔越大对站点越友好</p>
                </div>
            </div>

            <label class="mt-4 flex items-center gap-2 text-sm text-gray-700">
                <input v-model="form.respect_robots" type="checkbox" class="rounded border-gray-300" />
                遵守目标站点的 robots.txt（推荐）
            </label>
            <p v-if="!form.respect_robots" class="mt-1 text-xs text-amber-700">
                关闭后将不检查站点的抓取规则，请确认你有权限抓取该站点，并自行承担相应责任。
            </p>
        </section>

        <div v-if="errorList.length" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p v-for="(m, i) in errorList" :key="i">{{ m }}</p>
        </div>

        <div class="flex gap-3">
            <button
                type="button"
                class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                :disabled="form.processing"
                @click="submit"
            >
                {{ form.processing ? '提交中…' : '创建并开始抓取' }}
            </button>
            <button
                type="button"
                class="rounded-md border border-gray-300 bg-white px-4 py-2 text-sm font-medium text-gray-700 hover:bg-gray-50 disabled:opacity-50"
                :disabled="testing || !startUrls.length"
                @click="runPreview"
            >
                {{ testing ? '抓取中…' : '测试第一个网址' }}
            </button>
        </div>

        <div v-if="previewErrors.length" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p v-for="(m, i) in previewErrors" :key="i">{{ m }}</p>
        </div>

        <section v-if="result" class="space-y-3 rounded-lg border border-gray-200 bg-white p-5">
            <div class="flex flex-wrap items-center gap-3 text-sm text-gray-600">
                <span>HTTP {{ result.status }}</span>
                <span class="break-all">{{ result.final_url }}</span>
                <span
                    v-if="result.matches_pattern !== null"
                    :class="result.matches_pattern ? 'text-green-700' : 'text-amber-700'"
                >
                    {{ result.matches_pattern ? '匹配 URL 规则' : '不匹配 URL 规则' }}
                </span>
            </div>

            <div v-if="Object.keys(result.field_errors).length" class="rounded-md bg-amber-50 p-3 text-sm text-amber-800">
                <p v-for="(msg, name) in result.field_errors" :key="name">{{ name }}：{{ msg }}</p>
            </div>

            <pre class="max-h-96 overflow-auto rounded-md bg-gray-50 p-3 text-xs text-gray-800">{{ JSON.stringify(result.data, null, 2) }}</pre>
        </section>
    </div>
</template>
