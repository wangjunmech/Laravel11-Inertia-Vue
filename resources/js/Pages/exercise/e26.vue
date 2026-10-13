<template>
  <div class="container">
    <div class="text-xl p-2">
      产品工艺选择表
      <span class="cursor-pointer text-blue-500 hover:underline" @click="showData">打印当前数据到控制台</span>
    </div>
    <!-- 快捷选择区 -->
    <div class="text-xl p-2">
      <!-- 面包屑导航 -->
      <div class="breadcrumb-wrap flex flex-wrap">
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
      <hr class="mb-6">
      <div>        
        基础材料选择：
      </div>
      <div
        class="cascader-box rounded-lg relative"
        :style="{ height: boxHeight + 'px' }"
      >
        <div
          v-for="(column, colIdx) in menuColumns"
          :key="colIdx"
          :ref="el => setColumnRef(el, colIdx)"
          class="menu-column"
          :class="{ 'menu-column--active': colIdx === menuColumns.length - 1 && colIdx > 0 }"
          :style="{
            left: getColLeft(colIdx) + 'px',
            width: getColWidth(colIdx) + 'px',
            zIndex: colIdx
          }"
          @mouseenter="onHoverColumn(colIdx)"
        >
          <TreeMenuItem
            v-for="item in column"
            :key="item.id"
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
          />
        </div>
      </div>
      <hr class="mb-6">
      <div>        
        材料形态选择
      </div>
    <div
        class="cascader-box rounded-lg relative"
        :style="{ height: 40 + 'px' }"
      >
    </div>
    <hr class="mb-6">

      <div>        
        成型工艺选择
      </div>
      <div
        class="cascader-box rounded-lg relative"
        :style="{ height: boxHeight + 'px' }"
      >
        <div
          v-for="(column, colIdx) in menuColumns"
          :key="colIdx"
          :ref="el => setColumnRef(el, colIdx)"
          class="menu-column"
          :class="{ 'menu-column--active': colIdx === menuColumns.length - 1 && colIdx > 0 }"
          :style="{
            left: getColLeft(colIdx) + 'px',
            width: getColWidth(colIdx) + 'px',
            zIndex: colIdx
          }"
          @mouseenter="onHoverColumn(colIdx)"
        >
          <TreeMenuItem
            v-for="item in column"
            :key="item.id"
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
          />
        </div>
      </div>
    </div>
    <hr>
    <!-- 快捷添加区 -->
    <div>快速添加工艺流程*******************************</div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, watch, onBeforeUnmount } from 'vue'
import TreeMenuItem from '@/Components/TreeMenuItem.vue'

// ========== 自定义配置 ==========
const baseColWidth = 200        // 每列的基础宽度
const stackOverlap = ref(-30)   // 列之间的重叠宽度
const foldSideWidth = ref(100)  // 折叠侧边栏的宽度
const hoverActiveCol = ref(1)

const letters = ref([
  // 一级：材料大类 pid=0
  { id: 1, area: '金属材料', pid: 0 },
  { id: 2, area: '高分子塑料', pid: 0 },
  { id: 3, area: '橡胶材料', pid: 0 },
  { id: 4, area: '复合材料', pid: 0 },

  // 二级：材料子类
  { id: 5, area: '铝合金', pid: 1 },
  { id: 6, area: '铜合金', pid: 1 },
  { id: 7, area: '碳钢', pid: 1 },
  { id: 8, area: '不锈钢', pid: 1 },
  { id: 9, area: '镁合金', pid: 1 },
])

const processes = ref([
  // 一级：材料大类 pid=0
  { id: 1, area: '注塑', pid: 0 },
  { id: 2, area: '挤出', pid: 0 },
  { id: 3, area: '压铸', pid: 0 },
  { id: 4, area: '3D打印', pid: 0 },

  // 二级：材料子类
  { id: 5, area: '单色注塑', pid: 1 },
  { id: 6, area: '双色注塑', pid: 1 },
  { id: 7, area: '多色注塑', pid: 1 },

])
const idMap = computed(() => {
  const map = {}
  letters.value.forEach(item => map[item.id] = item)
  return map
})

const showData = () => {
  let jsonData = JSON.stringify(letters.value, null, 2)
  console.log('当前数据', jsonData)
}

const getChildren = pid => letters.value.filter(i => i.pid === pid)
const hasChild = id => getChildren(id).length > 0

const selectedPath = ref([])
const menuColumns = computed(() => {
  const cols = [getChildren(0)]
  for (const id of selectedPath.value) {
    const childList = getChildren(id)
    childList.length && cols.push(childList)
  }
  return cols
})

const onHoverColumn = idx => hoverActiveCol.value = idx

const getColWidth = idx => {
  return hoverActiveCol.value > idx ? foldSideWidth.value : baseColWidth
}

const getColLeft = targetIdx => {
  let total = 0
  for (let i = 0; i < targetIdx; i++) {
    total += getColWidth(i) + stackOverlap.value
  }
  return total
}

const breadList = computed(() => selectedPath.value.map(id => idMap.value[id]))

const onMouseEnter = (colIndex, item) => {
  selectedPath.value = selectedPath.value.slice(0, colIndex)
  selectedPath.value.push(item.id)
}

const onSelectItem = (colIndex, item) => {
  selectedPath.value = selectedPath.value.slice(0, colIndex)
  selectedPath.value.push(item.id)
  console.log('选中路径节点', item)
}

const handleBreadClick = item => {
  const path = []
  let cur = item
  while (cur) {
    path.unshift(cur.id)
    cur = idMap.value[cur.pid]
  }
  selectedPath.value = path
}

// 获取最大ID，新增节点用
const maxId = computed(() => letters.value.reduce((max, o) => Math.max(max, o.id), 0))

// 当前正在编辑的节点 id
const editingId = ref(null)

// 反查从根到某节点的完整路径
const getPathToItem = (item) => {
  const path = []
  let cur = item
  while (cur) {
    path.unshift(cur.id)
    cur = idMap.value[cur.pid]
  }
  return path
}

// 添加子级
const handleAddChild = (item) => {
  const newId = maxId.value + 1
  letters.value.push({
    id: newId,
    area: '新子项',
    pid: item.id
  })
  // 用完整路径直接覆盖，而不是在已有路径后面再 push 一次
  selectedPath.value = getPathToItem(item)
  editingId.value = newId
}

// 添加同级
const handleAddSibling = (item) => {
  const newId = maxId.value + 1
  letters.value.push({
    id: newId,
    area: '新同级项',
    pid: item.pid
  })
  editingId.value = newId   // 新增后立即进入编辑态
}

// 确认重命名
const onRename = ({ id, value }) => {
  const target = idMap.value[id]
  if (target) target.area = value
  editingId.value = null
}

// ========== 高度自适应相关 ==========
const boxHeight = ref(320)   // 初始默认值，避免首次渲染前高度塌陷
const columnRefs = ref([])   // 收集每一列 DOM 的引用

const setColumnRef = (el, idx) => {
  if (el) {
    columnRefs.value[idx] = el
    resizeObserver.observe(el)
  }
}

const measureHeight = () => {
  const heights = columnRefs.value
    .filter(Boolean)
    .map(el => el.scrollHeight)
  boxHeight.value = heights.length ? Math.max(...heights) : 320
}

// 提前创建，避免 onMounted 时序问题
const resizeObserver = new ResizeObserver(measureHeight)

// menuColumns 变化后（新增/删除/展开新列）重新测量，并同步 columnRefs 长度
watch(menuColumns, (newCols) => {
  columnRefs.value.length = newCols.length   // 截断多余的旧引用
  nextTick(measureHeight)
}, { deep: true, immediate: true })

onBeforeUnmount(() => {
  resizeObserver.disconnect()
})
</script>

<style scoped>
.container {
  padding: 20px;
}
.breadcrumb-wrap {
  margin-bottom: 10px;
  font-size: 16px;
  display: flex;
  align-items: center;
  gap: 4px;
}
.label {
  color: #222;
  margin-right: 8px;
}
.bread-hover {
  cursor: pointer;
  padding: 0 6px;
  color: #333;
}
.bread-hover:hover {
  color: rgb(255, 0, 137);
}
.separator {
  margin: 0 10px;
  color: #999;
}

.cascader-box {
  position: relative;
  border: 1px dashed #e923ff; 
  transition: height 0.24s ease;
}
.menu-column {
  position: absolute;
  top: 0;
  background: #fff;
  border-right: 1px solid #eee;
  transition: left 0.24s ease, width 0.24s ease, border-color 0.2s ease;
}
.menu-column--active {
  border: 2px solid #ef4444;
  border-radius: 8px;
}
</style>