<template>
  <div class="w-full">
    <!-- 顶部栏 面包屑导航 -->
    <div class="bg-lime-400 w-full px-3 py-2 flex items-center gap-2 flex-wrap">
      <span>🗁文件管理器：</span>
      <template v-for="(item, index) in fileStore.breadcrumbList" :key="item.id">
        <span
          class="cursor-pointer hover:underline"
          @click="fileStore.selectFolder(item.id)"
        >
          {{ item.name }}
        </span>
        <span v-if="index < fileStore.breadcrumbList.length - 1">/</span>
      </template>
    </div>

    <!-- 左右弹性容器：横向铺满 -->
    <div class="flex w-full overflow-hidden relative">
      <!-- 左侧树形栏 -->
      <div
        ref="leftSideRef"
        :style="{ width: `${sideWidth}px` }"
        class="flex-shrink-0 bg-white border-r border-[#e4e7ed] p-3 flex flex-col min-h-[500px]"
      >
        <el-tree
            :data="treeData"
            node-key="id"
            default-expand-all
            :current-node-key="fileStore.currentParentId"
            :props="{ label: 'label', children: 'children' }"
            @node-click="handleTreeClick"
            @node-dblclick="treeNodeDblClick"
            draggable
            allow-drop
            :allow-drag="handleAllowDrag"
            :allow-drop="handleAllowDrop"
            @node-drop="handleNodeDrop"
            @node-drag-enter="handleDragEnter"
            @node-drag-leave="handleDragLeave"
        >
          <template #default="{ node }">
            <div 
              style="display:flex;align-items:center;gap:6px"
              :class="[
                draggingHoverId === node.id ? 'bg-red-200 rounded px-1 py-0.5 transition-colors' : '',
                node.id !== 0 ? 'cursor-grab active:cursor-grabbing' : ''
              ]"
            >
              <span v-if="node.id === 0">💾</span>
              <span v-else>{{ node.expanded ? '📂' : '📁' }}</span>
              <span>{{ node.label }}</span>
            </div>
          </template>
        </el-tree>
        <div class="mt-auto p-3 flex items-center gap-1.5 cursor-pointer rounded hover:bg-[#ff2706]">
          <el-icon><Delete /></el-icon>
          <span>回收站</span>
        </div>
      </div>

      <!-- 宽度拖拽分割线 -->
      <div
        ref="resizeLineRef"
        class="w-[3px] h-full absolute top-0 cursor-ew-resize select-none transition-colors duration-150 bg-transparent hover:bg-red-500 z-10"
        @mousedown.prevent="startDrag"
      ></div>

      <!-- 右侧文件列表区域 -->
      <div class="flex-1 p-3 overflow-y-auto">
        <div class="flex items-center gap-3 mb-5 flex-wrap">
          <el-button type="primary"><Upload /> 上传文件</el-button>
          <el-button @click="openNewFolder"><FolderAdd /> 📂新增文件夹</el-button>

          <div class="w-[260px] ml-auto">
            <el-input
              placeholder="搜索当前文件夹"
              v-model="fileStore.searchKey"
              clearable
            />
          </div>

          <div class="flex items-center gap-3">
            <el-icon><Grid /></el-icon>
            <el-icon><List /></el-icon>
            <el-icon><Setting /></el-icon>
            <span>已用0.0MB <el-link>升级</el-link></span>
          </div>
        </div>

        <div
          class="gap-2 w-full grid"
          style="grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));"
        >
          <div
            class="border border-[#e4e7ed] rounded p-2 pt-0 text-center cursor-pointer relative group select-none"
            :class="{ 'border-[#f78f10] bg-[#ecf5ff]': fileStore.selectedIds.includes(item.id) }"
            v-for="item in fileStore.filterFileList"
            :key="item.id"
            @click.stop.prevent="toggleSelect(item)"
            @dblclick.stop.prevent="enterFolder(item)"
          >
            <div
              class="absolute top-1 left-1 transition-opacity opacity-0 group-hover:opacity-100"
              :class="{ 'opacity-100': fileStore.selectedIds.includes(item.id) }"
            >
              <el-checkbox
                v-model="fileStore.selectedIds"
                :label="item.id"
                @click.stop
              />
            </div>

            <div class="absolute top-1 right-1 transition-opacity opacity-0 group-hover:opacity-100" @click.stop>
              <el-dropdown trigger="click">
                <div class="w-7 h-7 border border-gray-300 rounded flex items-center justify-center hover:bg-gray-100 text-lg">···</div>
                <template #dropdown>
                  <el-dropdown-menu>
                    <el-dropdown-item @click="openMoveDialogSingle(item)">
                      <el-icon><Move /></el-icon> 移动
                    </el-dropdown-item>
                    <el-dropdown-item @click="deleteSingleItem(item)">
                      <el-icon><Delete /></el-icon> 删除
                    </el-dropdown-item>
                  </el-dropdown-menu>
                </template>
              </el-dropdown>
            </div>

            <div class="text-[48px] leading-none mt-4">📁</div>

            <div
              @mouseenter="enterRename(item)"
              @mouseleave="cancelRename(item)"
              class="mt-1 inline-block"
            >
              <div
                v-if="renameItemId !== item.id"
                class="text-[14px] hover:text-blue-500 hover:underline select-none"
              >
                {{ item.name }}
              </div>
              <el-input
                v-else
                v-model="tempRenameName"
                size="small"
                ref="renameInputRef"
                @blur="confirmRename(item)"
                @keyup.enter="confirmRename(item)"
              />
            </div>
          </div>
        </div>

        <el-dropdown
          v-model="fileStore.contextMenu.show"
          :style="{ left: fileStore.contextMenu.x + 'px', top: fileStore.contextMenu.y + 'px', position: 'fixed' }"
          trigger="manual"
        >
          <template #dropdown>
            <el-dropdown-menu>
              <el-dropdown-item @click="openMoveDialog">
                <el-icon><Move /></el-icon> 移动
              </el-dropdown-item>
              <el-dropdown-item @click="fileStore.deleteSelected">
                <el-icon><Delete /></el-icon> 删除
              </el-dropdown-item>
            </el-dropdown-menu>
          </template>
          <span></span>
        </el-dropdown>

        <div class="flex items-center gap-4 pt-4 mt-2 border-t border-[#e4e7ed]">
          <div>
            <el-checkbox
              :model-value="isAllSelected"
              @change="handleSelectAll"
            >全选本页</el-checkbox>
          </div>
          <div class="flex items-center gap-2.5">
            <el-button size="small" @click="openMoveDialog">移动</el-button>
            <el-button size="small" @click="fileStore.deleteSelected">删除</el-button>
            <el-button size="small">下载</el-button>
            <el-button size="small">查看下载记录</el-button>
          </div>

          <el-pagination
            v-model:current-page="fileStore.page"
            v-model:page-size="fileStore.pageSize"
            :total="fileStore.total"
            layout="prev, pager, next"
            @size-change="()=>fileStore.page=1"
          />
          <el-select v-model="fileStore.pageSize" size="small">
            <el-option label="100条/页" value="100" />
          </el-select>
        </div>
        <div class="mt-2">本页{{ fileStore.filterFileList.length }}项 共{{ fileStore.total }}项</div>
      </div>
    </div>

    <el-dialog v-model="showNewFolderDialog" title="新建文件夹">
      <el-input
        v-model="newFolderName"
        placeholder="请输入文件夹名称"
        autofocus
        @keyup.enter="confirmCreateFolder"
      />
      <template #footer>
        <el-button @click="showNewFolderDialog = false">取消</el-button>
        <el-button type="primary" @click="confirmCreateFolder">确定创建</el-button>
      </template>
    </el-dialog>

    <el-dialog v-model="moveDialogVisible" title="移动到">
      <el-tree
        :key="moveDialogVisible"
        :data="treeData"
        node-key="id"
        default-expand-all
        :current-node-key="fileStore.currentParentId"
        :props="{ label: 'label', children: 'children' }"
        @node-click="doMove"
      >
        <template #default="{ node }">
          <div style="display:flex;align-items:center;gap:6px">
            <span v-if="node.id === 0">💾</span>
            <span v-else>{{ node.expanded ? '📂' : '📁' }}</span>
            <span>{{ node.label }}</span>
          </div>
        </template>
      </el-tree>
    </el-dialog>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onUnmounted } from 'vue'
import { useFileStore } from '../stores/fileStore.js'

const fileStore = useFileStore()
const moveDialogVisible = ref(false)

const treeData = computed(() => fileStore.folderTree)

// ========== 侧边栏宽度拖拽控制 ==========
const leftSideRef = ref(null)
const resizeLineRef = ref(null)
const SIDE_WIDTH_KEY = 'folder_side_width'
const MIN_WIDTH = 120
const MAX_WIDTH = 450
const INIT_WIDTH = 220
const sideWidth = ref(Number(localStorage.getItem(SIDE_WIDTH_KEY)) || INIT_WIDTH)

let isDragging = false
let startX = 0
let startWidth = 0

const startDrag = (e) => {
  isDragging = true
  startX = e.clientX
  startWidth = sideWidth.value
  document.addEventListener('mousemove', doDrag)
  document.addEventListener('mouseup', stopDrag)
}
const doDrag = (e) => {
  if (!isDragging) return
  const diff = e.clientX - startX
  let newWidth = startWidth + diff
  if (newWidth < MIN_WIDTH) newWidth = MIN_WIDTH
  if (newWidth > MAX_WIDTH) newWidth = MAX_WIDTH
  sideWidth.value = newWidth
  if (resizeLineRef.value) {
    resizeLineRef.value.style.left = `${newWidth}px`
  }
}
const stopDrag = () => {
  isDragging = false
  localStorage.setItem(SIDE_WIDTH_KEY, sideWidth.value)
  document.removeEventListener('mousemove', doDrag)
  document.removeEventListener('mouseup', stopDrag)
}
onUnmounted(() => {
  document.removeEventListener('mousemove', doDrag)
  document.removeEventListener('mouseup', stopDrag)
})

nextTick(() => {
  if (resizeLineRef.value) {
    resizeLineRef.value.style.left = `${sideWidth.value}px`
  }
})

// 树形点击切换目录
const handleTreeClick = (data) => {
  fileStore.selectFolder(data.id)
}
const treeNodeDblClick = (data) => {
  fileStore.selectFolder(data.id)
}

// 右侧多选逻辑
const toggleSelect = (item) => {
  const idx = fileStore.selectedIds.indexOf(item.id)
  if (idx > -1) {
    fileStore.selectedIds.splice(idx, 1)
  } else {
    fileStore.selectedIds.push(item.id)
  }
}
const enterFolder = (item) => {
  fileStore.selectFolder(item.id)
}

const isAllSelected = computed(() => {
  const pageIds = fileStore.filterFileList.map(i => i.id)
  return pageIds.length && pageIds.every(id => fileStore.selectedIds.includes(id))
})
const handleSelectAll = (val) => {
  const pageIds = fileStore.filterFileList.map(i => i.id)
  if (val) {
    fileStore.selectedIds = [...new Set([...fileStore.selectedIds, ...pageIds])]
  } else {
    fileStore.selectedIds = fileStore.selectedIds.filter(id => !pageIds.includes(id))
  }
}

// 批量移动弹窗
const openMoveDialog = () => {
  if (!fileStore.contextMenu.targetItem && !fileStore.selectedIds.length) return
  fileStore.tempMoveSingleItem = null
  moveDialogVisible.value = true
}
const doMove = (targetFolder) => {
  if (fileStore.tempMoveSingleItem) {
    const targetItem = fileStore.tempMoveSingleItem
    if (targetFolder.id === targetItem.id) return
    const idx = fileStore.fileList.findIndex(i => i.id === targetItem.id)
    if (idx > -1) fileStore.fileList[idx].parentId = targetFolder.id
    fileStore.tempMoveSingleItem = null
  } else if (fileStore.contextMenu.targetItem) {
    fileStore.moveItem(targetFolder.id)
  } else {
    fileStore.selectedIds.forEach(id => {
      const item = fileStore.fileList.find(i => i.id === id)
      if (item && item.id !== targetFolder.id) item.parentId = targetFolder.id
    })
    fileStore.selectedIds = []
  }
  moveDialogVisible.value = false
}

// 新建文件夹
const showNewFolderDialog = ref(false)
const newFolderName = ref('')
const openNewFolder = () => {
  newFolderName.value = ''
  showNewFolderDialog.value = true
}
const confirmCreateFolder = () => {
  const name = newFolderName.value.trim()
  if (!name) return
  fileStore.addFolder(name)
  showNewFolderDialog.value = false
}

// 单点移动、删除
const openMoveDialogSingle = (item) => {
  fileStore.tempMoveSingleItem = item
  moveDialogVisible.value = true
}
const deleteSingleItem = (item) => {
  fileStore.selectedIds = [item.id]
  fileStore.deleteSelected()
}

// 重命名逻辑
const renameItemId = ref(null)
const tempRenameName = ref('')
const renameInputRef = ref(null)
const enterRename = (item) => {
  renameItemId.value = item.id
  tempRenameName.value = item.name
  nextTick(() => {
    renameInputRef.value?.focus()
  })
}
const cancelRename = (item) => {
  if (renameItemId.value === item.id) {
    renameItemId.value = null
  }
}
const confirmRename = (item) => {
  const newName = tempRenameName.value.trim()
  if (newName && newName !== item.name) {
    const idx = fileStore.fileList.findIndex(f => f.id === item.id)
    if (idx > -1) fileStore.fileList[idx].name = newName
  }
  renameItemId.value = null
}

// 拖拽高亮控制【原版拖拽可用判定，无修改】
const draggingHoverId = ref(null)
const handleAllowDrag = () => true
const handleAllowDrop = () => true
const handleDragEnter = (draggingNode, dropNode) => {
  draggingHoverId.value = dropNode.id
}
const handleDragLeave = () => {
  draggingHoverId.value = null
}

// 拖拽结束【原版：仅修改对象属性，拖拽正常，但是右侧列表不更新】
const handleNodeDrop = (draggingNode, dropNode, type) => {
  draggingHoverId.value = null
  if (type !== 'inner') return

  const moveId = draggingNode.id
  const targetPid = dropNode.id

  // 直接修改对象属性，数据改了，但Pinia getter不刷新
  const targetFolder = fileStore.fileList.find(item => item.id === moveId)
  if (targetFolder) {
    targetFolder.parentId = targetPid
  }
}
</script>