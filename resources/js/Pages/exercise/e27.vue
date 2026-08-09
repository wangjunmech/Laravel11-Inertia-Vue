<template>
    <div>
        <div>input输入过滤使用方法，引入resources\js\Rules\notAllowedInputCharacters.js </div>
        <!-- 过滤输入特殊字符测试 function keyUp(data, exclude)-->
         <!-- 
        v‑model 绑定响应式变量 testValue
        @keyup 按键抬起时执行过滤，[2] 获取清洗完成之后的字符串，回填输入框
        @paste.prevent 拦截粘贴，同样走一遍过滤，防止粘贴带入违禁字符 -->
        <!-- @keyup="testValue = keyUp(testValue,['-','_'])[2]" ，加上参数允许-_等字符输入-->

        <input 
            type="text"
            v-model="testValue"
            @keyup="testValue = keyUp(testValue,['-','_'])[2]"            
            @paste.prevent="testValue = keyUp($event.clipboardData.getData('text'),['-','_'])[2]"
            placeholder="尝试输入 # @ ￥ < script 等内容"
        >
        <div>当前输入值：{{ testValue }}</div>
    </div>
</template>

<script setup>
import { ref } from 'vue'
import { keyUp } from '@/Rules/notAllowedInputCharacters.js'

// 绑定输入框
const testValue = ref('')
</script>

<style scoped>

</style>