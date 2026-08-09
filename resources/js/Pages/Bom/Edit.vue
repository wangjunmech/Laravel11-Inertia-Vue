<template>
  <div class="p-6 bg-gray-50 min-h-screen">
    <!-- 消息弹窗：status由计算属性实时跟随props变化，开关由pinia控制 -->
      <SessionMessages ref="sessionMsgRef"
      :status="props.status" 
      v-if="appStore.showFlag"
    />
<!-- {{ appStore.showFlag }} -->
<!-- {{ page.value.flash }} -->
    <!-- 顶部操作栏 -->
    <div class="bg-white p-4 rounded shadow mb-4 flex justify-between items-center">
      <div>
        <h2 class="text-xl font-bold">BOM物料清单:<span class="bg-red-300">http://localhost:8000/bom/edit/5</span></h2>
        <p class="text-sm text-gray-500 mt-1">
          当前版本：{{ bomVersion.version_no }}
          <div v-if="bomStore.isEdited" class="text-orange-500 ml-2 bg-yellow-200 w-auto inline-flex w-fit p-2 px-10 border-red-600 rounded-lg">
            <b>存在未保存修改</b>
          </div>
        </p>
      </div>
      <div class="flex gap-2">
        <n-button @click="copyVersion" secondary>复制新版本</n-button>
        <n-button @click="handleExport">导出Excel</n-button>
        <n-button type="primary" @click="saveBom" :loading="saveLoading">保存BOM</n-button>
      </div>
    </div>

    <!-- 虚拟滚动树形表格核心区域 -->
    <div class="bg-white rounded shadow p-2">
      <n-data-table
        :data="flatList"
        :columns="tableColumns"
        virtual-scroll
        max-height="680px"
        :row-key="getRowKey"
        bordered
        :tree="treeOptions"
        scroll-x
      >
      </n-data-table>
    </div>

    <!-- 底部总成本汇总 -->
    <div class="mt-4 bg-white p-4 rounded shadow flex justify-end">
      <span class="text-lg">物料总成本合计：
        <b class="text-red-600 text-xl ml-2">{{ totalCost }} 元</b>
      </span>
    </div>
  </div>

  <!-- 编号弹窗选择器 -->
  <MaterialSelectModal
    class="flex max-w-[1200px]"
    v-model:show="materialModalShow"
    :all-material-list="materialList"
    @choose="handleChooseMaterial"
  />
</template>

<script setup>
import { ref, computed, watch, nextTick, h, onMounted, onBeforeUnmount } from 'vue'
import { router, usePage } from '@inertiajs/vue3'
import { useBomStore } from '@/stores/bomStore'
import { flattenTree, calcTotalCost } from '@/utils/bomCalc'
import { exportBomExcel } from '@/utils/excel'
import { NInputNumber, NButton, NSelect, NDataTable, NDropdown } from 'naive-ui'
import { debounce } from 'lodash'
import SessionMessages from "../../Components/SessionMessages.vue"
import MaterialSelectModal from '@/components/MaterialSelectModal.vue'
import { useAppStore } from '@/stores/app.js'

const appStore = useAppStore();
const bomStore = useBomStore();
const page = usePage()
// appStore.setShowFlag(true);
// 接收后端传入的status参数
const props = defineProps({
  bomTree: Array,
  bomVersion: Object,
  product: Object,
  materialList: {
    type: Array,
    default: () => []
  },
  status: String,
  trigger: Number
})



// 1. 安全判断某个字段是否被编辑过（全面兼容 Set、数组、普通对象）
const isFieldEdited = (row, fieldName) => {
  if (!row.editedFields) return false
  if (Array.isArray(row.editedFields)) {
    return row.editedFields.includes(fieldName)
  }
  if (row.editedFields instanceof Set) {
    return row.editedFields.has(fieldName)
  }
  return !!row.editedFields[fieldName]
}

// 2. 通用的添加编辑标记方法（确保多字段修改时能完美累加，不会互相覆盖）
const markFieldEdited = (row, fieldName) => {
  if (!row.editedFields) {
    row.editedFields = new Set()
  } else if (Array.isArray(row.editedFields)) {
    row.editedFields = new Set(row.editedFields)
  } else if (!(row.editedFields instanceof Set)) {
    row.editedFields = new Set(Object.keys(row.editedFields))
  }
  row.editedFields.add(fieldName)
}

// 新增弹窗状态
const materialModalShow = ref(false)
// 记录当前要回填数据的行
const currentEditRow = ref(null)

// onMounted(() => {
//   router.on('finish', () => {
//     const msg = page.value?.flash?.status
//     if (msg) {
//       console.log('收到后端消息：', msg)
//       appStore.setShowFlag(true)
//     }
//   })
// })

// 接收弹窗选中的物料，回填到当前行
const handleChooseMaterial = (materialItem) => {
  console.log("父组件收到选中物料：", materialItem)
  if (!currentEditRow.value || !materialItem) return

  const targetId = currentEditRow.value.id
  console.log("正在当前树中查找的目标行 ID:", targetId)

  let targetNode = null
  const findAndModify = (list) => {
    for (const item of list) {
      if (item.id == targetId) {
        targetNode = item
        return true
      }
      if (item.children?.length && findAndModify(item.children)) return true
    }
    return false
  }

  findAndModify(bomStore.bomTree)

  if (!targetNode) {
    console.warn("未能在树形结构中找到匹配的节点，ID为:", targetId)
    return
  }

  targetNode.material_id = materialItem.id
  targetNode.material = { ...materialItem }
  targetNode.unit = materialItem.unit || 'pcs'
  markFieldEdited(targetNode, 'code')
  markFieldEdited(targetNode, 'name')
  markFieldEdited(targetNode, 'unit')

  bomStore.isEdited = true
  bomStore.updateTree([...bomStore.bomTree])

  currentEditRow.value = null
  console.log("物料绑定并更新树成功，修改后的节点:", targetNode)
}

// 单位下拉菜单相关
const unitDropdownShow = ref(false)
const currentUnitEditRow = ref(null)

const unitMenuOptions = [
  {
    label: 'qty.数量类',
    key: 'num',
    children: [
      { label: 'PCS(个)', key: 'pcs' },
      { label: 'SET(套)', key: 'set' },
      { label: 'Pair(对 / 双)', key: 'pair' },
      { label: 'Assy(组件)', key: 'ass' },
    ]
  },
  {
    label: 'weight.重量类',
    key: 'weight',
    children: [
      { label: 'KG(千克)', key: 'kg' },
      { label: 'G(克)', key: 'g' },
      { label: 'T(吨)', key: 't' },
    ]
  },
  {
    label: 'length.长度类',
    key: 'length',
    children: [
      { label: 'M(米)', key: 'm' },
      { label: 'CM(厘米)', key: 'cm' },
      { label: 'MM(毫米)', key: 'mm' },
    ]
  },
  {
    label: 'area.面积类',
    key: 'area',
    children: [
      { label: '㎡(平方米)', key: 'm2' },
      { label: 'c㎡(平方厘米)', key: 'cm2' },
    ]
  }
]

const handleSelectUnit = (unitKey) => {
  if (!currentUnitEditRow.value) return
  currentUnitEditRow.value.unit = unitKey
  markFieldEdited(currentUnitEditRow.value, 'unit')
  bomStore.isEdited = true
  bomStore.updateTree([...bomStore.bomTree])
  unitDropdownShow.value = false
  currentUnitEditRow.value = null
}

const openMaterialSelect = (row) => {
  currentEditRow.value = row
  setTimeout(() => {
    materialModalShow.value = true
  }, 30)
}

const saveLoading = ref(false)
const getRowKey = (row) => row.id

bomStore.initData(props.bomTree, props.bomVersion.id, props.materialList)

const flatList = computed(() => flattenTree(bomStore.bomTree))
const totalCost = computed(() => calcTotalCost(bomStore.bomTree))

const treeOptions = {
  childrenKey: 'children',
  indent: 26,
  expandAll: false
}

const clearAllEditedMark = (treeArr) => {
  treeArr.forEach(node => {
    node.editedFields = null
    if (node.children?.length) {
      clearAllEditedMark(node.children)
    }
  })
}

// 表格列定义完全保留你的原版
const tableColumns = [
  {
    title: '序号',
    key: 'rowIndex',
    width: 60,
    render: row => row.rowIndex
  },
  {
    title: '分级序号',
    key: 'levelCode',
    width: 100,
    render: row => row.levelCode
  },
  { title: '层级', key: 'depth', width: 70, render: row => row.depth + 1 },
  {
    title: '物料编码',
    key: 'material_code',
    width: 160,
    render: (row) => {
      const isEdit = isFieldEdited(row, 'code')
      return h('div', {
        onClick: () => {
          currentEditRow.value = row
          materialModalShow.value = true
        },
        style: `cursor:pointer;min-height:32px;display:flex;align-items:center;padding:0 6px;user-select:none;${isEdit ? 'background:#fef9c3;' : ''}`
      }, row.material?.code ?? '点击选择物料')
    }
  },
  {
    title: '物料名称',
    key: 'material_name',
    width: 260,
    render: (row) => {
      const isEdit = isFieldEdited(row, 'name')
      return h('div', {
        style: `min-height:32px;display:flex;align-items:center;padding:0 6px;${isEdit ? 'background:#fef9c3;' : ''}`
      }, row.material?.name ?? '总成节点')
    }
  },
  {
    title: '单台用量',
    key: 'qty',
    width: 130,
    render: (row) => {
      const isEdit = isFieldEdited(row, 'qty')
      return h('div', {
        style: `display: flex; align-items: center; padding: 2px 4px; border-radius: 4px; ${isEdit ? 'background: #fef9c3;' : ''}`
      }, [
        h(NInputNumber, {
          value: row.qty,
          min: 0.000001,
          step: 1,
          onUpdateValue: debounce((val) => updateQty(row, val), 300),
          style: `width: 100%; ${isEdit ? '--n-color: #fef9c3;' : ''}`
        })
      ])
    }
  },
  {
    title: '单位',
    key: 'unit',
    width: 90,
    render: (row) => {
      const isEdit = isFieldEdited(row, 'unit')
      return h(NDropdown, {
        options: unitMenuOptions,
        trigger: 'click',
        placement: 'bottom-start',
        onSelect: (key) => handleSelectUnit(key),
      }, {
        default: () => h('div', {
          onClick: () => {
            currentUnitEditRow.value = row
          },
          style: `
          cursor:pointer;
          min-height:32px;
          display:flex;
          background:#ffff00;
          padding:0 6px;
          align-items:center;
          justify-content:center;
          border:1px solid #e5e7eb;
          border-radius:4px;
          ${isEdit ? 'background:#ffcc00;' : ''}
        `
        }, row.unit?.toUpperCase() ?? 'PCS')
      })
    }
  },
  {
    title: '损耗率(%)',
    key: 'loss_rate',
    width: 130,
    render: (row) => {
      const isEdit = isFieldEdited(row, 'loss_rate')
      return h('div', {
        style: `display: flex; align-items: center; padding: 2px 4px; border-radius: 4px; ${isEdit ? 'background: #ffcc00;' : ''}`
      }, [
        h(NInputNumber, {
          value: row.loss_rate,
          min: 0,
          max: 100,
          onUpdateValue: debounce((val) => updateLoss(row, val), 300),
          style: `width: 100%; ${isEdit ? '--n-color: #fff9c3;' : ''}`
        })
      ])
    }
  },
  { title: '采购单价', key: 'price', width: 120, render: row => row.material?.price ?? 0 },
  { title: '小计成本', key: 'subtotal', width: 120 },
  {
    title: '操作',
    key: 'action',
    width: 180,
    render: (row) => h('div', { style: 'display:flex;gap:6px;' }, [
      h(NButton, { size: 'small', type: 'info', onClick: () => addChildRow(row) }, { default: () => '加子项' }),
      h(NButton, { size: 'small', type: 'error', danger: true, onClick: () => deleteRow(row) }, { default: () => '删除' })
    ])
  }
]

const updateQty = (row, val) => {
  const safeVal = Number(val) || 0.000001
  row.qty = safeVal
  markFieldEdited(row, 'qty')
  bomStore.isEdited = true
  bomStore.updateTree(bomStore.bomTree)
}

const updateLoss = (row, val) => {
  const safeVal = Number(val) || 0
  row.loss_rate = safeVal
  markFieldEdited(row, 'loss_rate')
  bomStore.isEdited = true
  bomStore.updateTree(bomStore.bomTree)
}

const addChildRow = (parentRow) => {
  const newChild = {
    id: Date.now(),
    parent_id: parentRow.id,
    material_id: null,
    material: null,
    material_code: '',
    material_name: '',
    qty: 0,
    loss_rate: 0,
    subtotal: 0,
    depth: parentRow.depth + 1,
    children: [],
    unit: 'pcs',
  }
  const addChildToNode = (treeArr, targetId, newItem) => {
    treeArr.forEach(node => {
      if (node.id === targetId) {
        node.children.push(newItem)
      } else if (node.children?.length) {
        addChildToNode(node.children, targetId, newItem)
      }
    })
  }
  const newTree = [...bomStore.bomTree]
  addChildToNode(newTree, parentRow.id, newChild)
  bomStore.isEdited = true
  bomStore.updateTree(newTree)
}

const deleteRow = (delRow) => {
  const removeNode = (treeArr, targetId) => {
    for (let i = 0; i < treeArr.length; i++) {
      if (treeArr[i].id === targetId) {
        treeArr.splice(i, 1)
        return true
      }
      if (treeArr[i].children?.length) {
        if (removeNode(treeArr[i].children, targetId)) return true
      }
    }
    return false
  }
  const newTree = [...bomStore.bomTree]
  removeNode(newTree, delRow.id)
  bomStore.isEdited = true
  bomStore.updateTree(newTree)
}

// 保存接口：删掉内部手动setShowFlag，交给上方watch统一管控
const saveBom = () => {
  saveLoading.value = true

  const submitTree = JSON.parse(JSON.stringify(bomStore.bomTree))
  const cleanTreeForBackend = (nodes) => {
    nodes.forEach(node => {
      delete node.material
      if (node.children && node.children.length) {
        cleanTreeForBackend(node.children)
      }
    })
  }
  cleanTreeForBackend(submitTree)

  console.log('提交给后端纯净入库数据：', submitTree)

  router.post(route('bom.save', bomStore.curVersionId), {
    tree: submitTree
  }, {
    preserveScroll: true,
    onSuccess: () => {
      saveLoading.value = false
      bomStore.isEdited = false
      const tree = [...bomStore.bomTree]
      clearAllEditedMark(tree)
      bomStore.updateTree(tree)
      appStore.setShowFlag(true);
    }
  })
}

const copyVersion = () => {
  router.post(route('bom.copy', bomStore.curVersionId))
}

const handleExport = () => {
  exportBomExcel(bomStore.bomTree)
}

</script>

<style scoped>
</style>