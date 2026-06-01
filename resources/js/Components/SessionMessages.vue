<script setup>
import { ref } from 'vue'
// 组件里改成这样导入pinia
import { useAppStore } from '../stores/app.js'
const appStore = useAppStore()

const props = defineProps({
    status: String,

});

function hideSMsg() {
    appStore.setShowFlag(0)
    // console.log('去修改Pinia Appstore中的值...');
    // console.log(appStore.showFlag);
}
</script>

<template>
    <div v-if="status" class="mb-4 p-3 font-medium text-sm text-green-700 rounded-full bg-green-200 hover:bg-red-300 hover:text-yellow-200"  id="smsg"
    @click="hideSMsg"
    >
       <h3> {{ status}} </h3>
    </div>
</template>
<style scoped>
/* #smsg :hover{
    background-color:yellow;
} */

</style>
<style scoped>
/* 鼠标 hover 时，隐藏原来的文字，显示 8888 */
#smsg:hover h3 {
  visibility: hidden;
  position: relative;
}

#smsg:hover h3::after {
  content: "点击隐藏当前的消息通知！"; /* 你要显示的新文字 */
  visibility: visible;
  position: absolute;
  left: 0;
  top: 0;
}
</style>