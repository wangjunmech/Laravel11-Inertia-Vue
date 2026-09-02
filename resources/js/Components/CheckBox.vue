<template>
  <!-- 复选框组件 -->
  <div class="flex items-center gap-2 select-none">
    <input
      type="checkbox"
      :id="name"
      :checked="isChecked"
      @change="$emit('change', $event.target.checked)"
      class="rounded-md border border-slate-700 outline-0 text-indigo-500 ring-indigo-500 cursor-pointer"
    />
    <label :for="name" class="cursor-pointer"><slot /></label>
  </div>
</template>

<script setup>
defineProps(['name', 'isChecked']);
defineEmits(['change']);
</script>