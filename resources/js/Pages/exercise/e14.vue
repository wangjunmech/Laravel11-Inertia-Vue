<!-- resources\js\Pages\exercise\e13.vue -->
<template>
<div class="p-6 mx-auto max-w-screen-x1">
  <div>
      <!-- 父容器开启相对定位，给按钮做参照物 -->
      <div class="relative w-full">            
        <FileManager/>
      </div>    
  </div>
</div>
</template>
<script setup>
import { ref, computed,onUnmounted  } from 'vue'
import FileManager from "../../Components/FileManager2.vue";
// 组件里改成这样导入pinia
import { useAppStore } from '../../stores/app.js'// 直接从Store的 app.js 导入pinia
const appStore = useAppStore()
appStore.navShow = 0;//控制头部菜单显示
onUnmounted(() => {
  appStore.navShow = true;
})
</script>
<style scoped>

</style>