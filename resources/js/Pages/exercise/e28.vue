<template>
  <div class="">
    <div class="text-3xl">3D-Tool : Model Info自定义文本提取器 : 提取内容到剪贴板</div>
    <hr class="my-3">
    <div class="m-5 text-2xl" >请在下面的文本框中粘贴Model Info文本，然后点击右边的按钮提取对应的内容:</div>
    <div class="flex items-start">
      <!-- 使用v-model绑定文本，不再用id拿dom -->
      <textarea
        v-model="inputText"
        class="w-1/4 h-80 rounded-lg border p-2"
        placeholder="粘贴Parts、Material等文本..."
      ></textarea>
      <div class="flex flex-col m-5 gap-3">
        <button
          type="button"
          @click="getSize"
          class="btn-action"
          title="点击获取长宽高尺寸"
        >
          获取尺寸（小数向上取整，单位：mm）
        </button>
        <button
          type="button"
          @click="getMat"
          class="btn-action"
          title="点击获取材料"
        >
          获取材料
        </button>
        <button
          type="button"
          @click="getMatDensity"
          class="btn-action"
          title="点击获取材料密度"
        >
          获取材料密度
        </button>
        <button
          type="button"
          @click="getWeight"
          class="btn-action"
          title="点击获取重量"
        >
          获取重量信息
        </button>
      </div>
    </div>
    <hr class="my-3">
    <!-- 黄色输出区域，展示解析结果 -->
    <div class="bg-yellow-100 w-full min-h-20 p-4">
      <div v-if="result" class="text-2xl">{{ result }}</div>
      <div v-else class="text-gray-500">解析结果输出在这里...</div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'

// 文本框绑定
const inputText = ref('')
// 输出结果
const result = ref('')

/**
 * 公共方法：根据key正则提取值
 * @param {string} keyLabel 例如 "X\[mm\]"、"Material"
 * @returns {string|null}
 */
function extractValue(keyLabel) {
    console.log(keyLabel)
  if (!inputText.value.trim()) {
    result.value = "⚠️请先粘贴文本到输入框！"
    return null
  }
  // 正则：匹配 键名: 后面的内容，忽略tab/空格
  const reg = new RegExp(`${keyLabel}:\\s*(.+)$`, 'm')
  const match = inputText.value.match(reg)
  if (match) {
    return match[1].trim()
  }
  return null
}
/**
 * 复制文本到剪贴板
 * @param {string} text
 */
async function copyToClipboard(text) {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    console.log("✅已复制剪贴板：", text)
  } catch (err) {
    console.error("❌复制失败", err)
    result.value += "\n（复制失败，请手动复制）"
  }
}

// 获取 X Y Z，四舍五入
const getSize = () => {
  const x = extractValue('X\\[mm\\]')
  const y = extractValue('Y\\[mm\\]')
  const z = extractValue('Z\\[mm\\]')
  if(!x || !y || !z){
    result.value = "❌未能读取X/Y/Z尺寸"
    return
    }
  //Math.ceil数值向上取整
  const xNum = Math.ceil(parseFloat(x))
  const yNum = Math.ceil(parseFloat(y))
  const zNum = Math.ceil(parseFloat(z))
    // result.value = `尺寸 X:${xNum} mm, Y:${yNum} mm, Z:${zNum} mm`
    result.value = `${xNum} X ${yNum} X ${zNum}`
    copyToClipboard(`${xNum} X ${yNum} X ${zNum}`)
}

const getMat = () => {
  const mat = extractValue('Material')
  if(!mat){
    result.value = "❌未能读取Material材料"
    return
    }
    //正则取出密度括号前面的材料
    const reg = /^([^()]+)\(/
    const m = mat.match(reg)    
    if(m){
    console.log(m) //输出 PC
    }
    result.value = `${m[1]}`  
    copyToClipboard(result.value)
}

const getMatDensity = () => {
  const density = extractValue('Density\\[g/cm³\\]')
  if(!density){
    result.value = "❌未能读取密度"
    return
    }
  
  result.value = `密度：${density} g/cm³`
  result.value = density
    copyToClipboard(density)
}

const getWeight = () => {
  const weight = extractValue('Weight\\[g\\]')
  if(!weight){
    result.value = "❌未能读取重量"
    return
  }
    result.value = `重量：${weight} g`
    result.value = weight  
    copyToClipboard(weight)
}

</script>

<style scoped>
/* 抽离按钮公共样式，减少模板重复代码 */
.btn-action {
  @apply bg-slate-400 text-red-600 text-2xl rounded-lg p-2 px-4 hover:bg-red-200 cursor-pointer;
}
</style>
