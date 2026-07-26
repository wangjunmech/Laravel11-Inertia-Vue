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

    <!-- 悬浮横向级联菜单（你要的 hover 右侧弹出下级） -->
    <div class="cascader-box">
      <div
        v-for="(column, colIdx) in menuColumns"
        :key="colIdx"
        class="menu-column"
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
          <span v-if="hasChild(item.id)" class="right-arrow">▶</span>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed } from 'vue'

// 原始分类数据
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

// ID映射快速查找
const idMap = computed(() => {
  const map = {}
  letters.value.forEach(item => map[item.id] = item)
  return map
})

// 获取某id的子节点
const getChildren = (pid) => {
  return letters.value.filter(item => item.pid === pid)
}

// 判断是否存在子级
const hasChild = (id) => getChildren(id).length > 0

// 已选中整条路径ID数组 [1,6,11,15,19]
const selectedPath = ref([])

// 拼装多列菜单（悬浮右侧一列一列生成）
const menuColumns = computed(() => {
  const cols = []
  // 第一列永远是根节点 pid=0
  cols.push(getChildren(0))
  // 顺着选中路径依次生成后续每一列
  let parentId = null
  for (const id of selectedPath.value) {
    const children = getChildren(id)
    if (children.length) cols.push(children)
  }
  return cols
})

// 面包屑列表
const breadList = computed(() => {
  return selectedPath.value.map(id => idMap.value[id])
})

// 鼠标悬浮：刷新后续菜单列
const onMouseEnter = (colIndex, item) => {
  // 截断当前层级之后所有选中
  selectedPath.value = selectedPath.value.slice(0, colIndex)
  // 把当前hover项加入路径
  selectedPath.value.push(item.id)
}

// 点击选中当前项，确定最终路径
const onSelectItem = (colIndex, item) => {
  selectedPath.value = selectedPath.value.slice(0, colIndex)
  selectedPath.value.push(item.id)
  console.log('最终选中', item)
}

// 点击面包屑定位菜单
const handleBreadClick = (item) => {
  // 回溯完整路径
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

/* 级联菜单核心样式 */
.cascader-box {
  display: flex;
  border: 1px solid #ccff23;
  width: fit-content;
}
.menu-column {
  /* min-width: 160px; */
  border-right: 1px solid #eee;
}
.menu-column:last-child {
  border-right: none;
}
.menu-item {
  padding: 2px 14px;/*表格行高宽间距*/
  cursor: pointer;
  display: flex;
  justify-content: space-between;
  align-items: center;
}

.menu-item.active {
  background-color: #9dc4ea;
  color: #f93636;
}
.right-arrow {
    margin-left: 20px;
  font-size: 12px;
  color: #999;
}
</style>