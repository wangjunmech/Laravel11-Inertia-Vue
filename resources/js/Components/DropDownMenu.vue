<!-- DropdownMenu.vue 优化后完整代码 -->
<template>
  <div class="dropdown-wrap relative inline-block">
    <slot></slot>

    <div
      v-if="isOpen"
      ref="panelRef"
      :class="[
        'dropdown-panel absolute z-50 bg-white border border-gray-200 rounded-lg shadow-lg py-1 min-w-[115px] max-w-[220px]',
        placement === 'left' ? 'left-0' : 'left-1/2 -translate-x-1/2'
      ]"
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
import { ref, watch, onUnmounted } from 'vue'

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
    default: 'left',
    validator: val => ['left', 'center'].includes(val)
  }
})

const emit = defineEmits(['update:isOpen'])
const panelRef = ref(null)
const wrapRef = ref(null)

// 点击菜单项
const handleMenuItemClick = (menuItem) => {
  if (typeof menuItem.onClick === 'function') {
    menuItem.onClick()
  }
  emit('update:isOpen', false)
}

// 全局点击关闭
const outsideClickHandler = (e) => {
  if (!wrapRef.value) return
  // 点击不在整个下拉容器内，关闭
  if (!wrapRef.value.contains(e.target)) {
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