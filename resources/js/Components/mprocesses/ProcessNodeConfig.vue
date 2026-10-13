<template>
  <el-drawer
    :model-value="open"
    @update:model-value="$emit('update:open', $event)"
    title="工艺参数配置"
    direction="rtl"
    size="400px"
  >
    <el-form v-if="node" label-width="100px">
      <el-form-item label="工序名称">
        <el-input v-model="localNode.label" />
      </el-form-item>
      <el-form-item label="工艺类型">
        <el-input v-model="localNode.type" disabled />
      </el-form-item>
      <el-form-item label="参数">
        <pre>{{ JSON.stringify(localNode.params, null, 2) }}</pre>
      </el-form-item>
    </el-form>
    <template #footer>
      <el-button @click="$emit('update:open', false)">取消</el-button>
      <el-button type="primary" @click="handleConfirm">保存</el-button>
    </template>
  </el-drawer>
</template>

<script setup>
import { ref, watch } from 'vue'
const props = defineProps({
  open: Boolean,
  node: Object
})
const emit = defineEmits(['update:open', 'update-node'])
const localNode = ref(null)

watch(() => props.node, (val) => {
  if(val) localNode.value = JSON.parse(JSON.stringify(val))
}, { immediate:true })

const handleConfirm = () => {
  emit('update-node', localNode.value)
  emit('update:open', false)
}
</script>
