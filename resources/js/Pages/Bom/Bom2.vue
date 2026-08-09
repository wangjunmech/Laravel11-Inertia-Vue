<template>
  <div>类别筛选器，单位选择，保存，导入</div>
  <div class="bom-container">
    <div class="bom-header">
      <h2 class="text-2xl font-bold">结构化BOM创建器⏱</h2>
      <!-- 搜索输入框 -->
        <input
          title="搜索BOM"
          v-model="searchKeyword"
          @dblclick="searchKeyword = ''"
          @keyup.enter.prevent="searchKeyword = ''"
          @keyup.esc.prevent="searchKeyword = ''"
          placeholder="🔍搜索编号，名称"
          class="rounded-lg px-1 py-2 max-w-[220px] ml-auto border border-gray-300"
        >

      <!-- 复选框绑定修改：不再直接v-model，改用change事件手动控制勾选状态 -->
      <label class="config-toggle">
        <input 
          type="checkbox" 
          :checked="noRepeatName" 
          @change="handleToggleNoRepeat"
          class="mr-2"
        >
        BOM产品编号全局无重复({{ noRepeatName?'是':'否' }})
      </label>
      <label class="config-toggle">
        <input type="checkbox" v-model="showIdLevel" class="mr-2">
        显示ID层级 ({{ showIdLevel }})
      </label>
      <div class="button-group">
        <button 
          @click="console.log(JSON.stringify(bomData))" 
          class="ml-4 px-3 py-1 bg-blue-500 hover:bg-green-600 text-white rounded transition-colors"
        >
          exportConsole
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
    <!-- BOM信息汇总区 -->
     <div class="flex bg-gray-400 rounded pl-2">
        <div>BOM项目条数:</div>
        <div>BOM总计成本:</div>
        <div>BOM创建时间</div>
        <div>BOM版本号</div>

     </div>

    <!-- 表头字段显示 -->
    <div>
      <div class="flex items-center w-full ">
        <!-- 左侧树形名称区 -->
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
            <!-- 加.number修饰符 + step=1只能输入整数 + 失焦校验范围 -->
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

        <!-- 中间8列表头 -->
        <div class="flex">
          <div class=" bg-blue-400 m-1 px-4 rounded-lg">序号</div>
          <div class=" bg-yellow-400 m-1 px-4 rounded-lg">类别</div>
          <div class="flex bg-green-100 m-1 px-4 rounded-lg w-[220px] justify-center border">产品编号</div>
          <div class="flex bg-green-400 m-1 px-4 rounded-lg w-[110px]">品名英</div>
          <div class="flex bg-green-400 m-1 px-4 rounded-lg w-[110px]">品名中</div>
          <div class="flex bg-red-200 m-1 px-4 rounded-lg">用量</div>
          <div class="flex bg-red-400 m-1 px-4 rounded-lg">单位</div>
          <div class="flex bg-green-400 m-1 px-4 rounded-lg">损耗率</div>
          <div class="flex bg-green-400 m-1 px-4 rounded-lg">采购单价</div>
          <div class="flex bg-gray-200 m-1 px-4 rounded-lg">小计成本</div>
        </div>

        <!-- 右侧操作区,可修改[]中的宽度 -->
        <div class="w-[140px] flex-shrink-0 h-full">
          <div class="h-full flex items-center justify-center ">右侧编辑操作区</div>
        </div>
      </div>
    </div>
    <hr class="h-2 border-2 bg-gray-200">

    <!-- 向子组件传递两个参数 短横线写法完全规范 -->
    <BomNode 
      :bom-data="currentBomData"
      @update:bom-data="updatebomData"
      :show-id-level="showIdLevel"
      :no-repeat-name="noRepeatName"
      :name-prefix="namePrefix"
      :digit-length="digitLength"
      :search-keyword="searchKeyword"
    />
        <!-- 底部总成本汇总 -->
    <div class="mt-4 bg-slate-200 p-4 rounded shadow flex justify-end">
      <span>BOM项目总条数：{{bomItemCount-1}}
      </span>
      <span class="w-6"></span>

      <span>BOM物料总成本合计：
        <b class="text-red-600  ml-2">{{ BomTotalCost }} 元</b>
      </span>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onBeforeUnmount,computed, h  } from 'vue';
import BomNode from "./BomNode.vue";
import * as XLSX from 'xlsx';
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';


// 目前过滤条件只匹配 nameEn，需求一般是：产品编号 (label)、品名英文、品名中文任意匹配；
// 搜索后树形结构容易断裂，需要保留完整父级链路（不能只过滤零散节点，否则树形缩进直接错乱）；
// 没有清空搜索时恢复原始数据；
// 输入框没有绑定回车触发、实时防抖（可选优化）。
const searchKeyword = ref('')


/**
 * 树形搜索过滤，保留完整父链
 * @param {Array} tree 原始bom树
 * @param {String} keyword 搜索关键词
 * @returns 过滤后的树形数组
 */
const filterBomTree = (tree, keyword) => {
  if (!keyword.trim()) return [...tree]
  const kw = keyword.toLowerCase().trim()
  const result = []
  for (const node of tree) {
    const isMatch =
      node.label?.toLowerCase().includes(kw) ||
      node.nameEn?.toLowerCase().includes(kw) ||
      node.nameCn?.toLowerCase().includes(kw)

    const childMatched = filterBomTree(node.children || [], keyword)

    if (isMatch || childMatched.length > 0) {
      const newNode = {...node}
      newNode.children = childMatched
      newNode.isOpen = true
      // ✅新增标记：只有本身匹配关键词才打上true
      newNode._isKeywordMatch = isMatch
      result.push(newNode)
    }
  }
  return result
}

// 计算属性：对外提供过滤后的BOM数据
const currentBomData = computed(() => {
  // const tree = filterBomTree(bomData.value, searchKeyword.value)
  // // 有搜索词，移除root节点
  // if(searchKeyword.value.trim()){
  //   return tree.filter(n => n.id !== 0)
  // }  
  return filterBomTree(bomData.value, searchKeyword.value)
})


const activeMenuNodeId = ref(null)
// provide('activeMenuNodeId', activeMenuNodeId)
/**
 * 导出BOM为Excel第1步：扁平化BOM树形数据
 * 递归遍历BOM树，扁平化输出每行数据
 * @param {Array} nodes 节点数组
 * @param {Number} level 当前层级
 * @param {Array} list 收集结果数组
 */
const flattenBomTree = (nodes, level = 0, list = []) => {
  nodes.forEach(node => {
    // 层级缩进空格：根节点无空格，子节点逐级加空格区分树形
    const indentSpace = '　'.repeat(level);
    list.push({
      level: level,
      id: node.id,
      pid: node.parentId ?? '无',
      sn: node.sn,
      cateName: node.cateName,
      name: indentSpace + node.label, // 名称自带缩进体现树形结构
      nameEn: node.nameEn,
      nameCn: node.nameCn,
      quantity: node.quantity,
      unit: node.unit,
      wasteRate: node.wasteRate + '%',
      price: node.price,
      subtotal: node.subtotal.toFixed(2)
    })

    // 递归遍历子节点，层级+1
    if (node.children && node.children.length > 0) {
      flattenBomTree(node.children, level + 1, list)
    }
  })
  return list
}

/**
* 导出BOM为Excel第2步：导出操作
 */
const handleExport = async () => {
  try {
    // 1. 将树形数据扁平化
    const bomList = flattenBomTree(bomData.value)
    if (bomList.length === 0) {
      alert('暂无BOM数据可导出');
      return;
    }

    // 2. 创建工作簿 & 工作表
    const workbook = new ExcelJS.Workbook();
    const worksheet = workbook.addWorksheet('BOM物料清单');

    // 3. 定义表头列
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

    // 4. 设置表头样式：加粗、居中、背景浅蓝
    const headerRow = worksheet.getRow(1);
    headerRow.font = { bold: true };
    headerRow.alignment = { vertical: 'middle', horizontal: 'center' };
    headerRow.fill = {
      type: 'pattern',
      pattern: 'solid',
      fgColor: { argb: 'E6F7FF' }
    };

    // 5. 填充所有BOM行数据
    worksheet.addRows(bomList);
        // 数字列右对齐设置，可选
            worksheet.getColumn('quantity').alignment = { horizontal: 'right' }
            worksheet.getColumn('price').alignment = { horizontal: 'right' }
            worksheet.getColumn('subtotal').alignment = { horizontal: 'right' }
            worksheet.getColumn('level').alignment = { horizontal: 'center' }
            worksheet.getColumn('id').alignment = { horizontal: 'center' }
            worksheet.getColumn('pid').alignment = { horizontal: 'center' }
            worksheet.getColumn('sn').alignment = { horizontal: 'center' }
            

    // 奇数行隔行变色，可选
          worksheet.eachRow((row, rowNum) => {
            row.alignment = { vertical: 'middle' };
            // 跳过表头，数据行隔行底色
            if (rowNum > 1 && rowNum % 2 === 0) {
              row.fill = {
                type: 'pattern',
                pattern: 'solid',
                fgColor: { argb: 'aef777' }
              }
            }
          });


    // 6. 全局单元格垂直居中
    worksheet.eachRow((row) => {
      row.alignment = { vertical: 'middle' };
    });

    // 7. 生成二进制文件并下载
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




// 1. 新增：前缀、数字位数响应式变量（必须声明）
const namePrefix = ref('code')   // 默认前缀
const digitLength = ref(4)       // 默认4位数字

const showIdLevel = ref(false)
const noRepeatName = ref(true) // 默认开启全局不重复

// 数字框失焦校验：强制锁定1~8
const checkDigitRange = () => {
  if (digitLength.value < 1) digitLength.value = 1
  if (digitLength.value > 8) digitLength.value = 8
}

// 树节点基础数据
const bomData = ref(
[{"id":0,"label":"整机总成编号","children":[{"id":4,"label":"code0004","children":[{"id":8,"label":"code0008","children":[],"isOpen":false,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover000001","nameCn":"扫地机支架","quantity":6,"unit":"g","wasteRate":1,"price":8,"subtotal":48.48,"bgactive":false,"sn":2,"editField":null,"parentId":4,"unitPopupOpen":false}],"isOpen":true,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labeltr test","nameCn":"扫地机外下壳","quantity":1,"unit":"pcs","wasteRate":1,"price":12,"subtotal":12.12,"bgactive":false,"sn":1,"editField":null,"parentId":0,"unitPopupOpen":false},{"id":1,"label":"code0001","children":[],"isOpen":true,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"topcover","nameCn":"扫地机电池盒上","quantity":2,"unit":"ass","wasteRate":1,"price":5,"subtotal":10.1,"bgactive":false,"sn":3,"editField":null,"parentId":0,"unitPopupOpen":false}],"productSn":"x000001","nameEn":"Full Machine","nameCn":"整机总成","quantity":1,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"isOpen":true,"isEdit":false,"isNew":false,"bgactive":false,"sn":0,"editField":null,"parentId":null,"unitPopupOpen":false}])

// 接收子组件传递回来的新数据
const updatebomData = (newData) => {
  bomData.value = newData
}

// 导出 TXT 文件
const exportToTxt = () => {
  try {
    const dataStr = JSON.stringify(bomData.value, null, 2)
    const blob = new Blob([dataStr], { type: 'text/plain;charset=utf-8' })
    const downloadLink = document.createElement('a')
    downloadLink.href = URL.createObjectURL(blob)
    downloadLink.download = `bom-data-${new Date().getTime()}.txt`
    downloadLink.click()
    URL.revokeObjectURL(downloadLink.href)
  } catch (error) {
    console.error('导出失败：', error)
    alert('导出文件失败，请检查控制台错误信息。')
  }
}

// ===================== 新增核心逻辑 =====================
/**
 * 递归遍历整棵BOM树，收集所有 productSn 产品编号
 * @param {Array} nodes 节点数组
 * @param {Set} snList 存储已出现编号
 * @param {Array} duplicateList 存储重复编号
 */
const traverseAllSn = (nodes, snList = new Set(), duplicateList = []) => {
  nodes.forEach(node => {
    const sn = node.label?.trim()
    // 非空才校验
    if (sn) {
      if (snList.has(sn)) {
        // 重复了，推入重复列表
        duplicateList.push(sn)
      } else {
        snList.add(sn)
      }
    }
    // 递归遍历子节点
    if (node.children && node.children.length > 0) {
      traverseAllSn(node.children, snList, duplicateList)
    }
  })
  return duplicateList
}

/**
 * 复选框切换事件
 * @param {Event} e 原生change事件
 */
const handleToggleNoRepeat = (e) => {
  // 想要勾选开启防重复
  if (e.target.checked) {
    // 校验全局编号是否重复
    const repeatSnArr = traverseAllSn(bomData.value)
    if (repeatSnArr.length > 0) {
      // 存在重复：取消勾选 + 弹窗警告
      alert(`检测到产品编号存在全局重复：\n${[...new Set(repeatSnArr)].join('、')}\n\n请修改重复编号后，再开启全局不重复模式`)
      noRepeatName.value = false
    } else {
      // 无重复：正常开启
      noRepeatName.value = true
    }
  } else {
    // 取消勾选：直接关闭，允许重复
    noRepeatName.value = false
  }
}
// BOM项目条数
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
  loop(bomData.value)
  return count
})
// BOM总成本，一次性递归遍历全部节点累加小计成本
const BomTotalCost = computed(()=>{
  let sum = 0
  // 递归遍历函数
  function loop(list){
    list.forEach(item=>{
      sum += Number(item.subtotal ?? 0)
      if(item.children && item.children.length > 0){
        loop(item.children)
      }
    })
  }
  loop(bomData.value)
  // sum = sum.toFixed(2)//四舍五入保留两位小数,文本类型
  sum = Number(sum.toFixed(2))//数字类型
  return sum
})

</script>

<style scoped>
.bom-container {
  width:98%;
  margin: 30px auto;  
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  padding: 15px;
}
.bom-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
  flex-wrap: wrap;
  gap: 16px;
  border-bottom: 1px solid #f0f0f0;
  padding-bottom: 10px;
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