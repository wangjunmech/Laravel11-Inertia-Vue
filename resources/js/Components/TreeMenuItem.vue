<!-- TreeMenuItem.vue -->
<template>
  <div 
  :class="{ active: isActive }"
  class="menu-item-wrap bg-red-100 border border-blue-500 rounded-md p-1 relative">
    <div 
      class="menu-item flex items-center justify-between overflow-hidden"
      
      @mouseenter="$emit('mouseenter')"
      @click="$emit('select')"
    >
      <input
        v-if="isEditing"
        ref="inputRef"
        v-model="editValue"
        class="edit-input"
        @click.stop
        @dblclick.stop
        @blur="confirmEdit"
        @keyup.enter="confirmEdit"
        @keyup.esc="cancelEdit"
      />
      <span 
        v-else 
        class="truncate" 
        @dblclick.stop="$emit('startEdit', item.id)"
      >
        {{ item.area }}
      </span>

      <span v-if="hasChild && !isEditing" class="arrow flex-shrink-0">▶︎</span>
    </div>

    <button 
      v-if="isActive"
      class="btn-add-child w-5 h-5 rounded-full border border-red-500 text-red-500 flex items-center justify-center text-xs bg-white hover:bg-red-50 transition-colors absolute right-0 top-1/2 -translate-y-1/2 translate-x-1/2 z-10 flex-shrink-0" 
      @click.stop="$emit('add-child')"
    >
      +
    </button>

    <button 
    v-if="isActive"
      class="btn-add-sibling w-5 h-5 rounded-full border border-green-500 text-green-500 flex items-center justify-center text-xs bg-white hover:bg-green-50 transition-colors absolute bottom-0 left-1/2 -translate-x-1/2 translate-y-1/2 z-10 flex-shrink-0" 
      @click.stop="$emit('add-sibling')"
    >
      +
    </button>
  </div>
</template>

<script setup>
import { ref, watch, nextTick } from 'vue'

const props = defineProps({
  item: Object,
  isActive: Boolean,
  hasChild: Boolean,
  isEditing: Boolean
})
const emit = defineEmits([
  'mouseenter', 'select', 'addChild', 'addSibling',
  'rename', 'cancelEdit', 'startEdit'   // 新增 startEdit
])

const inputRef = ref(null)
const editValue = ref(props.item.area)

watch(() => props.isEditing, (val) => {
  if (val) {
    editValue.value = props.item.area
    nextTick(() => {
      inputRef.value?.focus()
      inputRef.value?.select()
    })
  }
},{ immediate: true }
)

const confirmEdit = () => {
  if (!props.isEditing) return
  const value = editValue.value.trim()
  emit('rename', { id: props.item.id, value: value || props.item.area })
}

const cancelEdit = () => {
  editValue.value = props.item.area
  emit('cancelEdit', props.item.id)
  inputRef.value?.blur()
}
</script>


<style scoped>
.edit-input {
  width: 100%;
  border: 1px solid #3b82f6;
  border-radius: 4px;
  padding: 0 4px;
  font-size: inherit;
  font-family: inherit;
  outline: none;
  background: #fff;
}
.menu-item-wrap.active
 {
  background-color: #bbf7d0; /* 和hover的lime‑200保持一致，可自行改色 */
}
</style>