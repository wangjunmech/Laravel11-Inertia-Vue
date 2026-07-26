<template>
    <div>
    <div>36进制：滚动鼠标中键增加数字</div>
    <input
        v-model="value"
        class="border px-3 py-2 w-48 rounded outline-none focus:border-blue-500"
        @wheel.prevent="handleWheel"
        @input="filterInput"
    />
  </div>
</template>

<script setup>
import { ref, watch } from 'vue'

// 36进制字符表
const chars = '0123456789abcdefghijklmnopqrstuvwxyz'
const charMap = new Map([...chars].map((c, i) => [c, i]))

// 默认值0
const value = ref('0')

// 滚轮增减
const handleWheel = (e) => {
  const delta = e.deltaY > 0 ? -1 : 1
  value.value = add36(value.value, delta)
}

// 36进制加减核心函数
const add36 = (str, num) => {
  const arr = [...str].reverse()
  let carry = num
  for (let i = 0; i < arr.length && carry !== 0; i++) {
    let idx = charMap.get(arr[i]) + carry
    if (idx >= 36) {
      carry = Math.floor(idx / 36)
      idx = idx % 36
    } else if (idx < 0) {
      carry = -1
      idx += 36
    } else {
      carry = 0
    }
    arr[i] = chars[idx]
  }
  // 进位新增高位
  while (carry > 0) {
    const idx = carry % 36
    carry = Math.floor(carry / 36)
    arr.push(chars[idx])
  }
  // 借位负数，无高位则最低位保持0
  if (carry < 0) return '0'
  return arr.reverse().join('')
}

// 过滤非法输入，只保留0-9a-z小写
const filterInput = () => {
  value.value = value.value.toLowerCase().replace(/[^0-9a-z]/g, '') || '0'
}

// 禁止空值
watch(value, (v) => {
  if (!v) value.value = '0'
})
</script>