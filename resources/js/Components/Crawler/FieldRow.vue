<script setup>
// field 是父组件 reactive 数组里的对象，这里直接 v-model 修改它
defineProps({
    field: { type: Object, required: true },
    canRemove: { type: Boolean, default: true },
})
defineEmits(['remove'])

const input = 'rounded-md border-gray-300 text-sm shadow-sm focus:border-indigo-500 focus:ring-indigo-500'
</script>

<template>
    <div class="flex flex-wrap items-center gap-2">
        <input v-model="field.name" :class="[input, 'w-36']" placeholder="字段名，如 title" />
        <input v-model="field.selector" :class="[input, 'min-w-[12rem] flex-1']" placeholder="CSS 选择器，如 h1.title" />
        <select v-model="field.type" :class="[input, 'w-28']">
            <option value="text">文本</option>
            <option value="html">HTML</option>
            <option value="attr">属性</option>
        </select>
        <input v-if="field.type === 'attr'" v-model="field.attr" :class="[input, 'w-28']" placeholder="如 href" />
        <label class="flex items-center gap-1 text-sm text-gray-600">
            <input v-model="field.multiple" type="checkbox" class="rounded border-gray-300" />
            多个
        </label>
        <button
            type="button"
            class="rounded px-2 py-1 text-sm text-red-600 hover:bg-red-50 disabled:opacity-30"
            :disabled="!canRemove"
            @click="$emit('remove')"
        >
            删除
        </button>
    </div>
</template>
