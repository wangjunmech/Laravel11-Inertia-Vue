<!-- DropdownMenu.vue -->
<template>
  <div class="dropdown-wrap relative inline-block">
    <!-- 触发插槽 -->
    <slot @click="handleTriggerClick"></slot>

    <div
      v-if="isOpen"
      ref="panelRef"
      class="dropdown-panel absolute z-50 bg-white border border-gray-500 rounded-lg shadow-lg py-1 min-w-[115px] max-w-[220px] overflow-hidden"
      :class="placementClass"
    >
      <div
        v-for="(item, idx) in menuList"
        :key="idx"
        class="menu-item px-3 py-1.5 text-sm cursor-pointer transition-colors"
        :class="[item.danger ? 'text-red-500' : 'text-gray-700']"
        @click="handleMenuItemClick(item)"
      >
        {{ item.label }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onUnmounted } from 'vue'

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  menuList: {
    type: Array,
    required: true,
    default: () => []
  },
  // 支持两种格式：
  // 1. 预设简写：left / right / center / top
  // 2. 自定义偏移：right[30px]、left[20px]、top[15px]、bottom[8px]
  placement: {
    type: String,
    default: 'left'
  }
})

const emit = defineEmits(['update:isOpen'])
const panelRef = ref(null)

// ========== 核心：解析 placement 字符串 ==========
const parsePlacement = (str) => {
  // 正则匹配格式：方位[数值px] 例：right[30px]
  const reg = /^(left|right|top|bottom)\[(\d+)px\]$/
  const match = str.match(reg)

  // 情况1：匹配到自定义偏移格式
  if (match) {
    const direction = match[1]
    const offset = match[2]
    return {
      type: 'custom',
      dir: direction,
      offset: Number(offset)
    }
  }

  // 情况2：普通预设方位
  return {
    type: 'preset',
    dir: str
  }
}

// ========== 计算最终定位样式类 ==========
const placementClass = computed(() => {
  const res = parsePlacement(props.placement)
  const offsetVal = res.offset ?? 4 // 默认间距4px

  // 预设基础方位映射
  const presetMap = {
    left: `top-full left-[${offsetVal}px] origin-top-left`,
    right: `top-full right-[${offsetVal}px] origin-top-right`,
    center: `top-full left-1/2 -translate-x-1/2 mt-[${offsetVal}px] origin-top`,
    top: `bottom-full left-1/2 -translate-x-1/2 mb-[${offsetVal}px] origin-bottom`
  }

  // 自定义方向（left/right/top/bottom 四边任意偏移）
  if (res.type === 'custom') {
    switch (res.dir) {
      case 'left':
        // 向下弹出，距离左侧 N px
        return ` left-[${res.offset}px] mt-1 origin-top-left`
      case 'right':
        // 向下弹出，距离右侧 N px（你要的 right[30px]）
        return ` right-[${res.offset}px] mt-1 origin-top-right`
      case 'top':
        // 向上弹出，距离顶部 N px
        return `bottom-full top-[${res.offset}px] mb-1 origin-bottom`
      case 'bottom':
        // 向下弹出，距离底部 N px
        return ` bottom-[${res.offset}px] mt-1 origin-top`
      default:
        return presetMap.left
    }
  }

  // 走预设默认样式
  return presetMap[res.dir] || presetMap.left
})

// 点击触发器切换开关
const handleTriggerClick = () => {
  emit('update:isOpen', !props.isOpen)
}

// 点击菜单项执行回调 + 关闭下拉
const handleMenuItemClick = (menuItem) => {
  if (typeof menuItem.onClick === 'function') {
    menuItem.onClick()
  }
  emit('update:isOpen', false)
}

// 点击页面空白处关闭菜单
const outsideClickHandler = (e) => {
  if (!panelRef.value) return
  if (!panelRef.value.contains(e.target)) {
    emit('update:isOpen', false)
  }
}

watch(
  () => props.isOpen,
  (val) => {
    if (val) {
      document.addEventListener('click', outsideClickHandler)
    } else {
      document.removeEventListener('click', outsideClickHandler)
    }
  }
)

onUnmounted(() => {
  document.removeEventListener('click', outsideClickHandler)
})
</script>

<style scoped>
.dropdown-wrap {
  position: relative;
}
.menu-item:hover {
  background-color: #7db1ff;
}
</style>