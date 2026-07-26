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
          v-model="item.tempLabel"
          @blur="saveLabel(item, $event)"
          @keyup.enter="$event.target.blur()"
          @focus="$event.target.select()"
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
import { ref, watch, provide, inject } from 'vue'

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

// 全局自增 ID 计数器，彻底解决拼接 ID 带来的碰撞冲突风险
const globalMaxId = props.level === 0 ? ref(0) : inject('globalMaxId')
const isVerifying = props.level === 0 ? ref(false) : inject('globalIsVerifying')

const localShowInfo = ref(props.showIdLevel)
watch(() => props.showIdLevel, (newVal) => { localShowInfo.value = newVal })
const vFocus = { mounted: (el) => el.focus() }

// 初始化：由根节点扫描并同步当前全树的最大 ID
if (props.level === 0) {
  const initMaxId = (nodes) => {
    let max = 0
    const scan = (list) => {
      for (const n of list) {
        const idNum = Number(n.id)
        if (!isNaN(idNum) && idNum > max) max = idNum
        if (n.children) scan(n.children)
      }
    }
    scan(nodes)
    return max
  }
  globalMaxId.value = initMaxId(props.treeData)
}

// 开启编辑状态
const startEdit = (item) => {
  item.tempLabel = item.label 
  item.isEdit = true
}

// 保存逻辑
const saveLabel = (item, event) => {
  if (!item.isEdit || isVerifying.value) return 

  const rawLabel = item.tempLabel || ''
  const trimmedLabel = rawLabel.trim().replace(/\s/g, "")
   
  // 1. 空值拦截
  if (!trimmedLabel) {
    isVerifying.value = true
    alert('节点名称不能为空！')
    
    setTimeout(() => {
      if (event && event.target) {
        event.target.focus()
      }
      isVerifying.value = false
    }, 50)
    return
  }

  // 2. 全局重名拦截
  if (props.noRepeatName && trimmedLabel !== item.label) {
    const isExist = checkLabelExistsGlobal(trimmedLabel, rootTreeRef, item.id)
    if (isExist) {
      isVerifying.value = true
      alert(`名称【${trimmedLabel}】已存在，请换一个名称！`)
      
      setTimeout(() => {
        if (event && event.target) {
          event.target.focus()
          event.target.select()
        }
        isVerifying.value = false 
      }, 50)
      return
    }
  }

  // 3. 校验无误，正式写入数据源
  item.label = trimmedLabel
  item.isEdit = false
  item.isNew = false
  delete item.tempLabel // 成功落盘后清除临时缓存变量
  emit('update:tree-data', [...props.treeData])
}

// 添加子节点
const addChild = (item) => {
  if (!item.children) item.children = []
  
  // 安全的全局自增 ID
  globalMaxId.value += 1
  const newId = globalMaxId.value
  
  // 扫描全树（包含其他正处于编辑状态节点的 tempLabel）来生成唯一的新名称
  const autoLabel = generateUniqueLabel(rootTreeRef)

  const newField = { 
    id: newId, 
    label: autoLabel,
    tempLabel: autoLabel, // 核心：创建时直接把临时编辑名也锁死在当前节点上
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
  if (props.level === 0 && props.treeData.length <= 1) { 
    alert('唯一的根节点禁止删除！'); 
    return 
  }
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
    targetNode.children.push(dragNode)
    targetNode.isOpen = true
  } else {
    const parentNode = findParentGlobal(targetId, fullTree)
    const siblings = parentNode ? parentNode.children : fullTree
    const tIndex = siblings.findIndex(n => n.id === targetId)

    if (position === 'before') {
      siblings.splice(tIndex, 0, dragNode)
    } else {
      siblings.splice(tIndex + 1, 0, dragNode)
    }
  }

  emit('update:tree-data', fullTree)
}

// 根节点向全树分发全局变量
if (props.level === 0) {
  provide('globalDraggingId', globalDraggingId)
  provide('globalDropTargetId', globalDropTargetId)
  provide('globalDropPosition', globalDropPosition)
  provide('executeGlobalMove', rootMoveNodeCenter)
  provide('rootTreeRef', rootTreeRef)
  provide('globalIsVerifying', isVerifying) 
  provide('globalMaxId', globalMaxId) 
}
</script>

<script>
// ========================================================
// 🛰️ 全局纯工具函数集（脱离 setup 闭包）
// ========================================================

const checkLabelExistsGlobal = (label, nodes, currentId) => {
  for (const node of nodes) {
    // 校验重名时，如果节点正在编辑中，比对临时 tempLabel，否则比对正式 label
    const activeLabel = node.isEdit && node.tempLabel ? node.tempLabel : node.label
    if (activeLabel === label && node.id !== currentId) {
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
  const checkAndSetMax = (labelStr) => {
    if (labelStr && labelStr.startsWith('节点')) {
      const numPart = labelStr.replace('节点', '')
      if (/^\d+$/.test(numPart)) {
        const num = parseInt(numPart, 10)
        if (num > maxNum) maxNum = num
      }
    }
  }

  // 深度扫描全树：同时侦测“已落盘名字”与“编辑中的临时名字”
  const scanLabels = (nodes) => {
    for (const node of nodes) {
      if (node.isEdit && node.tempLabel) {
        checkAndSetMax(node.tempLabel)
      } else {
        checkAndSetMax(node.label)
      }
      if (node.children) scanLabels(node.children)
    }
  }
  
  scanLabels(rootTree)
  return `节点${maxNum + 1}`
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
</script>


<style scoped>
.tree-item-wrap { display: flex; flex-direction: column; border-left: #db83e7 1px dashed; margin-left: 5px; padding-left: 5px; }
.tree-item-wrap.is-dragging { opacity: 0.9; filter: grayscale(40%); background-color: #9eedc0;  }
.tree-node { display: flex; align-items: center; margin: 4px 0; padding: 4px 8px; border-radius: 4px; height: 32px; position: relative; transition: background-color 0.1s, border 0.1s; box-sizing: border-box; border-top: 2px solid transparent; border-bottom: 2px solid transparent; }
.tree-node.bgactive { background-color: #9eedc0; }
.tree-node.drop-as-child { background-color: #c1e9af !important; outline: 2px dashed #c5eeb0; }
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
