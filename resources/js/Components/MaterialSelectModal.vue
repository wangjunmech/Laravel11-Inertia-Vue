<template>
  <n-modal v-model:show="modalVisible" title="选择物料档案" width="680px" preset="card">
    <!-- 搜索框 -->
    <n-input
      v-model:value="searchKey"
      placeholder="输入物料编码/名称搜索物料"
      clearable
      class="mb-4"
    />
    <!-- 物料表格：通过 row-props 绑定行双击事件 -->
    <n-data-table
      :data="filterMaterialList"
      bordered
      max-height="420"
      :row-key="(row, index) => row.id ?? index"
      :columns="columns"
      row-hover
      :row-class-name="() => 'cursor-pointer'"
      :row-props="rowProps"
    />
  </n-modal>
</template>

<script setup>
import { ref, computed, defineEmits, defineProps } from 'vue'
import { NModal, NDataTable, NInput } from 'naive-ui'

// 接收父组件传值
const props = defineProps({
  show: Boolean,
  allMaterialList: {
    type: Array,
    default: () => []
  }
})
// 向外派发选中物料事件
const emit = defineEmits(['update:show', 'choose'])

const searchKey = ref('')

// 弹窗显隐双向绑定
const modalVisible = computed({
  get() {
    return props.show
  },
  set(val) {
    emit('update:show', val)
  }
})

// 表格列配置
const columns = [
  { title: '物料编码', key: 'code', width: 160 },
  { title: '物料名称', key: 'name' },
  { title: '单位', key: 'unit', width: 80 },
  { title: '采购单价', key: 'price', width: 120 }
]

// 搜索过滤物料列表
const filterMaterialList = computed(() => {
  const list = props.allMaterialList
  if (!searchKey.value.trim()) return list
  const keyword = searchKey.value.toLowerCase()
  return list.filter(item => {
    const code = item.code ?? ''
    const name = item.name ?? ''
    return code.toLowerCase().includes(keyword) || name.toLowerCase().includes(keyword)
  })
})

// 为每一行动态绑定原生事件
const rowProps = (row) => {
  return {
    onDblclick: () => {
      console.log('双击行数据：', row)
      emit('choose', row)
      // 向父组件派发关闭指令，双向绑定同步生效
      emit('update:show', false)
    }
  }
}
</script>