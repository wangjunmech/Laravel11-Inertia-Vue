<template>
  <!-- 弹出菜单按数字键快捷 -->
   <div></div>
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
        'bg-[#f096f0]': item.bgactive,//光标当前悬停的节点背景颜色
        'bg-[#c1e9af] outline-2 outline-dashed outline-[#c5eeb0]': globalDropTargetId === item.id && globalDropPosition === 'inside',
        'border-t-2 border-[#ef0b44] ': globalDropTargetId === item.id && globalDropPosition === 'before',
        'border-b-2 border-[#db83e7] ': globalDropTargetId === item.id && globalDropPosition === 'after',
        'bg-[#fff9c4]': item.isNew
      }"
      @mouseenter="item.bgactive = true"
      @mouseleave="item.bgactive = false"
      @dragover.prevent.stop="handleDragOver($event, item)"
      @dragleave.stop="handleDragLeave"
      @drop.prevent.stop="handleDrop($event, item)"
    >
      <!-- 展开收起最左边的指示箭头 -->
      <div
        class="w-5 h-5 flex mr-1.5 text-[#888] text-xs "
        @click="item.isOpen = !item.isOpen"
      >
        <!-- <div>{{activeMenuId}}{{ activeItemId }}</div>调试当前点击打开哪个菜单 -->
          <template v-if="item.children && item.children.length > 0">
            {{ item.isOpen ? '▼' : '▶' }}
          </template>
          <template v-else>
            {{ item.isOpen ? '▽' : '▷' }}
          </template>
        </div>

      <!-- 左侧节点名称区域 -->
      <div class="flex bg-red-200 m-2 justify-start h-6 rounded-s-full rounded min-w-[60px] w-full ">
        <!-- 编辑按钮下拉菜单容器（铅笔），点击弹出菜单 -->
        
          <div
            title="单击打开快捷添加操作菜单"
            :class="[
                  activeMenuId === item.id 
                    ? 'bg-red-600'  // 打开菜单：红色背景+白字
                    : ''           // 默认绿色
                ]" 
            class="flex-shrink-0 bg-green-200 rounded-full w-6 h-6 items-center justify-center cursor-pointer transition-colors hover:ring-2 hover:ring-red-500 hover:ring-offset-1"
            @click.stop="toggleMenu(item.id)"
            >✏
          </div>

            <!-- 单击打开快捷添加操作菜单子组件调用 -->
             <!-- {{activeMenuId === item.id}} 说明子菜单打开了 -->
          <DropNumShortCut 
            class=""
                  :isOpen="activeMenuId === item.id"
                  @update:isOpen="(val) => val ? toggleMenu(item.id) : closeAllMenu()"
                  :menu-list="[
                    { label: '添加（同级）', onClick: () => handleAddSibling(item, index) },
                    { label: '添加子物料', onClick: () => handleAddChild(item) },
                    { label: '添加工程流程子料号', onClick: () => handleProcessCode(item) },
                    { label: '用已有编号', onClick: () => selectFromExisting(item) },
                    { label: '删除',danger:true, onClick: () => handleDelete(index) },
                  ]"
            >
          </DropNumShortCut>
       
        
        <!-- 产品编号编辑输入框 -->
        <input
          title="编号长度最多20位"
          v-if="item.isEdit"
          v-model="item.tempLabel"
          @blur="saveLabel(item, bomData, $event)"
          @keyup.enter="$event.target.blur()"
          @keyup="item.tempLabel=keyUp(item.tempLabel,['-','_'])[2]"
          @paste.prevent="item.tempLabel = keyUp($event.clipboardData.getData('text'),['-','_'])[2]"
          @focus="$event.target.select()"
          class="border border-[#409eff] rounded  py-0.5 outline-none min-w-[120px] text-sm"
          v-focus
          maxlength="20"
          
        />
        <span
          v-else
          @click="startEdit(item)"
          class="px-1.5 py-0.5 rounded text-sm text-[#333]  hover:bg-black/5 w-full cursor-pointer"
          v-html="highlightText(item.label, props.searchKeyword)"
        >          
        </span>
      </div>


      <!-- BOM多字段单元格区域 -->
      <div class="ml-auto flex gap-1 flex-1 justify-end">
              
      <div class="flex rounded-lg bg-blue-400 m-1 px-5 justify-center min-w-[50px]">
        {{ item.sn }}
      </div>

      <!-- 类别点击下拉菜单 -->             
      <div 
        class="flex rounded-lg bg-yellow-400 m-1 px-3 justify-center min-w-[60px] relative">
          <div class="flex cursor-pointer"           
            @click.stop="closeAllMenu();activeCateId = item.id"     
          >
            {{ item.cateName || "类别" }}
          </div>

          <div
            v-if="activeCateId === item.id"
            class="absolute z-50 w-[150px] left-0 top-full mt-1 bg-white border rounded shadow-lg p-2"
            @click.stop
          >
              <!-- 判断是否开启新增输入框 -->
              <input
                  v-if="showCateInput"
                  ref="inputRef"
                  v-model="newCateText"
                  class="w-full border px-2 py-1"
                  @keyup.enter="addNewCate(item)"
                  @blur="addNewCate(item)"
              />
              <!-- 普通下拉选项 -->
              <template v-else>
                  <div
                      v-for="option in cateOptionList"
                      :key="option.value"
                      class="px-3 py-2 hover:bg-gray-100 cursor-pointer"
                      @click="chooseCate(option, item)"
                  >
                      {{ option.cate }}
                  </div>
                  <!-- 点击唤起输入框 -->
                  <div
                      class="px-3 py-2 hover:bg-gray-100 cursor-pointer text-blue‑500 border‑t"
                      @click="openAddInput"
                  >
                      添加类别
                  </div>
              </template>
          </div>
        </div>
        <!-- 物料编号操作下拉菜单外层 --> 
            <div 
                class="flex m-1 px-2 rounded min-w-[210px] cursor-pointer border-2"
                :class="[
                  activeItemId === item.id 
                    ? 'bg-red-400 text-white'  // 打开菜单：红色背景+白字
                    : 'bg-green-100'           // 默认绿色
                ]" 
                @click.stop="toggleItemMenu(item.id)"               
              >
              <span v-html="highlightText(item.label, props.searchKeyword)"></span>
            <!-- 物料编号操作下拉菜单组件 -->
                <DropNumShortCut    
                class="left-[60px] top-[0px] rounded-sm "                      
                  :isOpen="activeItemId === item.id"
                  @update:isOpen="(val) => val ? toggleItemMenu(item.id) : closeAllMenu()"                  
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
              </DropNumShortCut>       
          </div>  
        <!-- 品名英文 -->        
        <div         
          class="flex bg-green-300 m-1 px-2 rounded-sm justify-center min-w-[110px] cursor-pointer">
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
              @click="openEditField(item, 'nameEn')">
              <span 
                class="block truncate"
                style="max-width: 9ch;"
                :title="item.nameEn"
                v-html="highlightText(item.nameEn, props.searchKeyword)"
              >
              </span>
          </div>
        </div>

        <!-- 品名中文 -->
        <div class="flex bg-green-300 m-1 px-2 rounded-sm  justify-center min-w-[110px] cursor-pointer">
          <input
            v-if="item.editField === 'nameCn'"
            v-model="item.nameCn"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-full border border-blue-500 m-1 px-1 py-0.5 text-sm outline-none"
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
              v-html="highlightText(item.nameCn, props.searchKeyword)"
            >
              
            </span>
          </div>
        </div>

        <!-- 用量 -->
        <div class="flex items-center bg-red-200 m-1 px-2 rounded-sm min-w-[60px]">
          <input
            v-if="item.editField === 'quantity'"
            v-model.number="item.quantity"
            type="number" min="0" step="1"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-16 border border-blue-500 px-1 py-0.5 text-sm outline-none "
            v-focus
          />
          <span v-else @click="openEditField(item, 'quantity')" class="w-full min-w-0 cursor-pointer text-center">
            <!-- 点击此标签修改用量 -->
            {{ item.quantity }}
          </span>
        </div>

        
        <div class="flex bg-red-400 m-1 px-2 rounded-sm  justify-center min-w-[60px]">
          <!-- 单位选择************选择组件调用 -->
          <DropdownSubMenu
            v-model="item.unitPopupOpen"
            :unit-group-list="unitGroupList"
            placement="left"
            @select="(key)=>{
              item.unit = key
                if(bomStore){
                  bomStore.isEdited = true
            }
              bomStore.isEdited = true
              bomStore.updateTree([...bomStore.bomTree])
              
            }"
          >
            {{ item.unit }}
          </DropdownSubMenu>
        </div>

        <!-- 损耗率 -->
        <div class="flex bg-green-300 m-1 px-2 rounded-sm  justify-center min-w-[60px]">
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
        <div class="flex bg-green-400 m-1 px-2 rounded-sm  justify-center min-w-[80px]">
          <input
            v-if="item.editField === 'price'"
            v-model.number="item.price"
            type="number" min="0" step="0.01"
            @blur="saveField(item)"
            @keyup.enter="saveField(item)"
            class="w-28 border border-blue-500 m-1 px-1 py-0.5 text-sm outline-none"
            v-focus
          />
          <span v-else @click="openEditField(item, 'price')" class="cursor-pointer text-sm min-w-[60px]">
            {{ item.price }}
          </span>
        </div>

        <!-- 小计成本 只读 -->
        <div class="flex bg-gray-200 m-1 px-2  min-w-[100px] rounded">
          <span class="text-sm font-semibold">{{ item.subtotal.toFixed(2) }}</span>
        </div>
      </div>

      <!-- 右侧功能按钮区 -->
      <div class="ml-auto flex items-center gap-1">
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

    <!-- 子节点嵌套缩进 -->
    <div
      v-if="item.isOpen && item.children && item.children.length > 0"
      class="ml-[15px]"
      :class="{
          'bg-[#eef0f5]': item.bgactive,//子节点的背景颜色
          // 新增高亮样式
          'bg-amber-100': item._isKeywordMatch
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
      />
    </div>
  </div>
</template>

<script setup>
// 1. 模块导入
import { ref, watch, onMounted, onUnmounted, provide, inject, computed,nextTick, h } from 'vue'
import DropdownMenu from '@/Components/DropdownMenu.vue'
import DropdownSubMenu from '@/Components/DropdownSubMenu.vue'
import { useBomStore } from '@/Stores/bomStore.js'
//引入单位数据供点击弹出单位子菜单用
import { unitGroupList } from "@/Components/data/unitOptions.js"
import DropNumShortCut from '@/Components/DropNumShortCut.vue'
import {keyUp} from '@/Rules/notAllowedInputCharacters.js'//字符过滤函数

const bomStore = useBomStore()
// 2. Props、Emits 定义
const props = defineProps({
  bomData: { type: Array, default: () => [] },
  level: { type: Number, default: 0 },
  showIdLevel: { type: Boolean, default: true },
  noRepeatName: { type: Boolean, default: true },
  namePrefix: { type: String, default: 'cod' },
  digitLength: { type: Number, default: 1 },
  searchKeyword: { type: String, default: '' }
})

const emit = defineEmits(['update:bom-data'])

// 3. 基础局部响应式变量 + 自定义指令
const localShowInfo = ref(props.showIdLevel)
watch(() => props.showIdLevel, (v) => { localShowInfo.value = v })
const vFocus = { mounted: el => el.focus() }
const activeMenuNodeId = ref(null)

// 4. 顶层变量声明（全局拖拽、菜单、编号相关占位变量）
let globalDraggingId, globalDropTargetId, globalDropPosition, executeGlobalMove, rootbomRef
let globalMaxId, isVerifying, activeMenuId, closeAllMenu, toggleMenu, vClickOutside
let refreshSerialNumber
let activeItemId, toggleItemMenu, closeItemMenu, ItemDetail,SupplierInfo,MoldInfo, WhereToUse,JigTools,InspectionTools,SIP,SOP,activeCateId
let openUnitDropdownId,showCateInput,newCateText,inputRef,openAddInput,addNewCate

// 输入过滤函数配合resources\js\Rules\notAllowedInputCharacters.js
const handleFilter = (val)=>{
  // exclude不传，代表启用全部黑名单
  const [ok, tip, newVal] = keyUp(val,[])
  if(!ok){
    console.warn(tip)
    // 清洗之后的值回填输入框
    item.name = newVal
  }
}

// 辅助函数：高亮显示匹配关键词
const highlightText = (text, keyword) => {
  // console.log('关键词：', keyword, '文本：', text)
  if (!keyword || !text) return text
  const reg = new RegExp(`(${keyword})`, 'gi')
  return String(text).replace(reg, '<span style="background:#ffeb3b;color:#000">$1</span>')
}


const pencilMenuList = ref([
  { label: '添加（同级）', onClick: (item,index)=>handleAddSibling(item,index) },
  { label: '添加子物料', onClick: (item)=>handleAddChild(item) },
  { label: '用已有编号', onClick: (item)=>selectFromExisting(item) },
  { label: '删除', danger:true, onClick: (index)=>handleDelete(index) },
])

// 类别下拉数据源
const cateOptionList =ref([
  {cate:"Plastic",value:1},
  {cate:"PCBA",value:2},
  {cate:"Metal",value:3},
  {cate:"Semi-ASS",value:4},
  {cate:"Std",value:5},

])

const chooseCate = (opt,rowItem)=>{
  if(!rowItem) return
  rowItem.cateName = opt.cate
  activeCateId.value = null
}
// 点击页面任意地方关闭下拉
// document.addEventListener("click",()=>openCate.value=false)

// 5. 生命周期钩子
onMounted(() => {  
  refreshAllParentId(props.bomData)// 页面挂载完毕，树形数据已经拿到，初始化所有节点父ID
    if(props.level === 0){
    document.addEventListener('click', closeAllMenu)
  }
})
onUnmounted(() => {
  if(props.level === 0){
    document.addEventListener('click', closeAllMenu)
  }
})
// 6. 根层级/子层级区分：注入全局状态 或 初始化全局状态并向下provide
if (props.level !== 0) {
  // 非根节点：全部从顶层注入全局状态与方法

  // 类别处理injection
  activeCateId = inject('activeCateId')
  showCateInput = inject('showCateInput')
  newCateText = inject('newCateText')
  inputRef = inject('inputRef')
  openAddInput = inject('openAddInput')
  addNewCate = inject('addNewCate')
  // chooseCate = inject('chooseCate')
  // cateOptionList = inject('cateOptionList')


  globalDraggingId = inject('globalDraggingId')
  globalDropTargetId = inject('globalDropTargetId')
  globalDropPosition = inject('globalDropPosition')
  executeGlobalMove = inject('executeGlobalMove')
  rootbomRef = inject('rootbomRef')
  globalMaxId = inject('globalMaxId')
  isVerifying = inject('globalIsVerifying')
  vClickOutside = inject('vClickOutside')
  refreshSerialNumber = inject('refreshSerialNumber')
  
  // 物料下拉菜单全套注入接收
  activeMenuId = inject('activeMenuId')
  activeItemId = inject('activeItemId')
 

  toggleMenu = inject('toggleMenu')
  toggleItemMenu = inject('toggleItemMenu')

  closeAllMenu = inject('closeAllMenu')
  closeItemMenu = inject('closeItemMenu')

//单位下拉菜单注入接收
  openUnitDropdownId = inject('openUnitDropdownId')

} else {
  // 根层级：初始化所有全局响应式状态、函数，最后批量向下注入子组件
  globalDraggingId = ref(null)
  globalDropTargetId = ref(null)
  globalDropPosition = ref(null)
  rootbomRef = props.bomData
  globalMaxId = ref(0)
  isVerifying = ref(false)

  // 铅笔右键菜单状态
  activeMenuId = ref(null)
  // 物料详情下拉菜单状态
  activeItemId = ref(null)
  //类别下拉
  activeCateId = ref(null)
  showCateInput = ref(false)
  newCateText = ref('')
  inputRef = ref(0)

  // 打开类别输入框并且自动聚焦
const openAddInput = async ()=>{
    showCateInput.value = true
    await nextTick()
    inputRef.value.focus()
  }


// 新增自定义类别
const addNewCate = (rowItem)=>{
    const name = newCateText.value.trim()
    if(!name) {
        showCateInput.value = false
        return
    }
    // 添加进下拉选项
    cateOptionList.value.push({
        cate:name,
        value:Date.now()
    })
    // 当前行赋值新类别
    rowItem.cateName = name
    // 重置全部状态、关闭弹窗
    newCateText.value = ''
    showCateInput.value = false
    activeCateId.value = null
}
  //关闭全部弹出菜单
  closeAllMenu = () => {
    activeMenuId.value = null
    activeItemId.value = null
    activeMenuNodeId.value = null // 关闭菜单清空高亮
    activeCateId.value = null// 关闭类别菜单
    showCateInput.value = false// 关闭类别输入框
  }

  // 关闭物料详情下拉菜单
  closeItemMenu = () => {
    activeItemId.value = null
    activeMenuNodeId.value = null // 关闭菜单清空高亮
  }
  // 切换物料详情下拉菜单
  toggleMenu = (nodeId) => {
    // console.log("toggleMenu called with nodeId:::::", nodeId);
    activeMenuId.value = null
    activeItemId.value = null    
    activeMenuId.value = activeMenuId.value === nodeId ? null : nodeId
  }
  // 切换物料详情下拉菜单
  toggleItemMenu = (nodeId) => {
    // console.log("toggleItemMenu--> called with nodeId:::::", nodeId);
    if (activeMenuNodeId.value === nodeId) {
      activeMenuNodeId.value = null
      activeItemId.value = null
    } else {
      // 新增：打开产品菜单前，强制关闭铅笔菜单！
      activeMenuId.value = null
      activeMenuNodeId.value = nodeId
      activeItemId.value = nodeId
    }
  } 


  // 点击外部关闭菜单自定义指令
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

  // 初始化树形最大节点ID
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



  // DFS遍历刷新全局流水序号sn
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

  // 监听BOM数据源变化，同步刷新序号、父ID
  watch(
    () => props.bomData,
    (newTree) => {
      rootbomRef = newTree // 同步更新全局树形引用
      refreshSerialNumber(newTree)
      refreshAllParentId(newTree)
    },
    { deep: true }
  )

  // 全局所有状态向下注入子孙组件
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
  provide('activeItemId', activeItemId)

  //类别处理
  provide('openAddInput', openAddInput)
  provide('chooseCate', chooseCate)
  provide('addNewCate', addNewCate)
  provide('activeCateId', activeCateId)
  provide('showCateInput', showCateInput)
  provide('newCateText', newCateText)
  provide('inputRef', inputRef)
  provide('cateOptionList', cateOptionList)


  provide('toggleItemMenu', toggleItemMenu)
  provide('closeItemMenu', closeItemMenu)
  provide('ItemDetail', ItemDetail)
  provide('WhereToUse', WhereToUse)
  provide('activeMenuNodeId', activeMenuNodeId)
  //根节点level===0内部
  provide('openUnitDropdownId', openUnitDropdownId)

}

// 7. 通用基础工具函数
/*** 新建节点默认模板 */
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
  cateName:"类别",
  nameEn: 'part name',
  nameCn: '中文产品名',
  quantity: 1,
  unit: 'pcs',
  unitPopupOpen: false,
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
    if(!node.cateName) node.cateName = "类别"
    // 递归遍历子节点，子节点父ID = 当前节点id
    if (node.children && node.children.length > 0) {
      refreshAllParentId(node.children, node.id);
    }
  });
}

// 8. 单元格字段编辑相关方法
// 打开单元格编辑状态
const openEditField = (item, fieldKey) => {
  item.editField = fieldKey
}

// 保存单元格编辑字段、计算小计、产品编号查重校验
const saveField = (item, event) => {
  item.editField = null
  item.subtotal = Number((item.quantity * item.price * (1 + item.wasteRate / 100)).toFixed(2))
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber()
}

// 9. 节点增删操作方法
// 新增同级节点
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

// 删除当前节点
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

// ******铅笔下拉菜单绑定回调函数
      const handleAddSibling = (item, idx) => {
        console.log('****添加（同级）')
        addSibling(idx)
        closeAllMenu()
      }
      const handleAddChild = (item) => {
        console.log('****添加子物料')
        addChild(item)
        closeAllMenu()
      }
      const selectFromExisting = (item) => {
        console.log('****用已有编号')
        // 此处可后续写打开选择已有产品编号弹窗逻辑
        closeAllMenu()
      }
      const handleDelete = (idx) => {
        deleteNode(idx)
        closeAllMenu()
      }
// ******物料编号相关操作下拉菜单绑定回调函数

  ItemDetail = (item) => {
    console.log(item.label+'****物料详情')
    activeItemId.value = null
  }
  SupplierInfo = (item) => {
    console.log('****供应商信息')
    activeItemId.value = null
  }
  MoldInfo = (item) => {
    console.log('****模具信息')
    activeItemId.value = null
  }
  WhereToUse = () => {
    console.log('****使用场合')
    activeItemId.value = null
  }
  JigTools = () => {
    console.log('****工装夹具')
    activeItemId.value = null
  }
  InspectionTools = () => {
    console.log('****检测工具')
    activeItemId.value = null
  }
  SIP = () => {
    console.log('****检验规范')
    activeItemId.value = null
  }
  SOP = () => {
    console.log('****操作指导')
    activeItemId.value = null
  }
  // 注释：以下弹窗菜单回调函数仅定义空壳，无内部业务实现，暂时未使用
  /*
  const SupplierInfo = () => {}
  const MoldInfo = () => {}
  const JigTools = () => {}
  const InspectionTools = () => {}
  const SIP = () => {}
  const SOP = () => {}
  */

// 10. 节点名称编辑逻辑
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

// 11. 子节点数据更新回调
const updateChildren = (item, newChildren) => {
  item.children = newChildren
  emit('update:bom-data', [...props.bomData])
  refreshSerialNumber()
}

// 12. 拖拽全套事件监听方法
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

// 13. 根节点拖拽移动核心逻辑
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

// 全局工具函数 
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

// 注释：escapeReg 重复封装了 escapeRegExp 逻辑，重复冗余函数
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