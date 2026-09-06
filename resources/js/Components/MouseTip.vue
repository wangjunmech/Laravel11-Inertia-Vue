<template>
  <!--暂时不用 -->
  <span
    v-show="x !== null && y !== null"
    class="mouse-tip"
    :style="{ left: `${x}px`, top: `${y}px` }"
  >
    <slot></slot>
  </span>
</template>

<script setup>
defineProps({
  x: { type: Number, default: 0 },
  y: { type: Number, default: 0 },
})
</script>

<style scoped>
.mouse-tip {
  position: fixed;
  z-index: 99999;
  padding: 3px 8px;
  background: #775e04;
  color: #fff;
  font-size: 13px;
  border-radius: 4px;
  pointer-events: none;
  white-space: nowrap;
  user-select: none;
}
</style>
