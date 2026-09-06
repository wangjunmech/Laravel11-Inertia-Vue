<template>
  <div
    ref="wrapperRef"
    class="ctx-menu-wrapper has-ctx-menu"
    @contextmenu="handleRightClick"
    @mouseenter="onMouseEnter"
    @mouseleave="onMouseLeave"
  >
    <!-- 插槽：外部传入任意需要右键能力的内容 -->
    <slot></slot>

    <!-- tip:是否显示右键提示，直接写在本组件内部，变量控制显隐 -->
    <span
      v-show="innerShowTip"
      class="mouse-tip"
      :style="{ left: `${tipX}px`, top: `${tipY}px` }"
    >
      {{ tipText }}
    </span>

    <!-- 原右键菜单组件 -->
    <PopupMenu ref="menuRef" :menu-list="menuList" @select="onMenuSelect" />
  </div>
</template>

<script setup>
import { ref, onUnmounted, defineProps, defineEmits } from 'vue'
import PopupMenu from './PopupMenu.vue'

const props = defineProps({
  menuList: {
    type: Array,
    required: true,
  },
  // 是否开启悬浮提示
  showHelperTip: {
    type: Boolean,
    default: true,
  },
  // 父组件传入tip显示文字
  tipText: {
    type: String,
    default: '🖱︎右键更多功能'
  }
})

const emit = defineEmits(['select'])

const wrapperRef = ref(null)
const menuRef = ref(null)
const innerShowTip = ref(false)
const tipX = ref(0)
const tipY = ref(0)
let moveListener = null

// 清理鼠标移动监听 + 隐藏tip
const clearTipAndListener = () => {
  innerShowTip.value = false
  if (moveListener) {
    document.removeEventListener('mousemove', moveListener)
    moveListener = null
  }
}

// 鼠标移动回调：实时校验鼠标是否还在当前组件DOM内
const onMouseMove = (e) => {
  if (!wrapperRef.value) return
  // 检测鼠标当前位置是否落在本wrapper元素内部
  const hoverEl = document.elementFromPoint(e.clientX, e.clientY)
  if (!wrapperRef.value.contains(hoverEl)) {
    // 鼠标已经移出本组件范围，立刻关闭
    clearTipAndListener()
    return
  }
  // 更新tip坐标
  tipX.value = e.clientX + 12
  tipY.value = e.clientY + 12
}

// 右键触发
const handleRightClick = (e) => {
  clearTipAndListener()
  menuRef.value.openMenu(e)
}

// 鼠标进入
const onMouseEnter = () => {
  if (!props.showHelperTip) return
  innerShowTip.value = true
  moveListener = onMouseMove
  document.addEventListener('mousemove', moveListener)
}

// 鼠标离开
const onMouseLeave = () => {
  clearTipAndListener()
}

// 菜单选中事件透传给父组件
const onMenuSelect = (item) => {
  emit('select', item)
}

// 组件销毁兜底清理
onUnmounted(() => {
  clearTipAndListener()
})
</script>

<style scoped>
.ctx-menu-wrapper {
  display: contents;
}
.has-ctx-menu {
  position: relative;
  transition: all 0.2s;
}
.has-ctx-menu:hover {
  cursor: context-menu;
  outline: 2px dashed #409eff;
  outline-offset: 2px;
}

.mouse-tip {
  position: fixed;
  z-index: 99999;
  padding: 3px 8px;
  background: #775e04;
  color: #fff;
  font-size: 13px;
  border-radius: 4px;
  pointer-events: none;
  white-space: nowrap;
  user-select: none;
}
</style>
