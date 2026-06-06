<template>
  <div class="tree-container">
    <div class="tree-header">
      <h2 class="text-2xl font-bold">递归树组件示例</h2>
      <label class="config-toggle">
        <input type="checkbox" v-model="showIdLevel" class="mr-2">
        显示ID层级 ({{ showIdLevel }})
      </label>
        <label class="config-toggle">
        <input type="checkbox" v-model="noRepeatName" class="mr-2">
        防止重复名称 ({{ noRepeatName }})
      </label>
      <div class="button-group">
        <button 
          @click="console.log(JSON.stringify(treeData))" 
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

    <TreeNode 
      :tree-data="treeData"
      @update:tree-data="updateTreeData"
      :showIdLevel="showIdLevel"
      :noRepeatName="noRepeatName"
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TreeNode from "../../Components/TreeNode.vue"; // 请根据实际路径调整

const showIdLevel = ref(true)
const noRepeatName = ref(true)

// 树节点基础数据
const treeData = ref([
  {
    id: 0, // root 固定 ID
    label: 'root 根节点',
    children: [],
    isOpen: true,
    isEdit: false,
    isNew: false
  }
])

// 接收子组件传递回来的新数据，保持单向数据流
const updateTreeData = (newData) => {
  treeData.value = newData
}

// 🌟 核心新增：导出并下载 TXT 文件的逻辑
const exportToTxt = () => {
  try {
    // 1. 将数据转换为格式化好的 JSON 字符串 (null, 2 表示使用 2 个空格缩进，让文本更好看)
    const dataStr = JSON.stringify(treeData.value, null, 2)
    
    // 2. 创建 Blob 对象，指定类型为文本/明文
    const blob = new Blob([dataStr], { type: 'text/plain;charset=utf-8' })
    
    // 3. 创建一个虚拟的 <a> 标签
    const downloadLink = document.createElement('a')
    
    // 4. 为虚拟标签生成并绑定一个指向该 Blob 对象的临时 URL 内存地址
    downloadLink.href = URL.createObjectURL(blob)
    
    // 5. 设置下载下来的文件名（可以加入当前时间戳防重名，这里先用固定名）
    downloadLink.download = `tree-data-${new Date().getTime()}.txt`
    
    // 6. 触发该虚拟链接的点击事件，激活浏览器下载行为
    downloadLink.click()
    
    // 7. 释放内存中刚刚生成的临时 URL（极好的习惯，防止大量导出时内存泄漏）
    URL.revokeObjectURL(downloadLink.href)
  } catch (error) {
    console.error('导出失败：', error)
    alert('导出文件失败，请检查控制台错误信息。')
  }
}
</script>

<style scoped>
.tree-container {
  width:800px;
  margin: 30px auto;  
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #fff;
  box-shadow: 0 4px 12px rgba(0, 0, 0, 0.05);
  padding: 15px;
}
.tree-header {
  display: flex;
  align-items: center;
  justify-content: space-between;
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
/* 增加按钮样式过渡 */
.button-group button {
  font-size: 14px;
  cursor: pointer;
}
</style>