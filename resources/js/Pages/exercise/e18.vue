<template>
  <div class="app-container w-screen h-screen flex bg-gray-50 overflow-hidden">
    <!-- 左侧分类侧边栏 -->
    <aside class="sidebar w-[220px] bg-white border-r border-gray-200 p-5 flex flex-col">
      <!-- 标题+导出导入按钮行 -->
      <div class="sidebar-title flex items-center gap-3 mb-5">
        <h2 class="text-lg font-semibold text-gray-800 shrink-0">分类导航</h2>
        <button
          @click="exportAllDataToJson"
          class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded border border-gray-300 shrink-0"
        >
          导出
        </button>
        <button
          @click="$refs.fileInput.click()"
          class="px-2 py-1 bg-gray-100 hover:bg-gray-200 text-xs rounded border border-gray-300 shrink-0"
        >
          导入
        </button>
        <!-- 隐藏文件上传input，仅接收json -->
        <input
          ref="fileInput"
          type="file"
          accept=".json"
          class="hidden"
          @change.stop="handleImportJsonFile"
        />
      </div>
      <!-- 左侧分类拖拽容器 -->
      <div ref="catSortRef" class="category-list flex-1 overflow-y-auto cat-drag-wrap">
        <div
          v-for="cat in categoryList"
          :key="cat.id"
          class="category-item flex items-center justify-between py-2.5 px-3 rounded-lg cursor-pointer mb-1.5 text-gray-600 hover:bg-gray-100 drag-item"
          :class="{ active: activeCatId === cat.id }"
          @click.stop="switchCategory(cat.id)"
        >
          <span>{{ cat.name }}</span>
          <!-- 删除分类点击事件 -->
          <button
            class="del-cat-btn w-5 h-5 rounded-full border-none bg-transparent text-gray-400 cursor-pointer text-base leading-none hover:bg-red-100 hover:text-red-500"
            @click.stop="openDeleteCatConfirm(cat.id)"
          >×</button>
        </div>
      <!-- 新增分类 -->
      <div class="add-category">
        <input
          v-model="newCatName"
          placeholder="输入分类名"
          class="flex-1 border border-gray-200 rounded-md outline-none text-sm w-2"
          @keyup.enter="addCategory"
        />        
        <button
          class="whitespace-nowrap px-2.5 py-2 border-none bg-blue-600 text-white rounded-md cursor-pointer text-sm"
          @click="addCategory"
        >+ 添加</button>
      </div>
      </div>

    </aside>

    <!-- 右侧收藏图标区域 -->
    <main class="bookmark-wrap flex-1 p-[30px] overflow-hidden relative" @click="hideContextMenu">
      <div class="header-bar flex justify-between items-center mb-6">
        <h2 class="text-xl font-medium text-gray-800">{{ getActiveCatName() }}</h2>
        <button
          class="add-book-btn py-2.5 px-[18px] bg-blue-600 text-white border-none rounded-lg cursor-pointer text-[15px]"
          @click="openAddModal"
        >+ 添加网址</button>
      </div>

      <!-- 拖拽容器 -->
      <div ref="sortWrapRef" class="bookmark-grid grid grid-cols-[repeat(auto-fill,minmax(110px,1fr))] gap-4 book-drag-wrap">
        <div
          v-for="item in currentBookList"
          :key="item.id"
          class="book-card flex flex-col items-center cursor-pointer py-2 select-none drag-item"
          @click="openUrl(item.url)"
          @contextmenu.prevent="openRightMenu($event, item)"
        >
          <div class="icon-box w-[88px] h-[88px] bg-white rounded-xl flex items-center justify-center shadow-sm border border-gray-100 hover:shadow-md transition-all overflow-hidden p-0">
            <img :src="item.icon" alt="" class="site-icon w-full h-full object-contain" />
          </div>
          <div class="site-name text-[13px] text-gray-800 text-center mt-2 break-all max-w-[90px]">{{ item.name }}</div>
        </div>
      </div>

      <!-- 右键主菜单【新增编辑按钮】 -->
      <div
        v-if="contextMenu.show"
        class="context-menu fixed bg-white shadow-lg rounded-md py-1 z-[9999] border border-gray-200 min-w-[120px]"
        :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
        @click.stop
      >
        <div
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm"
          @click="openEditModal"
        >编辑</div>
        <!-- 修改点击事件为删除确认弹窗 -->
        <div
          class="px-4 py-2 hover:bg-gray-100 cursor-pointer text-sm text-red-500"
          @click="openDeleteConfirm"
        >删除此网站</div>

        <div class="border-t border-gray-100 my-1"></div>

        <div class="relative px-4 py-2 cursor-pointer text-sm">
          <span
            class="block px-0 py-2 hover:bg-gray-100"
            @mouseenter="showMoveSub = true"
          >移动到某一分类 ▸</span>

          <!-- 子菜单 fixed 不受外层裁切 -->
          <div
            v-if="showMoveSub"
            class="fixed bg-white shadow-lg border border-gray-200 rounded-md py-1 min-w-[110px] z-[10000]"
            :style="{
              left: (contextMenu.x + 130) + 'px',
              top: (contextMenu.y + 48) + 'px'
            }"
            @mouseenter="showMoveSub = true"
            @mouseleave="showMoveSub = false"
          >
            <div
              v-for="cat in filterCategoryList"
              :key="cat.id"
              class="px-4 py-1.5 hover:bg-gray-100 text-sm cursor-pointer"
              @click="moveItemToCategory(cat.id)"
            >
              {{ cat.name }}
            </div>
          </div>
        </div>
      </div>
    </main>

    <!-- 添加/编辑共用弹窗 -->
    <div
      v-if="showAddModal"
      class="modal-mask fixed inset-0 bg-black/50 flex items-center justify-center z-[999]"
      @click.self="closeModal"
    >
      <div class="modal-box w-[460px] bg-white rounded-xl p-6">
        <h3 class="text-lg font-medium text-gray-800 mb-5">{{ editTargetId ? '编辑网址' : '添加网站收藏' }}</h3>

        <div class="form-item mb-4">
          <label class="mb-1.5 text-sm text-gray-600">网站网址：</label>
          <input
            v-model="tempUrl"
            placeholder="https://www.baidu.com"
            class="w-full px-2.5 py-2.5 border border-gray-300 rounded-md outline-none"
            @input="autoGetIcon"
          />
        </div>

        <div class="form-item mb-4">
          <label class="mb-1.5 text-sm text-gray-600">网站名称：</label>
          <input
            v-model="tempName"
            placeholder="百度"
            class="w-full px-2.5 py-2.5 border border-gray-300 rounded-md outline-none"
          />
        </div>

        <div class="form-item mb-4">
          <label class="mb-1.5 text-sm text-gray-600">自定义图标地址（选填）：</label>
          <input
            v-model="tempCustomIconUrl"
            placeholder="填写图片URL则优先使用该图标"
            class="w-full px-2.5 py-2.5 border border-gray-300 rounded-md outline-none"
            @input="handleCustomIconChange"
          />
        </div>

        <div class="form-item mb-4">
          <label class="mb-1.5 text-sm text-gray-600">预览图标：</label>
          <div class="w-[48px] h-[48px] bg-white rounded-md border border-gray-100 flex items-center justify-center overflow-hidden p-2">
            <img
              v-if="tempPreviewIcon"
              :src="tempPreviewIcon"
              class="preview-icon w-full h-full object-contain"
            />
            <span v-else class="tip text-[13px] text-gray-400">输入网址自动获取图标</span>
          </div>
        </div>

        <div class="btn-group flex gap-3 justify-end mt-6">
          <button
            class="cancel py-2.5 px-5 rounded-md border-none cursor-pointer bg-gray-100 text-gray-700"
            @click="closeModal"
          >取消</button>
          <button
            class="confirm py-2.5 px-5 rounded-md border-none cursor-pointer bg-blue-600 text-white"
            @click="submitBookmark"
          >保存
          </button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, onMounted, watch, nextTick } from 'vue'
import Sortable from 'sortablejs'

// 分类列表
const categoryList = ref([])
const activeCatId = ref('')
const newCatName = ref('')
const showAddModal = ref(false)

// 新增网站临时数据
const tempUrl = ref('')
const tempName = ref('')
const tempCustomIconUrl = ref('')
const tempPreviewIcon = ref('')

// 编辑模式标记：有值代表编辑，空代表新增
const editTargetId = ref('')

// 全部收藏数据
const allBookmarks = ref([])

// 文件上传DOM引用（导入功能用）
const fileInput = ref(null)
// 书签拖拽容器
const sortWrapRef = ref(null)
// 分类拖拽容器 新增
const catSortRef = ref(null)

// 拖拽实例
let bookSortInstance = null
let catSortInstance = null

// 右键菜单状态：初始值改为空字符串，杜绝null判断异常
const contextMenu = ref({
  show: false,
  x: 0,
  y: 0,
  targetId: '',
  sourceCatId: ''
})
// 子菜单显示开关
const showMoveSub = ref(false)
// 待删除书签ID（书签删除确认用）
const deleteTargetId = ref('')

// 【核心修复】预过滤分类，移除模板v-if，消除ESLint警告+null闪烁问题
const filterCategoryList = computed(() => {
  // 无分类ID时直接返回空数组，不渲染任何选项
  if (!contextMenu.value.sourceCatId) return []
  const targetId = String(contextMenu.value.sourceCatId)
  return categoryList.value.filter(cat => String(cat.id) !== targetId)
})

// 销毁所有拖拽实例
function destroyAllSort() {
  if (bookSortInstance) {
    bookSortInstance.destroy()
    bookSortInstance = null
  }
  if (catSortInstance) {
    catSortInstance.destroy()
    catSortInstance = null
  }
}

// 初始化【书签拖拽】 修复正确逻辑
function initBookSortable() {
  if (!sortWrapRef.value) return
  bookSortInstance = new Sortable(sortWrapRef.value, {
    animation: 150,
    delay: 0,
    ghostClass: 'opacity-40',
    onEnd(evt) {
      // 1、拿到当前页面渲染的排序数组
      const newSortedList = [...currentBookList.value]
      // 2、在视图数组内完成换位
      const dragItem = newSortedList.splice(evt.oldIndex, 1)[0]
      newSortedList.splice(evt.newIndex, 0, dragItem)

      // 3、拆分全局数组：非当前分类 + 当前分类新排序数组
      const other = allBookmarks.value.filter(item => item.catId !== activeCatId.value)
      // 4、重新合并全局数据源，精准替换当前分类片段
      allBookmarks.value = [...other, ...newSortedList]
      saveBookmark()
    }
  })
}

// 初始化【左侧分类拖拽】
function initCatSortable() {
  if (!catSortRef.value) return
  catSortInstance = new Sortable(catSortRef.value, {
    animation: 150,
    delay: 80,
    ghostClass: 'opacity-40',
    onEnd(evt) {
      // 直接操作分类数组顺序
      const tempArr = [...categoryList.value]
      const dragCat = tempArr.splice(evt.oldIndex, 1)[0]
      tempArr.splice(evt.newIndex, 0, dragCat)
      categoryList.value = tempArr
      saveCategory()
    }
  })
}

onMounted(() => {
  const localCat = localStorage.getItem('bookmark_category')
  if (localCat) {
    categoryList.value = JSON.parse(localCat)
  } else {
    categoryList.value = [
      { id: 'cat1', name: '主页' },
      { id: 'cat2', name: '设计' },
      { id: 'cat3', name: '编程' },
      { id: 'cat4', name: 'AI' },
      { id: 'cat5', name: '摸鱼' }
    ]
  }
  activeCatId.value = categoryList.value[0]?.id

  const localBook = localStorage.getItem('bookmark_data')
  if (localBook) allBookmarks.value = JSON.parse(localBook)

  nextTick(() => {
    initCatSortable()
    initBookSortable()
  })
})

// 切换分类只重建书签拖拽，分类拖拽无需重建
watch(activeCatId, () => {
  nextTick(() => {
    if (bookSortInstance) bookSortInstance.destroy()
    initBookSortable()
  })
})

// 本地持久化
const saveCategory = () => {
  localStorage.setItem('bookmark_category', JSON.stringify(categoryList.value))
}
const saveBookmark = () => {
  localStorage.setItem('bookmark_data', JSON.stringify(allBookmarks.value))
}

// 分类操作
const switchCategory = (id) => {
  activeCatId.value = id
}
const getActiveCatName = () => {
  const cat = categoryList.value.find(c => c.id === activeCatId.value)
  return cat?.name || ''
}

const addCategory = () => {
  const name = newCatName.value.trim()
  if (!name) return
  const newCat = {
    id: 'cat_' + Date.now(),
    name
  }
  categoryList.value.push(newCat)
  newCatName.value = ''
  saveCategory()
}

// 打开删除分类确认弹窗
const openDeleteCatConfirm = (cid) => {
  // 不能只剩最后一个分类
  if (categoryList.value.length <= 1) {
    alert('至少保留一个分类，无法删除！')
    return
  }
  const catInfo = categoryList.value.find(c => c.id === cid)
  const bookmarkCount = allBookmarks.value.filter(b => b.catId === cid).length
  const sure = confirm(`确定删除分类【${catInfo.name}】？\n该分类下共${bookmarkCount}条书签会被一并永久删除，不可恢复！`)
  if (!sure) return

  // 执行删除分类+关联书签
  categoryList.value = categoryList.value.filter(c => c.id !== cid)
  allBookmarks.value = allBookmarks.value.filter(b => b.catId !== cid)

  // 切换到第一个分类
  activeCatId.value = categoryList.value[0].id
  saveCategory()
  saveBookmark()
}

// 当前分类收藏列表
const currentBookList = computed(() => {
  return allBookmarks.value.filter(item => item.catId === activeCatId.value)
})

const autoGetIcon = () => {
  if (tempCustomIconUrl.value.trim()) return
  const url = tempUrl.value.trim()
  if (!url) {
    tempPreviewIcon.value = ''
    return
  }
  tempPreviewIcon.value = `https://favicon.yandex.net/favicon/${url}`
}

const handleCustomIconChange = () => {
  const customUrl = tempCustomIconUrl.value.trim()
  if (customUrl) {
    tempPreviewIcon.value = customUrl
  } else {
    autoGetIcon()
  }
}

// 顶部按钮打开新增弹窗
const openAddModal = () => {
  // 清空编辑标记与输入框
  editTargetId.value = ''
  tempUrl.value = ''
  tempName.value = ''
  tempCustomIconUrl.value = ''
  tempPreviewIcon.value = ''
  showAddModal.value = true
}

// 右键打开编辑弹窗，回填当前条目数据
const openEditModal = () => {
  const targetId = contextMenu.value.targetId
  editTargetId.value = targetId
  // 找到当前编辑条目
  const targetItem = allBookmarks.value.find(item => item.id === targetId)
  if (targetItem) {
    tempUrl.value = targetItem.url
    tempName.value = targetItem.name
    tempCustomIconUrl.value = targetItem.icon
    tempPreviewIcon.value = targetItem.icon
  }
  contextMenu.value.show = false
  showMoveSub.value = false
  showAddModal.value = true
}

// 关闭弹窗，清空临时数据
const closeModal = () => {
  showAddModal.value = false
  editTargetId.value = ''
  tempUrl.value = ''
  tempName.value = ''
  tempCustomIconUrl.value = ''
  tempPreviewIcon.value = ''
}

// 提交：区分新增 / 编辑
const submitBookmark = () => {
  const url = tempUrl.value.trim()
  const name = tempName.value.trim()
  if (!url || !name) return alert('网址和名称不能为空')

  if (editTargetId.value) {
    // 编辑模式：修改原有数据
    const targetItem = allBookmarks.value.find(item => item.id === editTargetId.value)
    if (targetItem) {
      targetItem.url = url
      targetItem.name = name
      targetItem.icon = tempCustomIconUrl.value || `https://favicon.yandex.net/favicon/${url}`
    }
  } else {
    // 新增模式：push新条目
    const newBook = {
      id: 'book_' + Date.now(),
      catId: activeCatId.value,
      url,
      name,
      icon: tempCustomIconUrl.value || `https://favicon.yandex.net/favicon/${url}`
    }
    allBookmarks.value.push(newBook)
  }
  saveBookmark()
  closeModal()
}

const openUrl = (url) => {
  window.open(url, '_blank')
}

// 打开右键菜单【修复赋值顺序：先赋值分类ID，再显示菜单，避免渲染瞬间为空】
const openRightMenu = (e, item) => {
  contextMenu.value.sourceCatId = item.catId
  contextMenu.value.targetId = item.id
  contextMenu.value.x = e.clientX
  contextMenu.value.y = e.clientY
  contextMenu.value.show = true
  showMoveSub.value = false
}

// 点击空白关闭菜单
const hideContextMenu = () => {
  contextMenu.value.show = false
  showMoveSub.value = false
}

// 打开书签删除确认弹窗
const openDeleteConfirm = () => {
  deleteTargetId.value = contextMenu.value.targetId
  // 关闭右键悬浮菜单
  contextMenu.value.show = false
  showMoveSub.value = false
  const confirmResult = confirm('确定永久删除这条书签？删除后数据无法恢复！')
  if (confirmResult) {
    executeDelete()
  } else {
    deleteTargetId.value = ''
  }
}

// 执行书签真实删除
const executeDelete = () => {
  allBookmarks.value = allBookmarks.value.filter(item => item.id !== deleteTargetId.value)
  saveBookmark()
  deleteTargetId.value = ''
}

// 移动收藏到指定分类
const moveItemToCategory = (targetCatId) => {
  const targetItem = allBookmarks.value.find(item => item.id === contextMenu.value.targetId)
  if (targetItem) {
    targetItem.catId = targetCatId
    saveBookmark()
  }
  contextMenu.value.show = false
  showMoveSub.value = false
}

// -------------------------- JSON导出完整数据 --------------------------
const exportAllDataToJson = () => {
  // 打包完整备份对象
  const backupData = {
    categoryList: categoryList.value,
    allBookmarks: allBookmarks.value,
    exportTime: new Date().toLocaleString()
  }
  // 格式化JSON，方便阅读
  const jsonStr = JSON.stringify(backupData, null, 2)
  const blob = new Blob([jsonStr], { type: "application/json;charset=utf-8" })
  const url = URL.createObjectURL(blob)
  const a = document.createElement("a")
  a.href = url
  a.download = `书签备份_${new Date().getTime()}.json`
  document.body.appendChild(a)
  a.click()
  document.body.removeChild(a)
  URL.revokeObjectURL(url)
}

// -------------------------- JSON导入数据覆盖本地 --------------------------
const handleImportJsonFile = (e) => {
  const file = e.target.files[0]
  if (!file) return
  if (!file.name.endsWith('.json')) {
    alert('仅支持导入 .json 备份文件！')
    fileInput.value = null
    return
  }
  const reader = new FileReader()
  reader.onload = (ev) => {
    try {
      const jsonText = ev.target.result
      const data = JSON.parse(jsonText)
      if (!data.categoryList || !Array.isArray(data.categoryList) || !data.allBookmarks || !Array.isArray(data.allBookmarks)) {
        throw new Error('文件结构不合法，请使用本工具导出的json备份')
      }
      const confirmImport = confirm('导入会完全覆盖当前所有分类、书签数据，无法撤销，确定继续？')
      if (!confirmImport) {
        fileInput.value = null
        return
      }
      categoryList.value = data.categoryList
      allBookmarks.value = data.allBookmarks
      saveCategory()
      saveBookmark()
      activeCatId.value = categoryList.value[0]?.id || ''
      alert('导入成功，页面即将刷新')
      location.reload()
    } catch (err) {
      alert('导入失败：' + err.message)
    }
    fileInput.value = null
  }
  reader.readAsText(file, 'utf-8')
}
</script>

<style scoped>
.category-item.active {
  background: #eff6ff;
  color: #2563eb;
  font-weight: 500;
}
.opacity-40 {
  opacity: 0.4;
}
.add-category {
  margin-top: 8px;
  display: flex;
  gap: 6px;
}
/* 拖拽光标基础样式，按下立刻生效，无JS冲突 */
.cat-drag-wrap .drag-item {
  cursor: grab;
}
.cat-drag-wrap .drag-item:active {
  cursor: grabbing;
}
.book-drag-wrap .drag-item {
  cursor: grab;
}
.book-drag-wrap .drag-item:active {
  cursor: grabbing;
}
</style>