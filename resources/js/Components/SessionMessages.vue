<script setup>
import { watch, onUnmounted } from 'vue'
import { useAppStore } from '../stores/app.js'
const appStore = useAppStore()

const props = defineProps({
    status: String,
});

function hideSMsg() {
    appStore.setShowFlag(false)
}

let timer = null

// 只要 status 存在，且弹窗显示，就启动 3 秒后自动关闭
watch(() => props.status, (val) => {
    if (val && appStore.showFlag) {
        clearTimeout(timer)
        timer = setTimeout(() => {
            hideSMsg()
        }, 3000)
    }
}, { immediate: true })

onUnmounted(() => {
    clearTimeout(timer)
})
</script>

<template>
    <div v-if="appStore.showFlag && status" class="mb-4 p-3 font-medium text-sm text-green-700 rounded-full bg-green-200 hover:bg-red-300 hover:text-yellow-200 toast-box" id="smsg" @click="hideSMsg">
        <h3> {{ status }} </h3>
    </div>
      <!-- 消息弹窗3秒自动消失：测试父组件resources\js\Pages\Bom\Edit.vue -->
</template>

<style scoped>
#smsg:hover h3 {
  visibility: hidden;
  position: relative;
}

#smsg:hover h3::after {
  content: "点击隐藏当前的消息通知！";
  visibility: visible;
  position: absolute;
  left: 0;
  top: 0;
}

.toast-box {
  animation: slideIn 0.4s ease-out forwards;
}

@keyframes slideIn {
  0% {
    opacity: 0;
    transform: translateY(-20px);
  }
  100% {
    opacity: 1;
    transform: translateY(0);
  }
}
</style>