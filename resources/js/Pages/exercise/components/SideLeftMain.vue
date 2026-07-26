<template>
<div class="side-main" :style="{ width: `${sideWidth}px` }">
  <div class="logo-box"></div>
  <div class="menu-list">
    <div class="menu-item">
      <span class="icon">⌂</span>
      <span class="text">概况</span>
    </div>
    <div class="menu-item">
      <span class="icon">📊</span>
      <span class="text">统计</span>
    </div>
    <div class="menu-item">
      <span class="icon">📄</span>
      <span class="text">文章</span>
    </div>
    <div class="menu-item active">
      <span class="icon">📦</span>
      <span class="text">产品</span>
    </div>
    <div class="menu-item">
      <span class="icon">📣</span>
      <span class="text">营销</span>
    </div>
    <div class="menu-item">
      <span class="icon">📝</span>
      <span class="text">表单</span>
    </div>
    <div class="menu-item">
      <span class="icon">💬</span>
      <span class="text">互动</span>
    </div>
    <div class="menu-item">
      <span class="icon">🖼️</span>
      <span class="text">图册</span>
    </div>
    <div class="menu-item">
      <span class="icon">🗄️</span>
      <span class="text">资源库</span>
    </div>
    <div class="menu-item">
      <span class="icon">💱</span>
      <span class="text">交易管理</span>
    </div>
    <div class="menu-item">
      <span class="icon">🔍</span>
      <span class="text">搜索优化</span>
    </div>
    <div class="menu-item">
      <span class="icon">🌐</span>
      <span class="text">域名管理</span>
    </div>
    <div class="menu-item">
      <span class="icon">⚙️</span>
      <span class="text">系统设置</span>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const sideWidth = ref(Number(localStorage.getItem('mainSideWidth')) || 180)
const MIN_WIDTH = 60
const MAX_WIDTH = 300

let isDrag = false
let startX = 0
let startW = 0
let rafTimer = null

// 对外暴露拖拽启动方法
const startDrag = (e) => {
  isDrag = true
  startX = e.clientX
  startW = sideWidth.value
  document.body.style.cursor = 'col-resize'
  document.body.style.userSelect = 'none'
}

const mouseMove = (e) => {
  if (!isDrag || rafTimer) return
  rafTimer = requestAnimationFrame(() => {
    const offset = e.clientX - startX
    let w = startW + offset
    w = Math.max(MIN_WIDTH, Math.min(w, MAX_WIDTH))
    sideWidth.value = w
    rafTimer = null
  })
}

const mouseUp = () => {
  if (!isDrag) return
  isDrag = false
  cancelAnimationFrame(rafTimer)
  document.body.style.cursor = ''
  document.body.style.userSelect = ''
  localStorage.setItem('mainSideWidth', sideWidth.value)
  rafTimer = null
}

onMounted(() => {
  document.addEventListener('mousemove', mouseMove)
  document.addEventListener('mouseup', mouseUp)
})
onUnmounted(() => {
  document.removeEventListener('mousemove', mouseMove)
  document.removeEventListener('mouseup', mouseUp)
  cancelAnimationFrame(rafTimer)
})

defineExpose({ startDrag })
</script>

<style scoped>
.side-main {
  background-color: #191a1f;
  color: #fff;
  flex-shrink: 0;
  overflow-y: auto;
  will-change: width;
  height: 100%;
}
.logo-box {
  height: 64px;
  border-bottom: 1px solid #2c2d34;
  background: linear-gradient(135deg, #3488eb, #36cc90);
}
.menu-list {
  padding-top: 10px;
}
.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  cursor: pointer;
  color: #b8b9c0;
}
.menu-item.active {
  background-color: #2c2d34;
  color: #fff;
  border-right: 3px solid #2563eb;
}
.menu-item:hover:not(.active) {
  background-color: #24252b;
  color: #fff;
}
.icon {
  font-size: 18px;
  width: 22px;
  text-align: center;
}
</style>