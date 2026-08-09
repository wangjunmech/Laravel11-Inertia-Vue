<template>
  <div ref="containerRef" class="relative w-full">
    <!-- mousedown 鼠标按下直接打开弹窗，不需要松开左键 -->
    <div
      class="dropdown-trigger cursor-pointer bg-red-400 w-full h-full flex items-center justify-center"
      @mousedown.stop="togglePopup"
    >
      <slot></slot>
    </div>

    <!-- 外层一级弹窗，移除 overflow-hidden才能显示子菜单 -->
    <div
      v-if="modelValue"      
      class="absolute z-[9999] top-full left-0 mt-1 min-w-[130px] bg-gray-200 border border-red-400 rounded-lg shadow-lg "
      @click.stop
    >
      <div
        v-for="item in unitGroupList"
        :key="item.key"
        class="menu-item px-3 py-1.5 flex justify-between items-center text-sm hover:bg-gray-400 relative"
        @mouseenter="activeHoverKey = item.key"
        @mouseleave="activeHoverKey = null"
      >
        <span>{{ item.label }}</span>
        <span>></span>
        
        <div
          v-if="activeHoverKey === item.key"
          class="absolute z-[9999] left-full top-0 min-w-[130px]  bg-gray-200 border border-red-400  rounded-lg shadow-lg overflow-hidden"
          @click.stop
        >
          <div
            v-for="subItem in item.children"
            :key="subItem.key"
            class="px-3 py-1.5 text-sm hover:bg-gray-400 cursor-pointer"
            @click="selectSub(subItem)"
          >
            {{ subItem.label }}
          </div>
        </div>
      </div>
    </div>
  </div>
  
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const emit = defineEmits(['update:modelValue', 'select'])
const props = defineProps({
  modelValue: {
    type: Boolean,
    default: false
  },
  unitGroupList: {
    type: Array,
    default: () => []
  }
})
// const activeHover = ref(null)
const containerRef = ref(null)
// 每个组件实例独立悬浮状态，多行互不干扰
const activeHoverKey = ref(null)

const togglePopup = () => {
  emit('update:modelValue', !props.modelValue)
  activeHoverKey.value = null
}

const selectSub = (subItem) => {
  emit('select', subItem.key)
  emit('update:modelValue', false)
  activeHoverKey.value = null
}

// 点击页面空白关闭菜单
const closeOutside = (e) => {
  if (!containerRef.value) return
  if (containerRef.value.contains(e.target)) return
  emit('update:modelValue', false)
  activeHoverKey.value = null
}

onMounted(() => {
  document.addEventListener('click', closeOutside)
})
onUnmounted(() => {
  document.removeEventListener('click', closeOutside)
})
</script>

<style scoped>
.menu-item {
  cursor: pointer;
  user-select: none;
}
</style>