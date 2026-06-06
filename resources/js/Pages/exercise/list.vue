<script setup>
import { router} from '@inertiajs/vue3'

// 🔥 只用这一个方法，所有按钮共享！
const goTo = (path) => {
  router.visit(`/exercise/${path}`)
}
</script>
<template>
<div class="flex items-center h-[40px] p-1 bg-slate-500 rounded-lg shadow">
  <h2 class="text-2xl font-bold text-gray-800">Exercise List</h2>
</div>
<!-- 卡片列表：自动换行 + 间距均匀 + 响应式 -->
  <div class="flex flex-wrap gap-1">
    <Link :href="route('exercise.dragDarkLight')" class="p-1 m-1 rounded-lg shadow bg-blue-200"
    >dragDarkLight</Link>

    <div class="p-1 m-1 rounded-lg shadow bg-blue-200">
      <button 
        @click="goTo('e1')">
        Recursive Tree
      </button>
    </div>

    <div class="p-1 m-1 rounded-lg shadow bg-blue-200">
      <button 
        @click="goTo('e2')">
        E_List2
      </button>
    </div>
    
  </div>
</template>