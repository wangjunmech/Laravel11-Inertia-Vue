<template>
  <div class="p-6">
    <div class="text-xl mb-8 bg-green-300 p-2 rounded-full">光标定位到输入框中滚动鼠标中键进行数字增减对比</div>
    <!-- 每一行使用grid三列布局：左 中(箭头) 右 -->
    <div class="grid grid-cols-[280px_80px_240px] items-center gap-4">
      <div>
        <div>2进制/binary</div>
        <input
          v-model="str2"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="(e)=>handleWheelBase(e,2)"
          @blur="()=>onBaseBlur(2)"
        />
      </div>
      <div class="text-4xl text-center">↔</div>
      <div>
        <div>十进制</div>
        <input
          v-model.number="numDec"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="handleWheelDec"
          @blur="onDecBlur"
        />
      </div>
    </div>
    <hr class="my-4">

    <div class="grid grid-cols-[280px_80px_240px] items-center gap-4">
      <div>
        <div>8进制/octal</div>
        <input
          v-model="str8"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="(e)=>handleWheelBase(e,8)"
          @blur="()=>onBaseBlur(8)"
        />
      </div>
      <div class="text-4xl text-center">↔</div>
      <div>
        <div>十进制</div>
        <input
          v-model.number="numDec"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="handleWheelDec"
          @blur="onDecBlur"
        />
      </div>
    </div>
    <hr class="my-4">

    <div class="grid grid-cols-[280px_80px_240px] items-center gap-4">
      <div>
        <div>16进制/hexadecimal</div>
        <input
          v-model="str16"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="(e)=>handleWheelBase(e,16)"
          @blur="()=>onBaseBlur(16)"
        />
      </div>
      <div class="text-4xl text-center">↔</div>
      <div>
        <div>十进制</div>
        <input
          v-model.number="numDec"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="handleWheelDec"
          @blur="onDecBlur"
        />
      </div>
    </div>
    <hr class="my-4">

    <div class="grid grid-cols-[280px_80px_240px] items-center gap-4">
      <div>
        <div>32进制/duotricemary / base‑32</div>
        <input
          v-model="str32"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="(e)=>handleWheelBase(e,32)"
          @blur="()=>onBaseBlur(32)"
        />
      </div>
      <div class="text-4xl text-center">↔</div>
      <div>
        <div>十进制</div>
        <input
          v-model.number="numDec"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="handleWheelDec"
          @blur="onDecBlur"
        />
      </div>
    </div>
    <hr class="my-4">

    <div class="grid grid-cols-[280px_80px_240px] items-center gap-4">
      <div>
        <div>36进制/base36</div>
        <input
          v-model="str36"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="(e)=>handleWheelBase(e,36)"
          @blur="()=>onBaseBlur(36)"
        />
      </div>
      <div class="text-4xl text-center">↔</div>
      <div>
        <div>十进制</div>
        <input
          v-model.number="numDec"
          class="border px-3 py-2 w-full rounded outline-none focus:border-blue-500 mt-1"
          @wheel.prevent="handleWheelDec"
          @blur="onDecBlur"
        />
      </div>
    </div>
    <hr class="my-4">
  </div>
</template>

<script setup>
import { ref } from 'vue'


// 0‑36进制统一字符集
const chars = '0123456789abcdefghijklmnopqrstuvwxyz'
const charMap = new Map([...chars].map((c, i) => [c, i]))


// 各进制字符串状态
const str2 = ref('0')
const str8 = ref('0')
const str16 = ref('0')
const str32 = ref('0')
const str36 = ref('0')


// 全局唯一十进制
const numDec = ref(0)


// 根据进制拿到对应的ref对象
function getStrRef(base) {
  const map = {
    2: str2,
    8: str8,
    16: str16,
    32: str32,
    36: str36
  }
  return map[base]
}


/**
 * 任意进制字符串转十进制
 * @param {string} s
 * @param {number} base 2~36
 * @returns {number}
 */
function baseToDec(s, base) {
  let val = 0
  for (const c of s.toLowerCase()) {
    const d = charMap.get(c)
    if(d >= base) break
    val = val * base + d
  }
  return val
}


/**
 * 十进制数字转 2‑36进制字符串
 * @param {number} n
 * @param {number} base
 * @returns {string}
 */
function decToBase(n, base) {
  if (n <= 0) return '0'
  const res = []
  let num = n
  while (num > 0) {
    res.push(chars[num % base])
    num = Math.floor(num / base)
  }
  return res.reverse().join('')
}


/**
 * 进制字符串加减，支持2‑36，最小值0
 * @param {string} str
 * @param {number} delta
 * @param {number} base
 * @returns {string}
 */
function baseAdd(str, delta, base) {
  const arr = [...str.toLowerCase()].reverse()
  let carry = delta


  for (let i = 0; i < arr.length && carry !== 0; i++) {
    const digit = charMap.get(arr[i])
    let idx = digit + carry


    if (idx >= base) {
      carry = Math.floor(idx / base)
      idx = idx % base
    } else if (idx < 0) {
      carry = -1
      idx += base
    } else {
      carry = 0
    }
    arr[i] = chars[idx]
  }


  while (carry > 0) {
    const idx = carry % base
    carry = Math.floor(carry / base)
    arr.push(chars[idx])
  }


  if (carry < 0) return '0'
  return arr.reverse().join('')
}


/** 全部进制根据十进制刷新 */
function syncAllBaseFromDec() {
  str2.value = decToBase(numDec.value, 2)
  str8.value = decToBase(numDec.value, 8)
  str16.value = decToBase(numDec.value, 16)
  str32.value = decToBase(numDec.value, 32)
  str36.value = decToBase(numDec.value, 36)
}


// 某进制输入框滚轮
const handleWheelBase = (e, base) => {
  const delta = e.deltaY > 0 ? -1 : 1
  const refStr = getStrRef(base)
  refStr.value = baseAdd(refStr.value, delta, base)
  numDec.value = baseToDec(refStr.value, base)
  syncAllBaseFromDec()
}


// 某进制输入框失焦清洗
const onBaseBlur = (base) => {
  const refStr = getStrRef(base)
  // 过滤只保留当前进制允许字符
  const validChars = chars.slice(0, base)
  const reg = new RegExp(`[^${validChars}]`, 'g')
  let clean = refStr.value.toLowerCase().replace(reg, '')
  clean = clean.replace(/^0+/, '') || '0'
  refStr.value = clean
  numDec.value = baseToDec(clean, base)
  syncAllBaseFromDec()
}


// 可编辑十进制滚轮
const handleWheelDec = (e) => {
  const delta = e.deltaY > 0 ? -1 : 1
  let newVal = numDec.value + delta
  if (newVal < 0) newVal = 0
  numDec.value = newVal
  syncAllBaseFromDec()
}


// 十进制失焦清洗
const onDecBlur = () => {
  if (isNaN(numDec.value) || numDec.value < 0) {
    numDec.value = 0
  }
  syncAllBaseFromDec()
}
</script>
