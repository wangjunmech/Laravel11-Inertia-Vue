<template>
  <div class="tree-container ">
    <div class="tree-header mb-6">
      <h2 class="text-2xl font-bold">项目编号生成器</h2>
    </div>
    <div>规则数位说明：       
      <span class="r1" :class="{ active: hoverClass === 'r1' }">客户编号</span>+ 
      <span class="r2" :class="{ active: hoverClass === 'r2' }">业务员编号</span>+ 
      <span class="r3" :class="{ active: hoverClass === 'r3' }">项目序号</span>+ 
      <span class="r4" :class="{ active: hoverClass === 'r4' }">项目交付地代码</span>+ 
    </div>
    <div>
      编码示例，光标解释：
      <span 
        class="r1"
        @mouseenter="hoverClass = 'r1'"
        @mouseleave="hoverClass = ''"
        :class="{ active: hoverClass === 'r1' }"
      >GM102</span>-
      <span 
        class="r2"
        @mouseenter="hoverClass = 'r2'"
        @mouseleave="hoverClass = ''"
        :class="{ active: hoverClass === 'r2' }"
      >16</span>-
      <span 
        class="r3"
        @mouseenter="hoverClass = 'r3'"
        @mouseleave="hoverClass = ''"
        :class="{ active: hoverClass === 'r3' }"
      >1</span>-
      <span 
        class="r4"
        @mouseenter="hoverClass = 'r4'"
        @mouseleave="hoverClass = ''"
        :class="{ active: hoverClass === 'r4' }"
      >ad</span>
    </div>

    <div>
      规则数位说明：
      <span class="r1" :class="{ active: hoverClass === 'r1' }">客户编号</span>+ 
      <span class="r2" :class="{ active: hoverClass === 'r2' }">业务员编号</span>+ 
      <span class="r3" :class="{ active: hoverClass === 'r3' }">项目序号</span>+ 
      <span class="r4" :class="{ active: hoverClass === 'r4' }">项目交付地代码</span>+ 
    </div>
    <!-- 表单区域 -->
    <div class="space-y-4">
      <div class="flex flex-wrap gap-4 items-center">
        <div class="flex flex-col gap-1">
          <label>下拉选择：</label>
          <select
            v-model="selectedMainMaterialCode"
            class="border p-2 rounded min-w-56"
            @change="handleMainMaterialChange"
          >
            <option value="">请选择产品材料大类</option>
            <option
              v-for="m in materialCategory"
              :key="m.mcid"
              :value="m.mcode"
            >
              {{ m.mcid }} {{ m.mcateCn }} ......【{{ m.mdesc }}】
            </option>
            <option value="_add_category_main">+ 添加分类</option>
          </select>
        </div>

        <button
          type="button"
          @click="openFloatModal = true"
          class="bg-blue-400 text-white rounded-lg p-2 px-4"
          title="打开粗糙度参考浮动窗口"
        >
          弹出对话框参考
        </button>
      </div>
    </div>

    <!-- 弹窗2：粗糙度参考说明 -->
    <div v-if="openFloatModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="openFloatModal = false">
      <div class="bg-white rounded-lg w-[720px] p-5 shadow-xl relative" @click.stop>
        <button @click="openFloatModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-black text-xl">×</button>
        <h3 class="text-lg font-bold mb-4">参考</h3>
        <div class="text-sm space-y-3 text-gray-700">
          <div class="astro-chart-wrap overflow-x-auto my-6"></div>
        </div>
        <div class="flex justify-end mt-6">
          <button @click="openFloatModal = false" class="px-4 py-2 bg-blue-500 text-white rounded">关闭浮动窗口</button>
        </div>
      </div>
    </div>

    <!-- 添加分类弹窗【修复滚动核心区域】 -->
    <!-- 添加分类弹窗【修复滚动最终版】 -->
<div v-if="openCategoryModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="closeCategoryModal">
  <div class="bg-white rounded-lg w-[900px] max-h-[80vh] shadow-xl relative flex flex-col overflow-hidden" @click.stop>
    <button @click="closeCategoryModal" class="absolute top-3 right-3 text-gray-400 hover:text-black text-xl z-10">×</button>
    <h3 class="text-lg font-bold p-4 border-b shrink-0">弹窗名称</h3>

    <!-- 红色外层：去掉flex高度限制，只做包裹 -->
    <div class="overflow-hidden bg-red-300">
      <!-- 核心改动：给滚动容器设置最大高度，强制溢出出现滚动条 -->
      <div ref="leftListScrollRef" class="p-4 max-h-[60vh] overflow-y-auto">
        <h4 class="font-semibold mb-3">现有一级材料大类</h4>
        <div class="space-y-2 mb-6">
          <div
            v-for="item in materialCategory"
            :key="item.mcid"
            @click="selectedModalMainMcid = item.mcid"
            class="p-2 border rounded cursor-pointer hover:bg-slate-100"
            :class="{ 'bg-blue-100 border-blue-400': selectedModalMainMcid === item.mcid }"
          >
            <div class="font-medium">{{ item.mcateCn }} ({{ item.mcode }})</div>
            <div class="text-xs text-gray-500">{{ item.mdesc }}</div>
          </div>
        </div>

        <!-- 新增一级大类表单 -->
        <div class="border-t pt-4">
          <h4 class="font-semibold mb-3">新增一级材料大类</h4>
          <div class="space-y-2">
            <div class="flex gap-2 items-center">
              <label class="w-20 shrink-0">英文编码:</label>
              <input v-model="newMainForm.mcode" class="border p-1.5 rounded flex-1" placeholder="如 PL、ME" maxlength="2">
            </div>
            <div class="flex gap-2 items-center">
              <label class="w-20 shrink-0">中文名称:</label>
              <input v-model="newMainForm.mcateCn" class="border p-1.5 rounded flex-1" placeholder="塑料">
            </div>
            <div class="flex gap-2 items-center">
              <label class="w-20 shrink-0">英文名称:</label>
              <input v-model="newMainForm.mcateEn" class="border p-1.5 rounded flex-1" placeholder="Plastic">
            </div>
            <div class="flex gap-2 items-center">
              <label class="w-20 shrink-0">描述说明:</label>
              <input v-model="newMainForm.mdesc" class="border p-1.5 rounded flex-1" placeholder="材料备注说明">
            </div>
            <button @click="submitAddMainCategory" class="w-full bg-green-500 text-white py-2 rounded mt-2">
              提交新增一级大类
            </button>
          </div>
        </div>
      </div>
    </div>

    <!-- 底部按钮固定底部 -->
    <div class="p-4 border-t flex justify-end shrink-0">
      <button @click="closeCategoryModal" class="px-6 py-2 border rounded">关闭浮动窗口</button>
    </div>
  </div>
</div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'

// 全局弹窗开关
const openFloatModal = ref(false)
const openCategoryModal = ref(false)
const leftListScrollRef = ref(null)
const openFromType = ref('')
const hoverClass = ref('')
const selectedModalMainMcid = ref('')

// 新增表单
const newMainForm = ref({
  mcode: '',
  mcateCn: '',
  mcateEn: '',
  mdesc: ''
})
const newSubForm = ref({ mcateCn: '', mcateEn: '', msdesc: '' })

// 测试加长数据，打开弹窗内容必然溢出，滚动条立刻出现
const materialCategory = ref([
  {"mcid":"1","mcode":"PL","mcateCn":"塑料","mcateEn":"Plastic","mdesc":"纯塑料制品不含胶袋薄膜等"},
  {"mcid":"2","mcode":"ME","mcateCn":"金属","mcateEn":"Metal","mdesc":"纯金属制品"},
  {"mcid":"3","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"4","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"5","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"6","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"7","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"8","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"9","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"10","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"11","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"12","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"13","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"14","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"15","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"16","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"17","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"18","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"19","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
  {"mcid":"20","mcode":"OT","mcateCn":"其他","mcateEn":"Other","mdesc":"其他材料"},
])

const selectedMainMaterialCode = ref('')

// 下拉切换
const handleMainMaterialChange = () => {
  if (selectedMainMaterialCode.value === '_add_category_main') {
    openFromType.value = 'main'
    openCategoryModal.value = true
    selectedMainMaterialCode.value = ''
  }
}

// 关闭弹窗
const closeCategoryModal = () => {
  openCategoryModal.value = false
  openFromType.value = ''
  selectedModalMainMcid.value = ''
  newMainForm.value = { mcode: '', mcateCn: '', mcateEn: '', mdesc: '' }
  newSubForm.value = { mcateCn: '', mcateEn: '', msdesc: '' }
}

// 提交新增
const submitAddMainCategory = () => {
  const { mcode, mcateCn, mcateEn, mdesc } = newMainForm.value
  if (!mcode || !mcateCn || !mcateEn) {
    alert('编码、中文名称、英文名称不能为空！')
    return
  }
  const newMcid = String(Math.max(...materialCategory.value.map(i => Number(i.mcid))) + 1)
  materialCategory.value.push({
    mcid: newMcid,
    mcode: mcode.toUpperCase(),
    mcateCn,
    mcateEn,
    mdesc
  })
  alert('一级材料大类新增成功！')
  newMainForm.value = { mcode: '', mcateCn: '', mcateEn: '', mdesc: '' }
}
</script>

<style scoped>
.tree-container {
  box-sizing: border-box;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}

.astro-chart {
  @apply border-collapse;
}
.astro-chart th,
.astro-chart td {
  @apply border border-gray-300 px-3 py-2;
}
.astro-chart th {
  @apply bg-gray-100 font-semibold;
}
.astro-chart td:not(:last-child) {
  @apply text-center;
}
.astro-chart .benchmark {
  @apply bg-sky-50;
}
.active {
  background-color: #ff5804ee;
}
</style>