<script setup>
// 单页抓取测试http://localhost:8000/crawler/create
import { reactive, ref } from 'vue'
import { Head } from '@inertiajs/vue3'
import axios from 'axios'
import FieldRow from '@/Components/Crawler/FieldRow.vue'

// 如果你的项目有统一布局（如 AuthenticatedLayout），把下面模板最外层包进去即可

const newField = () => ({ name: '', selector: '', type: 'text', attr: '', multiple: false })

const form = reactive({
    url: '',
    url_pattern: '',
    fields: [newField()],
})

const loading = ref(false)
const result = ref(null)
const errors = ref([])

const addField = () => form.fields.push(newField())
const removeField = (i) => form.fields.splice(i, 1)

async function runPreview() {
    loading.value = true
    result.value = null
    errors.value = []

    try {
        const { data } = await axios.post('/crawler/preview', {
            url: form.url,
            url_pattern: form.url_pattern || null,
            fields: form.fields.map((f) => ({ ...f, attr: f.type === 'attr' ? f.attr : null })),
        })
        result.value = data
    } catch (e) {
        const body = e.response?.data
        errors.value = body?.errors ? Object.values(body.errors).flat() : [body?.message ?? '请求失败']
    } finally {
        loading.value = false
    }
}

const input = 'w-full rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500'
</script>

<template>
    <Head title="单页抓取测试" />

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <h1 class="text-xl font-semibold text-gray-900">单页抓取测试</h1>

        <section class="space-y-4 rounded-lg border border-gray-200 bg-white p-5">
            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">测试网址</label>
                <input v-model="form.url" :class="input" placeholder="https://example.com/article/1" />
            </div>

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">URL 匹配正则（可选）</label>
                <input v-model="form.url_pattern" :class="input" placeholder="^https://example\.com/article/\d+" />
                <p class="mt-1 text-xs text-gray-500">填写后会检查上面的网址是否符合规则，不用写 / 分隔符。</p>
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <label class="text-sm font-medium text-gray-700">抓取字段</label>
                    <button type="button" class="text-sm text-indigo-600 hover:underline" @click="addField">
                        添加字段
                    </button>
                </div>
                <FieldRow
                    v-for="(f, i) in form.fields"
                    :key="i"
                    :field="f"
                    :can-remove="form.fields.length > 1"
                    @remove="removeField(i)"
                />
            </div>

            <div>
                <button
                    type="button"
                    class="rounded-md bg-indigo-600 px-4 py-2 text-sm font-medium text-white hover:bg-indigo-500 disabled:opacity-50"
                    :disabled="loading || !form.url"
                    @click="runPreview"
                >
                    {{ loading ? '抓取中…' : '测试这一页' }}
                </button>
            </div>
        </section>

        <div v-if="errors.length" class="rounded-md border border-red-200 bg-red-50 p-4 text-sm text-red-700">
            <p v-for="(m, i) in errors" :key="i">{{ m }}</p>
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
