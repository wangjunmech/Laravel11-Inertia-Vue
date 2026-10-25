<script setup>
import { computed, ref } from 'vue'
import { Head, Link, useForm } from '@inertiajs/vue3'
import axios from 'axios'
import FieldRow from '@/Components/Crawler/FieldRow.vue'

// 如果你的项目有统一布局（如 AuthenticatedLayout），把下面模板最外层包进去即可

const newField = () => ({ name: '', selector: '', type: 'text', attr: '', multiple: false })

//弹窗显示控制
const showModal = ref(false)
const handleOk = () => {
  console.log('确认')
  showModal.value = false
}

const props = defineProps({ prefill: { type: Object, default: null } })

// 从已有任务"修改并创建新任务"进来时，用原任务的设置初始化表单
const p = props.prefill
const form = useForm({
    name: p?.name ?? '',
    start_urls_text: p?.start_urls?.join('\n') ?? '',
    url_pattern: p?.url_pattern ?? '',
    follow_pattern: p?.follow_pattern ?? '',
    fields: p?.fields?.length ? p.fields.map((f) => ({ ...f, attr: f.attr ?? '' })) : [newField()],
    max_pages: p?.max_pages ?? 100,
    max_depth: p?.max_depth ?? 2,
    delay_ms: p?.delay_ms ?? 500,
    concurrency: p?.concurrency ?? 1,
    respect_robots: p?.respect_robots ?? true,
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
    <Head :title="prefill ? '修改并创建新任务' : '新建抓取任务'" />
    <div v-if="showModal" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[999]" @click.self="showModal = false">
  <!-- 弹窗主体：宽600px，高520px，flex纵向排布，用来做固定头尾+滚动内容 -->
  <div class="bg-white w-4/5 h-[520px] rounded-lg flex flex-col p-4">
    <!-- 头部 -->
    <div class="flex justify-between items-center mb-3">
      <h3 class="text-lg font-medium">抓取任务填写说明</h3>
    </div>
    <div>选择器示例，抓取表格的tobdy中的第一个tr下的第2个td的写法:<span class="bg-red-100 text-red-600 rounded-md px-2">tbody tr:nth-child(1) td:nth-child(2)</span></div>

    <!-- 内容区：flex-1占剩余高度，overflow-y-auto 超出自动出滚动条 -->
    <div class="flex-1 overflow-y-auto">
        <div class="bg-yellow-100">
            要抓的是 forum-48-1.html、forum-48-2.html…这一整个系列页面本身，所以这些页面既是"要提取内容的目标页"，又是"要跟进的翻页链接"。
            <table class="border border-blue-300 px-3 py-2 text-left">
                <thead>
                    <tr>
                        <th class="border border-blue-300 px-3 py-2 text-left">输入项</th>
                        <th class="border border-blue-300 px-3 py-2 text-left">填写内容（域名换成你的站点，下面以 www.mjzl.cn 为例）</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">起始网址</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">https://www.mjzl.cn/forum-48-1.html</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">URL 匹配正则</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">^https?://www\.mjzl\.cn/forum-48-\d+\.html$</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">跟进链接规则</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">填一样的：^https?://www\.mjzl\.cn/forum-48-\d+\.html$</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">最大页数</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">设成不小于总页数，它只是上限</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">最大深度</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">最大深度上限是 5，页数很多时可能走不完。如果任务结束时"已提取"数量少于总页数，就是这个原因</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">并发数</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">2 或 3，最大为5</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">抓取字段</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">按需填写</td>
                    </tr>
                </tbody>
            </table>
        </div>
        <div class="bg-red-100 mt-4">
            要提取内容的目标页是forum-48-1.html、forum-48-2.html…这些页面里面的类名为class="s xst"的链接。
            <table class="border border-blue-300 px-3 py-2 text-left">
                <thead>
                    <tr>
                        <th class="border border-blue-300 px-3 py-2 text-left">输入项</th>
                        <th class="border border-blue-300 px-3 py-2 text-left">填写内容（域名换成你的站点，下面以 www.mjzl.cn 为例）</th>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">起始网址</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">https://www.mjzl.cn/forum-48-1.html</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">URL 匹配正则</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">^https?://www\.mjzl\.cn/thread-\d+-1-1\.html$</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">跟进链接规则</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">^https?://www\.mjzl\.cn/forum-48-\d+\.html$</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">最大页数</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">设成不小于总页数，它只是上限</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">最大深度</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">最大深度上限是 5，页数很多时可能走不完。如果任务结束时"已提取"数量少于总页数，就是这个原因</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">并发数</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">2 或 3，最大为5</td>
                    </tr>
                    <tr>
                        <td class="border border-blue-300 px-3 py-2 text-left">抓取字段</td>
                        <td class="border border-blue-300 px-3 py-2 text-left">选择器按帖子页的结构写，不是列表页</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    <!-- 底部按钮 -->
    <div class="mt-4 flex justify-end gap-2">
      <button @click="showModal = false" class="px-3 py-1 border rounded">取消</button>
      <button @click="handleOk" class="px-3 py-1 bg-blue-500 text-white rounded">确定</button>
    </div>
  </div>
</div>

    <div class="mx-auto max-w-4xl space-y-6 p-6">
        <div class="flex items-center justify-between">
            <h1 class="text-xl font-semibold text-gray-900">{{ prefill ? '修改并创建新任务' : '新建抓取任务' }}</h1>
            <button class="btn bg-red-200 p-2 rounded-full" @click="showModal = true">任务填写说明</button>
            <Link href="/crawler" class="text-sm text-indigo-600 hover:underline">返回任务列表</Link>
        </div>

        <p v-if="prefill" class="rounded-md bg-indigo-50 p-3 text-sm text-indigo-800">
            已带入原任务的设置。修改后点「创建并开始抓取」会生成一个新任务，原任务和它的结果不会受影响。
        </p>

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

            <div>
                <label class="mb-1 block text-sm font-medium text-gray-700">跟进链接规则（可选，强烈建议填写）</label>
                <input v-model="form.follow_pattern" :class="input" placeholder="^https://example\.com/forum-10-\d+\.html" />
                <p class="mt-1 text-xs text-gray-500">
                    填写后，只有匹配「URL 匹配正则」或这个规则的链接才会被请求，其余链接直接丢弃，速度会快很多。
                    例如从版块列表页出发，这里填列表翻页的网址规则。留空则会跟进所有同域名链接（很慢）。
                </p>
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
            <div class="grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
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
                <div>
                    <label class="mb-1 block text-sm font-medium text-gray-700">并发数</label>
                    <select v-model="form.concurrency" :class="input">
                        <option :value="1">1（逐页抓取）</option>
                        <option :value="2">2</option>
                        <option :value="3">3</option>
                        <option :value="5">5</option>
                    </select>
                    <p class="mt-1 text-xs text-gray-500">同时请求的页面数，越大越快，对站点的压力也越大</p>
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
