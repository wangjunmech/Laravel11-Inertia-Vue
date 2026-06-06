<script setup>
import { ref } from 'vue'
// 分割线百分比 0%最左，100%最右，初始50%居中
const splitPercent = ref(50)
let dragging = false
let wrapDom = null

// 按下圆球
const dragDown = (e) => {
  e.preventDefault() // 阻止图片默认拖拽、文字选中
  dragging = true
  wrapDom = document.getElementById('compare-wrap')
  // 全局绑定移动&抬起（关键！之前绑容器失效，必须绑document）
  document.addEventListener('mousemove', dragMove)
  document.addEventListener('mouseup', dragEnd)
  document.addEventListener('mouseleave', dragEnd)
  // 移动端触屏
  document.addEventListener('touchmove', dragMove)
  document.addEventListener('touchend', dragEnd)
}

// 拖动计算位置
const dragMove = (e) => {
  if (!dragging || !wrapDom) return
  const rect = wrapDom.getBoundingClientRect()
  // 兼容鼠标/触屏坐标
  const clientX = e.touches ? e.touches[0].clientX : e.clientX
  // 相对容器横向距离
  let x = clientX - rect.left
  // 限制在容器0~宽之间
  x = Math.max(0, Math.min(x, rect.width))
  // 转百分比
  splitPercent.value = (x / rect.width) * 100
}

// 结束拖拽，清空监听
const dragEnd = () => {
  dragging = false
  document.removeEventListener('mousemove', dragMove)
  document.removeEventListener('mouseup', dragEnd)
  document.removeEventListener('mouseleave', dragEnd)
  document.removeEventListener('touchmove', dragMove)
  document.removeEventListener('touchend', dragEnd)
}
</script>

<template>
<div class="flex flex-col items-center justify-center gap-6 py-8">
    <div>Details,拖动效果</div>
    <div id="compare-wrap" class="relative w-[375px] h-[620px] overflow-hidden rounded-xl select-none">
    <!-- 底层B图：完整显示 -->
    <img :src="'/storage/images/bgimgs/B.png'" class="absolute inset-0 w-full h-full object-cover pointer-events-none" />
    <!-- 上层A图：右侧裁切，裁切宽度=splitPercent% -->
    <img :src="'/storage/images/bgimgs/A.png'" class="absolute inset-0 w-full h-full object-cover pointer-events-none"
        :style="`clip-path: inset(0 ${100 - splitPercent}% 0 0)`" />

    <!-- 中间蓝色竖线 -->
    <div class="absolute top-0 bottom-0 w-[3px] bg-sky-400 z-10" :style="{left:`${splitPercent}%`,transform:'translateX(-50%)'}"></div>

    <!-- 拖拽圆球【只绑mousedown/touchstart】 -->
    <div @mousedown="dragDown" @touchstart="dragDown"
        class="absolute top-1/2 w-10 h-10 rounded-full bg-sky-500 z-20 flex items-center justify-center cursor-ew-resize select-none"
        :style="{left:`${splitPercent}%`,transform:'translate(-50%,-50%)'}">
        <svg width="14" height="8" fill="none">
        <path d="M1 4L4 1 M1 4L4 7 M13 4L10 1 M13 4L10 7 M1 4H13" stroke="#fff" stroke-linecap="round"/>
        </svg>
        <!-- <div class="absolute inset-0 rounded-full bg-sky-500 animate-ping duration-200 opacity-40"></div> -->
        <div class="absolute inset-0 rounded-full bg-sky-500 opacity-80"
style="animation: ping 0.2s cubic-bezier(0,0,0.2,1) infinite;">
</div>
    </div>
    </div>
</div>
</template>