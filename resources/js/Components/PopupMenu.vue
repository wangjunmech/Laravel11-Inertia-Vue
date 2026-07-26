<template>
  <div
    v-if="showMenu"
    class="context-menu"
    :style="{ left: `${x}px`, top: `${y}px` }"
    @click.stop
  >
    <div
      class="menu-item"
      v-for="(item, index) in menuList"
      :key="index"
      @click="handleClick(item)"
    >
      {{ item.label }}
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const props = defineProps({
  // 菜单配置 [{label: '名称', command: '标识'}]
  menuList: {
    type: Array,
    default: () => []
  }
})
const emit = defineEmits(['select'])

// 菜单位置、显示状态
const showMenu = ref(false)
const x = ref(0)
const y = ref(0)

// 打开右键菜单
const openMenu = (e) => {
  e.preventDefault()
  showMenu.value = true
  // 边界判断，防止菜单超出可视区域
  x.value = e.clientX
  y.value = e.clientY
  nextTick(() => {
    const menuDom = document.querySelector('.context-menu')
    if (!menuDom) return
    const rect = menuDom.getBoundingClientRect()
    // 右边溢出修正
    if (rect.right > window.innerWidth) {
      x.value = window.innerWidth - rect.width - 10
    }
    // 底部溢出修正
    if (rect.bottom > window.innerHeight) {
      y.value = window.innerHeight - rect.height - 10
    }
  })
}

// 关闭菜单
const closeMenu = () => {
  showMenu.value = false
}

// 菜单项点击
const handleClick = (item) => {
  emit('select', item)
  closeMenu()
}

// 全局点击关闭
const globalClick = () => closeMenu()

onMounted(() => {
  document.addEventListener('click', globalClick)
})
onUnmounted(() => {
  document.removeEventListener('click', globalClick)
})

// 对外暴露方法
defineExpose({
  openMenu
})
</script>

<style scoped>
.context-menu {
  position: fixed;
  z-index: 9999;
  background: #fff;
  border: 1px solid #e5e7eb;
  border-radius: 4px;
  box-shadow: 0 2px 12px rgba(0, 0, 0, 0.1);
  padding: 4px 0;
  min-width: 120px;
}
.menu-item {
  padding: 6px 16px;
  cursor: pointer;
  font-size: 14px;
}
.menu-item:hover {
  background-color: #f2f3f5;
}
</style>