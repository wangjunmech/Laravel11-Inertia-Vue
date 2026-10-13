<template>
  <div ref="containerRef" class="canvas-wrap"></div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { Graph } from '@antv/g6'
import { useProcessStore } from '@/stores/useProcessStore.js'
const store = useProcessStore()
const emit = defineEmits(['node-dblclick'])
const containerRef = ref(null)
let graph = null

// 对外暴露方法，父组件通过 ref 调用
defineExpose({
  addNodeFromMaterial,
  getGraphData,
  setGraphData,
  clearCanvas,
  updateNode
})

onMounted(() => {
  graph = new Graph({
    container: containerRef.value,
    width: containerRef.value.clientWidth,
    height: containerRef.value.clientHeight,
    modes: {
      default: ['drag-node', 'drag-canvas', 'zoom-canvas']
    },
    defaultNode: {
      size: [160, 80],
      type: 'rect',
      style: { fill: '#fff', stroke: '#2277ff', lineWidth: 2 },
      labelCfg: { style: { fontSize:14 } }
    },
    defaultEdge: {
      type: 'line',
      style: { stroke: '#666', lineWidth: 2 }
    }
  })

  // 双击节点事件
  graph.on('node:dblclick', (e) => {
    const nodeModel = e.item.getModel()
    emit('node-dblclick', nodeModel)
  })

  graph.render()
})

onUnmounted(() => {
  graph?.destroy()
})

// 从素材新增节点
function addNodeFromMaterial(material) {
  const id = `node_${Date.now()}`
  const node = {
    id,
    label: material.name,
    type: material.type,
    params: material.params,
    x: 100 + Math.random() * 120,
    y: 100 + Math.random() * 120
  }
  graph.addItem('node', node)
}

// 获取画布数据
function getGraphData() {
  return graph.save()
}

// 加载画布数据
function setGraphData(data) {
  graph.clear()
  graph.data(data)
  graph.render()
}

// 清空画布
function clearCanvas() {
  graph.clear()
}

// 更新节点
function updateNode(nodeData) {
  graph.updateItem(nodeData.id, nodeData)
}
</script>

<style scoped>
.canvas-wrap {
  width:100%;
  height:100%;
  border:1px solid #eee;
}
</style>
