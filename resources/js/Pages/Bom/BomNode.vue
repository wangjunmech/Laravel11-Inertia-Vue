<template>
  <div
    v-for="(item, index) in bomData"
    :key="item.id || index"
    :id="'node-wrap-' + item.id"
    class="flex flex-col border-l border-dashed border-[#db83e7]"
    :class="{
      'opacity-90 grayscale-40 bg-[#9eedc0]': globalDraggingId === item.id
    }"
  >
    <div
      class="flex items-center my-1 px-2 py-1 rounded h-8 relative transition-colors border-t-2 border-b-2 border-transparent box-border"
      :class="{
        'bg-[#9eedc0]': item.bgactive,
        'bg-[#c1e9af] outline-2 outline-dashed outline-[#c5eeb0]': globalDropTargetId === item.id && globalDropPosition === 'inside',
        'border-t-2 border-[#db83e7] ': globalDropTargetId === item.id && globalDropPosition === 'before',
        'border-b-2 border-[#db83e7] ': globalDropTargetId === item.id && globalDropPosition === 'after',
        'bg-[#fff9c4]': item.isNew
      }"
      @mouseenter="item.bgactive = true"
      @mouseleave="item.bgactive = false"
      @dragover.prevent.stop="handleDragOver($event, item)"
      @dragleave.stop="handleDragLeave"
      @drop.prevent.stop="handleDrop($event, item)"
    >
      <!-- 展开收起箭头 -->
      <div
        class="w-5 h-5 flex items-center justify-center mr-1.5 text-[#888] text-xs select-none"
        @click="item.isOpen = !item.isOpen"
      >
        <template v-if="item.children && item.children.length > 0">
          {{ item.isOpen ? '▼' : '▶' }}
        </template>
        <template v-else>
          {{ item.isOpen ? '▽' : '▷' }}
        </template>
      </div>

      <!-- 左侧节点名称区域 -->
      <div class="flex flex-1 items-center bg-red-200">
        <!-- 编辑按钮下拉菜单容器 -->
        <div class="relative inline-block mr-1" v-click-outside="closeAllMenu">
          <div
            title="单击打开快捷添加操作菜单"
            class="bg-yellow-200 hover:bg-yellow-300 rounded-full w-6 h-6 flex items-center justify-center cursor-pointer transition-colors"
            @click.stop="toggleMenu(item.id)"
          >✏</div>

          <div
            v-if="activeMenuId === item.id"
            class="absolute z-50 top-full left-0 mt-1 bg-white border border-gray-200 rounded shadow-lg py-1 min-w-[115px] max-w-[220px] overflow-hidden"
          >
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
              @click="handleAddSibling(item, index)"
            >添加（同级）</div>
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
              @click="handleAddChild(item)"
            >添加子物料</div>
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 text-red-500 transition-colors"
              @click="handleDelete(index)"
            >删除</div>
          </div>
        </div>

        <!-- 名称编辑输入框 -->
        <input
          v-if="item.isEdit"
          v-model="item.tempLabel"
          @blur="saveLabel(item, bomData, $event)"
          @keyup.enter="$event.target.blur()"
          @focus="$event.target.select()"
          class="border border-[#409eff] rounded px-1.5 py-0.5 outline-none min-w-[120px] text-sm"
          v-focus
        />
        <span
          v-else
          @click="startEdit(item)"
          class="px-1.5 py-0.5 rounded text-sm text-[#333] w-28 hover:bg-black/5 cursor-pointer"
        >
          {{ item.label }}
        </span>
      </div>
      
      <div class="flex rounded-lg bg-blue-400 m-2 px-5 items-center justify-center min-w-[50px]">
        <!-- 全局流水序号：仅替换插值，DOM结构完全保留 -->
        
          {{ item.sn }}
        
      </div>

      <!-- 8个BOM字段可点击编辑单元格 -->
      <div class="flex">
        <!-- 1.产品编号【新增重复校验】 -->
        <!-- 编辑按钮下拉菜单容器 -->
        <div class="relative inline-block mr-1 flex bg-green-400 m-2 px-2 items-center justify-center min-w-[110px] cursor-pointer" v-click-outside="closeItemMenu">
          <div
            title="单击打开物料相关查询操作菜单"
            @click.stop="toggleItemMenu(item.id)"
          >{{ item.label }}</div>

          <div
            v-if="activeItemId === item.id"
            class="absolute z-50 top-full left-0 mt-1 bg-white border border-gray-200 rounded shadow-lg py-1 min-w-[115px] max-w-[220px] overflow-hidden"
          >
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
              @click="ItemDetail(item, index)"
            >物料详情</div>
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
              @click="WhereToUse(item)"
            >使用场合</div>

          </div>
        </div>

        <!-- 2.品名英文 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[60px]">
          <input
            v-if="item.editField === 'nameEn'"
            v-model="item.nameEn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-28 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <span v-else @click="openEditField(item, 'nameEn')" class="cursor-pointer text-sm">
            {{ item.nameEn }}
          </span>
        </div>

        <!-- 3.品名中文 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[60px]">
          <input
            v-if="item.editField === 'nameCn'"
            v-model="item.nameCn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-28 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <span v-else @click="openEditField(item, 'nameCn')" class="cursor-pointer text-sm">
            {{ item.nameCn }}
          </span>
        </div>

        <!-- 4.用量 -->
        <div class="flex bg-red-200 m-2 px-2 items-center justify-center rounded-sm min-w-[60px]">
          <input
            v-if="item.editField === 'quantity'"
            v-model.number="item.quantity"
            type="number" min="0" step="0.01"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-16 border border-blue-500 px-1 py-0.5 text-sm outline-none "
            v-focus
          />
          <span v-else @click="openEditField(item, 'quantity')" class="cursor-pointer text-sm min-w-[30px]">
            {{ item.quantity }}
          </span>
        </div>

        <!-- 5.单位 -->
        <div class="flex bg-red-400 m-2 px-2 items-center justify-center rounded-sm min-w-[70px]">
          <input
            v-if="item.editField === 'unit'"
            v-model="item.unit"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-16 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <span v-else @click="openEditField(item, 'unit')" class="cursor-pointer text-sm min-w-[60px]">
            {{ item.unit }}
          </span>
        </div>

        <!-- 6.损耗率 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[100px]">
          <input
            v-if="item.editField === 'wasteRate'"
            v-model.number="item.wasteRate"
            type="number" min="0" max="100" step="0.1"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-20 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <span v-else @click="openEditField(item, 'wasteRate')" class="cursor-pointer text-sm min-w-[60px]">
            {{ item.wasteRate }}%
          </span>
        </div>

        <!-- 7.采购单价 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[100px]">
          <input
            v-if="item.editField === 'price'"
            v-model.number="item.price"
            type="number" min="0" step="0.01"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-28 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <span v-else @click="openEditField(item, 'price')" class="cursor-pointer text-sm min-w-[60px]">
            {{ item.price }}
          </span>
        </div>

        <!-- 8.小计 只读 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[100px]">
          <span class="text-sm font-semibold">{{ item.subtotal.toFixed(2) }}</span>
        </div>
      </div>

      <!-- 右侧功能按钮区 -->
      <div class="flex items-center">
        <div
          class="text-[11px] text-[#888] whitespace-nowrap mx-3 select-none"
          @click="localShowInfo = !localShowInfo"
        >
          <span v-if="!localShowInfo" class="opacity-50 hover:opacity-100">👁</span>
          <div v-else class="flex gap-2 bg-[#f4f4f5] px-1.5 py-0.5 rounded-full">
            <span>ID: {{ item.id }}</span>
            <span>Lv: {{ level }}</span>
          </div>
        </div>

        <div
          class="w-6 h-6 flex items-center justify-center text-[#409eff] font-bold cursor-pointer rounded text-sm hover:bg-[#e8f3ff]"
          @click="addChild(item)"
          title="添加子节点"
        >𒋲</div>

        <div
          class="w-6 h-6 flex items-center justify-center ml-2 cursor-pointer rounded text-xs hover:bg-[#fee0e0]"
          @click="deleteNode(index)"
          title="删除当前节点"
        >❌</div>

        <div class="ml-3">
          <span
            class="text-[#aaa] text-sm px-1 py-0.5 cursor-move select-none hover:text-[#f56c6c]"
            draggable="true"
            @dragstart="handleDragStart($event, item)"
            @dragend="handleDragEnd"
            title="按下拖动移动节点"
          >⇅</span>
        </div>
      </div>
    </div>

    <!-- 子节点嵌套缩进 -->
    <div
      v-if="item.isOpen && item.children && item.children.length > 0"
      class="ml-[15px]"
    >
      <BomNode
        :bom-data="item.children"
        :level="level + 1"
        @update:bom-data="(val) => updateChildren(item, val)"
        :showIdLevel="showIdLevel"
        :noRepeatName="noRepeatName"
        :name-prefix="namePrefix"
        :digit-length="digitLength"
      />
    </div>
  </div>
</template>

<script setup>
import { ref, watch, provide, inject } from 'vue'

const props = defineProps({
  bomData: { type: Array, default: () => [] },
  level: { type: Number, default: 0 },
  showIdLevel: { type: Boolean, default: true },
  noRepeatName: { type: Boolean, default: true },
  namePrefix: { type: String, default: 'cod' },
  digitLength: { type: Number, default: 1 }
})

const emit = defineEmits(['update:bom-data'])

let globalDraggingId, globalDropTargetId, globalDropPosition, executeGlobalMove, rootbomRef
let globalMaxId, isVerifying, activeMenuId, closeAllMenu, toggleMenu, vClickOutside,activeItemId,toggleItemMenu
// 【新增】全局序号刷新方法变量
let refreshSerialNumber

if (props.level !== 0) {
  globalDraggingId = inject('globalDraggingId')
  globalDropTargetId = inject('globalDropTargetId')
  globalDropPosition = inject('globalDropPosition')
  executeGlobalMove = inject('executeGlobalMove')
  rootbomRef = inject('rootbomRef')
  globalMaxId = inject('globalMaxId')
  isVerifying = inject('globalIsVerifying')
  activeMenuId = inject('activeMenuId')
  activeItemId = inject('activeItemId') 
  closeAllMenu = inject('closeAllMenu')
  toggleMenu = inject('toggleMenu')
  toggleItemMenu = inject('toggleItemMenu')
  vClickOutside = inject('vClickOutside')
  // 【新增子组件接收刷新函数】
  refreshSerialNumber = inject('refreshSerialNumber')
} else {
  globalDraggingId = ref(null)
  globalDropTargetId = ref(null)
  globalDropPosition = ref(null)
  rootbomRef = props.bomData
  globalMaxId = ref(0)
  isVerifying = ref(false)
  activeMenuId = ref(null)
  activeItemId = ref(null)
  closeAllMenu = () => {
    activeMenuId.value = null
    activeItemId.value = null
  }

  // 切换快捷编辑菜单显示状态，显示或不显示 
  toggleMenu = (nodeId) => {
    activeMenuId.value = activeMenuId.value === nodeId ? null : nodeId
  }

  toggleItemMenu = (nodeId) => {
    activeItemId.value = activeItemId.value === nodeId ? null : nodeId
  }

  vClickOutside = {
    mounted(el, binding) {
      if (el._outsideHandler) return
      el._outsideHandler = (e) => {
        if (!el.contains(e.target)) binding.value()
      }
      document.addEventListener('click', el._outsideHandler)
    },
    unmounted(el) {
      if (el._outsideHandler) {
        document.removeEventListener('click', el._outsideHandler)
        delete el._outsideHandler
      }
    }
  }

  const initMaxId = (nodes) => {
    let max = 0
    const loop = (arr) => {
      arr.forEach(n => {
        const num = Number(n.id)
        if (!isNaN(num) && num > max) max = num
        if (n.children) loop(n.children)
      })
    }
    loop(nodes)
    return max
  }
  globalMaxId.value = initMaxId(props.bomData)

  // ===================== 【纯新增】DFS全局流水序号逻辑 =====================
  refreshSerialNumber = (tree = rootbomRef) => {
    let num = 0
    const dfs = (list) => {
      list.forEach(node => {
        node.sn = num++
        if (node.children?.length) dfs(node.children)
      })
    }
    dfs(tree)
  }
  // 首次加载自动编号
  refreshSerialNumber()
  // 监听外部传入数据变化，重新排序号
  watch(() => props.bomData, () => {
    rootbomRef = props.bomData
    refreshSerialNumber()
  }, { deep: true })
  // ======================================================================

  provide('globalDraggingId', globalDraggingId)
  provide('globalDropTargetId', globalDropTargetId)
  provide('globalDropPosition', globalDropPosition)
  provide('executeGlobalMove', rootMoveNodeCenter)
  provide('rootbomRef', rootbomRef)
  provide('globalIsVerifying', isVerifying)
  provide('globalMaxId', globalMaxId)
  provide('activeMenuId', activeMenuId)
  provide('closeAllMenu', closeAllMenu)
  provide('toggleMenu', toggleMenu)
  provide('toggleItemMenu', toggleItemMenu) 
  provide('vClickOutside', vClickOutside)
  // 【新增注入序号刷新函数】
  provide('refreshSerialNumber', refreshSerialNumber)
}

const localShowInfo = ref(props.showIdLevel)
watch(() => props.showIdLevel, (v) => { localShowInfo.value = v })
const vFocus = { mounted: el => el.focus() }

// 新建节点默认模板【追加 sn: 0】
const getDefaultNode = (nodeId, nodeName) => ({
  id: nodeId,
  label: nodeName,
  tempLabel: nodeName,
  children: [],
  isOpen: false,
  isEdit: true,
  isNew: true,
  editField: null,
  sn: 0, // 新增全局序号初始值
  productSn: 'x000001',
  nameEn: 'labelcover',
  nameCn: '标签外壳',
  quantity: 1,
  unit: 'pcs',
  wasteRate: 0,
  price: 0,
  subtotal: 0,
  bgactive: false
})

// 打开单元格编辑
const openEditField = (item, fieldKey) => {
  item.editField = fieldKey
}

// ========== 核心修改：重写saveField，增加productSn唯一性校验 ==========
const saveField = (item, event) => {
  // 只针对产品编号做重复校验
  if(item.editField === 'productSn'){
    const inputSn = item.productSn.trim()
    if(!inputSn){
      alert('产品编号不能为空')
      event?.target?.focus()
      return
    }

    // 校验编号是否重复
    let isRepeat = false
    if(props.noRepeatName){
      // 全局唯一：遍历整棵树
      isRepeat = checkProductSnGlobal(inputSn, rootbomRef, item.id)
    }else{
      // 同级局部唯一：只校验当前父节点下所有同级节点
      const siblingList = getCurrentSiblings(item, rootbomRef)
      isRepeat = checkProductSnSibling(inputSn, siblingList, item.id)
    }

    if(isRepeat){
      alert(props.noRepeatName ? '该产品编号在整个BOM中已存在，不可重复' : '同级下该产品编号已存在，请更换')
      event?.target?.focus()
      return
    }
  }

  // 原有全部计算逻辑保留不动
  item.editField = null
  item.subtotal = Number((item.quantity * item.price * (1 + item.wasteRate / 100)).toFixed(2))
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber() // 新增：保存后刷新全局序号
}

// 全局遍历校验产品编号
const checkProductSnGlobal = (sn, treeNodes, excludeId) => {
  for(const node of treeNodes){
    if(node.productSn === sn && node.id !== excludeId){
      return true
    }
    if(node.children && node.children.length){
      if(checkProductSnGlobal(sn, node.children, excludeId)){
        return true
      }
    }
  }
  return false
}

// 获取当前节点所有同级节点数组
const getCurrentSiblings = (targetItem, tree) => {
  // 查找父节点
  const parent = findParentGlobal(targetItem.id, tree)
  if(parent){
    return parent.children
  }else{
    // 根层级节点，同级就是根数组
    return tree
  }
}

// 同级范围内校验产品编号
const checkProductSnSibling = (sn, siblingArr, excludeId) => {
  return siblingArr.some(node => node.productSn === sn && node.id !== excludeId)
}

// 新增同级节点
const addSibling = (idx) => {
  if (props.level == 0) {
    alert('根节点禁止新增同级节点！')
    return
  }
  globalMaxId.value += 1
  const newId = globalMaxId.value
  const newLabel = generateUniqueLabel(rootbomRef, props.bomData, props.namePrefix, props.digitLength, props.noRepeatName)
  const newNode = getDefaultNode(newId, newLabel)

  const list = [...props.bomData]
  list.splice(idx + 1, 0, newNode)
  emit('update:bom-data', list)
  refreshSerialNumber() // 新增
}

// 新增子节点
const addChild = (item) => {
  if (!item.children) item.children = []
  globalMaxId.value += 1
  const newId = globalMaxId.value
  const autoLabel = generateUniqueLabel(rootbomRef, props.bomData, props.namePrefix, props.digitLength, props.noRepeatName)
  const newField = getDefaultNode(newId, autoLabel)

  item.children.push(newField)
  item.isOpen = true
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber() // 新增
}

// 删除节点
const deleteNode = (index) => {
  if (props.level === 0 && props.bomData.length <= 1) {
    alert('唯一的根节点禁止删除！')
    return
  }
  const targetItem = props.bomData[index]
  const hasChildren = targetItem.children && targetItem.children.length > 0
  const msg = hasChildren
    ? '该节点包含子节点，删除后所有后代一并移除，确认删除？'
    : '确认删除当前节点？'

  if (confirm(msg)) {
    const updatedData = [...props.bomData]
    updatedData.splice(index, 1)
    emit('update:bom-data', updatedData)
    refreshSerialNumber() // 新增
  }
}

const handleAddSibling = (item, idx) => {
  addSibling(idx)
  closeAllMenu()
}
const handleAddChild = (item) => {
  addChild(item)
  closeAllMenu()
}
const handleDelete = (idx) => {
  deleteNode(idx)
  closeAllMenu()
}

const startEdit = (item) => {
  item.tempLabel = item.label
  item.isEdit = true
}

// 保存节点名称（修复了你原有变量未定义报错）
const saveLabel = (item, siblingsArr, event) => {
  if (!item.isEdit || isVerifying.value) return

  const rawLabel = item.tempLabel || ''
  const trimmedLabel = rawLabel.trim().replace(/\s/g, "")

  if (!trimmedLabel) {
    isVerifying.value = true
    alert('节点名称不能为空！')
    setTimeout(() => {
      if (event && event.target) event.target.focus()
      isVerifying.value = false
    }, 50)
    return
  }
  // 调用复用校验函数
  const hasRepeat = checkLabelRepeat(trimmedLabel, item, siblingsArr, rootbomRef, props.noRepeatName)
  if (hasRepeat) {
    alert(props.noRepeatName ? '该名称整BOM内已存在，不可重复' : '同级目录下该名称已存在，请更换')
    setTimeout(() => {
      event?.target?.focus()
      isVerifying.value = false
    }, 50)
    return
  }

 
  item.label = trimmedLabel
  item.isEdit = false
  item.isNew = false
  delete item.tempLabel
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber() // 新增
}

// 原有名称重复校验函数
const checkLabelRepeat = (label, currentItem, siblings, tree, isGlobal) => {
  if(isGlobal){
    return checkLabelExistsGlobal(label, tree, currentItem.id)
  }else{
    return siblings.some(n => n.label === label && n.id !== currentItem.id)
  }
}

const updateChildren = (item, newChildren) => {
  item.children = newChildren
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber() // 新增
}

// 拖拽相关
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

// 根节点拖拽移动核心
function rootMoveNodeCenter(dragId, targetId, position) {
  const fullbom = JSON.parse(JSON.stringify(props.bomData))
  const dragNode = findNodeGlobal(dragId, fullbom)
  const targetNode = findNodeGlobal(targetId, fullbom)

  if (!dragNode || !targetNode) return
  if (isDescendantGlobal(dragNode, targetNode)) {
    alert('操作失败：不能将节点放置到它自己的后代节点中！')
    return
  }

  removeNodeGlobal(dragId, fullbom)

  if (position === 'inside') {
    if (!targetNode.children) targetNode.children = []
    targetNode.children.push(dragNode)
    targetNode.isOpen = true
  } else {
    const parentNode = findParentGlobal(targetId, fullbom)
    const siblings = parentNode ? parentNode.children : fullbom
    const tIndex = siblings.findIndex(n => n.id === targetId)

    if (position === 'before') {
      siblings.splice(tIndex, 0, dragNode)
    } else {
      siblings.splice(tIndex + 1, 0, dragNode)
    }
  }
  // 拖拽完成刷新序号
  refreshSerialNumber(fullbom)
  emit('update:bom-data', fullbom)
}
</script>

<script>
// 全局工具函数 100% 原样保留未修改
const checkLabelExistsGlobal = (label, nodes, currentId) => {
  for (const node of nodes) {
    const activeLabel = node.isEdit && node.tempLabel ? node.tempLabel : node.label
    if (activeLabel === label && node.id !== currentId) return true
    if (node.children && node.children.length > 0) {
      if (checkLabelExistsGlobal(label, node.children, currentId)) return true
    }
  }
  return false
}

const escapeRegExp = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

/**
 * 生成物料编号
 * @param tree 整棵树数据
 * @param siblings 当前同级数组（同级模式才用）
 * @param prefix 前缀
 * @param len 数字位数
 * @param isGlobal 全局唯一开关,用于判断编号无重复是全局模式还是同级模式
 */
function generateUniqueLabel(tree, siblings, prefix, len, isGlobal) {
  let maxNum = 0
  const reg = new RegExp(`^${escapeReg(prefix)}(\\d+)$`)

  if (isGlobal) {
    // 全局模式：遍历整棵树所有节点
    const traverse = (list) => {
      list.forEach(node => {
        const match = node.label.match(reg)
        if (match) {
          const n = parseInt(match[1])
          if (n > maxNum) maxNum = n
        }
        if (node.children.length) traverse(node.children)
      })
    }
    traverse(tree)
  } else {
    // 同级模式：只遍历当前父节点下的同级节点
    siblings.forEach(node => {
      const match = node.label.match(reg)
      if (match) {
        const n = parseInt(match[1])
        if (n > maxNum) maxNum = n
      }
    })
  }

  const newNum = maxNum + 1
  return prefix + newNum.toString().padStart(len, '0')
}

// 正则转义工具
function escapeReg(str) {
  return str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')
}

const findNodeGlobal = (id, nodes) => {
  for (const node of nodes) {
    if (node.id === id) return node
    if (node.children) {
      const res = findNodeGlobal(id, node.children)
      if (res) return res
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
      const res = findParentGlobal(childId, node.children, node)
      if (res) return res
    }
  }
  return null
}

const isDescendantGlobal = (parent, childNode) => {
  if (!parent.children?.length) return false
  for (const child of parent.children) {
    if (child.id === childNode.id) return true
    if (isDescendantGlobal(child, childNode)) return true
  }
  return false
}
</script>