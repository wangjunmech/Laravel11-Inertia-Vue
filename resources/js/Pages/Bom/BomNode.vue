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
        <!-- 编辑按钮下拉菜单容器（铅笔） -->
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
              @click.stop="handleAddSibling(item, index)"
            >添加（同级）</div>
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 transition-colors"
              @click.stop="handleAddChild(item)"
            >添加子物料</div>
            <div
              class="px-3 py-1.5 text-sm cursor-pointer hover:bg-gray-100 text-red-500 transition-colors"
              @click.stop="handleDelete(index)"
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
        {{ item.sn }}
      </div>

      <!-- BOM多字段单元格区域 -->
      <div class="flex">
        <!-- 产品编号 下拉菜单 -->        
        <DropdownMenu
          :isOpen="activeItemId === item.id"
          @update:isOpen="(val) => val ? toggleItemMenu(item.id) : closeAllMenu()"
          @click.stop="toggleItemMenu(item.id)"
          placement="center"
          :menu-list="[
            { label: '物料详情', onClick: () => ItemDetail(item) },
            { label: '供应商信息', onClick: () => SupplierInfo(item) },
            { label: '模具信息', onClick: () => MoldInfo(item) },
            { label: '使用场合', onClick: () => WhereToUse(item) },
            { label: '工装夹具', onClick: () => JigTools(item) },
            { label: '检测工具', onClick: () => InspectionTools(item) },
            { label: '检验规范', onClick: () => SIP(item) },
            { label: '操作指导', onClick: () => SOP(item) }
          ]"
        >
            <div 
                class="relative inline-block mr-1 flex m-2 px-2 items-center justify-center min-w-[110px] cursor-pointer transition-colors duration-200"
                :class="[
                  activeMenuNodeId === item.id 
                    ? 'bg-red-500 text-white'  // 打开菜单：红色背景+白字
                    : 'bg-green-400'           // 默认绿色
                ]"
              >
            {{ item.label }}
          </div>
        </DropdownMenu>

        <!-- 品名英文 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[110px] cursor-pointer">
          <input
            v-if="item.editField === 'nameEn'"
            v-model="item.nameEn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-full border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <div 
            v-else 
            class="w-full min-w-0 cursor-pointer text-center"
            @click="openEditField(item, 'nameEn')"
          >
            <span 
              class="block truncate"
              style="max-width: 9ch;"
              :title="item.nameEn"
            >
              {{ item.nameEn }}
            </span>
          </div>
        </div>

        <!-- 品名中文 -->
        <div class="flex bg-green-400 m-2 px-2 items-center justify-center min-w-[110px] cursor-pointer">
          <input
            v-if="item.editField === 'nameCn'"
            v-model="item.nameCn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-full border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <div 
            v-else 
            class="w-full min-w-0 cursor-pointer text-center"
            @click="openEditField(item, 'nameCn')"
          >
            <span 
              class="block truncate"
              style="max-width: 9ch;"
              :title="item.nameCn"
            >
              {{ item.nameCn }}
            </span>
          </div>
        </div>

        <!-- 用量 -->
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

        <!-- 单位 -->
        <div class="flex bg-red-400 m-2 px-2 items-center justify-center rounded-sm min-w-[50px]">
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

        <!-- 损耗率 -->
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

        <!-- 采购单价 -->
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

        <!-- 小计 只读 -->
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
            <!-- 显示当前的父级元素id -->
            <span>PID:  {{ item.parentId ?? '--' }}</span>
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
import { ref, watch, onMounted, provide, inject } from 'vue'
import DropdownMenu from '@/Components/DropdownMenu.vue'
const props = defineProps({
  bomData: { type: Array, default: () => [] },
  level: { type: Number, default: 0 },
  showIdLevel: { type: Boolean, default: true },
  noRepeatName: { type: Boolean, default: true },
  namePrefix: { type: String, default: 'cod' },
  digitLength: { type: Number, default: 1 }
})

const emit = defineEmits(['update:bom-data'])

onMounted(() => {
  // 页面挂载完毕，树形数据已经拿到，初始化所有节点父ID
  refreshAllParentId(props.bomData)
})

// 顶层变量声明
let globalDraggingId, globalDropTargetId, globalDropPosition, executeGlobalMove, rootbomRef
let globalMaxId, isVerifying, activeMenuId, closeAllMenu, toggleMenu, vClickOutside
let refreshSerialNumber
let activeItemId, toggleItemMenu, closeItemMenu, ItemDetail, WhereToUse

const localShowInfo = ref(props.showIdLevel)
watch(() => props.showIdLevel, (v) => { localShowInfo.value = v })
const vFocus = { mounted: el => el.focus() }


// // 【全局选中高亮节点ID】所有层级共用
const activeMenuNodeId = ref(null)
// 非根层级：全部注入接收
if (props.level !== 0) {
  globalDraggingId = inject('globalDraggingId')
  globalDropTargetId = inject('globalDropTargetId')
  globalDropPosition = inject('globalDropPosition')
  executeGlobalMove = inject('executeGlobalMove')
  rootbomRef = inject('rootbomRef')
  globalMaxId = inject('globalMaxId')
  isVerifying = inject('globalIsVerifying')
  activeMenuId = inject('activeMenuId')
  closeAllMenu = inject('closeAllMenu')
  toggleMenu = inject('toggleMenu')
  vClickOutside = inject('vClickOutside')
  refreshSerialNumber = inject('refreshSerialNumber')

  // 物料下拉菜单全套注入接收
  activeItemId = inject('activeItemId')
  // activeMenuNodeId = inject('activeMenuNodeId')
  toggleItemMenu = inject('toggleItemMenu')
  closeItemMenu = inject('closeItemMenu')
  ItemDetail = inject('ItemDetail')
  WhereToUse = inject('WhereToUse')
} else {
  // ==================== 根层级初始化所有全局状态与函数 ====================
  globalDraggingId = ref(null)
  globalDropTargetId = ref(null)
  globalDropPosition = ref(null)
  rootbomRef = props.bomData
  globalMaxId = ref(0)
  isVerifying = ref(false)

  // 铅笔菜单状态
  activeMenuId = ref(null)
  closeAllMenu = () => {
    activeMenuId.value = null
    activeItemId.value = null
    activeMenuNodeId.value = null // 关闭菜单清空高亮
  }
  toggleMenu = (nodeId) => {
    activeItemId.value = null
    activeMenuId.value = activeMenuId.value === nodeId ? null : nodeId

  }

  // 产品编号物料下拉菜单状态
  activeItemId = ref(null)
  closeItemMenu = () => {
    activeItemId.value = null
    activeMenuNodeId.value = null // 关闭菜单清空高亮
    
  }
  toggleItemMenu = (nodeId) => {
  // 点击同一个：关闭；点击其他：切换选中
    if (activeMenuNodeId.value === nodeId) {
      activeMenuNodeId.value = null
      activeItemId.value = null
    } else {
      activeMenuNodeId.value = nodeId
      activeItemId.value = nodeId
    }
  }
  ItemDetail = () => {
    activeItemId.value = null
    // 此处可后续写打开详情弹窗逻辑
  }
  WhereToUse = () => {
    activeItemId.value = null
    // 此处可后续写使用场合逻辑
  }

  // 自定义点击外部关闭指令
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

  // 计算最大ID
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

  // DFS全局流水序号
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

  watch(
  () => props.bomData,
  (newTree) => {
    rootbomRef = newTree // 同步更新全局树形引用
    refreshSerialNumber(newTree)
    refreshAllParentId(newTree)
  },
  { deep: true }
)

  // 全部全局变量向下注入（子组件可拿到）
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
  provide('vClickOutside', vClickOutside)
  provide('refreshSerialNumber', refreshSerialNumber)

  // 物料菜单相关注入
  provide('activeItemId', activeItemId)
  provide('toggleItemMenu', toggleItemMenu)
  provide('closeItemMenu', closeItemMenu)
  provide('ItemDetail', ItemDetail)
  provide('WhereToUse', WhereToUse)
  provide('activeMenuNodeId', activeMenuNodeId)
}

// 新建节点默认模板
const getDefaultNode = (nodeId, nodeName, parentPid = null) => ({
  id: nodeId,
  parentId: parentPid,
  label: nodeName,
  tempLabel: nodeName,
  children: [],
  isOpen: false,
  isEdit: true,
  isNew: true,
  editField: null,
  sn: 0,
  nameEn: 'labelcover',
  nameCn: '标签外壳',
  quantity: 1,
  unit: 'pcs',
  wasteRate: 0,
  price: 0,
  subtotal: 0,
  bgactive: false
})


/**
 * 递归遍历树形结构，给所有节点赋值 parentId
 * @param nodes 当前遍历节点数组
 * @param pid 父节点ID
 */
const refreshAllParentId = (nodes, pid = null) => {
  nodes.forEach(node => {
    node.parentId = pid;
    // 递归遍历子节点，子节点父ID = 当前节点id
    if (node.children && node.children.length > 0) {
      refreshAllParentId(node.children, node.id);
    }
  });
}

// 打开单元格编辑
const openEditField = (item, fieldKey) => {
  item.editField = fieldKey
}

// 保存字段+小计计算+编号查重
const saveField = (item, event) => {
  if(item.editField === 'productSn'){
    const inputSn = item.productSn.trim()
    if(!inputSn){
      alert('产品编号不能为空')
      event?.target?.focus()
      return
    }
    let isRepeat = false
    if(props.noRepeatName){
      isRepeat = checkProductSnGlobal(inputSn, rootbomRef, item.id)
    }else{
      const siblingList = getCurrentSiblings(item, rootbomRef)
      isRepeat = checkProductSnSibling(inputSn, siblingList, item.id)
    }
    if(isRepeat){
      alert(props.noRepeatName ? '该产品编号在整个BOM中已存在，不可重复' : '同级下该产品编号已存在，请更换')
      event?.target?.focus()
      return
    }
  }

  item.editField = null
  item.subtotal = Number((item.quantity * item.price * (1 + item.wasteRate / 100)).toFixed(2))
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber()
}

// 全局校验产品编号重复
const checkProductSnGlobal = (sn, treeNodes, excludeId) => {
  for(const node of treeNodes){
    if(node.productSn === sn && node.id !== excludeId) return true
    if(node.children?.length){
      if(checkProductSnGlobal(sn, node.children, excludeId)) return true
    }
  }
  return false
}

// 获取同级节点
const getCurrentSiblings = (targetItem, tree) => {
  const parent = findParentGlobal(targetItem.id, tree)
  return parent ? parent.children : tree
}

// 同级校验产品编号
const checkProductSnSibling = (sn, siblingArr, excludeId) => {
  return siblingArr.some(node => node.productSn === sn && node.id !== excludeId)
}

// 新增同级
const addSibling = (idx) => {
  if (props.level == 0) {
    alert('根节点禁止新增同级节点！')
    return
  }
  globalMaxId.value += 1
  const newId = globalMaxId.value
  const newLabel = generateUniqueLabel(rootbomRef, props.bomData, props.namePrefix, props.digitLength, props.noRepeatName)
// 同级节点父ID = 当前节点的parentId
  const newNode = getDefaultNode(newId, newLabel, props.bomData[idx].parentId)
  const list = [...props.bomData]
  list.splice(idx + 1, 0, newNode)
  emit('update:bom-data', list)
  refreshSerialNumber()
  refreshAllParentId(list) // 新增同级刷新PID
}

// 新增子节点
const addChild = (item) => {
  if (!item.children) item.children = []
  globalMaxId.value += 1
  const newId = globalMaxId.value
  const autoLabel = generateUniqueLabel(rootbomRef, props.bomData, props.namePrefix, props.digitLength, props.noRepeatName)
// 子节点父ID = 父节点item.id
  const newField = getDefaultNode(newId, autoLabel, item.id)
  item.children.push(newField)
  item.isOpen = true
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber()
  refreshAllParentId(props.bomData) // 新增子节点刷新PID
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
    refreshSerialNumber()
    refreshAllParentId(updatedData) // 删除后刷新所有父ID
  }
}

// 铅笔菜单点击事件
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

// 节点名称编辑
const startEdit = (item) => {
  item.tempLabel = item.label
  item.isEdit = true
}

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
  refreshSerialNumber()
}

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
  refreshSerialNumber()
}

// 拖拽全套逻辑
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
  refreshSerialNumber(fullbom)
  refreshAllParentId(fullbom) // 拖拽结束刷新所有父ID
  emit('update:bom-data', fullbom)
}
</script>

<script>
// 全局工具函数 完整保留
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

function generateUniqueLabel(tree, siblings, prefix, len, isGlobal) {
  let maxNum = 0
  const reg = new RegExp(`^${escapeRegExp(prefix)}(\\d+)$`)

  if (isGlobal) {
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

const escapeReg = (str) => str.replace(/[.*+?^${}()|[\]\\]/g, '\\$&')

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

<style scoped>
/* 左侧节点名称盒子 固定宽度+文字截断 */
.name-box {
  width: 220px;       /* 固定统一宽度，可按需调大小 */
  flex-shrink: 0;     /* 绝不被压缩 */
  white-space: nowrap;/* 文字禁止换行 */
  overflow: hidden;   /* 超出隐藏 */
  text-overflow: ellipsis; /* 末尾显示...省略号 */
}
</style>