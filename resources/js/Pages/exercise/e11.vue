<template>
  <div class="container">
    <!-- 面包屑导航 -->
    <div class="breadcrumb-wrap">
      <span class="label">当前路径：</span>
      <span
        v-for="(item, index) in breadList"
        :key="item.id"
        class="breadcrumb-item bread-hover"
        @click="handleBreadClick(item)"
      >
        {{ item.area }}
        <span v-if="index < breadList.length - 1" class="separator">/</span>
      </span>
    </div>

    <div class="cascader-box">
      <div
        v-for="(column, colIdx) in menuColumns"
        :key="colIdx"
        class="menu-column"
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
          class="menu-item"
          :class="{ active: selectedPath.includes(item.id) }"
          @mouseenter="onMouseEnter(colIdx, item)"
          @click="onSelectItem(colIdx, item)"
        >
          {{ item.area }}
          <span v-if="hasChild(item.id)" class="arrow">▶︎</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// ========== 自定义配置区 ==========
const baseColWidth = 140        // 菜单默认完整宽度
const stackOverlap = ref(-30)    // 常态列偏移，负数重叠、正数留间隙
const foldSideWidth = ref(60)    // 收拢后侧边窄条宽度
const hoverActiveCol = ref(-1)   // 当前悬浮列标记
// ==================================

const letters = ref([
  { id: 1, area: 'A', pid: 0 },
  { id: 2, area: 'B', pid: 0 },
  { id: 3, area: 'C', pid: 0 },
  { id: 4, area: 'D', pid: 0 },
  { id: 5, area: 'E', pid: 0 },
  { id: 6, area: 'AA', pid: 1 },
  { id: 7, area: 'BB', pid: 2 },
  { id: 8, area: 'CC', pid: 3 },
  { id: 9, area: 'DD', pid: 4 },
  { id: 10, area: 'EE', pid: 5 },
  { id: 11, area: 'AAA', pid: 6 },
  { id: 12, area: 'BBB', pid: 7 },
  { id: 13, area: 'CCC', pid: 8 },
  { id: 14, area: 'DDD', pid: 9 },
  { id: 15, area: 'AAAA', pid: 11 },
  { id: 16, area: 'BBBB', pid: 12 },
  { id: 17, area: 'CCCC', pid: 13 },
  { id: 18, area: 'DDDD', pid: 14 },
  { id: 19, area: 'AAAAA', pid: 15 },
  { id: 20, area: 'BBBBB', pid: 16 },
  { id: 21, area: 'CA', pid: 3 },
  { id: 22, area: 'CB', pid: 3 },
  { id: 23, area: 'CC', pid: 3 },
  { id: 24, area: 'CD', pid: 3 },
])

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

// 监听悬浮列
const onHoverColumn = idx => hoverActiveCol.value = idx

// 获取当前列宽度：悬浮在右侧时，左边列收拢变窄
const getColWidth = idx => {
  return hoverActiveCol.value > idx ? foldSideWidth.value : baseColWidth
}

// 核心：递归累加左侧总宽度，动态计算left，右侧整体跟随左移、无缝隙
const getColLeft = targetIdx => {
  if (targetIdx === 0) return 0
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
  console.log('选中', item)
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
</script>

<style scoped>
.container {
  padding: 20px;
}
.breadcrumb-wrap {
  margin-bottom: 20px;
  font-size: 16px;
  display: flex;
  align-items: center;
}
.label {
  color: #222;
  margin-right: 8px;
}
.breadcrumb-item {
  cursor: pointer;
  padding: 0 6px;
  color: #333;
}
.bread-hover:hover {
  color: rgb(255, 0, 89);
}
.separator {
  margin: 0 10px;
  color: #999;
}

.cascader-box {
  position: relative;
  border: 1px solid #ccff23;
}
.menu-column {
  position: absolute;
  top: 0;

  background: #fff;
  border-right: 1px solid #eee;
  overflow: hidden;
  /* 位移动画同步宽度变化，顺滑过渡 */
  transition: left 0.24s ease, width 0.24s ease;
}
.menu-item {
  padding: 2px 14px;
  border-left: 1px solid #f80d0d;
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
  border-top-right-radius: 20px;
  border-bottom-right-radius: 20px;
  white-space: nowrap;
}
.menu-item.active {
  background-color: #9dc4ea;
  color: #f93636;
}
.arrow {
  margin-left: 20px;
  font-size: 12px;
  color: #999;
}
</style>