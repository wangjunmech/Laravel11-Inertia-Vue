<template>
  <!-- 顶部标题栏 + 面包屑,flex-wrap控制自动换行 -->
  <div class="flex flex-wrap p-2 bg-emerald-300 text-sm items-center gap-1 ">
    <span>🗁</span>
    <span>文件管理器：</span>
    <span
      v-for="(item, idx) in fileStore.breadcrumbPath"
      :key="item.id"
      @click="fileStore.setCurrentParentId(item.id)"
      class="text-cyan-700 cursor-pointer hover:underline mx-1"
    >
      {{ item.name }}
      <span v-if="idx !== fileStore.breadcrumbPath.length - 1"> / </span>
    </span>
  </div>

  <!-- 最外层容器绑定点击关闭右键菜单 -->
  <div class="flex" @click="closeContextMenu">
    <!-- 左侧树形目录区域 -->
    <div class="text-sm w-fit border-r border-gray-200 p-3 min-h-[600px] bg-white flex flex-col">
      <div v-for="node in rootTree" :key="node.id" class="my-1">
        <RecursiveTreeNode
          :node="node"
          :active-id="fileStore.activeFolderId"
          :drag-hover-id="dragHoverId"
          :drag-highlight-color="dragHighlightColor"
          @node-click="onTreeClick"
          @node-drag-start="onDragStart"
          @node-drop="handleTreeDrop"
          @node-drag-over="handleDragOverNode"
          @node-drag-leave="handleDragLeaveNode"
        />
      </div>
      <div class="mt-auto mt-7 py-2 px-2 rounded-lg bg-red-400 h-10 flex items-center justify-center gap-2 cursor-pointer">
        <span>🗑️</span>
        <span>回收站</span>
      </div>
    </div>

    <!-- 右侧文件网格/列表区域 -->
    <div class="flex-1 bg-indigo-200 p-3">
      <!-- 顶部按钮栏 -->
      <div class="flex items-center flex-wrap gap-4 mb-6">
        <button @click="handleUpload" class="bg-indigo-600 text-white rounded-lg px-4 py-2">
          ⬆️ 上传文件
        </button>
        <button @click="openNewFolderModal" class="bg-yellow-100 border border-gray-300 rounded-lg px-4 py-2">
          📂 新增文件夹
        </button>
        <div class="items-center gap-4 ml-4">
          <span 
            @click="switchView('grid')"
            title="网格显示"
            class="cursor-pointer text-xl text-sky-600 "
            :class="currentView === 'grid' ? 'text-blue-600 font-bold' : 'text-gray-700'"
          >⊞</span>
          <span 
            @click="switchView('list')"
            title="列表显示"
            class="cursor-pointer"
            :class="currentView === 'list' ? 'text-blue-600 font-bold' : 'text-gray-700'"
          >☰</span>
          <span title="详情显示">⚙</span>
          <span>已用0.0MB <a href="#" class="text-cyan-700">升级</a></span>
        </div>
        <input
          v-model="searchKeyword"
          placeholder="🔍搜索当前文件夹"
          class="rounded-lg px-1 py-2 max-w-[220px] ml-auto border border-gray-300"
        >
      </div>

      <!-- 网格视图 -->
      <div v-if="currentView === 'grid'" class="grid grid-cols-[repeat(auto-fill,minmax(130px,1fr))] gap-3 select-none">
        <div
          v-for="item in currentFolderList"
          :key="item.id"
          @click.stop.prevent="toggleSelect(item)"
          @dblclick.stop.prevent="openFolder(item)"
          @contextmenu.prevent="openContextMenu($event, item)"
          class="m-2 rounded-lg border relative py-4 px-2 text-center cursor-pointer"
          :class="[
            selectedSet.has(item.id) ? 'border-amber-500 bg-[#eff6ff]' : 'border-gray-200 bg-white'
          ]"
          @mouseenter="hoverItemId=item.id"
          @mouseleave="hoverItemId=null"
        >
          <div class="absolute top-1.5 left-1.5 transition-opacity duration-200"
            :class="hoverItemId===item.id || selectedSet.has(item.id) ? 'opacity-100' : 'opacity-0'">
            <input type="checkbox" :checked="selectedSet.has(item.id)" @click.stop="toggleSelect(item)">
          </div>
          <div class="text-[50px] mt-3">📁</div>
          <div class="mt-2">
            <input
              v-if="editingItemId === item.id"
              ref="inlineInputRef"
              :data-id="item.id"
              v-model="tempEditName"
              @click.stop
              @blur="handleRenameBlur(item)"
              @keyup.enter="handleRenameEnter(item)"
              @keyup.esc="cancelInlineRename(true)"
              class="w-full border border-blue-500 px-1 py-0.5 text-sm outline-none"
            />
            <span
              v-else
              class="text-sm break-all inline-block w-full"
              @dblclick.stop="startInlineRename(item)"
            >
              {{ item.name }}
            </span>
          </div>
        </div>
      </div>

      <!-- 列表视图 -->
      <div v-if="currentView === 'list'" class="select-none bg-white rounded-lg border border-gray-200 overflow-hidden">
        <div class="flex items-center px-4 py-2 bg-gray-100 border-b border-gray-200 text-sm font-medium">
          <div class="w-8"><input type="checkbox" v-model="isSelectAll"></div>
          <div class="flex-1">名称</div>
          <div class="w-32">类型</div>
          <div class="w-40">修改时间</div>
          <div class="w-20">操作</div>
        </div>
        <div v-for="item in currentFolderList" :key="item.id" class="flex items-center px-4 py-3 border-b border-gray-200 hover:bg-gray-50">
          <div class="w-8">
            <input 
              type="checkbox" 
              :checked="selectedSet.has(item.id)" 
              @click.stop="toggleSelect(item)"
              @contextmenu.prevent="openContextMenu($event, item)"
            >
          </div>
          <div class="flex-1 flex items-center gap-2 cursor-pointer" 
               @click.stop.prevent="toggleSelect(item)"
               @dblclick.stop.prevent="item.id !== editingItemId.value ? (openFolder(item), startInlineRename(item)) : ''">
            <span>📁</span>
            <input
              v-if="editingItemId === item.id"
              ref="inlineInputRef"
              :data-id="item.id"
              v-model="tempEditName"
              @click.stop
              @blur="handleRenameBlur(item)"
              @keyup.enter="handleRenameEnter(item)"
              @keyup.esc="cancelInlineRename(true)"
              class="w-40 border border-blue-500 px-1 py-0.5 text-sm outline-none"
            />
            <span
              v-else
              class="text-sm break-all"
              @dblclick.stop="startInlineRename(item)"
            >
              {{ item.name }}
            </span>
          </div>
          <div class="w-32 text-sm text-gray-600">文件夹</div>
          <div class="w-40 text-sm text-gray-600">2025-06-22 10:00</div>
          <div class="w-20 flex gap-2">
            <button @click.stop="startInlineRename(item)" class="text-sm text-blue-600 hover:underline">重命名</button>
            <button @click.stop="menuDeleteHandle(item)" class="text-sm text-red-600 hover:underline">删除</button>
          </div>
        </div>
      </div>

      <!-- 底部操作栏 -->
      <div class="flex items-center gap-4 mt-8 pt-4 border-t border-gray-200 flex-wrap">
        <label>
          <input type="checkbox" v-model="isSelectAll"> 全选本页
        </label>
        <div class="flex gap-2">
          <button @click="openBatchMove" class="px-2 py-1 border border-gray-300 rounded">批量移动</button>
          <button @click="deleteSelected" class="px-2 py-1 border border-gray-300 rounded">多选删除</button>
          <button class="px-2 py-1 border border-gray-300 rounded">下载</button>
          <button class="px-2 py-1 border border-gray-300 rounded">查看下载记录</button>
        </div>
        <div class="ml-auto flex items-center gap-2">
          <button @click="page--" :disabled="page<=1">&lt;</button>
          <span>{{ page }}</span>
          <button @click="page++">&gt;</button>
          <select v-model="pageSize" class="border border-gray-300 rounded px-1">
            <option value="100">100条/页</option>
          </select>
        </div>
      </div>
      <div class="mt-2">本页{{ currentFolderList.length }}项 共{{ totalCount }}项</div>
    </div>
  </div>

  <!-- 右键菜单 -->
  <div
    v-if="contextMenu.show"
    class="fixed z-[100] bg-white border border-gray-200 shadow-lg rounded-md py-1 min-w-[110px]"
    :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
    @click.stop
  >
    <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" @click="menuRename">重命名</div>
    <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" @click="menuCopy">复制</div>
    <div class="px-4 py-2 hover:bg-gray-100 cursor-pointer" @click="menuMove">移动</div>
    <div class="border-t border-gray-200 my-1"></div>
    <div class="px-4 py-2 hover:bg-red-100 text-red-500 cursor-pointer" @click="menuDelete">删除</div>
  </div>

  <!-- 新建文件夹弹窗 -->
  <div v-if="showNewFolderModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[99]">
    <div class="bg-white rounded-lg w-[420px] p-5">
      <h3 class="text-lg mb-4 font-medium">新建文件夹</h3>
      <input
        ref="folderInputRef"
        v-model="newFolderName"
        placeholder="请输入文件夹名称（仅中文、英文、数字、下划线）"
        class="w-full border border-gray-300 rounded-lg px-3 py-2 box-border"
        @keyup.enter="createFolder"
      >
      <div class="flex justify-end gap-3 mt-5">
        <button @click="showNewFolderModal=false" class="px-4 py-2 border border-gray-300 rounded-lg bg-white">取消</button>
        <button @click="createFolder" class="px-4 py-2 bg-blue-500 text-white rounded-lg border-none">确定创建</button>
      </div>
    </div>
  </div>

  <!-- 拖拽移动确认弹窗 -->
  <div v-if="dragConfirmModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[99]">
    <div class="bg-white rounded-lg w-[440px] p-5">
      <h3 class="text-lg mb-4 font-medium">移动确认</h3>
      <p>确定要将 <b class="text-blue-500">{{ dragMoveName }}</b> 移入--> <b class="text-red-500">{{ dragTargetName }}</b> 文件夹内吗？</p>
      <div class="flex justify-end gap-3 mt-5">
        <button @click="cancelDragMove" class="px-4 py-2 border border-gray-300 rounded-lg bg-white">取消</button>
        <button @click="confirmDragMove" class="px-4 py-2 bg-blue-500 text-white rounded-lg border-none">确定移入</button>
      </div>
    </div>
  </div>

  <!-- 禁止父拖入子的提示弹窗 -->
  <div v-if="forbidDragModal" class="fixed inset-0 bg-black/40 flex items-center justify-center z-[99]">
    <div class="bg-white rounded-lg w-[440px] p-5">
      <h3 class="text-lg mb-4 font-medium text-red-500">🚫操作禁止</h3>
      <p v-html="forbidDragMsg"></p>
      <div class="flex justify-end gap-3 mt-5">
        <button @click="forbidDragModal=false" class="px-4 py-2 bg-blue-500 text-white rounded-lg border-none">确定</button>
      </div>
    </div>
  </div>

  <!-- 拖拽跟随提示浮窗【新增】 -->
<div
  v-if="dragTip.show"
  class="fixed z-[9999] bg-yellow-300 px-3 py-1.5 rounded text-sm whitespace-nowrap"
  :style="{
    left: dragTip.x + 15 + 'px',
    top: dragTip.y + 15 + 'px'
  }"
  @mouseenter="hideTipWhenHoverSelf"
  @mouseleave="clearTipTimer"
>
  {{ dragTip.text }}
</div>
</template>

<script setup>
import { ref, computed, defineComponent, h ,nextTick } from 'vue'
import { defineStore } from 'pinia'


const folderInputRef = ref([])
const inlineInputRef = ref([])
let editingItemId = ref(null)
let tempEditName = ref('')
let originalNameBackup = ref('')
// 防重复执行锁：解决回车失焦双重触发两次弹窗
let isRenameProcessing = ref(false)
// 死循环拦截标记：自动聚焦时跳过本次blur校验
let needSkipNextBlur = ref(false)

const currentView = ref('grid')
const dragHoverId = ref(null)
const dragHighlightColor = ref('#87CEEB')

// ========== 拖拽提示 新增变量 ==========
const dragTip = ref({
  show: false,
  x: 0,
  y: 0,
  text: ''
})
let draggingFolderId = ref(null)
let moveListener = null
let upListener = null


const contextMenu = ref({
  show: false,
  x: 0,
  y: 0,
  targetItem: null
})

// 名校验公共函数
function isNameValidChar(name) {
  if (!name) return false
  const reg = /^[\u4e00-\u9fa5a-zA-Z0-9_]+$/
  return reg.test(name)
}

function isNameDuplicate(name, excludeId = null) {
  return fileStore.currentDirList.some(item => {
    return item.name === name && item.id !== excludeId
  })
}

// 禁止拖拽相关
const forbidDragModal = ref(false)
const forbidDragMsg = ref('')
function isParentChildRelation(parentId, childId) {
  const targetFolder = fileStore.folderList.find(f => f.id === childId)
  if (!targetFolder) return false
  
  const findParent = (currentId) => {
    const folder = fileStore.folderList.find(f => f.id === currentId)
    if (!folder || folder.parentId === 0) return false
    if (folder.parentId === parentId) return true
    return findParent(folder.parentId)
  }
  
  return findParent(childId)
}

// Pinia仓库
const useFileStore = defineStore('fileManager', {
  state: () => ({
    currentParentId: 0,
    activeFolderId: null,
    folderList: [
      { id: 1, name: '新建文件夹1', parentId: 0 },
      { id: 2, name: '新建文件夹2', parentId: 1 },
      { id: 3, name: '新建文件夹3', parentId: 2 },
      { id: 4, name: '新建文件夹4', parentId: 2 },
      { id: 5, name: '新建文件夹5', parentId: 3 },
      { id: 6, name: '新建文件夹6', parentId: 4 },
    ],
    selectedIds: [],
  }),
  getters: {
    currentDirList(state) {
      return state.folderList.filter(f => f.parentId === state.currentParentId)
    },
treeRoot(state) {
  const buildTree = (pid, visited = new Set()) => {
    // 防循环：如果已经遍历过该ID，直接终止，杜绝无限递归
    if (visited.has(pid)) return []
    visited.add(pid)
    return state.folderList
      .filter(item => item.parentId === pid)
      .map(item => ({
        id: item.id,
        name: item.name,
        parentId: item.parentId,
        children: buildTree(item.id, visited)
      }))
  }
  return [{ id: 0, name: '全部', parentId: null, children: buildTree(0) }]
},
    breadcrumbPath(state) {
      const path = []
      let curr = state.currentParentId
      const all = state.folderList
      while (true) {
        if (curr === 0) {
          path.unshift({ id: 0, name: '全部' })
          break
        }
        const find = all.find(f => f.id === curr)
        if (!find) {
          path.unshift({ id: 0, name: '全部' })
          break
        }
        path.unshift(find)
        curr = find.parentId
      }
      return path
    }
  },
  actions: {
    setCurrentParentId(id) {
      this.currentParentId = id
      this.selectedIds = []
      this.activeFolderId = null
    },
    setActiveFolderId(id) {
      this.activeFolderId = id
    },
    addFolder(name) {
      const newId = Date.now()
      this.folderList.push({
        id: newId,
        name,
        parentId: this.currentParentId
      })
    },
    moveFolder(moveId, targetParentId) {
      const copy = JSON.parse(JSON.stringify(this.folderList))
      const item = copy.find(f => f.id === moveId)
      if (item) item.parentId = targetParentId
      this.folderList = copy
      this.activeFolderId = null
    },
    setSelectedIds(arr) {
      this.selectedIds = [...arr]
    },
    renameFolder(id, newName) {
      const item = this.folderList.find(f => f.id === id)
      if (item) item.name = newName
    },
  copyFolderById(copyId, targetParentId) {
  // 拿到源文件夹
  const sourceItem = this.folderList.find(f => f.id === copyId)
  if (!sourceItem) return

  // 处理副本名称，避免同目录重名
  let baseName = sourceItem.name
  let copyName = `${baseName}_副本`
  let repeatNum = 1
  while (this.folderList.some(f => f.parentId === targetParentId && f.name === copyName)) {
    repeatNum++
    copyName = `${baseName}_副本${repeatNum}`
  }

  // 存储【源ID → 新副本ID】映射，防止递归查找混乱
  const idMap = new Map()

  // 单层递归：只遍历原始数组，单向向下复制，不会回头循环
  const recursiveCopy = (srcId, newPid) => {
    const srcFolder = this.folderList.find(f => f.id === srcId)
    if (!srcFolder) return
    const newId = Date.now()
    idMap.set(srcId, newId)
    this.folderList.push({
      id: newId,
      name: srcFolder.name,
      parentId: newPid
    })
    // 只查找当前源的直接子级，继续复制
    const children = this.folderList.filter(f => f.parentId === srcId)
    children.forEach(child => recursiveCopy(child.id, newId))
  }

  // 创建顶层副本
  const topNewId = Date.now()
  idMap.set(copyId, topNewId)
  this.folderList.push({
    id: topNewId,
    name: copyName,
    parentId: targetParentId
  })

  // 复制原文件夹所有子结构，挂载到新顶层副本下
  const sourceDirectChildren = this.folderList.filter(f => f.parentId === copyId)
  sourceDirectChildren.forEach(child => recursiveCopy(child.id, topNewId))
},
deleteFolderById(delId) {
  let delAllIds = []
  // 递归遍历原始文件夹数组，找出目标ID + 所有后代ID
  const getAllChildIds = (parentId) => {
    this.folderList.forEach(folder => {
      if (folder.parentId === parentId) {
        delAllIds.push(folder.id)
        getAllChildIds(folder.id)
      }
    })
  }
  // 先把自己加入待删列表
  delAllIds.push(delId)
  // 递归收集所有子级
  getAllChildIds(delId)
  // 过滤删除
  this.folderList = this.folderList.filter(item => !delAllIds.includes(item.id))
  this.activeFolderId = null
}
  }
})

// 递归树形组件
const RecursiveTreeNode = defineComponent({
  props: ['node', 'activeId', 'dragHoverId', 'dragHighlightColor'],
  emits: ['node-click', 'node-drag-start', 'node-drop', 'node-drag-over', 'node-drag-leave'],
  setup(props, { emit }) {
    const isExpanded = ref(true)
    const toggleExpand = () => {
      if (props.node.children?.length) isExpanded.value = !isExpanded.value
    }
    const handleClick = () => emit('node-click', props.node)

    // 鼠标移入悬浮提示：XX文件夹可拖拽
    const nodeMouseEnter = (e) => {
      // 根节点、拖拽进行中不显示普通hover提示
      if (props.node.id === 0 || draggingFolderId.value !== null) return
      dragTip.value.text = `${props.node.name}文件夹可拖拽`
      dragTip.value.show = true
      dragTip.value.x = e.clientX
      dragTip.value.y = e.clientY
    }
    // 鼠标移出关闭hover提示
    const nodeMouseLeave = () => {
      if (draggingFolderId.value !== null) return
      dragTip.value.show = false
    }
    // 鼠标左键按下，开启拖拽跟随提示
const nodeMouseDown = (e) => {
  if (props.node.id === 0 || e.button !== 0) return
  draggingFolderId.value = props.node.id
  dragTip.value.text = `${props.node.name}文件夹，可拖动到`
  dragTip.value.show = true
  dragTip.value.x = e.clientX
  dragTip.value.y = e.clientY

  // 单击松开鼠标就自动隐藏提示，防止点击残留
  const clickUpHandler = () => {
    dragTip.value.show = false
    draggingFolderId.value = null
    window.removeEventListener('mouseup', clickUpHandler)
  }
  window.addEventListener('mouseup', clickUpHandler, { once: true })
}

const dragStart = (e) => {
  // 清空系统默认拖拽图片
//   e.dataTransfer.setDragImage(new Image(), 0, 0)
  e.dataTransfer.setData('dragId', props.node.id)
  emit('node-drag-start', props.node.id)
}
    const dragover = (e) => {
      e.preventDefault()
      e.stopPropagation()
      emit('node-drag-over', props.node.id)
      // 拖拽悬浮目标文件夹，切换放置提示文案
      if (draggingFolderId.value && props.node.id !== 0 && draggingFolderId.value !== props.node.id) {
        const dragItem = fileStore.folderList.find(f => f.id === draggingFolderId.value)
        dragTip.value.text = `${dragItem.name}文件夹，可以放置在${props.node.name}当中`
      }
    }
    const dragleave = (e) => {
      e.stopPropagation()
      emit('node-drag-leave')
      // 离开目标文件夹，恢复拖拽默认文案
      if (draggingFolderId.value) {
        const dragItem = fileStore.folderList.find(f => f.id === draggingFolderId.value)
        dragTip.value.text = `${dragItem.name}文件夹，可拖动到`
      }
    }
const drop = (e) => {
  e.preventDefault()
  e.stopPropagation()
  const dragId = Number(e.dataTransfer.getData('dragId'))
  if (dragId !== props.node.id) {
    emit('node-drop', dragId, props.node.id)
  }
  // 拖拽放下，隐藏提示
  dragTip.value.show = false
  draggingFolderId.value = null
}

    const isActive = computed(() => props.node.id === props.activeId)
    const isDragHover = computed(() => props.node.id === props.dragHoverId)
// 拖拽全程实时触发，专门用来同步浮窗位置
    const onDragMoving = (e) => {
    dragTip.value.x = e.clientX
    dragTip.value.y = e.clientY
    }
    return () => h('div', { class: 'ml-4 py-1' }, [
      h('div', {
        draggable: props.node.id !== 0,
          ondragstart: dragStart,
          ondrag: onDragMoving,
        ondragover: dragover,
        ondragleave: dragleave,
        ondrop: drop,
        onClick: handleClick,
        onmouseenter: nodeMouseEnter,
        onmouseleave: nodeMouseLeave,
          onmousedown: nodeMouseDown,
        
        class: [
          'flex items-center gap-1.5 px-1.5 py-1 rounded cursor-pointer transition-colors duration-150',
          isActive.value ? 'bg-red-500 text-white' : ''
        ],
        style: {
          cursor: props.node.id === 0 ? 'default' : 'grab',
          backgroundColor: isDragHover.value ? props.dragHighlightColor : ''
        }
      }, [
        props.node.children?.length
          ? h('span', { onClick: toggleExpand, class: 'cursor-pointer' }, isExpanded.value ? '▼' : '▶')
          : h('span', { class: 'w-3.5' }, ''),
        h('span', props.node.id === 0 ? '💾' : isExpanded.value ? '📂' : '📁'),
        h('span', props.node.name)
      ]),
      isExpanded.value && props.node.children?.length
        ? h('div', {}, props.node.children.map(child => h(RecursiveTreeNode, {
            node: child,
            key: child.id,
            activeId: props.activeId,
            dragHoverId: props.dragHoverId,
            dragHighlightColor: props.dragHighlightColor,
            onNodeClick: (val) => emit('node-click', val),
            onNodeDragStart: (val) => emit('node-drag-start', val),
            onNodeDrop: (a,b) => emit('node-drop', a,b),
            onNodeDragOver: (nid) => emit('node-drag-over', nid),
            onNodeDragLeave: () => emit('node-drag-leave')
          })))
        : null
    ])
  }
})

// 页面主逻辑
const fileStore = useFileStore()
const searchKeyword = ref('')
const page = ref(1)
const pageSize = ref(100)
const hoverItemId = ref(null)
const selectedSet = computed(() => new Set(fileStore.selectedIds))
const totalCount = computed(() => fileStore.currentDirList.length)
const rootTree = computed(() => fileStore.treeRoot)

const currentFolderList = computed(() => {
  let list = fileStore.currentDirList
  if (searchKeyword.value) {
    list = list.filter(f => f.name.includes(searchKeyword.value))
  }
  const start = (page.value - 1) * pageSize.value
  return list.slice(start, start + pageSize.value)
})

// 全选
const isSelectAll = computed({
  get() {
    if (!currentFolderList.value.length) return false
    return currentFolderList.value.every(item => selectedSet.value.has(item.id))
  },
  set(val) {
    if (val) {
      fileStore.setSelectedIds(currentFolderList.value.map(i => i.id))
      fileStore.setActiveFolderId(null)
    } else {
      fileStore.setSelectedIds([])
      fileStore.setActiveFolderId(null)
    }
  }
})

// 视图切换
const switchView = (viewType) => {
  currentView.value = viewType
  if(editingItemId.value) cancelInlineRename()
}

const toggleSelect = (item) => {
  if(editingItemId.value !== null && editingItemId.value !== item.id){
    cancelInlineRename()
  }
  const arr = [...selectedSet.value]
  const idx = arr.indexOf(item.id)
  if (idx > -1) {
    arr.splice(idx, 1)
    fileStore.setActiveFolderId(null)
  } else {
    arr.push(item.id)
    fileStore.setActiveFolderId(item.id)
  }
  fileStore.setSelectedIds(arr)
}

const openFolder = (item) => {
  if(editingItemId.value === item.id) return
  fileStore.setCurrentParentId(item.id)
  page.value = 1
}

// 新建文件夹
const openNewFolderModal = () => {
  if(editingItemId.value) cancelInlineRename()
  newFolderName.value = ''
  showNewFolderModal.value = true
  nextTick(() => {
    folderInputRef.value?.focus()
  })
}
const showNewFolderModal = ref(false)
const newFolderName = ref('')

const createFolder = () => {
  const name = newFolderName.value.trim()
  if (!name) {
    alert('文件夹名称不能为空')
    folderInputRef.value?.focus()
    return
  }
  if(isNameDuplicate(name)){
    alert(`当前目录已存在名为「${name}」的文件夹，无法创建！`)
    folderInputRef.value?.focus()
    return
  }
  if (!isNameValidChar(name)) {
    alert('文件夹名称只能包含中文、英文、数字和下划线，禁止使用其他符号！')
    folderInputRef.value?.focus()
    return
  }
  fileStore.addFolder(name)
  showNewFolderModal.value = false
}

// 拖拽相关
const dragConfirmModal = ref(false)
const dragMoveId = ref(null)
const dragTargetId = ref(null)
const dragMoveName = computed(() => fileStore.folderList.find(f => f.id === dragMoveId.value)?.name || '')
const dragTargetName = computed(() => {
  if (dragTargetId.value === 0) return '全部'
  return fileStore.folderList.find(f => f.id === dragTargetId.value)?.name || ''
})

const onDragStart = (id) => {
  if(editingItemId.value) cancelInlineRename()
  dragMoveId.value = id
}

const handleDragOverNode = (nodeId) => {
  dragHoverId.value = nodeId
}
const handleDragLeaveNode = () => {
  dragHoverId.value = null
}

const handleTreeDrop = (moveId, targetId) => {
  dragHoverId.value = null
  
  if (isParentChildRelation(moveId, targetId)) {
    forbidDragMsg.value = `禁止将父文件夹<b class="text-blue-500">【${fileStore.folderList.find(f => f.id === moveId)?.name}】</b>拖入其子文件夹【${fileStore.folderList.find(f => f.id === targetId)?.name}】！`
    forbidDragModal.value = true
    dragMoveId.value = null
    dragTargetId.value = null
    return
  }

  dragMoveId.value = moveId
  dragTargetId.value = targetId
  dragConfirmModal.value = true
}

const confirmDragMove = () => {
  fileStore.moveFolder(dragMoveId.value, dragTargetId.value)
  dragConfirmModal.value = false
  dragMoveId.value = null
  dragTargetId.value = null
  dragHoverId.value = null
}

const cancelDragMove = () => {
  dragConfirmModal.value = false
  dragMoveId.value = null
  dragTargetId.value = null
  dragHoverId.value = null
}

const onTreeClick = (node) => {
  if(editingItemId.value) cancelInlineRename()
  fileStore.setCurrentParentId(node.id)
  page.value = 1
}

// 占位函数
const handleUpload = () => alert('上传功能待开发')
const openItemMenu = () => {}
const openBatchMove = () => {}
const deleteSelected = () => {
  // 拿到选中ID数组
  const selectedArr = [...selectedSet.value]
  if (selectedArr.length === 0) {
    alert('请先勾选要删除的文件夹')
    return
  }
  if (!confirm(`确定批量删除选中的${selectedArr.length}个文件夹及其所有子内容？删除后不可恢复`)) return

  // 逐个调用删除
  selectedArr.forEach(id => {
    const findItem = fileStore.folderList.find(f => f.id === id)
    if (findItem) fileStore.deleteFolderById(findItem.id)
  })

  // 清空选中
  fileStore.setSelectedIds([])
  fileStore.setActiveFolderId(null)
}

// 右键菜单
const openContextMenu = (e, item) => {
  if(editingItemId.value && editingItemId.value !== item.id){
    cancelInlineRename()
  }
  contextMenu.value.targetItem = item
  contextMenu.value.x = e.clientX
  contextMenu.value.y = e.clientY
  contextMenu.value.show = true
}
const closeContextMenu = () => {
  contextMenu.value.show = false
  if(editingItemId.value) cancelInlineRename()
}

const menuRename = () => {
  const item = contextMenu.value.targetItem
  startInlineRename(item)
  contextMenu.value.show = false
}
const startInlineRename = (item) => {
  editingItemId.value = item.id
  originalNameBackup.value = item.name
  tempEditName.value = item.name
  isRenameProcessing.value = false
  needSkipNextBlur.value = false
  
  nextTick(()=>{
    const inputEl = inlineInputRef.value.find(el => el?.dataset.id === item.id.toString())
    if (inputEl) {
      inputEl.focus()
      inputEl.select()
    }
  })
}

// ====================== 重命名入口拆分：回车 / 失焦分开绑定 ======================
// 回车提交
const handleRenameEnter = (item) => {
  if(isRenameProcessing.value) return
  isRenameProcessing.value = true
  const res = confirmRename(item)
  // 无论成功/取消，回车提交后都结束编辑状态
  cancelInlineRename(true)
  nextTick(()=>{
    isRenameProcessing.value = false
  })
}

// 失焦事件处理（修复死循环核心）
const handleRenameBlur = (item) => {
  if(isRenameProcessing.value) return
  // 自动聚焦产生的blur直接跳过，根治死循环核心
  if(needSkipNextBlur.value){
    needSkipNextBlur.value = false
    return
  }
  const trimName = tempEditName.value.trim()
  // 前后名称完全一致，直接退出，不校验、不弹窗
  if(trimName === originalNameBackup.value){
    cancelInlineRename(true)
    return
  }
  // confirmRename 内部已经处理成功/取消的退出逻辑，这里不需要额外收尾
  confirmRename(item)
}

// ====================== 严格按你4条规则重命名核心逻辑 ======================
const confirmRename = (item) => {
  const newName = tempEditName.value.trim()

  // 兜底拦截：名称和原名称一致，直接退出，终止后续所有弹窗逻辑
  if (newName === originalNameBackup.value) {
    cancelInlineRename(true)
    return false
  }

  // 1、空名称：直接弹窗，不弹确认框
  if (!newName) {
    alert('文件夹名称不能为空')
    nextTick(() => {
      const inputEl = inlineInputRef.value.find(el => el?.dataset.id === item.id.toString())
      if (inputEl) inputEl.focus()
    })
    return false
  }

  // 2、重名：弹窗提示同名，设置标记跳过下一次blur，防止死循环
  if (isNameDuplicate(newName, item.id)) {
    alert(`当前目录下已有“${newName}”的同名文件，请重新修改名称！`)
    // 标记：接下来focus触发的blur直接跳过
    needSkipNextBlur.value = true
    nextTick(() => {
      const inputEl = inlineInputRef.value.find(el => el?.dataset.id === item.id.toString())
      if (inputEl) {
        inputEl.focus()
        inputEl.select()
      }
    })
    return false
  }

  // 走到这里：名称非空、不重名，弹出确认修改弹窗
  const isConfirm = confirm(`是否确认修改文件名为「${newName}」？\n取消则恢复原名称「${originalNameBackup.value}」`)
  if (!isConfirm) {
    // 点击取消：标记防死循环 + 退出编辑、自动恢复原名
    needSkipNextBlur.value = true
    cancelInlineRename(true) // 新增：取消直接退出编辑，还原文件名
    return false
  }

  // 3、特殊非法字符：确认点击后再弹规则提示
  if (!isNameValidChar(newName)) {
    alert('文件夹名称只能包含中文、英文、数字和下划线，禁止使用其他符号！')
    needSkipNextBlur.value = true
    nextTick(() => {
      const inputEl = inlineInputRef.value.find(el => el?.dataset.id === item.id.toString())
      if (inputEl) {
        inputEl.focus()
        inputEl.select()
      }
    })
    return false
  }

  // 4、全部校验通过，执行重命名
  fileStore.renameFolder(item.id, newName)
  cancelInlineRename(true) // 新增：改名成功后退出编辑，输入框消失
  return true
}

// ESC强制取消编辑
const cancelInlineRename = (isForce = false) => {
  isRenameProcessing.value = false
  needSkipNextBlur.value = false
  editingItemId.value = null
  tempEditName.value = ''
  originalNameBackup.value = ''
}

// 判断指定文件夹是否存在下级子文件夹
function hasChildrenFolder(folderId) {
  return fileStore.folderList.some(f => f.parentId === folderId)
}
const menuCopy = () => {
  const item = contextMenu.value.targetItem
  if (!item) return
  // 根目录禁止复制
  if (item.id === 0) {
    alert('根目录【全部】不允许复制')
    contextMenu.value.show = false
    return
  }

  // 如果正在重命名，先关闭编辑，防止界面错乱
  if (editingItemId.value !== null) {
    cancelInlineRename(true)
  }

  let needCopyAllChild = true
  // 判断是否含有子文件夹，弹出确认提示
  if (hasChildrenFolder(item.id)) {
    needCopyAllChild = confirm(`文件夹【${item.name}】包含下级子文件夹，是否连同所有子文件夹一并完整复制？\n取消则仅复制当前空文件夹外壳`)
    if (!needCopyAllChild) {
      // 用户选择不复制子文件夹，仅复制当前单层文件夹
      const sourceItem = fileStore.folderList.find(f => f.id === item.id)
      if (!sourceItem) {
        contextMenu.value.show = false
        return
      }
      // 处理副本名称防重
      let baseName = sourceItem.name
      let copyName = `${baseName}_副本`
      let repeatNum = 1
      while (fileStore.folderList.some(f => f.parentId === fileStore.currentParentId && f.name === copyName)) {
        repeatNum++
        copyName = `${baseName}_副本${repeatNum}`
      }
      const newId = Date.now()
      fileStore.folderList.push({
        id: newId,
        name: copyName,
        parentId: fileStore.currentParentId
      })
      contextMenu.value.show = false
      // 复制完成，自动触发副本重命名
      nextTick(() => {
        const newItem = fileStore.folderList.find(f => f.id === newId)
        if (newItem) startInlineRename(newItem)
      })
      return
    }
  }

  // 用户确认连带子文件夹一起复制，调用原有递归复制逻辑
  fileStore.copyFolderById(item.id, fileStore.currentParentId)
  contextMenu.value.show = false

  // ========== 关键：复制完成后，查找最新生成的副本，自动进入重命名 ==========
  const sourceItem = fileStore.folderList.find(f => f.id === item.id)
  let baseName = sourceItem.name
  let copyName = `${baseName}_副本`
  let repeatNum = 1
  while (fileStore.folderList.some(f => f.parentId === fileStore.currentParentId && f.name === copyName)) {
    repeatNum++
    copyName = `${baseName}_副本${repeatNum}`
  }
  // 找到刚复制出来的副本对象
  const newCopyItem = fileStore.folderList.find(f =>
    f.parentId === fileStore.currentParentId && f.name === copyName
  )
  if (newCopyItem) {
    nextTick(() => {
      startInlineRename(newCopyItem)
    })
  }
}
const menuMove = () => {
  alert('移动：拖动文件夹到左侧目标目录完成移动')
  contextMenu.value.show = false
}


// 单选删除文件夹（完善版：递归删除+清理选中、拖拽、编辑态）
const menuDeleteHandle = (item) => {
  // 禁止删除根目录
  if (item.id === 0) {
    alert('根目录【全部】禁止删除！')
    return
  }
  // 先判断当前是否正在重命名，强制退出编辑
  if (editingItemId.value !== null) {
    cancelInlineRename(true)
  }

  const confirmTip = `确定要删除【${item.name}】，该文件夹及其所有下级子文件夹将全部永久删除，不可恢复？`
  if (!confirm(confirmTip)) return

  // 执行仓库递归删除
  fileStore.deleteFolderById(item.id)

  // 清理选中列表：把被删除ID从选中集合剔除
  let newSelected = [...selectedSet.value].filter(id => id !== item.id)
  fileStore.setSelectedIds(newSelected)

  // 清空当前激活文件夹高亮
  fileStore.setActiveFolderId(null)

  // 如果当前进入的父目录刚好被删掉，自动切回根目录
  if (fileStore.currentParentId === item.id) {
    fileStore.setCurrentParentId(0)
    page.value = 1
  }

  // 清理拖拽悬浮残留
  dragHoverId.value = null
}
const menuDelete = () => {
  const item = contextMenu.value.targetItem
  if (!item) return
  // 关闭右键菜单
  contextMenu.value.show = false
  // 执行删除逻辑
  menuDeleteHandle(item)
}

let tipHideTimer = null
// 鼠标移入提示框，延迟隐藏
const hideTipWhenHoverSelf = () => {
  // 拖拽过程禁止自动隐藏
  if (draggingFolderId.value) return
  clearTimeout(tipHideTimer)
  tipHideTimer = setTimeout(() => {
    dragTip.value.show = false
  }, 300)
}
// 鼠标离开提示框，取消隐藏定时器
const clearTipTimer = () => {
  clearTimeout(tipHideTimer)
}
</script>