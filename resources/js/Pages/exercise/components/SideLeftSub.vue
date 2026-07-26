<template>
<div class="side-sub">
  <div class="back-bar">
    <span>← 返回设计</span>
  </div>
  <div class="sub-menu">
    <div class="sub-item active">管理产品</div>
    <div class="sub-item">功能设置</div>
    <div class="sub-item">添加产品</div>
    <div class="sub-item">收货设置</div>
    <div class="sub-item">产品参数</div>
    <div class="sub-item">产品分类</div>
    <div class="sub-item">产品标签</div>
  </div>
</div>
</template>

<script setup></script>

<style scoped>
.side-sub {
  width: 140px;
  background: #f7f8fa;
  border-right: 1px solid #e4e7ed;
  flex-shrink: 0;
  height: 100%;
  overflow-y: auto;
}
.back-bar {
  height: 64px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  border-bottom: 1px solid #e4e7ed;
  cursor: pointer;
}
.sub-menu {
  padding: 10px 0;
}
.sub-item {
  padding: 10px 16px;
  cursor: pointer;
  color: #4e5969;
}
.sub-item.active {
  background: #e8f3ff;
  color: #2563eb;
}
.sub-item:hover:not(.active) {
  background: #eef0f3;
}
</style>