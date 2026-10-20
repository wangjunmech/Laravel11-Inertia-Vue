<template>
  <div>
    <div class="title">{{ title }}
      <button class=" p-1 bg-blue-500 text-white rounded-lg" @click="handleSave">
        保存当前字典
      </button>
    </div>
    <!-- 面包屑 -->
    <div v-if="showBreadcrumb" class="breadcrumb-wrap">
      <span class="label">当前路径：</span>
      <span
        v-for="(item, index) in breadList"
        :key="item.id"
        class="bread-hover"
        @click="handleBreadClick(item)"
      >
        {{ item.area }}
        <span v-if="index < breadList.length - 1" class="separator">→</span>
      </span>
    </div>


    <div class=" rounded-lg relative" :style="{ height: boxHeight + 'px' }">
      <div
        v-for="(column, colIdx) in menuColumns"
        :key="colIdx"
        :ref="el => setColumnRef(el, colIdx)"
        class="menu-column "
        :class="{ 'menu-column--active': colIdx === menuColumns.length - 1 && colIdx > 0 }"
        :style="{
          left: getColLeft(colIdx) + 'px',
          width: getColWidth(colIdx) + 'px',
          zIndex: colIdx
        }"
        @mouseenter="hoverActiveCol = colIdx"
      >
        <TreeMenuItem
          v-for="item in column"
          :key="item.id"
          :class="{ 'item-locked': lockedId === item.id }"
          :item="item"
          :is-active="selectedPath.includes(item.id)"
          :has-child="hasChild(item.id)"
          :is-editing="editingId === item.id"
          @mouseenter="onMouseEnter(colIdx, item)"
          @select="onSelectItem(colIdx, item)"
          @add-child="handleAddChild(item)"
          @add-sibling="handleAddSibling(item)"
          @rename="onRename"
          @cancel-edit="editingId = null"
          @start-edit="editingId = $event"
          @contextmenu="onItemContextMenu"
        />
      </div>
    </div>


    <!-- 全局唯一右键菜单 -->
    <div
      v-if="ctxMenu.show"
      class="context-menu"
      :style="{ left: ctxMenu.x + 'px', top: ctxMenu.y + 'px' }"
      @click.stop
    >
      <div class="ctx-menu-item bg-yellow-300" @click="handleDelete">❌ 删除此项</div>
    </div>
  </div>
</template>


<script setup>
import { ref, computed, nextTick, watch, onBeforeUnmount, onMounted } from 'vue'
import TreeMenuItem from '@/Components/TreeMenuItem.vue'

const props = defineProps({
  modelValue: { type: Array, required: true,default: () => [] },   // [{ id, area, pid }]
  title: { type: String, default: '' },
  dictType: { type: String, required: true }, // 新增：字典类型 material/process/surface_process
  showBreadcrumb: { type: Boolean, default: true },
  baseColWidth: { type: Number, default: 200 },
  stackOverlap: { type: Number, default: -30 },
  foldSideWidth: { type: Number, default: 100 },
})
const emit = defineEmits(['update:modelValue', 'select', 'save']) //新增save事件


const hoverActiveCol = ref(1)
const selectedPath = ref([])
const editingId = ref(null)


// ===== 数据读取 =====
const idMap = computed(() => {
  const map = {}
  props.modelValue.forEach(item => (map[item.id] = item))
  return map
})
// const getChildren = pid => props.modelValue.filter(i => i.pid === pid)
const getChildren = (pid) => (props.modelValue || []).filter(i => i.pid === pid)
const hasChild = id => getChildren(id).length > 0

const lockedId = ref(null)   // 当前被点击锁定的节点 id

const onMouseEnter = (colIndex, item) => {
  if (lockedId.value !== null) return            // 锁定时忽略悬停
  selectedPath.value = [...selectedPath.value.slice(0, colIndex), item.id]
}

const onSelectItem = (colIndex, item) => {
  selectedPath.value = [...selectedPath.value.slice(0, colIndex), item.id]
  lockedId.value = lockedId.value === item.id ? null : item.id   // 再次点击则解除
  emit('select', { item, path: breadList.value })
}

const menuColumns = computed(() => {
  const cols = [getChildren(0)]
  for (const id of selectedPath.value) {
    const childList = getChildren(id)
    childList.length && cols.push(childList)
  }
  return cols
})


const breadList = computed(() =>
  selectedPath.value.map(id => idMap.value[id]).filter(Boolean)
)


// ===== 布局 =====
const getColWidth = idx => (hoverActiveCol.value > idx ? props.foldSideWidth : props.baseColWidth)
const getColLeft = targetIdx => {
  let total = 0
  for (let i = 0; i < targetIdx; i++) total += getColWidth(i) + props.stackOverlap
  return total
}


// ===== 选择 =====
const getPathToItem = item => {
  const path = []
  let cur = item
  while (cur) {
    path.unshift(cur.id)
    cur = idMap.value[cur.pid]
  }
  return path
}




const handleBreadClick = item => {
  selectedPath.value = getPathToItem(item)
}


// ===== 编辑：不直接改 props，而是 emit 新数组 =====
const nextId = () => props.modelValue.reduce((max, o) => Math.max(max, o.id), 0) + 1


const handleAddChild = item => {
  const newId = nextId()
  emit('update:modelValue', [...props.modelValue, { id: newId, area: '新子项', pid: item.id }])
  selectedPath.value = getPathToItem(item)
  editingId.value = newId
}


const handleAddSibling = item => {
  const newId = nextId()
  emit('update:modelValue', [...props.modelValue, { id: newId, area: '新同级项', pid: item.pid }])
  editingId.value = newId
}


const onRename = ({ id, value }) => {
  emit(
    'update:modelValue',
    props.modelValue.map(i => (i.id === id ? { ...i, area: value } : i))
  )
  editingId.value = null
}

// ========== 保存按钮点击事件 ==========
const handleSave = () => {
  // 抛出save事件，把类型+当前树数组传给父组件
  emit('save', {
    type: props.dictType,
    items: props.modelValue
  })
}

// ===== 高度自适应 =====
const boxHeight = ref(320)
const columnRefs = ref([])


const measureHeight = () => {
  const heights = columnRefs.value.filter(Boolean).map(el => el.scrollHeight)
  boxHeight.value = heights.length ? Math.max(...heights) : 320
}
const resizeObserver = new ResizeObserver(measureHeight)


const setColumnRef = (el, idx) => {
  if (el) {
    columnRefs.value[idx] = el
    resizeObserver.observe(el)
  }
}


watch(
  menuColumns,
  newCols => {
    columnRefs.value.length = newCols.length
    nextTick(measureHeight)
  },
  { deep: true, immediate: true }
)


// ========== 右键菜单逻辑 ==========
const ctxMenu = ref({
  show: false,
  x: 0,
  y: 0,
  item: null
})


const closeCtxMenu = () => {
  ctxMenu.value.show = false
  ctxMenu.value.item = null
}


const onItemContextMenu = ({ event, item }) => {
  event.preventDefault()
  ctxMenu.value = {
    show: true,
    x: event.clientX,
    y: event.clientY,
    item: item
  }
}


function getAllDescendantIds(rootId, allList) {
  const ids = [rootId]
  const children = allList.filter(x => x.pid === rootId)
  children.forEach(child => {
    ids.push(...getAllDescendantIds(child.id, allList))
  })
  return ids
}


const handleDelete = () => {
  const item = ctxMenu.value.item
  if (!item) return


  if (item.pid === 0) {
    alert('根节点不允许删除')
    closeCtxMenu()
    return
  }
  if (!confirm(`确定删除【${item.area}】以及全部子节点？`)) {
    closeCtxMenu()
    return
  }


  const removeIds = getAllDescendantIds(item.id, props.modelValue)
  const newTree = props.modelValue.filter(row => !removeIds.includes(row.id))
  emit('update:modelValue', newTree)
  if (removeIds.includes(lockedId.value)) lockedId.value = null

  if (selectedPath.value.some(id => removeIds.includes(id))) {
    selectedPath.value = []
  }
  closeCtxMenu()
}

const onDocClick = (e) => {
  if (e.target.closest?.('.context-menu')) return   // 点在菜单内部不关闭
  closeCtxMenu()
}
// 在其他地方右键也先关掉旧菜单（新菜单由 onItemContextMenu 重新打开）
const onDocContextMenu = (e) => {
  if (e.target.closest?.('.context-menu')) return
  closeCtxMenu()
}
const onKeydown = (e) => { if (e.key === 'Escape') closeCtxMenu() }

onMounted(() => {
  document.addEventListener('click', onDocClick, true)
  document.addEventListener('contextmenu', onDocContextMenu, true)
  window.addEventListener('keydown', onKeydown)
})


onBeforeUnmount(() => {
  resizeObserver.disconnect()
  document.removeEventListener('click', onDocClick, true)
  document.removeEventListener('contextmenu', onDocContextMenu, true)
  window.removeEventListener('keydown', onKeydown)
})
</script>



<style scoped>
.title { margin-bottom: 8px; }
.breadcrumb-wrap {
  display: flex;
  flex-wrap: wrap;
  align-items: center;
  font-size: 16px;
}
.label { color: #222; margin-right: 8px; }
.bread-hover { cursor: pointer; padding: 0 6px; color: #333; }
.bread-hover:hover { color: rgb(255, 0, 137); }
.separator { margin: 0 10px; color: #999; }


.menu-column {
  position: absolute;
  background: #fff;
  border-right: 1px solid #eee;
  transition: left 0.24s ease, width 0.24s ease, border-color 0.2s ease;
}
.menu-column--active {
  border: 2px solid #ef4444;
  border-radius: 8px;
}


.context-menu {
  position: fixed;
  background: #ffffff;
  border: 1px solid #d1d5db;
  border-radius: 6px;
  box-shadow: 0 3px 12px rgba(0,0,0,0.15);
  z-index: 9999;
  min-width: 110px;
}
.ctx-menu-item {
  padding: 6px 12px;
  cursor: pointer;
}
.ctx-menu-item:hover {
  background-color: #f3f4f6;
}

.item-locked {
  background-color: #ef4444 !important;
  color: #fff !important;
}
</style>
