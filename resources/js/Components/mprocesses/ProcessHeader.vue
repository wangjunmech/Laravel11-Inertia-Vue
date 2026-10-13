<template>
  <div class="process-header">
    <el-button type="primary" @click="$emit('save')">保存工艺</el-button>
    <el-button @click="$emit('import')">导入JSON</el-button>
    <el-button @click="$emit('export')">导出JSON</el-button>
    <el-button type="danger" @click="$emit('reset')">清空画布</el-button>
  </div>
</template>

<script setup>
</script>

<style scoped>
.process-header {
  padding: 10px 16px;
  border-bottom: 1px solid #eee;
}
</style>
