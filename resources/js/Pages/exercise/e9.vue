<template>
  <div class="container">
    <!-- 面包屑导航 -->
    <div>
      <span>当前路径：</span>
      <span class="bread cursor-pointer"
        v-for="(item, index) in breadList"
        :key="item.id"        
        @click="handleBreadClick(item)"
      >
        {{ item.area }}
        <span v-if="index < breadList.length - 1" class="separator">/</span>
      </span>
    </div>

    <!-- 多级联动下拉容器 -->
    <div >
      <select
        v-model="selectVal[0]"
        @change="handleLevelChange(0)"
        
      >
        <option value="">请选择一级分类</option>
        <option v-for="item in levelOneList" :key="item.id" :value="item.id">
          {{ item.area }}
        </option>
      </select>

      <select
        v-if="levelTwoList.length"
        v-model="selectVal[1]"
        @change="handleLevelChange(1)"
        
      >
        <option value="">请选择二级分类</option>
        <option v-for="item in levelTwoList" :key="item.id" :value="item.id">
          {{ item.area }}
        </option>
      </select>

      <select
        v-if="levelThreeList.length"
        v-model="selectVal[2]"
        @change="handleLevelChange(2)"
        
      >
        <option value="">请选择三级分类</option>
        <option v-for="item in levelThreeList" :key="item.id" :value="item.id">
          {{ item.area }}
        </option>
      </select>

      <select
        v-if="levelFourList.length"
        v-model="selectVal[3]"
        @change="handleLevelChange(3)"
      >
        <option value="">请选择四级分类</option>
        <option v-for="item in levelFourList" :key="item.id" :value="item.id">
          {{ item.area }}
        </option>
      </select>

      <select
        v-if="levelFiveList.length"
        v-model="selectVal[4]"
        @change="handleLevelChange(4)"
      >
        <option value="">请选择五级分类</option>
        <option v-for="item in levelFiveList" :key="item.id" :value="item.id">
          {{ item.area }}
        </option>
      </select>
    </div>

    
  </div>
</template>

<script setup>
import { ref, computed, watch } from 'vue'

// 原始数据源
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

// id映射表，快速查单个节点
const idMap = computed(() => {
  const map = {}
  letters.value.forEach(item => map[item.id] = item)
  return map
})

// 当前各级选中值 [一级id,二级id,三级id,四级id,五级id]
const selectVal = ref(['', '', '', '', ''])
// 当前最终选中ID
const currentId = ref('')

// ========== 各级下拉选项列表 ==========
const levelOneList = computed(() => letters.value.filter(item => item.pid === 0))
const levelTwoList = computed(() => letters.value.filter(item => item.pid === selectVal.value[0]))
const levelThreeList = computed(() => letters.value.filter(item => item.pid === selectVal.value[1]))
const levelFourList = computed(() => letters.value.filter(item => item.pid === selectVal.value[2]))
const levelFiveList = computed(() => letters.value.filter(item => item.pid === selectVal.value[3]))

// 面包屑路径数组
const breadList = computed(() => {
  const res = []
  if (!currentId.value) return res
  let cur = idMap.value[currentId.value]
  while (cur) {
    res.unshift(cur)
    cur = idMap.value[cur.pid]
  }
  return res
})

// 下拉切换处理：清空后面层级，更新选中ID
const handleLevelChange = (index) => {
  // 清空当前层级后面所有选择
  for (let i = index + 1; i < selectVal.value.length; i++) {
    selectVal.value[i] = ''
  }
  // 找到最后一个有值的选中项，作为当前节点
  let lastId = ''
  for (let i = 0; i < selectVal.value.length; i++) {
    if (selectVal.value[i]) lastId = selectVal.value[i]
  }
  currentId.value = lastId
}

// 点击面包屑跳转定位
const handleBreadClick = (item) => {
  currentId.value = item.id
  // 回溯路径，回填下拉框
  const pathArr = []
  let cur = idMap.value[item.id]
  while (cur) {
    pathArr.unshift(cur.id)
    cur = idMap.value[cur.pid]
  }
  // 重置下拉值
  selectVal.value = ['', '', '', '', '']
  pathArr.forEach((id, idx) => {
    selectVal.value[idx] = id
  })
}
</script>

<style scoped>
.container {
  padding: 20px;
}

.bread:hover{
    color:rgb(254, 29, 119);
}
.separator {
  margin: 0 8px;
  color: #ccc;
}

</style>