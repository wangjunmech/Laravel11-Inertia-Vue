<template>
    <div class="container items-center justify-between">
        <div class="text-xl p-2">
        产品工艺成本明细对比分析     
        </div>
    <!-- 工艺选择选择区 -->
    <div class="flex gap-2 w-full p-2 border border-orange-500 rounded-lg">
      <div class="flex-1 items-center space-x-4">
        <label for="processSelect" class="text-lg">选择工艺:</label>
        <select id="processSelect" v-model="selectedProcess" class="border rounded p-1">
          <option v-for="process in processes" :key="process.id" :value="process.id">
            {{ process.name }}
          </option>
        </select>  
        <div class="">
          88
          </div>
        <div class="">
          88
          </div>
      </div>
    <div class="flex-1 items-center space-x-4">
        <label for="processSelect" class="text-lg">选择工艺:</label>
        <select id="processSelect" v-model="selectedProcess" class="border rounded p-1">
          <option v-for="process in processes" :key="process.id" :value="process.id">
            {{ process.name }}
          </option>
        </select>   
      </div>
    <div class="flex-1 items-center space-x-4">
        <label for="processSelect" class="text-lg">选择工艺:</label>
        <select id="processSelect" v-model="selectedProcess" class="border rounded p-1">
          <option v-for="process in processes" :key="process.id" :value="process.id">
            {{ process.name }}
          </option>
        </select>   
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref } from 'vue'
const processes = ref([
  { id: 1, name: '工艺A' },
  { id: 2, name: '工艺B' },
  { id: 3, name: '工艺C' },
])

// import TableWithSelection from "../../Components/TableWithSelection.vue"; // 请根据实际路径调整

</script>

<style scoped>
/* some style */

</style>