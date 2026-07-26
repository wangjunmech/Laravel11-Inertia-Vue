<template>
  <div class="tree-container">
    <div class="tree-header">
      <h2 class="text-2xl font-bold">表格选择组件1</h2>
    <div style="background: lightgreen; padding: 10px;">
    <a href="https://www.emojiall.com/">  ❌</a>父组件中实时显示选中的列表{{ JSON.stringify(selectedItems.map(item => item.id)) }}
    </div>

      <div class="button-group">
        <button 
          @click="console.log(JSON.stringify(selectedItems))" 
          class="ml-4 px-3 py-1 bg-blue-500 hover:bg-green-600 text-white rounded transition-colors"
        >
          Export Selected items to Console
        </button>
        <button 
          @click="exportToTxt" 
          class="ml-4 px-3 py-1 bg-blue-500 hover:bg-green-600 text-white rounded transition-colors"
        >
          Export Selected items to local txt file📄
        </button>
      </div>
    </div>

    <TableWithSelection 
      :data="tabData"
      v-model="selectedItems"
      @update:table-data="updateTabData"   
    />
  </div>
</template>

<script setup>
import { ref } from 'vue'
import TableWithSelection from "../../Components/TableWithSelection.vue"; // 请根据实际路径调整

const selectedItems = ref([])
// 表格基础数据
const tabData = ref([
 [
{
"id": 1,
"word": "A1",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 2,
"word": "A2",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 3,
"word": "A3",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 4,
"word": "A4",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 5,
"word": "A5",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 6,
"word": "A6",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 7,
"word": "A",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 8,
"word": "A7",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 9,
"word": "A8",
"begin": "A",
"end": "A",
"length": 1,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 10,
"word": "A9-",
"begin": "A",
"end": "-",
"length": 2,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 11,
"word": "A 1",
"begin": "A",
"end": "1",
"length": 3,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 12,
"word": "Aam",
"begin": "A",
"end": "m",
"length": 3,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 13,
"word": "Aard-vark",
"begin": "A",
"end": "k",
"length": 9,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 14,
"word": "Aard-wolf",
"begin": "A",
"end": "f",
"length": 9,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 15,
"word": "Aaronic",
"begin": "A",
"end": "c",
"length": 7,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 16,
"word": "Aaronical",
"begin": "A",
"end": "l",
"length": 9,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 17,
"word": "Aaron's rod",
"begin": "A",
"end": "d",
"length": 11,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
},
{
"id": 18,
"word": "Aaron's rod",
"begin": "A",
"end": "d",
"length": 11,
"palindrome": 0,
"time": "2018-06-24 16:45:24"
}
]
])

// 接收子组件传递回来的新数据，保持单向数据流
const updateTabData = (newData) => {
  tabData.value = newData
}

// 🌟导出并下载 TXT 文件的逻辑
const exportToTxt = () => {
  try {
    // 1. 将数据转换为格式化好的 JSON 字符串 (null, 2 表示使用 2 个空格缩进，让文本更好看)
    const dataStr = JSON.stringify(selectedItems.value, null, 2)
    
    // 2. 创建 Blob 对象，指定类型为文本/明文
    const blob = new Blob([dataStr], { type: 'text/plain;charset=utf-8' })  //指定类型为文本----导出txt文件      
    // const blob = new Blob([dataStr], { type: 'application/json;charset=utf-8' })//指定类型为json----导出json文件

    // 3. 创建一个虚拟的 <a> 标签
    const downloadLink = document.createElement('a')
    
    // 4. 为虚拟标签生成并绑定一个指向该 Blob 对象的临时 URL 内存地址
    downloadLink.href = URL.createObjectURL(blob)
    
    // 5. 设置下载下来的文件名（可以加入当前时间戳防重名，这里先用固定名）
    downloadLink.download = `table-data-${new Date().getTime()}.txt`
    
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

</style>