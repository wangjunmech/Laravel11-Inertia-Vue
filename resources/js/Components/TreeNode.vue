<template>
  <div
    v-for="(item, index) in treeData"
    :key="item.id || index"
    :id="'node-wrap-' + item.id"
    class="tree-item-wrap"
    :class="{ 
      'is-dragging': globalDraggingId === item.id 
    }"
  >
    <div
      class="tree-node"
      :class="{
        'new-node': item.isNew,
        bgactive: item.bgactive,
        'drop-as-child': globalDropTargetId === item.id && globalDropPosition === 'inside',
        'drop-prev': globalDropTargetId === item.id && globalDropPosition === 'before',
        'drop-next': globalDropTargetId === item.id && globalDropPosition === 'after'
      }"
      @mouseenter="item.bgactive = true"
      @mouseleave="item.bgactive = false"
      @dragover.prevent.stop="handleDragOver($event, item)"
      @dragleave.stop="handleDragLeave"
      @drop.prevent.stop="handleDrop($event, item)"
    >
      <div class="tree-arrow" @click="item.isOpen = !item.isOpen">
        <template v-if="item.children && item.children.length > 0">
          {{ item.isOpen ? '▼' : '▶' }}
        </template>
        <template v-else>
          {{ item.isOpen ? '▽' : '▷' }}
        </template>
      </div>

      <div class="tree-label">
        <input
          v-if="item.isEdit"
          v-model="editingLabel"
          @blur="saveLabel(item, $event)"
          @keyup.enter="saveLabel(item, $event)"
          class="label-input"
          v-focus
        />
        <span
          v-else
          @click="startEdit(item)"
          class="label-text"
        >
          {{ item.label }}
        </span>
      </div>

      <div class="node-info" @click="localShowInfo = !localShowInfo">
        <span v-if="!localShowInfo" class="eye-icon">👁</span>
        <div v-else class="info-tags">
          <span>ID: {{ item.id }}</span>
          <span>Lv: {{ level }}</span>
        </div>
      </div>
          
      <div class="tree-add" @click="addChild(item)" title="添加子节点">𒋲</div>
      <div class="tree-delete" @click="deleteNode(index)" title="删除当前节点">❌</div>
      
      <div class="tree-drag">
        <span
          class="dragMark"
          draggable="true"
          @dragstart="handleDragStart($event, item)"
          @dragend="handleDragEnd"
          title="按下拖动移动节点"
        >⇅</span>
      </div>
    </div>

    <div
      v-if="item.isOpen && item.children && item.children.length > 0"
      class="tree-children"
    >
      <TreeNode
        :tree-data="item.children"
        :level="level + 1"
        @update:tree-data="(val) => updateChildren(item, val)"
        :showIdLevel="showIdLevel"
        :noRepeatName="noRepeatName"
      />
    </div>
  </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch, provide, inject } from 'vue'

const props = defineProps({
  treeData: { type: Array, default: () => [] },
  level: { type: Number, default: 0 },
  showIdLevel: { type: Boolean, default: true },
  noRepeatName: { type: Boolean, default: true }
})

const emit = defineEmits(['update:tree-data'])

// ========================================================
// 核心控制链路与全局状态
// ========================================================
const globalDraggingId = props.level === 0 ? ref(null) : inject('globalDraggingId')
const globalDropTargetId = props.level === 0 ? ref(null) : inject('globalDropTargetId')
const globalDropPosition = props.level === 0 ? ref(null) : inject('globalDropPosition')
const executeGlobalMove = props.level === 0 ? null : inject('executeGlobalMove')
const rootTreeRef = props.level === 0 ? props.treeData : inject('rootTreeRef')

const localShowInfo = ref(props.showIdLevel)
watch(() => props.showIdLevel, (newVal) => { localShowInfo.value = newVal })
const vFocus = { mounted: (el) => el.focus() }

// 当前层级的编辑缓冲区
const editingLabel = ref('')

// 🌟 【核心修复关键】：为了防止递归树多层 blur 事件链踩踏，使用 inject/provide 共享或全局唯一的校验锁
// 如果是根节点自己声明一个 ref，子节点则通过 inject 注入，保证整棵大树在同一时刻【只有一个全局弹窗锁】
const isVerifying = props.level === 0 ? ref(false) : inject('globalIsVerifying')

// 深度监听劫持：解决新增子节点由于响应式断层导致的 input 空白问题
watch(
  () => props.treeData,
  (newItems) => {
    if (!newItems) return
    const activeEditingItem = newItems.find(item => item.isEdit)
    if (activeEditingItem && !editingLabel.value) {
      editingLabel.value = activeEditingItem.label
    }
  },
  { deep: true, immediate: true }
)

// 开启编辑状态
const startEdit = (item) => {
  editingLabel.value = item.label 
  item.isEdit = true
}

// 🌟 【终极重构】：彻底斩断死循环链路的保存函数
const saveLabel = (item, event) => {
  // 1. 如果当前节点不在编辑状态，或者全局任何地方正在弹窗校验，直接强制拦截！
  if (!item.isEdit || isVerifying.value) return 

  const trimmedLabel = editingLabel.value.trim()
  
  // 2. 空值拦截
  if (!trimmedLabel) {
    isVerifying.value = true // 上锁
    alert('节点名称不能为空！')
    
    // 使用 setTimeout 避开浏览器的原生失焦事件排队，完成安全聚焦
    setTimeout(() => {
      if (event && event.target) {
        event.target.focus()
      }
      isVerifying.value = false // 聚焦完成后再解锁
    }, 50)
    return
  }

  // 3. 全局重名拦截
  if (props.noRepeatName && trimmedLabel !== item.label) {
    const isExist = checkLabelExistsGlobal(trimmedLabel, rootTreeRef, item.id)
    if (isExist) {
      isVerifying.value = true // 🌟 立即封锁全局一切失焦行为
      
      alert(`名称【${trimmedLabel}】在全局已存在，请换一个名称！`)
      
      // 🌟 【死循环解法核心】：将聚焦和解锁丢进宏任务队列，等浏览器把因点击引发的失焦全部处理完毕后执行
      setTimeout(() => {
        if (event && event.target) {
          event.target.focus()  // 强行把焦点抓回来
          event.target.select() // 全选文字，让用户体验拉满，直接打字就能覆盖
        }
        // 必须在焦点安稳回到 input 内部后，才能解开这把锁，彻底杜绝无限弹窗
        isVerifying.value = false 
      }, 50)
      return
    }
  }

  // 4. 校验无误，写入真正的数据源
  item.label = trimmedLabel
  item.isEdit = false
  item.isNew = false
  editingLabel.value = ''
  emit('update:tree-data', [...props.treeData])
}

// 添加子节点
const addChild = (item) => {
  if (!item.children) item.children = []
  const idx = item.children.length + 1
  const newId = Number(`${item.id}${idx}`)
  
  // 自动扫描全树，计算“新节点N+1”
  const autoLabel = generateUniqueLabel(rootTreeRef)

  const newField = { 
    id: newId, 
    label: autoLabel, 
    children: [], 
    isOpen: false, 
    isEdit: true, 
    isNew: true 
  }

  item.children.push(newField)
  item.isOpen = true

  emit('update:tree-data', [...props.treeData])
}

const deleteNode = (index) => {
  if (props.level === 0) { alert('根节点禁止删除！'); return }
  const targetItem = props.treeData[index]
  const hasChildren = targetItem.children && targetItem.children.length > 0
  const msg = hasChildren ? '该节点包含子节点，删除后所有后代一并移除，确认删除？' : '确认删除当前节点？'
  if (confirm(msg)) {
    const updatedData = [...props.treeData]
    updatedData.splice(index, 1) 
    emit('update:tree-data', updatedData)
  }
}

const updateChildren = (item, newChildren) => {
  item.children = newChildren
  emit('update:tree-data', [...props.treeData])
}

// ========================================================
// 拖拽控制中心
// ========================================================
const handleDragStart = (event, item) => {
  globalDraggingId.value = item.id
  event.dataTransfer.effectAllowed = 'move'
  event.dataTransfer.setData('text/plain', item.id.toString())
  const nodeWrapEl = document.getElementById(`node-wrap-${item.id}`)
  if (nodeWrapEl) {
    event.dataTransfer.setDragImage(nodeWrapEl, 10, 15)
  }
}

const handleDragOver = (event, targetItem) => {
  if (!globalDraggingId.value || globalDraggingId.value === targetItem.id) {
    globalDropTargetId.value = null
    return
  }
  globalDropTargetId.value = targetItem.id
  const rect = event.currentTarget.getBoundingClientRect()
  const relativeY = event.clientY - rect.top
  const height = rect.height

  if (relativeY < height * 0.25) {
    globalDropPosition.value = 'before'
  } else if (relativeY > height * 0.75) {
    globalDropPosition.value = 'after'
  } else {
    globalDropPosition.value = 'inside'
  }
}

const handleDragLeave = () => {
  globalDropTargetId.value = null
  globalDropPosition.value = null
}

const handleDrop = (event, targetItem) => {
  const dragId = Number(event.dataTransfer.getData('text/plain')) || globalDraggingId.value
  if (!dragId || dragId === targetItem.id) {
    handleDragEnd()
    return
  }
  if (props.level === 0) {
    rootMoveNodeCenter(dragId, targetItem.id, globalDropPosition.value)
  } else {
    executeGlobalMove(dragId, targetItem.id, globalDropPosition.value)
  }
  handleDragEnd()
}

const handleDragEnd = () => {
  globalDraggingId.value = null
  globalDropTargetId.value = null
  globalDropPosition.value = null
}

const rootMoveNodeCenter = (dragId, targetId, position) => {
  const fullTree = JSON.parse(JSON.stringify(props.treeData))
  const dragNode = findNodeGlobal(dragId, fullTree)
  const targetNode = findNodeGlobal(targetId, fullTree)

  if (!dragNode || !targetNode) return
  if (isDescendantGlobal(dragNode, targetNode)) {
    alert('操作失败：不能将节点放置到它自己的后代节点中！')
    return
  }

  removeNodeGlobal(dragId, fullTree)

  if (position === 'inside') {
    if (!targetNode.children) targetNode.children = []
    const newIdx = targetNode.children.length + 1
    dragNode.id = Number(`${targetNode.id}${newIdx}`)
    fixChildrenIds(dragNode)
    targetNode.children.push(dragNode)
    targetNode.isOpen = true
  } else {
    const parentNode = findParentGlobal(targetId, fullTree)
    const siblings = parentNode ? parentNode.children : fullTree
    const tIndex = siblings.findIndex(n => n.id === targetId)

    const parentId = parentNode ? parentNode.id : 0
    const newIdx = siblings.length + 2 
    dragNode.id = parentId === 0 ? newIdx : Number(`${parentId}${newIdx}`)
    fixChildrenIds(dragNode)

    if (position === 'before') {
      siblings.splice(tIndex, 0, dragNode)
    } else {
      siblings.splice(tIndex + 1, 0, dragNode)
    }
  }

  emit('update:tree-data', fullTree)
}

// 🌟 根节点向全树分发全局变量
if (props.level === 0) {
  provide('globalDraggingId', globalDraggingId)
  provide('globalDropTargetId', globalDropTargetId)
  provide('globalDropPosition', globalDropPosition)
  provide('executeGlobalMove', rootMoveNodeCenter)
  provide('rootTreeRef', rootTreeRef)
  provide('globalIsVerifying', isVerifying) // 分发全局唯一校验状态锁
}
</script>

<script>
// ========================================================
// 🛰️ 全局纯工具函数集（脱离 setup 闭包）
// ========================================================

const checkLabelExistsGlobal = (label, nodes, currentId) => {
  for (const node of nodes) {
    if (node.label === label && node.id !== currentId) {
      return true
    }
    if (node.children && node.children.length > 0) {
      const foundInChild = checkLabelExistsGlobal(label, node.children, currentId)
      if (foundInChild) return true
    }
  }
  return false
}

const generateUniqueLabel = (rootTree) => {
  let maxNum = 0
  const scanLabels = (nodes) => {
    for (const node of nodes) {
      if (node.label && node.label.startsWith('新节点')) {
        const numPart = node.label.replace('新节点', '')
        if (/^\d+$/.test(numPart)) {
          const num = parseInt(numPart, 10)
          if (num > maxNum) maxNum = num
        }
      }
      if (node.children) scanLabels(node.children)
    }
  }
  scanLabels(rootTree)
  return `新节点${maxNum + 1}`
}

const findNodeGlobal = (id, nodes) => {
  for (const node of nodes) {
    if (node.id === id) return node
    if (node.children) {
      const found = findNodeGlobal(id, node.children)
      if (found) return found
    }
  }
  return null
}

const removeNodeGlobal = (id, nodes) => {
  for (let i = 0; i < nodes.length; i++) {
    if (nodes[i].id === id) {
      nodes.splice(i, 1)
      return true
    }
    if (nodes[i].children) {
      if (removeNodeGlobal(id, nodes[i].children)) return true
    }
  }
  return false
}

const findParentGlobal = (childId, nodes, parent = null) => {
  for (const node of nodes) {
    if (node.id === childId) return parent
    if (node.children) {
      const found = findParentGlobal(childId, node.children, node)
      if (found) return found
    }
  }
  return null
}

const isDescendantGlobal = (parent, potentialDescendant) => {
  if (!parent.children || parent.children.length === 0) return false
  for (const child of parent.children) {
    if (child.id === potentialDescendant.id) return true
    if (isDescendantGlobal(child, potentialDescendant)) return true
  }
  return false
}

const fixChildrenIds = (node) => {
  if (!node.children || node.children.length === 0) return
  node.children.forEach((child, i) => {
    child.id = Number(`${node.id}${i + 1}`)
    fixChildrenIds(child)
  })
}
</script>

<style scoped>
.tree-item-wrap { display: flex; flex-direction: column; border-left: #db83e7 1px dashed; margin-left: 5px; padding-left: 5px; }
.tree-item-wrap.is-dragging { opacity: 0.3; filter: grayscale(40%); }
.tree-node { display: flex; align-items: center; margin: 4px 0; padding: 4px 8px; border-radius: 4px; height: 32px; position: relative; transition: background-color 0.1s, border 0.1s; box-sizing: border-box; border-top: 2px solid transparent; border-bottom: 2px solid transparent; }
.tree-node.bgactive { background-color: #eef7ea; }
.tree-node.drop-as-child { background-color: #c1e9af !important; outline: 2px dashed #67c23a; }
.tree-node.drop-prev { border-top: 2px solid #db83e7 !important; background-color: rgba(219, 131, 231, 0.1); }
.tree-node.drop-next { border-bottom: 2px solid #db83e7 !important; background-color: rgba(219, 131, 231, 0.1); }
.tree-arrow { width: 20px; height: 20px; display: flex; align-items: center; justify-content: center; cursor: pointer; color: #888; font-size: 11px; margin-right: 6px; user-select: none; }
.tree-label { flex: 1; display: flex; align-items: center; }
.label-text { padding: 2px 6px; border-radius: 4px; cursor: pointer; font-size: 14px; color: #333; width: 100%; }
.label-text:hover { background: rgba(0, 0, 0, 0.04); }
.label-input { border: 1px solid #409eff; border-radius: 4px; padding: 2px 6px; outline: none; min-width: 120px; font-size: 14px; }
.tree-add { width: 24px; height: 24px; display: flex; align-items: center; justify-content: center; color: #409eff; font-weight: bold; cursor: pointer; border-radius: 4px; font-size: 14px; }
.tree-add:hover { background: #e8f3ff; }
.tree-delete { width: 24px; height: 24px; margin-left: 8px; display: flex; align-items: center; justify-content: center; border-radius: 4px; cursor: pointer; font-size: 12px; }
.tree-delete:hover { background: #fee0e0; }
.tree-children { margin-left: 15px; }
.tree-drag { margin-left: 12px; }
.dragMark { cursor: move; color: #aaa; font-size: 14px; padding: 2px 4px; user-select: none;}
.dragMark:hover { color: #f56c6c; }
.new-node { background-color: #fff9c4 !important; }
.node-info { font-size: 11px; color: #888; white-space: nowrap; margin: 0 12px; user-select: none; }
.eye-icon { opacity: 0.5; }
.node-info:hover .eye-icon { opacity: 1; }
.info-tags { display: flex; gap: 8px; background: #f4f4f5; padding: 2px 6px; border-radius: 12px; }
</style>
