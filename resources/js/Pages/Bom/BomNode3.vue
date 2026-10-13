<template>
<div>
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
      class="flex justify-start"
      :class="{
        'bg-[#f096f0]': uiState(item.id).bgactive,
        'bg-[#c1e9af] outline-2 outline-dashed outline-[#c5eeb0]': globalDropTargetId === item.id && globalDropPosition === 'inside',
        'border-t-2 border-[#ef0b44] ': globalDropTargetId === item.id && globalDropPosition === 'before',
        'border-b-2 border-[#db83e7] ': globalDropTargetId === item.id && globalDropPosition === 'after',
        'bg-[#fff9c4]': uiState(item.id).isNew
      }"
      @mouseenter="uiState(item.id).bgactive = true"
      @mouseleave="uiState(item.id).bgactive = false"
      @dragover.prevent.stop="handleDragOver($event, item)"
      @dragleave.stop="handleDragLeave"
      @drop.prevent.stop="handleDrop($event, item)"
    >
      <div
        class="w-5 h-5 flex mr-1.5 text-[#888] text-xs "
        @click="uiState(item.id).isOpen = !uiState(item.id).isOpen"
      >
        <template v-if="item.children && item.children.length > 0">
          {{ uiState(item.id).isOpen ? '▼' : '▶' }}
        </template>
        <template v-else>
          {{ uiState(item.id).isOpen ? '▽' : '▷' }}
        </template>
      </div>

      <div
        class="flex bg-red-200 m-2 justify-start h-6 rounded-s-full rounded min-w-[60px] w-full"
        :class="isRepeatCode(item.partCode) ? 'bg-yellow-200' : 'bg-red-200'"
        :title="isRepeatCode(item.partCode) ? '⚠️前景黄色表示该物料编号存在重复' : '单击编辑产品编号'"
      >
        <div
          title="单击打开快捷添加操作菜单"
          :class="[
              activeMenuId === item.id
                ? 'bg-red-600'
                : ''
            ]"
          class="flex-shrink-0 bg-green-200 rounded-full w-6 h-6 items-center justify-center cursor-pointer transition-colors hover:ring-2 hover:ring-red-500 hover:ring-offset-1"
          @click.stop="toggleMenu(item.id)"
          >✏
        </div>

        <DropNumShortCut
          class=""
          :isOpen="activeMenuId === item.id"
          @update:isOpen="(val) => val ? toggleMenu(item.id) : closeAllMenu()"
          :menu-list="[
            { subMenuLabel: '添加（同级）', onClick: () => handleAddSibling(item, index) },
            { subMenuLabel: '添加子物料', onClick: () => handleAddChild(item) },
            { subMenuLabel: '添加工程流程子料号', onClick: () => handleProcessCode(item) },
            { subMenuLabel: '用已有编号', onClick: () => selectFromExisting(item) },
            { subMenuLabel: '删除',danger:true, onClick: () => handleDelete(index) },
          ]"
        >
        </DropNumShortCut>

        <input
          title="编号长度最多20位"
          v-if="uiState(item.id).isEdit"
          v-model="uiState(item.id).tempPartCode"
          @blur="savePartCode(item, bomData, $event)"
          @keyup.enter="$event.target.blur()"
          @keyup="uiState(item.id).tempPartCode=keyUp(uiState(item.id).tempPartCode,['-','_'])[2]"
          @paste.prevent="uiState(item.id).tempPartCode = keyUp($event.clipboardData.getData('text'),['-','_'])[2]"
          @focus="$event.target.select()"
          class="border border-[#409eff] rounded  py-0.5 outline-none min-w-[120px] text-sm"
          v-focus
          maxlength="20"
        />
        <span
          v-else
          @click="startEdit(item)"
          class="px-1.5 py-0.5 rounded text-sm text-[#333]  hover:bg-black/5 w-full cursor-pointer"
          v-html="highlightText(item.partCode, searchKeyword)"
        >
        </span>
      </div>

      <div class="ml-auto flex gap-1 items-center flex-1 justify-end">
        <div class="flex rounded-lg bg-blue-400 h-6 m-1 px-5 justify-center items-center min-w-[50px]">
          {{ item.sn }}
        </div>

        <div
          class="flex rounded-lg bg-yellow-400 m-1 px-3 justify-center min-w-[60px] relative">
          <div class="flex cursor-pointer"
            @click.stop="closeAllMenu();activeCateId = item.id"
          >
          {{ item.id === 0 ? 0 : getCateLabel(item) }}
          </div>

          <div
            v-if="activeCateId === item.id && item.id!=0"
            class="absolute z-50 w-[150px] left-10 mt-1 bg-white border rounded shadow-lg top-2"
            @click.stop
          >
            <input
              v-if="showCateInput"
              ref="inputRef"
              v-model="newCateText"
              class="absolute w-full h-8 border px-2 py-1 rounded"
              @keyup.enter="$emit('add-cate', item, newCateText);activeCateId = null;showCateInput=false"
              @blur="closeAllMenu()"
              v-focus=""
            />
            <template v-else>
              <div
                v-for="option in cateOptionList"
                :key="option.cateId"
                class="px-3 py-1 hover:bg-gray-100 cursor-pointer"
                @click="$emit('select-cate', item, option);activeCateId = null"
              >
                {{ option.cateName }}
              </div>
              <div
                class="px-3 hover:cursor-pointer bg-red-400 border-t"
                @click="openAddInput"
              >
                <span class="text-white">添加类别</span>
              </div>
            </template>
          </div>
        </div>

        <div
          class="flex m-1 px-2 rounded min-w-[210px] cursor-pointer border-2"
          :class="[
            activeItemId === item.id
              ? 'bg-red-400 text-white'
              : 'bg-green-100'
          ]"
          @click.stop="toggleItemMenu(item.id)"
        >
          <span v-html="highlightText(item.partCode, searchKeyword)"></span>
          <DropNumShortCut
            class="left-[60px] top-[0px] rounded-sm "
            :isOpen="activeItemId === item.id"
            @update:isOpen="(val) => val ? toggleItemMenu(item.id) : closeAllMenu()"
            placement="center"
            :menu-list="[
              { subMenuLabel: '物料详情', onClick: () => ItemDetail(item) },
              { subMenuLabel: '供应商信息', onClick: () => SupplierInfo(item) },
              { subMenuLabel: '模具信息', onClick: () => MoldInfo(item) },
              { subMenuLabel: '使用场合', onClick: () => WhereToUse(item) },
              { subMenuLabel: '工装夹具', onClick: () => JigTools(item) },
              { subMenuLabel: '检测工具', onClick: () => InspectionTools(item) },
              { subMenuLabel: '检验规范', onClick: () => SIP(item) },
              { subMenuLabel: '操作指导', onClick: () => SOP(item) }
            ]"
          >
          </DropNumShortCut>
        </div>

        <div
          v-if="getFieldVisible('nameEn')"
          class="flex bg-green-300 m-1 px-2 rounded-sm justify-center min-w-[110px] cursor-pointer">
          <input
            v-if="uiState(item.id).editField === 'nameEn'"
            v-model="item.nameEn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-full border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
            @dblclick="handleDblClickSelect"
          />
          <div
            v-else
            class="w-full min-w-0 cursor-pointer text-center"
            @click="openEditField(item, 'nameEn')">
            <span
              class="block truncate"
              style="max-width: 9ch;"
              :title="item.nameEn"
              v-html="highlightText(item.nameEn, searchKeyword)"
            >
            </span>
          </div>
        </div>

        <div
          v-if="getFieldVisible('nameCn')"
          class="flex bg-green-300 m-1 px-2 rounded-sm justify-center min-w-[110px] cursor-pointer">
          <input
            v-if="uiState(item.id).editField === 'nameCn'&& !uiState(item.id).lockField"
            v-model="item.nameCn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-full border border-blue-500 m-1 px-1 py-0.5 text-sm outline-none"
            v-focus
            @dblclick="handleDblClickSelect"
          />
          <div
            v-else
            class="w-full min-w-0 cursor-pointer text-center"
            @click="!uiState(item.id).lockField && openEditField(item, 'nameCn')"
          >
            <span
              class="block truncate"
              style="max-width: 9ch;"
              :title="item.nameCn"
              v-html="highlightText(item.nameCn, searchKeyword)"
            >
            </span>
          </div>
        </div>

        <div v-if="getFieldVisible('quantity')" class="flex items-center bg-red-200 m-1 px-2 rounded-sm min-w-[60px]">
          <input
            v-if="uiState(item.id).editField === 'quantity'"
            v-model.number="item.quantity"
            type="number" min="0" step="1"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-16 border border-blue-500 px-1 py-0.5 text-sm outline-none "
            v-focus
            @dblclick="handleDblClickSelect"
          />
          <span v-else @click="openEditField(item, 'quantity')" class="w-full min-w-0 cursor-pointer text-center">
            {{ item.quantity }}
          </span>
        </div>

        <div v-if="getFieldVisible('unit')" class="flex bg-red-400 m-1 px-2 rounded-sm items-center justify-center min-w-[60px]"
        @click.stop="!uiState(item.id).lockField && (uiState(item.id).unitPopupOpen = true)">
          <DropdownSubMenu
            v-model="uiState(item.id).unitPopupOpen"
            :unit-group-list="unitGroupList"
            placement="left"
            @select="(key) => {
              if(uiState(item.id).lockField) return
              item.unit = key
              bomStore.isEdited = true
              bomStore.updateTree([...bomStore.bomTree])
              }"
          >
            {{ item.unit }}
          </DropdownSubMenu>
        </div>

        <div v-if="getFieldVisible('wasteRate')" class="flex bg-green-300 m-1 px-2 rounded-sm  justify-center min-w-[60px]">
          <input
            v-if="uiState(item.id).editField === 'wasteRate' && !uiState(item.id).lockField"
            v-model.number="item.wasteRate"
            type="number" min="0" max="100" step="0.1"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-20 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            v-focus
            @dblclick="handleDblClickSelect"
          />
          <span v-else
          @click="!uiState(item.id).lockField && openEditField(item, 'wasteRate')"
           class="flex items-center cursor-pointer h-6 text-sm min-w-[60px]">
            {{ item.wasteRate }}%
          </span>
        </div>

        <div v-if="getFieldVisible('price')" class="flex bg-green-400 m-1 px-2 rounded-sm  justify-center min-w-[80px]">
          <input
            v-if="uiState(item.id).editField === 'price' && !uiState(item.id).lockField"
            v-model.number="item.price"
            type="number" min="0" step="0.01"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-28 border border-blue-500 m-1 px-1 py-0.5 text-sm outline-none"
            v-focus
            @dblclick="handleDblClickSelect"
           />
          <span v-else
          @click="!uiState(item.id).lockField && openEditField(item, 'price')"
           class="flex items-center cursor-pointer h-6 text-sm min-w-[60px]">
            {{ item.price }}
          </span>
        </div>

        <div v-if="getFieldVisible('subtotal')" class="flex bg-gray-200 m-1 px-2  min-w-[90px] rounded">
          <span class="flex text-sm h-6 items-center font-semibold">{{ item.subtotal.toFixed(2) }}</span>
        </div>
      </div>

      <div class="ml-auto flex items-center gap-1">
        <div
          class="text-[11px] text-[#888] whitespace-nowrap mx-3 select-none"
          @click="localShowInfo = !localShowInfo"
        >
          <span v-if="!localShowInfo" class="opacity-50 hover:opacity-100">👁</span>
          <div v-else class="flex gap-2 bg-[#f4f4f5] px-1.5 h py-0.5 rounded-full">
            <span>ID: {{ item.id }}</span>
            <span>Lv: {{ level }}</span>
            <span>PID:  {{ item.parentId ?? '--' }}</span>
          </div>
        </div>

        <div
          class="w-6 h-6 flex items-center justify-center text-[#409eff] font-bold cursor-pointer rounded text-sm hover:bg-red-400"
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

    <div
      v-if="uiState(item.id).isOpen && item.children && item.children.length > 0"
      class="ml-[15px]"
      :class="{
        'bg-[#eef0f5]': uiState(item.id).bgactive,
        'bg-amber-100': uiState(item.id)._isKeywordMatch
      }"
    >
      <BomNode
        :bom-data="item.children"
        :level="level + 1"
        @update:bom-data="(val) => updateChildren(item, val)"
        :showIdLevel="showIdLevel"
        :noRepeatName="noRepeatName"
        :name-prefix="namePrefix"
        :digit-length="digitLength"
        :search-keyword="searchKeyword"
        :repeat-code-list="repeatCodeList"
        :field-options="fieldOptions"
        :cate-option-list="cateOptionList"
        @select-cate="(row,opt)=>emit('select-cate',row,opt)"
        @add-cate="(row,txt)=>emit('add-cate',row,txt)"
        @drag-start="(val)=>emit('drag-start',val)"
        @drag-end="()=>emit('drag-end')"
        @drop-node="(info)=>emit('drop-node',info)"
        @add-sibling="(row,idx)=>emit('add-sibling',row,idx)"
        @add-child="(row)=>emit('add-child',row)"
        @delete-node="(idx)=>emit('delete-node',idx)"
      />
    </div>
  </div>
</div>
</template>

<script setup>
import { ref,toRaw, watch, nextTick, computed, inject } from 'vue'
import DropdownSubMenu from '@/Components/DropdownSubMenu.vue'
import { useBomStore } from '@/Stores/bomStore.js'
import { unitGroupList } from "@/Components/data/unitOptions.js"
import DropNumShortCut from '@/Components/DropNumShortCut.vue'
import {keyUp} from '@/Rules/notAllowedInputCharacters.js'

const bomStore = useBomStore()

// 全局UI状态注入
const uiStateMap = inject('uiStateMap')
const initNodeUiState = inject('initNodeUiState')
const cleanNodeUiState = inject('cleanNodeUiState')

// 拖拽全局状态
const globalDraggingId = inject('globalDraggingId', ref(null))
const globalDropTargetId = inject('globalDropTargetId', ref(null))
const globalDropPosition = inject('globalDropPosition', ref(''))

// 局部状态声明（全部补齐缺失变量）
const localShowInfo = ref(false)
const activeMenuId = ref(null)
const activeCateId = ref(null)
const activeItemId = ref(null)
const newCateText = ref('')
const showCateInput = ref(false)

// 安全获取节点UI状态（修复undefined报错）
const uiState = (nodeId) => {
  if(!uiStateMap.value.has(nodeId)){
    initNodeUiState(nodeId)
  }
  return uiStateMap.value.get(nodeId)
}

// Props 完整补全（修复截断报错）
const props = defineProps({
  bomData: { type: Array, default: () => [] },
  level: { type: Number, default: 0 },
  showIdLevel: { type: Boolean, default: true },
  noRepeatName: { type: Boolean, default: true },
  namePrefix: { type: String, default: 'cod' },
  digitLength: { type: Number, default: 1 },
  searchKeyword: { type: String, default: '' },
  repeatCodeList: { type: Array, default: () => [] },
  fieldOptions: { type: Object, default: () => ({}) },
  cateOptionList: { type: Array, default: () => [] }
})

// Emits 完整补全
const emit = defineEmits([
  'update:bom-data',
  'select-cate',
  'add-cate',
  'drag-start',
  'drag-end',
  'drop-node',
  'add-sibling',
  'add-child',
  'delete-node'
])

// ====================== 全部缺失方法补齐 ======================

// 判断重复编码
const isRepeatCode = (code) => {
  return props.repeatCodeList.includes(code)
}

// 关键字高亮
const highlightText = (str, key) => {
  if(!key || !str) return str
  return String(str).replace(new RegExp(key,'gi'), m => `<span style="color:red">${m}</span>`)
}

// 获取类别文本
const getCateLabel = (item) => {
  const target = props.cateOptionList.find(c => c.cateId === item.cateId)
  return target?.cateName || '未分类'
}

// 字段显隐控制
const getFieldVisible = (field) => {
  return !!props.fieldOptions[field]
}

// 开启字段编辑
const openEditField = (item, field) => {
  closeAllMenu()
  uiState(item.id).editField = field
}

// 保存单行字段
const saveField = (item) => {
  uiState(item.id).editField = ''
  bomStore.isEdited = true
  bomStore.updateTree([...bomStore.bomTree])
}

// 开始编辑编号
const startEdit = (item) => {
  closeAllMenu()
  uiState(item.id).isEdit = true
  uiState(item.id).tempPartCode = item.partCode
  nextTick(()=>{})
}

// 保存料号
const savePartCode = (item, list) => {
  item.partCode = uiState(item.id).tempPartCode
  uiState(item.id).isEdit = false
  bomStore.isEdited = true
  bomStore.updateTree([...bomStore.bomTree])
}

// 双击全选
const handleDblClickSelect = (e) => {
  e.target.select()
}

// 关闭所有菜单
const closeAllMenu = () => {
  activeMenuId.value = null
  activeCateId.value = null
  activeItemId.value = null
  showCateInput.value = false
}

// 节点菜单开关
const toggleMenu = (id) => {
  activeMenuId.value = activeMenuId.value === id ? null : id
}
const toggleItemMenu = (id) => {
  activeItemId.value = activeItemId.value === id ? null : id
}

// 打开新增类别输入框
const openAddInput = () => {
  showCateInput.value = true
  nextTick(()=>{})
}

// 新增同级
const handleAddSibling = (item, index) => {
  emit('add-sibling', item, index)
}

// 新增子级
const handleAddChild = (item) => {
  emit('add-child', item)
}

// 添加工艺子料号
const handleProcessCode = (item) => {
  emit('add-child', item)
}

// 选用已有编号
const selectFromExisting = (item) => {
  // 可自行扩展弹窗选物料
}

// 删除节点
const handleDelete = (index) => {
  emit('delete-node', index)
}
const deleteNode = (index) => {
  emit('delete-node', index)
}
const addChild = (item) => {
  emit('add-child', item)
}

// 更新子树数据
const updateChildren = (item, val) => {
  item.children = val
  emit('update:bom-data', [...props.bomData])
}

// ========== 拖拽全套方法补齐 ==========
const handleDragStart = (e, item) => {
  globalDraggingId.value = item.id
  emit('drag-start', item)
}
const handleDragEnd = () => {
  globalDraggingId.value = null
  globalDropTargetId.value = null
  globalDropPosition.value = ''
  emit('drag-end')
}
const handleDragLeave = () => {
  globalDropTargetId.value = null
  globalDropPosition.value = ''
}
const handleDragOver = (e, item) => {
  const rect = e.target.getBoundingClientRect()
  const y = e.clientY - rect.top
  const height = rect.height

  if(y < height * 0.25){
    globalDropPosition.value = 'before'
  }else if(y > height * 0.75){
    globalDropPosition.value = 'after'
  }else{
    globalDropPosition.value = 'inside'
  }
  globalDropTargetId.value = item.id
}
const handleDrop = (e, item) => {
  emit('drop-node', {
    target: item,
    position: globalDropPosition.value
  })
}

// ========== 右侧弹窗空方法占位（不报错，可后续扩展） ==========
const ItemDetail = ()=>{}
const SupplierInfo = ()=>{}
const MoldInfo = ()=>{}
const WhereToUse = ()=>{}
const JigTools = ()=>{}
const InspectionTools = ()=>{}
const SIP = ()=>{}
const SOP = ()=>{}
</script>

<style scoped>

</style>