<template>
<div class="flex">
  <div>
    <div>Image Snippet 
      <span class="text-sm text-gray-500">Image</span>
      <div>
        <!-- 父组件中的图片路径和子组件中的图片路径完全一样
          <img
          :src="'/test.jpg'"  
        /> -->
      </div>
    </div>


      <!-- 父容器开启相对定位，给按钮做参照物 -->
      <div class="relative w-full">
        <!-- 参数说明
                :image------默认图片路径，？？？
                :title------Html title
                :desc-------描述文本显示在图片下方,有传文本就显示
                :SizeLevel--图片显示窗口大小
                :lock-------锁定上传功能，默认用户只显示旋转和查看按钮
                :path-------路由路径（控制器名）
                :imageMaxSize----默认3MB ，填写3
         
        -->                
        <ImageSnippet             
            :image="listings?.image ?? ''"
            :title='"title txt"'
            :desc='"discriptionXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"'
            :SizeLevel=12
            :lock=0
            :path="'exercise.storeImages'" 
            :imageMaxSize="0.5"
            />
      </div>
    <hr>
    <div>
      <ImageSnippet             
            :image="listings?.image ?? ''"
            :title='"title txt"'
            :SizeLevel=4
            :lock=0
            :path="'exercise.storeImages'" 
            :imageMaxSize="0.5"
            />
    </div>
  </div>

  <!-- 点击打开弹窗案例 -->
<button class="ml-4 px-3 py-1 bg-blue-500 text-white rounded" @click="modalVisible = true">
    打开模态框弹窗
  </button>

  <!-- 基础弹窗组件 -->
  <BaseModal
    :visible="modalVisible"
    title="模态框标题默认文本"
    widthClass="w-2/5"
    heightClass="h-2/5"
    @close="modalVisible = false"
    @confirm="handleConfirm"
    :escClosable="true"
  >
  <!-- **********************模态框内容任意发挥 -->
    <!-- 如果有特殊需要可自定义头部插槽示例，替换默认标题的title内容 -->
     
    <template #header>
      <h3 class="text-lg font-medium">模态框标题已自定义</h3>
    </template>
    <div class="bg-green-200 w-[800px]">
        <div>33</div>

        <!-- 默认插槽 = 弹窗主体内容，随便写表格、图片、文字 -->
        <table class="border-collapse w-full">
          <thead>
            <tr>
              <th class="border border-gray-300 px-3 py-2 text-left">钢材牌号</th>
              <th class="border border-gray-300 px-3 py-2 text-left">硬度 HRC</th>
              <th class="border border-gray-300 px-3 py-2 text-left">用途</th>
            </tr>
          </thead>
          <tbody>
            <tr v-for="item in steelList" :key="item.id" class="hover:bg-gray-50">
              <td class="border border-gray-300 px-3 py-2">{{ item.name }}</td>
              <td class="border border-gray-300 px-3 py-2">{{ item.hardness }}</td>
              <td class="border border-gray-300 px-3 py-2">{{ item.use }}</td>
            </tr>
          </tbody>
        </table>
    </div>
    <!-- ******************** -->
  </BaseModal>
</div>
</template>
<script setup>
import { ref } from 'vue'
import {router,useForm } from '@inertiajs/vue3' 
import ImageSnippet from "../../Components/ImageSnippet.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import BaseModal from '../../Components/BaseModal.vue'
// 组件里改成这样导入pinia
import { useAppStore } from '../../stores/app.js'
const appStore = useAppStore()

const props = defineProps({
    listings:Object,
    image: Object,    
});

// 模态框显示状态
const modalVisible = ref(false)
const steelList = ref([
  { id: 1, name: 'P20', hardness: '28~32', use: '普通塑胶模具' },
  { id: 2, name: 'S136', hardness: '48~52', use: '耐腐蚀、镜面模具' },
  { id: 3, name: 'NAK80', hardness: '38~42', use: '高抛光塑胶模' },
  { id: 3, name: 'NAK80', hardness: '38~42', use: '高抛光塑胶模' },
  { id: 3, name: 'NAK80', hardness: '38~42', use: '高抛光塑胶模' },
  { id: 3, name: 'NAK80', hardness: '38~42', use: '高抛光塑胶模' },
  { id: 3, name: 'NAK80', hardness: '38~42', use: '高抛光塑胶模' },
])

const handleConfirm = () => {
  console.log('点击确定')
  modalVisible.value = false
}
</script>
<style scoped>
.submitBtn {
  cursor: pointer;
  display: inline-block;
}
.submitBtn.disabled {
  opacity: 0.5;
  pointer-events: none; /* 禁止点击 */
  cursor: not-allowed;
}

</style>