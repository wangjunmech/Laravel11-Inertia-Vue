<template>
  <div class="shortcut-panel">
    <h3>快捷键设置面板</h3>
    <div class="shortcut-row">
      <span>请按下快捷键</span>
      <input
        ref="inputRef"
        v-model="currentShortcut"
        class="shortcut-input"
        @keydown.capture="handleKeyDown"
        readonly
        placeholder="按下组合键"
      >
      <span>指定给功能</span>
      <select v-model="selectFuncId" class="func-select">
        <option value="">请选择功能</option>
        <option
          v-for="item in letters"
          :key="item.id"
          :value="item.id"
        >
          {{ item.fn }}
        </option>
      </select>
      <button @click="saveShortcut">保存</button>
    </div>

    <div class="saved-list" v-if="shortcutList.length">
      <h4>已配置快捷键</h4>
      <div v-for="item in shortcutList" :key="item.funcId">
        {{ getFuncName(item.funcId) }}：{{ item.key }}
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'

const inputRef = ref(null)
const letters = ref([
  { id: 1, fn: '打开' },
  { id: 2, fn: '编程' },
  { id: 3, fn: '视图' },
  { id: 4, fn: '工具' },
  { id: 5, fn: '数据' }
])

const currentShortcut = ref('')
const selectFuncId = ref('')
const shortcutList = ref([])

// 全局按键监听（页面生效快捷键）
const globalKeyHandler = (e) => {
  // 遍历所有保存的快捷键配置
  for (const item of shortcutList.value) {
    // 拆分保存的快捷键字符串，判断当前按键是否匹配
    const keys = item.key.split(' + ')
    const hasCtrl = keys.includes('Ctrl')
    const hasShift = keys.includes('Shift')
    const hasAlt = keys.includes('Alt')
    const mainKey = keys.find(k => !['Ctrl','Shift','Alt'].includes(k))

    if (
      e.ctrlKey === hasCtrl &&
      e.shiftKey === hasShift &&
      e.altKey === hasAlt &&
      e.key.toUpperCase() === mainKey
    ) {
      // 关键：拦截浏览器原生行为，阻止关闭标签
      e.preventDefault()
      e.stopImmediatePropagation()
      // 执行对应功能逻辑
      runFunc(item.funcId)
      break
    }
  }
}

// 输入框捕获快捷键（设置时拦截关闭标签）
const handleKeyDown = (e) => {
  // 强制拦截所有浏览器原生快捷键行为
  e.preventDefault()
  e.stopImmediatePropagation()

  let keyArr = []
  if (e.ctrlKey) keyArr.push('Ctrl')
  if (e.altKey) keyArr.push('Alt')
  if (e.shiftKey) keyArr.push('Shift')
  const filterKeys = ['Control', 'Alt', 'Shift', 'Meta']
  if (!filterKeys.includes(e.key)) {
    keyArr.push(e.key.toUpperCase())
  }
  currentShortcut.value = keyArr.join(' + ')
}

// 执行绑定功能
const runFunc = (funcId) => {
  switch (Number(funcId)) {
    case 1:
      alert('执行【打开】功能')
      break
    case 2:
      alert('执行【编程】功能')
      break
    case 3:
      alert('执行【视图】功能')
      break
    case 4:
      alert('执行【工具】功能')
      break
    case 5:
      alert('执行【数据】功能')
      break
  }
}

// 保存快捷键
const saveShortcut = () => {
  if (!selectFuncId.value) return alert('请选择功能')
  if (!currentShortcut.value) return alert('请输入快捷键')
  
  const existIndex = shortcutList.value.findIndex(v => v.funcId === selectFuncId.value)
  if (existIndex > -1) {
    shortcutList.value[existIndex].key = currentShortcut.value
  } else {
    shortcutList.value.push({
      funcId: selectFuncId.value,
      key: currentShortcut.value
    })
  }
  currentShortcut.value = ''
  alert('保存成功')
}

const getFuncName = (id) => {
  const target = letters.value.find(v => v.id === id)
  return target?.fn || '未知功能'
}

// 挂载全局按键监听
onMounted(() => {
  window.addEventListener('keydown', globalKeyHandler, true)
})
// 销毁移除监听，防止内存泄漏
onUnmounted(() => {
  window.removeEventListener('keydown', globalKeyHandler, true)
})
</script>

<style scoped>
.shortcut-panel {
  padding: 20px;
}
.shortcut-row {
  display: flex;
  gap: 12px;
  align-items: center;
  margin: 16px 0;
}
.shortcut-input {
  width: 160px;
  padding: 6px 8px;
}
.func-select {
  padding: 6px 8px;
  min-width: 100px;
}
.saved-list {
  margin-top: 20px;
  padding-top: 10px;
  border-top: 1px solid #eee;
}
</style>