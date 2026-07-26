<template>
  <ContextMenu ref="menuRef" :menu-list="menuOptions" @select="menuSelect" />

  <!-- 改成跟随鼠标的提示，初始隐藏 -->
  <span
    id="tip"
    v-show="showTip"
    class="mouse-tip"
    :style="{ left: `${tipX}px`, top: `${tipY}px` }"
  >
    🖱︎右键更多功能
  </span>

  <!-- 右键可触发容器，统一加 class: has-ctx-menu -->
  <div
    class="box has-ctx-menu"
    @contextmenu="handleRightClick"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    在此区域点击鼠标右键弹出菜单
  </div>

  <div>按钮绑定菜单</div>
  <button
    @contextmenu="handleRightClick"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
    class="btn bg-red-300 rounded-full p-3 cursor-pointer has-ctx-menu"
  >
    testtestestes
  </button>

  <div>字符绑定菜单</div>
  <button
    @contextmenu="handleRightClick"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
    class="flex text-5xl has-ctx-menu"
  >
    🪟
  </button>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import ContextMenu from "../../Components/PopupMenu.vue"

const menuRef = ref(null)
const showTip = ref(false)
const tipX = ref(0)
const tipY = ref(0)
let moveListener = null

// 右键菜单配置
const menuOptions = ref([
  { label: '复制', command: 'copy' },
  { label: '粘贴', command: 'paste' },
  { label: '删除', command: 'delete' },
  { label: '重命名', command: 'rename' },
  { label: '想干啥', command: 'test' }
])

// 触发右键
const handleRightClick = (e) => {
// 右键弹出菜单时，强制隐藏鼠标提示
  showTip.value = false
  if (moveListener) {
    document.removeEventListener('mousemove', moveListener)
    moveListener = null
  }
  menuRef.value.openMenu(e)
}

// 菜单选中回调
const menuSelect = (item) => {
  console.log('选中菜单：', item.command, item.label)
  switch (item.command) {
    case 'copy':
      alert('执行复制')
      break
    case 'delete':
      alert('执行删除')
      break
  }
}

// 鼠标实时更新tip坐标，偏移一点避免挡住光标
const onMouseMove = (e) => {
  tipX.value = e.clientX + 12
  tipY.value = e.clientY + 12
}

// 进入右键元素：显示提示 + 开启鼠标移动监听
const onMouseEnter = () => {
  showTip.value = true
  moveListener = onMouseMove
  document.addEventListener('mousemove', moveListener)
}

// 离开右键元素：隐藏提示 + 移除监听
const onMouseLeave = () => {
  showTip.value = false
  if (moveListener) {
    document.removeEventListener('mousemove', moveListener)
    moveListener = null
  }
}

// 组件销毁清理监听，防止内存残留
onUnmounted(() => {
  if (moveListener) {
    document.removeEventListener('mousemove', moveListener)
  }
})
</script>

<style scoped>
.box {
  width: 500px;
  height: 200px;
  background-color: lightblue;
  border: 1px solid #f46161;
  display: flex;
  align-items: center;
  justify-content: center;
}

/* 统一给所有支持右键的元素设置悬浮样式 */
.has-ctx-menu {
  position: relative;
  transition: all 0.2s;
}
.has-ctx-menu:hover {
  cursor: context-menu;
  outline: 2px dashed #409eff;
  outline-offset: 2px;
}

/* 鼠标跟随提示样式核心 */
.mouse-tip {
  position: fixed;
  z-index: 99999;
  padding: 3px 8px;
  background: #775e04;
  color: #fff;
  font-size: 13px;
  border-radius: 4px;
  pointer-events: none; /* 不会遮挡鼠标事件 */
  white-space: nowrap;
  user-select: none;
}
</style>