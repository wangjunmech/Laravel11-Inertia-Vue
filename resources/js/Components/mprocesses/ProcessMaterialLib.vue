<template>
  <div class="lib-wrap">
    <div class="lib-title">工艺模块素材</div>
    <div class="material-list">
      <div
        v-for="item in store.materialList"
        :key="item.id"
        class="material-card"
        draggable="true"
        @dragstart="handleDragStart($event, item)"
      >
        {{ item.name }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { useProcessStore } from '@/stores/useProcessStore.js'
const store = useProcessStore()
const emit = defineEmits(['drag-material'])

const handleDragStart = (e, material) => {
  e.dataTransfer.setData('material', JSON.stringify(material))
}
</script>

<style scoped>
.lib-wrap {
  padding:12px;
  height:100%;
  box-sizing: border-box;
  overflow-y:auto;
}
.lib-title {
  font-weight:bold;
  margin-bottom:12px;
}
.material-card {
  padding: 10px;
  border:1px solid #ccc;
  border-radius:6px;
  margin-bottom:8px;
  cursor: grab;
  background:#fff;
}
.material-card:active {
  cursor: grabbing;
}
</style>
