<template>
  <!-- 遮罩层 -->
  <div v-if="visible" class="fixed inset-0 bg-black/50 flex items-center justify-center z-[999]" @click.self="handleClose">
    <!-- 弹窗主体 -->
    <div :class="[widthClass, heightClass, 'bg-white rounded-lg flex flex-col p-4']">
      <!-- 头部：默认标题插槽 -->
      <div class="flex justify-between items-center mb-3 px-2 rounded-md bg-zinc-300">
        <slot name="header">
          <h3 class="flex text-lg font-medium">{{ title }}</h3>
        </slot>
      </div>

      <!-- 主体内容区，自动滚动【核心插槽】 -->
      <div class="flex-1 overflow-y-auto ">
        <slot />
      </div>

      <!-- 底部按钮插槽 -->
      <div class="mt-2 flex justify-end ">
        <slot name="footer">
          <button @click="handleClose" class="px-3 mx-2 bg-gray-300 border rounded">取消</button>
          <button @click="handleConfirm" class="px-3 mx-2  bg-blue-500 text-white rounded">确定</button>
        </slot>
      </div>
    </div>
  </div>
</template>

<script setup>
import {onMounted,onUnmounted } from 'vue'
const props = defineProps({
  visible: {
    type: Boolean,
    default: false
  },
  title: {
    type: String,
    default: ''
  },
  // 弹窗宽度，tailwind类名直接传
  widthClass: {
    type: String,
    default: 'w-[600px]'
  },
  heightClass: {
    type: String,
    default: 'h-[200px]'
    },
  escClosable: {
    type: Boolean,
    default: true
  },

})

const emit = defineEmits(['close', 'confirm'])

// ESC按键处理函数
const handleKeyDown = (e) => {
  // 条件：ESC键 + 弹窗是打开状态 + 允许ESC关闭
  if (e.key === 'Escape' && props.visible && props.escClosable) {
    handleClose()
  }
}
const handleClose = () => {
  emit('close')
}
const handleConfirm = () => {
  emit('confirm')
}

onMounted(() => {
  document.addEventListener('keydown', handleKeyDown)
})
onUnmounted(() => {
  document.removeEventListener('keydown', handleKeyDown)
})
</script>
