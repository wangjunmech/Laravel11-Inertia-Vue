<template>
  <div class="bom-container">
    <div class="bom-header">
      <h2 class="text-2xl font-bold">结构化BOM创建器⏱</h2>

      <label class="config-toggle">
        <input
          type="checkbox"
          :checked="noRepeatName"
          @change="handleToggleNoRepeat"
          class="mr-2"
        >
        BOM产品编号全局无重复({{ noRepeatName?'是':'否' }})
      </label>

      <div class="button-group">
        <button
          @click="console.log(JSON.stringify(bomBusinessTree,null,2))"
          class="ml-4 px-3 py-1 bg-blue-500 hover:bg-green-600 text-white rounded transition-colors"
        >
          exportConsole(纯净业务树)
        </button>
        <button
          @click="exportToTxt"
          class="ml-4 px-3 py-1 bg-blue-500 hover:bg-green-600 text-white rounded transition-colors"
        >
          Export📄
        </button>
        <button @click="handleExport" class="action-btn ml-4 px-3 py-1 bg-blue-500 hover:bg-green-600 rounded-lg">
            📄 导出 Excel 文件
        </button>
      </div>
    </div>

    <div class="flex ">
        <div class="justify-start items-start">
          <input
            title="搜索BOM"
            v-model="searchKeyword"
            @dblclick="searchKeyword = ''"
            @keyup.enter.prevent="searchKeyword = ''"
            @keyup.esc.prevent="searchKeyword = ''"
            placeholder="🔍搜索编号，名称"
            class="rounded-lg h-8 max-w-[220px] ml-auto border border-gray-300"
          >
        </div>

        <div>
        <div  class="action-btn ml-4 px-3 py-1 bg-orange-200 hover:bg-slate-200 rounded-lg cursor-pointer">
          <label >
           <span
            class="cursor-pointer"
            @click="cateFilterControl();searchByCate()"
            >搜索类别</span>
          </label>
        </div>
            <div v-if="cateFilterFlag" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click="cateFilterFlag=false">
              <div class="bg-white p-5 rounded-lg w-[280px]" @click.stop>
                <h3 class="font-bold mb-3">勾选需要搜索的类别</h3>
                <div v-for="(item) in cateOptionList" :key="item.cateId" class="flex gap-3 items-center my-2">
                  <input type="checkbox" v-model="item.showCate" >
                  <span>{{item.cateName}}</span>
                </div>
                <div class="flex gap-3 mt-4">
                  <button @click="checkAllCate" class="px-3 py-1 bg-gray-300 rounded">全选</button>
                  <button @click="unCheckAllCate" class="px-3 py-1 bg-gray-300 rounded">全不选</button>
                  <button @click="handleCateConfirm" class="px-3 py-1 bg-blue-500 text-white rounded">确定</button>
                </div>
              </div>
            </div>
        </div>

        <div>
          <div @click="showFiledControl" class="action-btn ml-4 px-3 py-1 bg-orange-200 hover:bg-slate-200 rounded-lg cursor-pointer">
               👁︎字段显示选择
          </div>
          <div v-if="showFieldPopup" class="fixed inset-0 z-50 flex items-center justify-center bg-black/40" @click="showFieldPopup=false">
                <div class="bg-white p-5 rounded-lg w-[280px]" @click.stop>
                  <h3 class="font-bold mb-3">勾选需要展示的列表字段</h3>
                  <div v-for="(item,idx) in fieldOptions" :key="item.key" class="flex gap-3 items-center my-2">
                    <input type="checkbox" v-model="item.visible" @change="toggleField(idx)">
                    <span>{{item.fieldLabel}}</span>
                  </div>
                  <div class="flex gap-3 mt-4">
                    <button @click="fieldOptions.forEach(f=>f.visible=true)" class="px-3 py-1 bg-gray-300 rounded">全选</button>
                    <button @click="fieldOptions.forEach(f=>f.visible=false)" class="px-3 py-1 bg-gray-300 rounded">全不选</button>
                    <button @click="showFieldPopup=false" class="px-3 py-1 bg-blue-500 text-white rounded">确定</button>
                  </div>
                </div>
          </div>
        </div>

        <div class="flex justify-center ml-10 bg-orange-200 items-center px-2 rounded">
          <label >
            <input type="checkbox" v-model="showIdLevel" class="mr-2">
            显示ID层级 ({{ showIdLevel }})
          </label>
        </div>

    </div>
    <hr class=" border-2 bg-gray-200">

     <div class="bg-gray-400 rounded pl-2">
        <div>BOM创建时间</div>
        <div>BOM版本号</div>
          <div class="text-red-500 ml-6 ">
            检测到重复产品编号：{{repeatCount}} 条
            <div v-for="(item,index) in repeatCodeList" :key="index" class="ml-3">
              编号【{{item.code}}】，重复次数：{{item.repeatNum}}
            </div>
          </div>
     </div>

    <div>
      <div class="flex items-center w-full ">
        <div class="flex-1">
          <div class="flex items-center">
            <label for="">自定义：</label>
            <input
              v-model="namePrefix"
              type="text"
              placeholder="编号生成前缀"
              class="w-[130px] py-1 border rounded mr-3"
            />
            <label for="">+后缀数位：</label>
            <input
              v-model.number="digitLength"
              type="number"
              min="1"
              max="8"
              step="1"
              placeholder="数位"
              class="w-[80px] py-1 border rounded"
              @blur="checkDigitRange"
            />
          </div>
        </div>

        <div class="flex">
          <div class=" bg-blue-400 m-1 px-4 rounded-lg">序号</div>
          <div class=" bg-yellow-400 m-1 px-4 rounded-lg">类别</div>
          <div class="flex bg-green-100 m-1 px-4 rounded-lg w-[220px] justify-center border">产品编号</div>
          <div v-if="fieldOptions.find(item => item.key === 'nameEn').visible" class="flex bg-green-400 m-1 px-4 rounded-lg w-[110px]">品名英</div>
          <div v-if="fieldOptions.find(item => item.key === 'nameCn').visible" class="flex bg-green-400 m-1 px-4 rounded-lg w-[110px]">品名中</div>
          <div v-if="fieldOptions.find(item => item.key === 'quantity').visible" class="flex bg-red-200 m-1 px-4 rounded-lg">用量</div>
          <div v-if="fieldOptions.find(item => item.key === 'unit').visible" class="flex bg-red-400 m-1 px-4 rounded-lg">单位</div>
          <div v-if="fieldOptions.find(item => item.key === 'wasteRate').visible" class="flex bg-green-400 m-1 px-4 rounded-lg">损耗率</div>
          <div v-if="fieldOptions.find(item => item.key === 'price').visible" class="flex bg-green-400 m-1 px-4 rounded-lg w-[80px] justify-center">单价</div>
          <div v-if="fieldOptions.find(item => item.key === 'subtotal').visible" class="flex bg-gray-200 m-1 px-4 rounded-lg">小计成本</div>
          <div v-if="showIdLevel" class="flex m-1 px-4 w-[80px]"></div>
        </div>

        <div class="w-[140px] flex-shrink-0 h-full">
          <div class="h-full flex items-center justify-center ">右侧编辑操作区</div>
        </div>
      </div>
    </div>
    <hr class="h-2 border-2 bg-gray-200">

    <BomNode3
      :bom-data="currentBomData"
      @update:bom-data="updatebomData"
      :show-id-level="showIdLevel"
      :no-repeat-name="noRepeatName"
      :name-prefix="namePrefix"
      :digit-length="digitLength"
      :search-keyword="searchKeyword"
      :repeat-code-list="repeatCodeList"
      :field-options="fieldOptions"
      :cate-option-list="cateOptionList"
      @select-cate="handleSelectCate"
      @add-cate="handleAddCate"
    />

    <div class="mt-4 bg-slate-200 p-4 rounded shadow flex justify-end">
      <span>BOM项目总条数：{{bomItemCount-1}}
      </span>
      <span class="w-6"></span>

      <span>BOM物料总成本合计：
        <b class="text-red-600  ml-2">{{ BomTotalCost }} 元</b>
      </span>
    </div>

    <div class="text-red-500">待改进问题：类别筛选器，单位选择，保存，导入</div>
  </div>
</template>

<script setup>
import { ref,watch, onMounted, onBeforeUnmount,computed, h } from 'vue';
import BomNode3 from "./BomNode3.vue";
import * as XLSX from 'xlsx';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

// ===================== 方案B：外置UI状态Map =====================
/**
 * UI状态定义，全部从node剥离，放到Map
 * {
 *   isOpen: boolean
 *   isEdit: boolean
 *   isNew: boolean
 *   bgactive: boolean
 *   editField: string|null
 *   unitPopupOpen: boolean
 *   _isKeywordMatch: boolean
 *   tempPartCode: string
 *   lockField: boolean
 * }
 */
const uiStateMap = ref(new Map())

/**
 * 初始化单个节点UI状态，写入map
 */
function initNodeUiState(node){
  if(!uiStateMap.value.has(node.id)){
    uiStateMap.value.set(node.id,{
      isOpen:true,
      isEdit:false,
      isNew:false,
      bgactive:false,
      editField:null,
      unitPopupOpen:false,
      _isKeywordMatch:false,
      tempPartCode:"",
      lockField:false
    })
  }
  if(node.children) node.children.forEach(child=>initNodeUiState(child))
}

/**
 * 递归初始化整棵树全部节点UI状态
 */
function initTreeUiState(tree){
  tree.forEach(n=>initNodeUiState(n))
}

/**
 * 删除节点同时清理uiStateMap
 */
function cleanNodeUiState(tree){
  tree.forEach(node=>{
    uiStateMap.value.delete(node.id)
    if(node.children) cleanNodeUiState(node.children)
  })
}

const searchKeyword = ref('')

const fieldOptions = ref([
  { fieldLabel:"品名英", key:"nameEn", visible:true },
  { fieldLabel:"品名中", key:"nameCn", visible:true },
  { fieldLabel:"用量", key:"quantity", visible:true },
  { fieldLabel:"单位", key:"unit", visible:true },
  { fieldLabel:"损耗率", key:"wasteRate", visible:true },
  { fieldLabel:"单价", key:"price", visible:true },
  { fieldLabel:"小计成本", key:"subtotal", visible:true }
])
const showFieldPopup = ref(false)
const showFiledControl = ()=>{
  showFieldPopup.value = !showFieldPopup.value
}

const cateFilterFlag = ref(false)
const cateFilterControl = ()=>{
  cateFilterFlag.value = !cateFilterFlag.value
}
const searchByCate = () => {}

const activeCateFilter = ref([])
const filterBomByCate = (tree, allowCateArr) => {
  if(!allowCateArr || allowCateArr.length === 0) return JSON.parse(JSON.stringify(tree))
  const result = []
  for(const node of tree){
    const childFiltered = filterBomByCate(node.children || [], allowCateArr)
    const nodeMatch = allowCateArr.includes(node.cateName)
    if(nodeMatch || childFiltered.length > 0){
      const newNode = JSON.parse(JSON.stringify(node))
      newNode.children = childFiltered
      result.push(newNode)
    }
  }
  return result
}

const handleCateConfirm = () => {
activeCateFilter.value = getCheckedCateList()
cateFilterFlag.value = false
}
const checkAllCate = () => {
  cateOptionList.value.forEach(item => {
    item.showCate = true
  })
}
const unCheckAllCate = () => {
  cateOptionList.value.forEach(item => {
    item.showCate = false
  })
}

const getCheckedCateList = () => {
  if(!Array.isArray(cateOptionList.value)){
    return []
  }
  return cateOptionList.value.filter(opt => opt.showCate).map(o=>o.cateName)
}

const activeCateSet = computed(()=>{
  return new Set(getCheckedCateList())
})

const filteredBomData = computed(()=>{
  if(activeCateSet.value.size === 0){
    return JSON.parse(JSON.stringify(bomBusinessTree.value))
  }
  return filterBomByCate(bomBusinessTree.value, [...activeCateSet.value])
})

const getFieldVisible = (key) =>{
  const findItem = fieldOptions.value.find(item=> item.key === key)
  return findItem?.visible ?? true
}

// ========== 纯净业务树，没有任何UI字段 ==========
const bomBusinessTree = ref(
  [{
    "id": 0, "parentId":null, "partCode": "整机总成编号",
    "cateId":0,"cateName":"类别",
    "nameEn": "Full Machine",
    "nameCn": "整机总成",
    "quantity":1,
    "unit":"pcs",
    "wasteRate":0,
    "price":0,
    "subtotal":0,
    "sn":0,
    "children":
      [{
        "id": 4, "parentId":0, "partCode": "code0001",
        "cateId":0,"cateName":"类别",
        "nameEn": "labselcover0001",
        "nameCn": "扫地机支架",
        "quantity": 6,
        "unit": "g",
        "wasteRate": 0,
        "price": 8,
        "subtotal": 48.48,
        "sn": 1,
        "children": []
      },
        {
          "id": 1, "parentId":0, "partCode": "code0003",
          "cateId":0,"cateName":"类别",
          "nameEn": "topcover",
          "nameCn": "扫地机电池上盖",
          "quantity": 2,
          "unit": "pcs",
          "wasteRate": 0,
          "price": 5,
          "subtotal": 10.10,
          "sn": 2,
          "children": []
        }
      ]
  }
  ]
)
// 初始化UI状态Map
initTreeUiState(bomBusinessTree.value)

const filterBomTree = (tree, keyword) => {
  if (!keyword.trim()) return JSON.parse(JSON.stringify(tree))
  const kw = keyword.toLowerCase().trim()
  const result = []
  for (const node of tree) {
    const isMatch =
      node.partCode?.toLowerCase().includes(kw) ||
      node.nameEn?.toLowerCase().includes(kw) ||
      node.nameCn?.toLowerCase().includes(kw)
    const childMatched = filterBomTree(node.children || [], keyword)
    if (isMatch || childMatched.length > 0) {
      const newNode = JSON.parse(JSON.stringify(node))
      newNode.children = childMatched
      result.push(newNode)
    }
  }
  return result
}

const currentBomData = computed(() => {
  let afterSearch = filterBomTree(bomBusinessTree.value, searchKeyword.value)
  let finalTree = filterBomByCate(afterSearch, activeCateFilter.value)
  return finalTree
})

const cateOptionList =ref([
  {cateName:"Plastic",cateId:1, showCate:true},
  {cateName:"PCBA",cateId:2, showCate:true},
  {cateName:"Metal",cateId:3, showCate:true},
  {cateName:"Std",cateId:4, showCate:true},
  {cateName:"Semi-ASS",cateId:5, showCate:true},
])

const handleSelectCate = (row, opt) => {
  row.cateName = opt.cateName
  row.cateId = opt.cateId
}

const getMaxCateId = () => {
  const arr = cateOptionList.value
  if (!arr || arr.length === 0) return 0
  return Math.max(...arr.map(item => item.cateId))
}

const handleAddCate = (row, text) => {
  let newCid = Number(getMaxCateId() + 1)
  const txt = text.trim()
  const existingCate = cateOptionList.value.find(item => item.cateName === txt)
  if (existingCate) {
    row.cateName = existingCate.cateName
    row.cateId = existingCate.cateId
    updatebomData([...bomBusinessTree.value])
    return
  }
  const newItem = {
    cateName: txt,
    cateId: newCid,
    showCate:true
  }
  cateOptionList.value.push(newItem)
  setNodeCate(bomBusinessTree.value, row.id, txt)
}

function setNodeCate(list, targetId, cateText) {
  for(let item of list){
    if(item.id === targetId){
      item.cateName = cateText
      item.cateId = getMaxCateId()
      return true
    }
    if(item.children) setNodeCate(item.children,targetId,cateText)
  }
}

const repeatCodeList = ref([])
const repeatCount = ref(0)
function traverseBom(list,arr=[]){
  list.forEach(item=>{
    arr.push(item)
    if(item.children && item.children.length>0){
      traverseBom(item.children,arr)
    }
  })
  return arr
}

function checkRepeatCode(){
  const allNode = traverseBom(bomBusinessTree.value)
  const codeMap = new Map()
  allNode.forEach(node=>{
    const num = node.partCode
    if(!codeMap.has(num)){
      codeMap.set(num,[])
    }
    codeMap.get(num).push(node)
  })
  repeatCodeList.value = []
  codeMap.forEach((nodes,code)=>{
    if(nodes.length >= 2){
      repeatCodeList.value.push({
        code:code,
        nodeList:nodes,
        repeatNum:nodes.length
      })
    }
  })
  repeatCount.value = repeatCodeList.value.length
}

watch(bomBusinessTree,()=>{
  checkRepeatCode()
},{deep:true})

const flattenBomTree = (nodes, level = 0, list = []) => {
  nodes.forEach(node => {
    const indentSpace = '　'.repeat(level);
    list.push({
      level: level,
      id: node.id,
      pid: node.parentId ?? '无',
      sn: node.sn,
      cateName: node.cateName,
      name: indentSpace + node.partCode,
      nameEn: node.nameEn,
      nameCn: node.nameCn,
      quantity: node.quantity,
      unit: node.unit,
      wasteRate: node.wasteRate + '%',
      price: node.price,
      subtotal: node.subtotal.toFixed(2)
    })
    if (node.children && node.children.length > 0) {
      flattenBomTree(node.children, level + 1, list)
    }
  })
  return list
}

const handleExport = async () => {
  try {
    const bomList = flattenBomTree(bomBusinessTree.value)
    if (bomList.length === 0) {
      alert('暂无BOM数据可导出');
      return;
    }
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('BOM物料清单');
    worksheet.columns = [
      { header: '层级', key: 'level', width: 8 },
      { header: '节点ID', key: 'id', width: 10 },
      { header: '父级PID', key: 'pid', width: 10 },
      { header: '流水序号SN', key: 'sn', width: 12 },
      { header: '类别', key: 'cateName', width: 22 },
      { header: '产品编号(树形缩进)', key: 'name', width: 32 },
      { header: '品名英文', key: 'nameEn', width: 22 },
      { header: '品名中文', key: 'nameCn', width: 20 },
      { header: '用量', key: 'quantity', width: 8 },
      { header: '单位', key: 'unit', width: 8 },
      { header: '损耗率', key: 'wasteRate', width: 10 },
      { header: '采购单价', key: 'price', width: 12 },
      { header: '小计成本', key: 'subtotal', width: 12 }
    ];
    const headerRow = worksheet.getRow(1);
    headerRow.font = { bold: true };
    headerRow.alignment = { vertical: 'middle', horizontal: 'center' };
    headerRow.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'E6F7FF' }
    };
    worksheet.addRows(bomList);
    worksheet.getColumn('quantity').alignment = { horizontal: 'right' }
    worksheet.getColumn('price').alignment = { horizontal: 'right' }
    worksheet.getColumn('subtotal').alignment = { horizontal: 'right' }
    worksheet.getColumn('level').alignment = { horizontal: 'center' }
    worksheet.getColumn('id').alignment = { horizontal: 'center' }
    worksheet.getColumn('pid').alignment = { horizontal: 'center' }
    worksheet.getColumn('sn').alignment = { horizontal: 'center' }
    worksheet.eachRow((row, rowNum) => {
      row.alignment = { vertical: 'middle' };
      if (rowNum > 1 && rowNum % 2 === 0) {
        row.fill = {
          type: 'pattern',
          pattern: 'solid',
          fgColor: { argb: 'aef777' }
        }
      }
    });
    worksheet.eachRow((row) => {
      row.alignment = { vertical: 'middle' };
    });
    const buffer = await workbook.xlsx.writeBuffer();
    const blob = new Blob([buffer], {
      type: 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'
    });
    saveAs(blob, `结构化BOM清单_${new Date().getTime()}.xlsx`);
  } catch (err) {
    console.error('Excel导出失败：', err);
    alert('导出Excel失败，请查看控制台报错');
  }
};

const namePrefix = ref('code')
const digitLength = ref(4)
const showIdLevel = ref(false)
const noRepeatName = ref(true)

const checkDigitRange = () => {
  if (digitLength.value < 1) digitLength.value = 1
  if (digitLength.value > 8) digitLength.value = 8
}

const updatebomData = (newData) => {
  bomBusinessTree.value = newData
  initTreeUiState(bomBusinessTree.value)
}

const exportToTxt = () => {
  try {
    const dataStr = JSON.stringify(bomBusinessTree.value, null, 2)
    const blob = new Blob([dataStr], { type: 'text/plain;charset=utf-8' })
    const downloadLink = document.createElement('a')
    downloadLink.href = URL.createObjectURL(blob)
    downloadLink.download = `bom-pure-data-${new Date().getTime()}.txt`
    downloadLink.click()
    URL.revokeObjectURL(downloadLink.href)
  } catch (error) {
    console.error('导出失败：', error)
    alert('导出文件失败，请检查控制台错误信息。')
  }
}

const traverseAllSn = (nodes, snList = new Set(), duplicateList = []) => {
  nodes.forEach(node => {
    const sn = node.partCode?.trim()
    if (sn) {
      if (snList.has(sn)) {
        duplicateList.push(sn)
      } else {
        snList.add(sn)
      }
    }
    if (node.children && node.children.length > 0) {
      traverseAllSn(node.children, snList, duplicateList)
    }
  })
  return duplicateList
}

const handleToggleNoRepeat = (e) => {
  if (e.target.checked) {
    const repeatSnArr = traverseAllSn(bomBusinessTree.value)
    if (repeatSnArr.length > 0) {
      alert(`检测到产品编号存在全局重复：\n${[...new Set(repeatSnArr)].join('、')}\n\n请修改重复编号后，再开启全局不重复模式`)
      noRepeatName.value = false
    } else {
      noRepeatName.value = true
    }
  } else {
    noRepeatName.value = false
  }
}

const bomItemCount = computed(()=>{
  let count = 0
  function loop(list){
    list.forEach(item=>{
      count++
      if(item.children && item.children.length > 0){
        loop(item.children)
      }
    })
  }
  loop(bomBusinessTree.value)
  return count
})

const BomTotalCost = computed(()=>{
  let sum = 0
  function loop(list){
    list.forEach(item=>{
      sum += Number(item.subtotal ?? 0)
      if(item.children && item.children.length > 0){
        loop(item.children)
      }
    })
  }
  loop(bomBusinessTree.value)
  sum = Number(sum.toFixed(2))
  return sum
})

provide('uiStateMap',uiStateMap)
provide('initNodeUiState',initNodeUiState)
provide('cleanNodeUiState',cleanNodeUiState)
</script>

<style scoped>
.bom-container {
  width:98%;
  margin: 10px auto;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  padding: 6px;
}
.bom-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 6px;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 6px;
  margin-bottom: 15px;
}
.config-toggle {
  display: flex;
  align-items: center;
  font-size: 14px;
  color: #666;
  cursor: pointer;
}
.button-group button {
  font-size: 14px;
  cursor: pointer;
}
</style>
