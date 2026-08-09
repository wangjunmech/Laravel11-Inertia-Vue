<template>
    <!-- 弹出菜单组件：按数字键快捷键执行相应菜单 -->
  <div class="dropdown-wrap relative inline-block">
    <slot @click="handleTriggerClick"></slot>
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
        @click="handleMenuItemClick(item,idx)"
      >
        <span class="mr-1">{{ idx + 1 }}.</span>
        {{ item.label }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, onUnmounted } from 'vue'

// 先声明emit，所有函数才可以调用
const emit = defineEmits(['update:isOpen', 'menuClick'])

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
  placement: {
    type: String,
    default: 'left'
  },
  uniqueId:{
    type:Number
  },
})
const panelRef = ref(null)

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
  const offsetVal = res.offset ?? 4
  const presetMap = {
    left: `top-full left-[${offsetVal}px] origin-top-left`,
    right: `top-full right-[${offsetVal}px] origin-top-right`,
    center: `top-full left-1/2 -translate-x-1/2 mt-[${offsetVal}px] origin-top`,
    top: `bottom-full left-1/2 -translate-x-1/2 mb-[${offsetVal}px] origin-bottom`
  }
  if (res.type === 'custom') {
    switch (res.dir) {
      case 'left': return ` left-[${res.offset}px] mt-1 origin-top-left`
      case 'right': return ` right-[${res.offset}px] mt-1 origin-top-right`
      case 'top': return `bottom-full top-[${res.offset}px] mb-1 origin-bottom`
      case 'bottom': return ` bottom-[${res.offset}px] mt-1 origin-top`
    }
  }
  return presetMap[res.dir] || presetMap.left
})

const handleTriggerClick = () => emit('update:isOpen', !props.isOpen)

const handleMenuItemClick = (menuItem,idx) => {
  console.log('菜单面板' +props.uniqueId+'**********下面的菜单' + idx+'被点击：');
  console.log(menuItem)
  menuItem.onClick?.()
  emit('menuClick',{
    type:props.uniqueId,
    list:props.menuList
  })
  emit('update:isOpen', false)
}

// 点击外部关闭菜单
const outsideClickHandler = (e) => {
  if (!panelRef.value || panelRef.value.contains(e.target)) return
  emit('update:isOpen', false)
}

// 键盘快捷键
const keyHandler = (e) => {
  if (!props.isOpen) return
  console.log('按下按键', e.key, e.code)
  const num = Number(e.key)
  // 校验数字范围
  if (Number.isInteger(num) && num >= 1 && num <= props.menuList.length) {
    e.preventDefault()
    const opt = props.menuList[num - 1]
    opt?.onClick?.()
    emit('update:isOpen', false)
  }
}

// 弹窗打开：绑定键盘、外部点击监听
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
  }
)

// 组件销毁强制清除全部监听，防止内存泄漏、快捷键残留冲突
onUnmounted(() => {
  window.removeEventListener('keydown', keyHandler)
  window.removeEventListener('click', outsideClickHandler)
})
</script>

<style scoped>
.dropdown-wrap {position: relative;}
.menu-item:hover {background-color: #7db1ff;}
</style>