<template>
  <div class="e35-container">
    <!-- 顶部工具栏 -->
    <ProcessHeader
      @save="handleSave"
      @import="handleImport"
      @export="handleExport"
      @reset="handleReset"
    />
    <div class="e35-body">
      <!-- 左侧分类树 -->
      <ProcessCategoryTree class="left-tree" />
      <!-- 中间素材库 -->
      <ProcessMaterialLib class="middle-lib" @drag-material="handleDragMaterial" />
      <!-- 右侧画布 -->
      <ProcessCanvas
        ref="canvasRef"
        class="right-canvas"
        @node-dblclick="onNodeDblClick"
      />
    </div>
    <!-- 节点配置抽屉 -->
    <ProcessNodeConfig
      v-model:open="nodeConfigOpen"
      :node="store.currentNode"
      @update-node="handleUpdateNode"
    />
    <!-- 确认弹窗 -->
    <ProcessConfirmDialog ref="confirmRef" />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import { useProcessStore } from '@/stores/useProcessStore.js'
import ProcessHeader from '@/Components/mprocesses/ProcessHeader.vue'
import ProcessCategoryTree from '@/Components/mprocesses/ProcessCategoryTree.vue'
import ProcessMaterialLib from '@/Components/mprocesses/ProcessMaterialLib.vue'
import ProcessCanvas from '@/Components/mprocesses/ProcessCanvas.vue'
import ProcessNodeConfig from '@/Components/mprocesses/ProcessNodeConfig.vue'
import ProcessConfirmDialog from '@/Components/mprocesses/ProcessConfirmDialog.vue'


const store = useProcessStore()
const canvasRef = ref(null)
const confirmRef = ref(null)
const nodeConfigOpen = ref(false)

// 素材拖拽完成，新增节点到画布
const handleDragMaterial = (material) => {
  canvasRef.value.addNodeFromMaterial(material)
}

// 双击画布节点
const onNodeDblClick = (node) => {
  store.setCurrentNode(node)
  nodeConfigOpen.value = true
}

// 更新节点参数
const handleUpdateNode = (newNodeData) => {
  canvasRef.value.updateNode(newNodeData)
}

// 保存
const handleSave = async () => {
  const graphJson = canvasRef.value.getGraphData()
  console.log('保存工艺数据：', graphJson)
  // TODO: axios post 提交后端
}

// 导出JSON
const handleExport = () => {
  const graphJson = canvasRef.value.getGraphData()
  const blob = new Blob([JSON.stringify(graphJson, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  a.download = '工艺编排.json'
  a.click()
  URL.revokeObjectURL(url)
}

// 导入JSON
const handleImport = () => {
  const input = document.createElement('input')
  input.type = 'file'
  input.accept = '.json'
  input.onchange = async (e) => {
    const file = e.target.files[0]
    const text = await file.text()
    const data = JSON.parse(text)
    canvasRef.value.setGraphData(data)
  }
  input.click()
}

// 清空画布
const handleReset = () => {
  confirmRef.value.show('确认清空画布所有工艺节点？', () => {
    canvasRef.value.clearCanvas()
  })
}
</script>

<style scoped>
.e35-container {
  height: 100vh;
  display: flex;
  flex-direction: column;
  overflow: hidden;
}
.e35-body {
  flex: 1;
  display: flex;
}
.left-tree {
  width: 240px;
  border-right: 1px solid #eee;
}
.middle-lib {
  width: 280px;
  border-right: 1px solid #eee;
}
.right-canvas {
  flex: 1;
}
</style>
