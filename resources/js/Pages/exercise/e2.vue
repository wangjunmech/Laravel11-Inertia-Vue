<template>
  <div class="import-container">
    <div>
        <h1>递归树数据导入与预览</h1>
        <hr class="p-2">
    </div>

    <div class="upload-zone">
      <div 
        class="drop-box"
        @dragover.prevent
        @drop.prevent="handleFileDrop"
        @click="triggerFileInput"
      >
        <p>📥 点击选择 JSON 或 TXT 文件，或将文件拖拽到此处</p>
        <input 
          type="file" 
          ref="fileInputRef" 
          accept=".json,.txt" 
          style="display: none" 
          @change="handleFileChange" 
        />
      </div>

      <div class="textarea-box">
        <textarea
          v-model="rawJsonText"
          placeholder="或者在这里直接粘贴导出的 JSON 数据字符串..."
          rows="6"
        ></textarea>
        <button @click="importFromText">确认从文本导入</button>
      </div>
    </div>

    <div v-if="errorMessage" class="error-msg">
      ⚠️ 导入失败：{{ errorMessage }}
    </div>

    <div v-if="importedTreeData.length > 0" class="preview-zone">
      <h3>📊 导入数据实时预览：</h3>
      <div class="tree-card">
        <TreeNode 
          :tree-data="importedTreeData" 
          :level="0"
          :showIdLevel="true"
          :noRepeatName="true"
          @update:tree-data="handleTreeUpdate"
        />
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
// 💡 记得引入你之前的递归树组件，路径根据你项目的实际情况修改
import TreeNode from "../../Components/TreeNode.vue"; // 请根据实际路径调整
const rawJsonText = ref('')
const errorMessage = ref('')
const importedTreeData = ref([])
const fileInputRef = ref(null)

// 触发隐藏的文件选择框
const triggerFileInput = () => {
  if (fileInputRef.value) fileInputRef.value.click()
}

// 处理文件选择上传
const handleFileChange = (event) => {
  const file = event.target.files[0]
  if (file) readFile(file)
}

// 处理文件拖拽上传
const handleFileDrop = (event) => {
  const file = event.dataTransfer.files[0]
  if (file) readFile(file)
}

// 核心读取文件逻辑
const readFile = (file) => {
    // //仅支持JSON文件，兼容部分用户可能误上传.txt后缀的JSON文件
    // if (file.type !== 'application/json' && !file.name.endsWith('.json')) {
    //     errorMessage.value = '仅支持导入 .json 格式的文件'
    //     return
    // }

    // 同时支持 json 和 txt 的 mime 类型及后缀名检测
    const isJson = file.type === 'application/json' || file.name.endsWith('.json')
    const isTxt = file.type === 'text/plain' || file.name.endsWith('.txt')

    if (!isJson && !isTxt) {
        errorMessage.value = '仅支持导入 .json 或 .txt 格式的文件'
        return
    }
    
    const reader = new FileReader()
    reader.onload = (e) => {
        const text = e.target.result
        rawJsonText.value = text
        validateAndParse(text)
    }
    reader.onerror = () => {
        errorMessage.value = '文件读取出错'
    }
    reader.readAsText(file)
}

// 从文本框输入导入
const importFromText = () => {
  validateAndParse(rawJsonText.value)
}

// 核心校验与解析工具函数
const validateAndParse = (jsonString) => {
  errorMessage.value = ''
  if (!jsonString || !jsonString.trim()) {
    errorMessage.value = '数据不能为空！'
    return
  }

  try {
    const parsedData = JSON.parse(jsonString)
    
    // 基础的递归树数据规范校验
    if (!Array.isArray(parsedData)) {
      errorMessage.value = '合法的树数据根节点必须是一个数组结构 [ ... ]'
      return
    }

    if (parsedData.length > 0) {
      const hasId = 'id' in parsedData[0]
      const hasLabel = 'label' in parsedData[0]
      if (!hasId || !hasLabel) {
        errorMessage.value = '数据格式不匹配，未检测到树节点的必要字段 (id, label)'
        return
      }
    }

    // 校验通过，塞给树响应式变量，触发组件渲染
    importedTreeData.value = parsedData
    alert('🎉 数据导入成功！已成功加载节点树。')
  } catch (err) {
    errorMessage.value = `JSON 格式解析错误，请检查字符串是否完整。(${err.message})`
  }
}

// 响应预览树内部的拖拽、修改、添加或删除
const handleTreeUpdate = (newTreeData) => {
  importedTreeData.value = newTreeData
  // 同时同步更新文本框里的内容，让数据保持最新状态
  rawJsonText.value = JSON.stringify(newTreeData, null, 2)
}
</script>
<style scoped>
.import-container { max-width: 800px; margin: 20px auto; padding: 20px; background: #f9f9f9; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
.upload-zone { display: flex; gap: 20px; flex-wrap: wrap; }     
.drop-box { flex: 1; border: 2px dashed #ccc; border-radius: 8px; padding: 20px; text-align: center; cursor: pointer; background-color: #f3c4c4; transition: background-color 0.3s ease; }
.drop-box:hover { background-color: #f0f0f0; }  
.textarea-box { flex: 1;display: flex; flex-direction: column; gap: 10px; }
.textarea-box textarea { flex: 1; padding: 10px; border: 1px solid #ccc; border-radius: 4px; font-family: monospace; }
.textarea-box button { align-self: flex-end; padding: 8px 16px; background-color: #007BFF; color: white; border: none; border-radius: 4px; cursor: pointer; transition: background-color 0.3s ease; }
.textarea-box button:hover { background-color: #0056b3; }
.error-msg { margin-top: 20px; color: #d9534f; font-weight: bold; }
.preview-zone { margin-top: 30px; }
.tree-card { padding: 20px; background-color: #fff; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }
</style>