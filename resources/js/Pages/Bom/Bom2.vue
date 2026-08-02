<template>
  <div class="bom-container">
    <div class="bom-header">
      <h2 class="text-2xl font-bold">BOM创建器⏱</h2>

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
      </div>
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
          <div class="flex bg-blue-400 m-2 px-5 rounded-lg">序号</div>
          <div class="flex bg-green-400 m-2 px-5 rounded-lg">产品编号</div>
          <div class="flex bg-green-400 m-2 px-5 rounded-lg">品名英</div>
          <div class="flex bg-green-400 m-2 px-5 rounded-lg">品名中</div>
          <div class="flex bg-red-200 m-2 px-5 rounded-lg">用量</div>
          <div class="flex bg-red-400 m-2 px-5 rounded-lg">单位</div>
          <div class="flex bg-green-400 m-2 px-5 rounded-lg">损耗率</div>
          <div class="flex bg-green-400 m-2 px-5 rounded-lg">采购单价</div>
          <div class="flex bg-green-400 m-2 px-5 rounded-lg">小计成本</div>
        </div>

        <!-- 右侧操作区 -->
        <div class="w-[190px] flex-shrink-0 h-full">
          <div class="h-full flex items-center justify-center">右侧编辑操作区</div>
        </div>
      </div>
    </div>

    <!-- 向子组件传递两个参数 短横线写法完全规范 -->
    <BomNode 
      :bom-data="bomData"
      @update:bom-data="updatebomData"
      :show-id-level="showIdLevel"
      :no-repeat-name="noRepeatName"
      :name-prefix="namePrefix"
      :digit-length="digitLength"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import BomNode from "./BomNode.vue";

// 1. 新增：前缀、数字位数响应式变量（必须声明）
const namePrefix = ref('code')   // 默认前缀
const digitLength = ref(4)       // 默认4位数字

const showIdLevel = ref(true)
const noRepeatName = ref(true) // 默认开启全局不重复

// 数字框失焦校验：强制锁定1~8
const checkDigitRange = () => {
  if (digitLength.value < 1) digitLength.value = 1
  if (digitLength.value > 8) digitLength.value = 8
}

// 树节点基础数据
const bomData = ref(
[{"id":0,"label":"root 根节点","children":[{"id":4,"label":"code0004","children":[{"id":8,"label":"code0008","children":[],"isOpen":false,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover000001","nameCn":"扫地机支架","quantity":6,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":2,"editField":null}],"isOpen":true,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover test","nameCn":"扫地机外下壳","quantity":1,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":1,"editField":null},{"id":1,"label":"code0001","children":[{"id":6,"label":"code0006","children":[{"id":7,"label":"code0007","children":[],"isOpen":false,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover top","nameCn":"扫地机外装饰条","quantity":4,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":5,"editField":null}],"isOpen":true,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover front","nameCn":"扫地机外电池盖","quantity":3,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":4,"editField":null}],"isOpen":true,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover","nameCn":"扫地机电池盒上","quantity":2,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":3,"editField":null},{"id":3,"label":"code0003","children":[],"isOpen":true,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover","nameCn":"电源线","quantity":5,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":6,"editField":null},{"id":2,"label":"code0002","children":[],"isOpen":false,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover","nameCn":"纸箱","quantity":7,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":7,"editField":null},{"id":5,"label":"code0005","children":[],"isOpen":false,"isEdit":false,"isNew":false,"productSn":"x000001","nameEn":"labelcover","nameCn":"彩盒","quantity":8,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false,"sn":8,"editField":null},{"id":9,"label":"code0009","children":[],"isOpen":false,"isEdit":false,"isNew":false,"editField":null,"sn":9,"productSn":"x000001","nameEn":"labelcover","nameCn":"标签外箱","quantity":1,"unit":"pcs","wasteRate":0,"price":0,"subtotal":0,"bgactive":false}],"productSn":"x000001","nameEn":"labelcover","nameCn":"扫地机外上壳","quantity":1,"unit":"pcs","wasteRate":0.2,"price":0,"subtotal":0,"isOpen":true,"isEdit":false,"isNew":false,"bgactive":false,"sn":0,"editField":null}])

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
</script>

<style scoped>
.bom-container {
  width:90%;
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