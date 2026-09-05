<template>
  <div class="container">
    <div class="text-xl p-2">产品工艺流程表 <span class="cursor-pointer text-blue-500 hover:underline" @click="showData">打印当前数据到控制台</span> </div>
    <!-- 面包屑导航 -->
    <div class="breadcrumb-wrap flex flex-wrap">
      <span class="label">当前路径：</span>
      <span
        v-for="(item, index) in breadList"
        :key="item.id"
        class="bread-hover "
        @click="handleBreadClick(item)"
      >
        {{ item.area }}
        <span v-if="index < breadList.length - 1" class="separator">→</span>
      </span>
    </div>

    <div class="cascader-box">
      <div
        v-for="(column, colIdx) in menuColumns"
        :key="colIdx"
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
</template>

<script setup>
import { ref, computed } from 'vue'
import TreeMenuItem from '@/Components/TreeMenuItem.vue'

// ========== 自定义配置 ==========

const baseColWidth = 200 // 每列的基础宽度
const stackOverlap = ref(-30)//type Number // 列之间的重叠宽度
const foldSideWidth = ref(100)//type Number // 折叠侧边栏的宽度
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

  { id: 10, area: '通用热塑性塑料', pid: 2 },
  { id: 11, area: '工程热塑性塑料', pid: 2 },
  { id: 12, area: '热固性塑料', pid: 2 },

  { id: 13, area: '丁腈橡胶NBR', pid: 3 },
  { id: 14, area: '三元乙丙EPDM', pid: 3 },
  { id: 15, area: '硅橡胶VMQ', pid: 3 },

  { id: 16, area: '玻纤FRP', pid: 4 },
  { id: 17, area: '碳纤维复材', pid: 4 },

  // 三级：成型工艺
  { id: 18, area: '压力压铸', pid: 5 },
  { id: 19, area: '模锻成型', pid: 5 },
  { id: 20, area: '砂型铸造', pid: 5 },

  { id: 21, area: '砂型铸造', pid: 6 },
  { id: 22, area: '热挤压成型', pid: 6 },

  { id: 23, area: '模锻成型', pid: 7 },
  { id: 24, area: '板料冲压', pid: 7 },

  { id: 25, area: '板料冲压', pid: 8 },
  { id: 26, area: '粉末冶金', pid: 8 },

  { id: 27, area: '压力压铸', pid: 9 },

  { id: 28, area: '注塑成型', pid: 10 },
  { id: 29, area: '挤出成型', pid: 10 },
  { id: 30, area: '吹塑成型', pid: 10 },

  { id: 31, area: '注塑成型', pid: 11 },
  { id: 32, area: '挤出成型', pid: 11 },

  { id: 33, area: '模压成型', pid: 12 },

  { id: 34, area: '模压硫化', pid: 13 },
  { id: 35, area: '模压硫化', pid: 14 },
  { id: 36, area: '模压硫化', pid: 15 },

  { id: 37, area: '手糊成型', pid: 16 },
  { id: 38, area: '模压复材成型', pid: 16 },
  { id: 39, area: '预浸料热压成型', pid: 17 },

  // 四级：主工序
  { id: 40, area: '原料烘干', pid: 31 },
  { id: 41, area: '熔融注射充模', pid: 31 },
  { id: 42, area: '保压冷却', pid: 31 },
  { id: 43, area: '开模顶出', pid: 31 },
  { id: 44, area: '除披锋', pid: 31 },

  { id: 45, area: '金属熔炼', pid: 18 },
  { id: 46, area: '高压充型', pid: 18 },
  { id: 47, area: '冷却脱模', pid: 18 },
  { id: 48, area: '切浇口除飞边', pid: 18 },

  { id: 49, area: '橡胶混炼裁料', pid: 36 },
  { id: 50, area: '模具高温硫化', pid: 36 },
  { id: 51, area: '脱模修边', pid: 36 },

  // 五级：后处理工序
  { id: 52, area: '回火去内应力', pid: 44 },
  { id: 53, area: '丝印镭雕', pid: 44 },
  { id: 54, area: '尺寸全检', pid: 44 },
  { id: 55, area: '成品包装', pid: 44 },

  { id: 56, area: '抛丸清理', pid: 48 },
  { id: 57, area: '去应力退火', pid: 48 },
  { id: 58, area: 'CNC机加工', pid: 48 },
  { id: 59, area: '阳极氧化', pid: 48 },

  { id: 60, area: '二次硫化', pid: 51 },
  { id: 61, area: '尺寸外观检验', pid: 51 },
  { id: 62, area: '成品包装', pid: 51 }
])

const idMap = computed(() => {
  const map = {}
  letters.value.forEach(item => map[item.id] = item)
  return map
})
const showData=() => {
    // console.log('当前数据', letters.value)//打印对象
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
.breadcrumb-item {
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
  border: 1px solid #ccff23;
  min-height: 320px;
}
.menu-column {
  position: absolute;
  top: 0;
  background: #fff;
  border-right: 1px solid #eee;

}
.menu-column--active {
  border: 2px solid #ef4444;   /* 红色边框，四周都要，所以用 border 而不是 border-right */
  border-radius: 8px;
}
</style>
