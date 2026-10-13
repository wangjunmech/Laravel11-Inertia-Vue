<template>
  <div class="tree-wrap">
    <div class="tree-title">产品分类</div>
    <el-tree
      :data="store.categoryTree"
      show-checkbox={false}
      node-key="id"
      default-expand-all
    />
  </div>
</template>

<script setup>
import { useProcessStore } from '@/stores/useProcessStore.js'
const store = useProcessStore()
</script>

<style scoped>
.tree-wrap {
  padding:12px;
  height:100%;
  box-sizing: border-box;
}
.tree-title {
  font-weight:bold;
  margin-bottom:8px;
}
</style>
