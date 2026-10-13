<template>
  <el-dialog v-model="visible" title="提示">
    <span>{{ message }}</span>
    <template #footer>
      <el-button @click="visible = false">取消</el-button>
      <el-button type="primary" @click="handleOk">确定</el-button>
    </template>
  </el-dialog>
</template>

<script setup>
import { ref } from 'vue'
const visible = ref(false)
const message = ref('')
let callback = null

defineExpose({
  show(msg, cb) {
    message.value = msg
    callback = cb
    visible.value = true
  }
})

const handleOk = () => {
  callback?.()
  visible.value = false
}
</script>
