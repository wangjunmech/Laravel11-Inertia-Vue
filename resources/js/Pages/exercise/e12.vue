<template>
  <div class="p-5">
    <!-- 面包屑顶部栏 -->
    <div class="mb-5 flex flex-wrap items-center justify-between gap-4">
      <div class="flex items-center gap-3 shrink-0">
        <label for="no-repeat-name-globally" class="inline-flex items-center gap-1">
          <input
            type="checkbox"
            name="no-repeat-name-globally"
            id="no-repeat-name-globally"
            v-model="globalUnique"
            @change="handleGlobalCheckChange"
          >
          <span>全局不重名</span>
        </label>
        <label for="siblingCheck" class="inline-flex items-center gap-1">
          <input
            type="checkbox"
            name="siblingCheck"
            id="siblingCheck"
            v-model="siblingUnique"
            @change="handleSiblingCheckChange"
          >
          <span>局部不重名</span>
        </label>
        <!-- 修正按钮点击事件 -->
        <span
          @click="triggerFileSelect"
          class="bg-red-500 text-white rounded-full px-2 py-1 cursor-pointer select-none"
        >
          导入数据
        </span>
        <span
          @click="saveDataToBackend"
          class="bg-red-500 text-white rounded-full px-2 py-1 cursor-pointer select-none"
        >
          保存数据到后台
        </span>

      </div>

      <div class="flex items-center gap-2 shrink-0">
        <span
          @click="openDataModal('global')"
          class="bg-red-500 text-white rounded-full px-2 py-1 cursor-pointer select-none"
        >
          预览结构数据
        </span>
        <span
          @click="showUniqueGlobalData"
          class="bg-sky-500 text-white rounded-full px-2 py-1 cursor-pointer select-none"
        >
          预览数据
        </span>
        <span
          @click="openDataModal('current')"
          class="bg-green-500 text-white rounded-full px-2 py-1 cursor-pointer select-none"
        >
          预览当前链
        </span>

      </div>
    </div>

    <!-- 面包屑中间路径 -->
    <div class="mb-2 min-w-[200px] flex-1">
      <span class="text-gray-800 mr-2">当前路径：</span>
      <span
        v-for="(item, index) in breadList"
        :key="item.id"
        class="px-1.5 cursor-pointer text-gray-700 hover:text-rose-500"
        @click="handleBreadClick(item)"
      >
        {{ item.area }}
        <span v-if="index < breadList.length - 1" class="mx-2 text-gray-400">/</span>
      </span>
    </div>

    <!-- 级联菜单容器 -->
    <div ref="menuWrapRef" class="relative border border-lime-400">
      <div
        v-for="(column, colIdx) in menuColumns"
        :key="colIdx"
        class="absolute top-0 bg-white border-l border-black border-r border-gray-200 overflow-hidden transition-[left_width] duration-[240ms] ease"
        :style="{
          left: getColLeft(colIdx) + 'px',
          width: getColWidth(colIdx) + 'px',
          zIndex: colIdx
        }"
        @mouseenter="onHoverColumn(colIdx)"
      >
        <div
            v-for="item in column"
            :key="item.id"
            class="px-3.5 py-0.5 cursor-pointer flex justify-between items-center whitespace-nowrap"
            :class="{ 'bg-sky-200 text-red-600': selectedPath.includes(item.id) }"
            :style="{
                borderLeftWidth: nodeBorderLeftWidth + 'px',
                borderLeftColor: nodeBorderLeftColor,
                borderLeftStyle: 'solid'
            }"
            @mouseenter="onMouseEnter(colIdx, item)"
            @click="onSelectItem(colIdx, item)"
            @dblclick="startEdit(item.id)"
            @contextmenu.prevent="openContextMenu($event, item, colIdx)"
            >
          <!-- 编辑输入框【优化焦点逻辑】 -->
            <input
            v-if="editId === item.id"
            v-model="editText"
            class="flex-1 border-none outline outline-1 outline-blue-500 px-1 py-0.5 m-0 box-border"
            :style="{ backgroundColor: editInputBgColor }"
            @click.stop
            @keyup.enter="confirmEdit(item)"
            ref="inputRef"
            />
          <template v-else>
            {{ item.area }}
          </template>
          <span v-if="hasChild(item.id)" class="ml-5 text-xs text-gray-500">▼</span>
        </div>
      </div>
    </div>

    <!-- 右键弹出菜单【已调换顺序：创建子菜单在上，兄弟在下】 -->
    <div
      v-if="contextMenu.show"
      class="fixed z-[9999] bg-white border border-gray-300 shadow-md py-1"
      :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
      @click.stop
    >
      <div class="px-4 py-1.5 cursor-pointer whitespace-nowrap hover:bg-gray-100" @click="createChild">创建子菜单</div>
      <div class="px-4 py-1.5 cursor-pointer whitespace-nowrap hover:bg-gray-100" @click="createBrother">创建兄弟菜单</div>
    </div>

    <!-- 隐藏文件选择input，用于本地导入txt -->
    <input
      ref="fileInputRef"
      type="file"
      accept=".txt"
      class="hidden"
      @change="handleFileImport"
    >
  </div>

  <!-- 数据预览弹窗（共用） -->
  <div v-if="showDataModal" class="fixed inset-0 bg-black/50 z-[99999]" @click="closeModal">
    <div 
        ref="modalDragRef"
        class="bg-white rounded-md overflow-hidden absolute select-none"
        :style="{ width: modalWidth + 'px', height: modalHeight + 'px', left: modalLeft + 'px', top: modalTop + 'px' }"
        @click.stop
    >
      <!-- 弹窗头部改为拖拽区域 -->
      <div 
        class="flex justify-between items-center px-4 py-3 border-b border-gray-200 gap-3 flex-wrap cursor-move"
        @mousedown="startDrag"
      >
        <h4>{{ modalTitle }}</h4>
        <div class="flex items-center gap-3">
          <label class="inline-flex items-center gap-1 cursor-pointer">
            <input
              type="checkbox"
              v-model="showIdFlag"
            />
            <span>显示/隐藏id</span>
          </label>
          <span
            @click="openExportSelect"
            class="bg-green-500 text-white rounded-full px-2 py-1 cursor-pointer select-none whitespace-nowrap"
          >
            导出数据到txt文件
          </span>
          <span class="text-2xl cursor-pointer text-gray-600 hover:text-black" @click="closeModal">×</span>
        </div>
      </div>
      <div class="p-4 overflow-y-auto" style="height: calc(100% - 72px);">
        <pre class="whitespace-pre font-mono leading-relaxed m-0">{{ modalTreeText }}</pre>
      </div>
      <!-- 右下角拉伸拖拽手柄 -->
      <div 
        class="absolute right-0 bottom-0 w-4 h-4 cursor-se-resize bg-gray-200 rounded-tl"
        @mousedown="startResize"
      ></div>
    </div>
</div>
     
  
</template>
<style>
.bodey{

    color: #f7ffae;
}
</style>
<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted, watch } from 'vue'

// 弹窗拖拽、缩放变量
const modalDragRef = ref(null)
// 弹窗初始位置+尺寸
const modalLeft = ref(window.innerWidth / 2 - 290)
const modalTop = ref(window.innerHeight / 2 - 200)
const modalWidth = ref(580)
const modalHeight = ref(420)
// 拖拽临时状态
let isDragging = false
let dragStartX = 0
let dragStartY = 0
let dragOriginLeft = 0
let dragOriginTop = 0
// 缩放临时状态
let isResizing = false
let resizeStartX = 0
let resizeStartY = 0
let resizeOriginW = 0
let resizeOriginH = 0

// ========== 自定义配置区 ==========
const baseColWidth = 140//单元格基础宽度
const stackOverlap = ref(-10)//单元格向左折叠宽度
const foldSideWidth = ref(50)//单元格折叠后显示宽度
const hoverActiveCol = ref(-1)//

// 【新增1：改名输入框自定义样式变量】
const editInputBgColor = ref('#f7ffae') // 自定义输入框背景颜色：#fff、#f0f8ff、白色、浅灰等


// 【新增2：节点左侧边框配置变量】
const nodeBorderLeftWidth = ref(2)    // 左边框像素宽度
const nodeBorderLeftColor = ref('#3a017f') // 自定义左边框颜色

const globalUnique = ref(true)//全局不重名
const siblingUnique = ref(false)//局部不重名
const nameReg = /^[\u4e00-\u9fa5a-zA-Z0-9_]+$/
// 独立控制ID显隐：默认false=隐藏ID/PID
const showIdFlag = ref(false)
// 记录当前打开弹窗类型，切换复选框用来刷新内容
let currentPreviewType = ''
// 文件选择DOM引用
const fileInputRef = ref(null)
// ==================================

const letters = ref([
  { id: 1, area: 'A', pid: 0 },

])

const contextMenu = ref({ show: false, x: 0, y: 0, targetItem: null, targetCol: 0 })
const editId = ref(null)
const editText = ref('')
const inputRef = ref(null)
const menuWrapRef = ref(null)

// 全局点击关闭右键菜单
document.addEventListener('click', () => {
  contextMenu.value.show = false
})

// 获取真实输入框DOM（修复ref为数组的问题）
const getInputDom = () => {
  const val = inputRef.value
  if (!val) return null
  // 多个同名ref会变成数组，取第一个
  return Array.isArray(val) ? val[0] : val
}

// ===================== 全局点击监听（修复contains报错） =====================
function globalPageClick(e) {
  if (!editId.value) return
  const inputDom = getInputDom()
  const wrapDom = menuWrapRef.value
  // 没有DOM直接退出
  if (!inputDom) {
    confirmEditLostFocus()
    return
  }
  // 点击在输入框内部 → 不退出
  if (inputDom.contains(e.target)) return
  // 点击在菜单容器内部空白 → 执行失焦提交
  if (wrapDom && wrapDom.contains(e.target)) {
    confirmEditLostFocus()
    return
  }
  // 点击页面任意其他外部位置 → 执行失焦提交
  confirmEditLostFocus()
}

// 挂载绑定、销毁移除，防止内存泄漏
onMounted(() => {
  document.addEventListener('click', globalPageClick)
})
onUnmounted(() => {
  document.removeEventListener('click', globalPageClick)
})

const idMap = computed(() => {
  const map = {}
  letters.value.forEach(item => map[item.id] = item)
  return map
})
const getChildren = pid => letters.value.filter(i => i.pid === pid)
const hasChild = id => getChildren(id).length > 0

const selectedPath = ref([])
const menuColumns = computed(() => {
  const cols = [getChildren(0)]
  for (const id of selectedPath.value) {
    const child = getChildren(id)
    child.length && cols.push(child)
  }
  return cols
})

const onHoverColumn = idx => hoverActiveCol.value = idx
const getColWidth = idx => hoverActiveCol.value > idx ? foldSideWidth.value : baseColWidth
const getColLeft = targetIdx => {
  if (targetIdx === 0) return 0
  let total = 0
  for (let i = 0; i < targetIdx; i++) total += getColWidth(i) + stackOverlap.value
  return total
}

const breadList = computed(() => selectedPath.value.map(id => idMap.value[id]))
const onMouseEnter = (colIndex, item) => {
  selectedPath.value = selectedPath.value.slice(0, colIndex)
  selectedPath.value.push(item.id)
}
const onSelectItem = (colIndex, item) => {
  if (editId.value !== null && editId.value !== item.id) cancelEditOutside()
  selectedPath.value = selectedPath.value.slice(0, colIndex)
  selectedPath.value.push(item.id)
}
const handleBreadClick = item => {
  cancelEditOutside()
  const path = []
  let cur = item
  while (cur) {
    path.unshift(cur.id)
    cur = idMap.value[cur.pid]
  }
  selectedPath.value = path
}

// 外部强制取消编辑（丢弃修改）
const cancelEditOutside = () => {
  editId.value = null
  editText.value = ''
}

// 失焦统一提交校验
const confirmEditLostFocus = async () => {
  if (editId.value === null) return
  const item = idMap.value[editId.value]
  await confirmEdit(item)
}

// ===================== 全局重名检测工具 =====================
const checkGlobalNameDuplicate = () => {
  const nameSet = new Set()
  const duplicateNames = []
  for (const node of letters.value) {
    if (nameSet.has(node.area)) duplicateNames.push(node.area)
    else nameSet.add(node.area)
  }
  return [...new Set(duplicateNames)]
}

// 全局复选框切换拦截校验
const handleGlobalCheckChange = () => {
  if (!globalUnique.value) {
    siblingUnique.value = false
    return
  }
  const duplicateList = checkGlobalNameDuplicate()
  if (duplicateList.length > 0) {
    globalUnique.value = false
    alert(`当前数据存在重复名称【${duplicateList.join('、')}】，无法开启全局不重名模式，可选择局部不重名`)
    return
  }
  const invalidNames = []
  for (const node of letters.value) {
    if (!nameReg.test(node.area)) invalidNames.push(`${node.area}(id:${node.id})`)
  }
  if (invalidNames.length > 0) {
    globalUnique.value = false
    alert(`存在名称包含非法符号【${invalidNames.join('、')}】，全局模式仅允许中文、字母、数字、下划线，请先修改后再开启`)
    return
  }
  siblingUnique.value = false
}
const handleSiblingCheckChange = () => {
  if (siblingUnique.value) globalUnique.value = false
}

// 新建节点自动生成合规名称
const getUniqueName = (baseName, pid, excludeId = null) => {
  let targetName = baseName.trim() || '未命名'
  targetName = targetName.replace(/[^\u4e00-\u9fa5a-zA-Z0-9_]/g, '')
  if (!targetName) targetName = '未命名'

  if (globalUnique.value) {
    const existSame = letters.value.some(node => {
      if (node.id === excludeId) return false
      return node.area === targetName
    })
    if (existSame) targetName = `${targetName}${getNewId()}`//新节点名称
  } else if (siblingUnique.value) {
    const siblings = getChildren(pid)
    const existSame = siblings.some(node => {
      if (node.id === excludeId) return false
      return node.area === targetName
    })
    if (existSame) targetName = `${targetName}${getNewId()}`//新节点名称
  }
  return targetName
}

// 右键菜单方法
const openContextMenu = (e, item, colIdx) => {
  contextMenu.value = { show: true, x: e.clientX, y: e.clientY, targetItem: item, targetCol: colIdx }
}
const getNewId = () => Math.max(...letters.value.map(i => i.id)) + 1

// 创建兄弟菜单
const createBrother = () => {
  const target = contextMenu.value.targetItem
  const newId = getNewId()
  const finalName = getUniqueName('新节点', target.pid)
  letters.value.push({ id: newId, area: finalName, pid: target.pid })
  startEdit(newId)
  contextMenu.value.show = false
}

// 创建子菜单
const createChild = () => {
  const target = contextMenu.value.targetItem
  const newId = getNewId()
  const finalName = getUniqueName('新节点', target.id)
  letters.value.push({ id: newId, area: finalName, pid: target.id })
  const path = []
  let cur = idMap.value[target.id]
  while (cur) {
    path.unshift(cur.id)
    cur = idMap.value[cur.pid]
  }
  selectedPath.value = path
  startEdit(newId)
  contextMenu.value.show = false
}

// 打开编辑框，自动聚焦（修复focus报错）
const startEdit = async (nodeId) => {
  if (editId.value !== null) cancelEditOutside()
  editId.value = nodeId
  const node = idMap.value[nodeId]
  editText.value = node.area
  await nextTick()
  const dom = getInputDom()
  if (dom) dom.focus()
}

// 编辑确认校验
const confirmEdit = async (item) => {
  if (editId.value !== item.id) return
  let inputVal = editText.value.trim()
  inputVal = inputVal.replace(/[^\u4e00-\u9fa5a-zA-Z0-9_]/g, '')
  if (!inputVal) {
    alert('名称不能为空，且仅允许中文、字母、数字、下划线')
    await nextTick()
    const dom = getInputDom()
    if (dom) dom.focus()
    return
  }
  let isDuplicate = false
  if (globalUnique.value) {
    isDuplicate = letters.value.some(node => node.id !== item.id && node.area === inputVal)
    if (isDuplicate) {
      alert('全局模式下该名称已存在，禁止重名，请修改名称')
      await nextTick()
      const dom = getInputDom()
      if (dom) dom.focus()
      return
    }
  } else if (siblingUnique.value) {
    const siblings = getChildren(item.pid)
    isDuplicate = siblings.some(node => node.id !== item.id && node.area === inputVal)
  }
  if (isDuplicate) {
    alert('同级名称重复，请修改后再提交')
    await nextTick()
    const dom = getInputDom()
    if (dom) dom.focus()
    return
  }
  item.area = inputVal
  editId.value = null
  editText.value = ''
}

// 弹窗相关
const showDataModal = ref(false)
const modalTreeText = ref('')
const modalTitle = ref('')

// 递归生成树形文本，根据showIdFlag动态决定是否拼接(id,pid)
const buildTree = (pid) => getChildren(pid).map(node => ({ ...node, children: buildTree(node.id) }))
const formatTreeText = (treeArr) => {
  let str = ''
  const render = (list, indent = '') => {
    list.forEach(item => {
      let line = `${indent}├─ ${item.area}`
      if (showIdFlag.value) {
        line += ` (id:${item.id}, pid:${item.pid})`
      }
      str += line + '\n'
      if (item.children?.length) render(item.children, indent + '  ')
    })
  }
  render(treeArr)
  return str
}

// 打开树形预览
const openDataModal = (type) => {
  currentPreviewType = type
  if (type === 'global') {
    modalTitle.value = '全局完整树形数据预览'
    const tree = buildTree(0)
    console.log('===== 全局完整树形结构化数据 =====', tree)
    // console.table(letters.value)
    let text = '完整树形结构：\n\n'
    text += formatTreeText(tree)
    let pathStr = breadList.value.map(i => {
      let name = i.area
      if (showIdFlag.value) name += ` (id:${i.id}, pid:${i.pid})`
      return name
    }).join(' / ')
    text += `\n当前选中路径：${pathStr}`
    modalTreeText.value = text
  } else if (type === 'current') {
    modalTitle.value = '当前选中路径数据预览'
    console.log('当前选中路径节点：', breadList.value)
    let text = '当前选中路径：\n'
    let pathStr = breadList.value.map(i => {
      let name = i.area
      if (showIdFlag.value) name += ` (id:${i.id}, pid:${i.pid})`
      return name
    }).join(' → ')
    text += pathStr
    modalTreeText.value = text
  }
  showDataModal.value = true
}

// 打开扁平列表预览
const showUniqueGlobalData = () => {
  currentPreviewType = 'flat'
  modalTitle.value = '全局无重复ID原始扁平数据'
//   console.log('全局无重复原始数组', letters.value)
  let text = '所有节点扁平列表（ID天然唯一）：\n\n'
  letters.value.forEach(item => {
    let line = ''
    if (showIdFlag.value) {
      line = `id:${item.id} | pid:${item.pid} | name:${item.area}`
    } else {
      line = `name:${item.area}`
    }
    text += line + '\n'
  })
  modalTreeText.value = text
  showDataModal.value = true
}

// 关闭弹窗
const closeModal = () => {
  showDataModal.value = false
  modalTreeText.value = ''
  currentPreviewType = ''
}

// 监听ID显隐复选框，实时刷新预览内容
watch(showIdFlag, () => {
  if (!showDataModal.value || !currentPreviewType) return
  if (currentPreviewType === 'global' || currentPreviewType === 'current') {
    openDataModal(currentPreviewType)
  } else if (currentPreviewType === 'flat') {
    showUniqueGlobalData()
  }
})

// 弹出导出选择框
const openExportSelect = () => {
  if (!modalTreeText.value || modalTreeText.value.trim() === '') {
    alert('暂无可导出的数据，请先加载预览内容')
    return
  }
  const tip = `请选择导出类型：
    1、导出【原始结构化全量数据】（固定包含id、pid）
    2、导出【当前预览展示内容】（跟随显示/隐藏ID开关）
    `
  const sel = window.prompt(tip, '1')
  if (sel === '1') {
      exportOriginRawTxt()
    } else if (sel === '2') {
      exportPreviewTxt()
  } else if (sel === null || sel.trim() === '') {
    return
  } else {
    alert('输入无效，请重新点击导出选择1或2')
  }
}


// ========== 弹窗拖拽逻辑 ==========
const startDrag = (e) => {
  isDragging = true
  dragStartX = e.clientX
  dragStartY = e.clientY
  dragOriginLeft = modalLeft.value
  dragOriginTop = modalTop.value
  document.addEventListener('mousemove', onDragMove)
  document.addEventListener('mouseup', stopDrag)
}
const onDragMove = (e) => {
  if (!isDragging) return
  const dx = e.clientX - dragStartX
  const dy = e.clientY - dragStartY
  modalLeft.value = dragOriginLeft + dx
  modalTop.value = dragOriginTop + dy
}
const stopDrag = () => {
  isDragging = false
  document.removeEventListener('mousemove', onDragMove)
  document.removeEventListener('mouseup', stopDrag)
}

// ========== 弹窗右下角缩放逻辑 ==========
const startResize = (e) => {
  e.stopPropagation()
  isResizing = true
  resizeStartX = e.clientX
  resizeStartY = e.clientY
  resizeOriginW = modalWidth.value
  resizeOriginH = modalHeight.value
  document.addEventListener('mousemove', onResizeMove)
  document.addEventListener('mouseup', stopResize)
}
const onResizeMove = (e) => {
  if (!isResizing) return
  const dw = e.clientX - resizeStartX
  const dh = e.clientY - resizeStartY
  // 设置最小宽高，防止缩没了
  modalWidth.value = Math.max(300, resizeOriginW + dw)
  modalHeight.value = Math.max(220, resizeOriginH + dh)
}
const stopResize = () => {
  isResizing = false
  document.removeEventListener('mousemove', onResizeMove)
  document.removeEventListener('mouseup', stopResize)
}
// 导出1：当前预览内容
const exportPreviewTxt = () => {
  const content = modalTreeText.value
  downloadTxt(content, '树形预览数据')
}

// 导出2：原始结构化数据（固定完整id、pid，不受showIdFlag控制）
const exportOriginRawTxt = () => {
  let content = '原始全量结构化数据（id | pid | name）\n\n'
  letters.value.forEach(item => {
    content += `id:${item.id} | pid:${item.pid} | name:${item.area}\n`
  })
  downloadTxt(content, '原始结构化全量数据')
}

// 通用下载封装函数
const downloadTxt = (content, fileNamePrefix) => {
  const blob = new Blob(['\uFEFF' + content], {
    type: 'text/plain;charset=utf-8'
  })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url
  const now = new Date()
  const timeStr = `${now.getFullYear()}${String(now.getMonth()+1).padStart(2,'0')}${String(now.getDate()).padStart(2,'0')}_${String(now.getHours()).padStart(2,'0')}${String(now.getMinutes()).padStart(2,'0')}`
  a.download = `${fileNamePrefix}_${timeStr}.txt`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

// ===================== 保存&导入函数 =====================
/**
 * 保存数据到后台
 * 后续替换url为你真实后端接口地址即可
 */
const saveDataToBackend = async () => {
  try {
    // 拿到当前完整树形数组
    const submitData = [...letters.value]
    console.log('即将提交后台的数据：', submitData)

    // 示例axios请求，如果你没引入axios，自行改成fetch
    /*
    const res = await axios.post('/api/tree/save', submitData)
    if (res.code === 200) {
      alert('数据保存成功！')
    } else {
      alert('保存失败：' + res.msg)
    }
    */
    alert('数据准备就绪，待对接后端接口即可提交保存\n控制台已打印待提交数组')
  } catch (err) {
    console.error('保存异常：', err)
    alert('保存出错，请检查网络或接口')
  }
}

/**
 * 触发本地文件选择弹窗
 */
const triggerFileSelect = () => {
  if (!fileInputRef.value) return
  fileInputRef.value.value = ''
  fileInputRef.value.click()
}

/**
 * 读取并解析选中txt文件
 */
const handleFileImport = (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (!file.name.endsWith('.txt')) {
    alert('仅支持导入 .txt 格式文件')
    return
  }

  const reader = new FileReader()
  reader.readAsText(file, 'UTF-8')
  reader.onload = () => {
    const text = reader.result.trim()
    if (!text) {
      alert('文件内容为空，无法导入')
      return
    }

    const lines = text.split('\n')
    const newList = []
    const idSet = new Set()
    let parseErrorCount = 0

    // 匹配格式：id:数字 | pid:数字 | name:任意内容
    const lineReg = /id:(\d+)\s*\|\s*pid:(\d+)\s*\|\s*name:(.+)/

    for (const line of lines) {
      const trimLine = line.trim()
      // 跳过标题行、空行
      if (!trimLine || trimLine.startsWith('原始全量') || trimLine.startsWith('所有节点')) continue

      const match = trimLine.match(lineReg)
      if (!match) {
        parseErrorCount++
        continue
      }

      const id = Number(match[1])
      const pid = Number(match[2])
      const area = match[3].trim()

      if (isNaN(id) || isNaN(pid)) {
        parseErrorCount++
        continue
      }
      if (idSet.has(id)) {
        alert(`检测到重复ID:${id}，导入终止`)
        return
      }
      idSet.add(id)
      newList.push({ id, pid, area })
    }

    if (newList.length === 0) {
      alert('未解析到有效菜单数据，请确认是导出的原始数据txt文件')
      return
    }

    // 覆盖数据源，重置选中路径刷新菜单
    letters.value = newList
    selectedPath.value = []

    let msg = `导入成功，共读取 ${newList.length} 条节点数据`
    if (parseErrorCount > 0) msg += `，${parseErrorCount} 行格式无效已跳过`
    alert(msg)
  }

  reader.onerror = () => {
    alert('文件读取失败，文件损坏或编码异常')
  }
}
</script>