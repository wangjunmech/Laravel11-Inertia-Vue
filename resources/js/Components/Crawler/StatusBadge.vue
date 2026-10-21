<script setup>
import { computed } from 'vue'

const props = defineProps({ status: { type: String, required: true } })

const map = {
    pending: ['等待中', 'bg-gray-100 text-gray-700'],
    running: ['抓取中', 'bg-blue-100 text-blue-700'],
    done: ['已完成', 'bg-green-100 text-green-700'],
    failed: ['失败', 'bg-red-100 text-red-700'],
    cancelled: ['已取消', 'bg-amber-100 text-amber-800'],
}

const info = computed(() => map[props.status] ?? [props.status, 'bg-gray-100 text-gray-700'])
</script>

<template>
    <span :class="['inline-block rounded-full px-2.5 py-0.5 text-xs font-medium', info[1]]">{{ info[0] }}</span>
</template>
