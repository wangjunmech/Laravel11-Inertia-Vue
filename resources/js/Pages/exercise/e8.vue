<template>
<div class="layout-root">
  <!-- 1 最左侧主导航 -->
  <SideLeftMain ref="mainSideRef" />
  <!-- 主导航拖拽分割线 -->
  <div class="resizer-main" @mousedown="$refs.mainSideRef.startDrag"></div>

  <!-- 2 中间二级侧边栏 -->
  <SideLeftSub />

  <!-- 右侧整体区域：顶部栏 + 主内容 -->
  <div class="right-wrap">
    <!-- 3 顶部栏 -->
    <HeaderTopBar />
    <!-- 4 产品列表页面 -->
    <PageProductList />
  </div>
</div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAppStore } from '../../stores/app.js'// 直接从Store的 app.js 导入pinia
const appStore = useAppStore()
appStore.navShow = 0;//控制头部菜单显示
onUnmounted(() => {
  appStore.navShow = true;
})
import SideLeftMain from './components/SideLeftMain.vue'
import SideLeftSub from './components/SideLeftSub.vue'
import HeaderTopBar from './components/HeaderTopBar.vue'
import PageProductList from './components/PageContent.vue'

const mainSideRef = ref(null)
</script>

<style scoped>
.layout-root {
  display: flex;
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  overflow: hidden;
}
.right-wrap {
  flex: 1;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
/* 主导航拖拽线 */
.resizer-main {
  width: 4px;
  background: #e4e7ed;
  cursor: col-resize;
  flex-shrink: 0;
  transition: background 0.15s;
}
.resizer-main:hover {
  background: #409eff;
}
</style>