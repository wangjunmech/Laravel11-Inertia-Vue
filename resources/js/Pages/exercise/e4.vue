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
            :image=listings.image
            :title='"title txt"'
            :desc='"discriptionXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXXX"'
            :SizeLevel=12
            :lock=0
            :path="'exercise.storeImages'" 
            :imageMaxSize="0.5"
            />
      </div>
    
  </div>
</div>
</template>
<script setup>
import { router,useForm } from '@inertiajs/vue3' 
import ImageSnippet from "../../Components/ImageSnippet.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
// 组件里改成这样导入pinia
import { useAppStore } from '../../stores/app.js'
const appStore = useAppStore()

const props = defineProps({
    listings:Object,
    image: Object,    
});


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