<template>
  <!-- resources\js\Components\DropNumShortCut.vue -->
  <div 
    class="dropdown-wrap relative inline-block" 
    @mouseenter="handleMenuEnter"
    @mouseleave="handleMenuLeave"
  >
    <!-- 触发区域 -->
    <div @click="handleTriggerClick">
      <slot></slot>
    </div>
    
    <!-- 下拉面板 -->
    <div
      v-if="isOpen"
      ref="panelRef"
      class="dropdown-panel absolute z-50 bg-white border border-gray-500 rounded-lg shadow-lg py-1 min-w-[180px] max-w-[220px] overflow-hidden"
      :class="placementClass"
      
    >
      <div
        v-for="(item, idx) in menuList"
        :key="idx"
        class="menu-item px-3 py-1.5 text-sm cursor-pointer transition-colors"
        :class="[item.danger ? 'text-red-500' : 'text-gray-700']"
        @click="handleMenuItemClick(item, idx)"
      >
        <span class="mr-1">{{ idx + 1 }}.</span>
        {{ item.subMenuLabel }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onUnmounted } from 'vue'

const emit = defineEmits(['update:isOpen', 'update:modelValue', 'menuClick'])
const activeHoverKey = ref(null)
let closeTimer = null

const props = defineProps({
  isOpen: {
    type: Boolean,
    default: false
  },
  modelValue: {
    type: Boolean,
    default: false
  },
  menuList: {
    type: Array,
    required: true,
    default: () => []
  },
  placement: {
    type: String,
    default: 'left'
  },
  uniqueId: {
    type: Number
  }
})

const panelRef = ref(null)

// 解析 placement
const parsePlacement = (str) => {
  const reg = /^(left|right|top|bottom)\[(\d+)px\]$/
  const match = str.match(reg)
  if (match) {
    return {
      type: 'custom',
      dir: match[1],
      offset: Number(match[2])
    }
  }
  return { type: 'preset', dir: str }
}

const placementClass = computed(() => {
  const res = parsePlacement(props.placement)
  const presetMap = {
    left: 'top-full left-0 mt-1 origin-top-left',
    right: 'top-full right-0 mt-1 origin-top-right',
    center: 'top-full left-1/2 -translate-x-1/2 mt-1 origin-top',
    top: 'bottom-full left-1/2 -translate-x-1/2 mb-1 origin-bottom'
  }
  
  if (res.type === 'custom') {
    switch (res.dir) {
      case 'left': return `left-[${res.offset}px] mt-1 origin-top-left`
      case 'right': return `right-[${res.offset}px] mt-1 origin-top-right`
      case 'top': return `bottom-full top-[${res.offset}px] mb-1 origin-bottom`
      case 'bottom': return `bottom-[${res.offset}px] mt-1 origin-top`
    }
  }
  return presetMap[res.dir] || presetMap.left
})

// 打开菜单
const openMenu = () => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
  emit('update:isOpen', true)
  emit('update:modelValue', true)
}

// 关闭菜单
const closeMenu = () => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
  emit('update:isOpen', false)
  emit('update:modelValue', false)
  activeHoverKey.value = null
}



// 鼠标离开整个组件区域（延迟关闭）
const handleMenuLeave = (e) => {
  // 检查鼠标是否移动到菜单面板上
  const relatedTarget = e.relatedTarget
  if (panelRef.value && panelRef.value.contains(relatedTarget)) {
    return
  }
  
  closeTimer = setTimeout(() => {
    closeMenu()
  }, 200)
}

// 鼠标进入面板（取消关闭）
const handlePanelEnter = () => {
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
}

// 鼠标离开面板（延迟关闭）
const handlePanelLeave = (e) => {
  // 检查鼠标是否移动到 wrap 区域
  const wrapEl = e.currentTarget.closest('.dropdown-wrap')
  if (wrapEl && wrapEl.contains(e.relatedTarget)) {
    return
  }
  
  closeTimer = setTimeout(() => {
    closeMenu()
  }, 200)
}

const handleTriggerClick = () => {
  if (props.isOpen) {
    closeMenu()
  } else {
    openMenu()
  }
}

const handleMenuItemClick = (menuItem, idx) => {
  console.log('菜单面板' + props.uniqueId + '**********下面的菜单' + idx + '被点击：')
  console.log(menuItem)
  menuItem.onClick?.()
  emit('menuClick', {
    type: props.uniqueId,
    list: props.menuList
  })
  closeMenu()
}

// 点击外部关闭菜单
const outsideClickHandler = (e) => {
  const wrapEl = e.currentTarget
  if (!panelRef.value || panelRef.value.contains(e.target)) return
  // 检查点击是否在 wrap 内部
  const wrap = panelRef.value.closest('.dropdown-wrap')
  if (wrap && wrap.contains(e.target)) return
  
  closeMenu()
}

// 键盘快捷键
const keyHandler = (e) => {
  if (!props.isOpen) return
  console.log('按下按键', e.key, e.code)
  const num = Number(e.key)
  if (Number.isInteger(num) && num >= 1 && num <= props.menuList.length) {
    e.preventDefault()
    const opt = props.menuList[num - 1]
    opt?.onClick?.()
    closeMenu()
  }
}

// 监听 isOpen 变化，绑定/解绑事件
watch(
  () => props.isOpen,
  (open) => {
    if (open) {
      window.addEventListener('keydown', keyHandler)
      window.addEventListener('click', outsideClickHandler)
    } else {
      window.removeEventListener('keydown', keyHandler)
      window.removeEventListener('click', outsideClickHandler)
    }
  },
  { immediate: true }
)

// 组件销毁清理
onUnmounted(() => {
  window.removeEventListener('keydown', keyHandler)
  window.removeEventListener('click', outsideClickHandler)
  if (closeTimer) {
    clearTimeout(closeTimer)
    closeTimer = null
  }
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