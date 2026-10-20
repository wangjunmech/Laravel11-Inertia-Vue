<template>
  <div class="container">
    <div class="text-xl p-2">
      产品工艺选择表
      <span class="cursor-pointer text-blue-500 hover:underline" @click="showData">打印当前数据到控制台</span>
    </div>

    <div class="">
      <div class="cascader-box rounded-lg relative p-2">
        <CascaderMenu
          v-model="material"
          dict-type="material"
          title="基础材料选择："
          @select="onMaterialSelect"
          @save="handleDictSave"          
        />
        <hr class="mb-6">
      </div>

      <div class="cascader-box rounded-lg relative p-2 mt-2">材料售卖形态选择       
        <div class="flex flex-row">
          <form >
            <div class="flex flex-row gap-x-4 items-center">
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="powder" checked="checked">粉
              </label>              
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="granule">颗粒
              </label>            
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="ingot">锭
              </label>            
           
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="pipe">型材-管棒
              </label>            
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="sheet">块
              </label>            
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="block">板/片
              </label>            
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="roll">卷
              </label>            
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="liquid">液体流体
              </label>            
              <label  class="flex items-center gap-x-1 bg-blue-300 rounded-lg px-2 m-1">
                <input type="radio" name="group" value="gas">气体
              </label>              
            </div>
          </form>
        </div>
      </div>
      <hr class="mb-6">      
      <CascaderMenu
        v-model="processes"
        title="成型工艺选择："
        dict-type="process"
        @select="onProcessSelect"
        @save="handleDictSave"
        class="cascader-box rounded-lg relative p-2"
      />
      <hr class="mb-6">      
      <CascaderMenu
        v-model="surfaceProcesses"
        title="表面处理后工艺选择："
        dict-type="surface_process"
        @select="onSurfaceProcessSelect"
        @save="handleDictSave"
        class="cascader-box rounded-lg relative p-2"
      />
    </div>

    <hr>
    <div>快速添加工艺流程*******************************</div>
  </div>
</template>
<script setup>
import { ref,onMounted } from 'vue'
import { useForm } from '@inertiajs/vue3'
import CascaderMenu from '@/Components/CascaderMenu.vue'
//引入写死的测试数据文件
// import { Materials } from "@/Components/data/Materials.js"
// import { Processes } from "@/Components/data/Processes.js"
// import { SurfaceProcesses } from "@/Components/data/SurfaceProcesses.js"
const material = ref([])
// material.value = Materials//数据赋值给material.value，后续会改成从后端接口获取

const processes = ref([])
// processes.value = Processes

const surfaceProcesses = ref([])
// surfaceProcesses.value = SurfaceProcesses

//用于监听选择的变量声明
const selectedMaterial = ref(null)
const selectedProcess = ref(null)
const selectedSurfaceProcess = ref(null)

// useForm 实例，用于字典保存
const form = useForm({
  type: '',
  items: []
})

// ===== 加载字典（这里用原生fetch，轻量；或者后端改成inertia props注入，后面再说）
async function loadAllDict() {
  const opts = { headers: { Accept: 'application/json' }, credentials: 'same-origin' }
  const [matRes, procRes, surfRes] = await Promise.all([
    fetch('/process-dict-tree/material', opts),
    fetch('/process-dict-tree/process', opts),
    fetch('/process-dict-tree/surface_process', opts),
  ])
  if (!matRes.ok || !procRes.ok || !surfRes.ok) {
    throw new Error('加载字典失败')
  }
  const [m, p, s] = await Promise.all([matRes.json(), procRes.json(), surfRes.json()])
  material.value = Array.isArray(m) ? m : []
  processes.value = Array.isArray(p) ? p : []
  surfaceProcesses.value = Array.isArray(s) ? s : []
}

const onMaterialSelect = ({ item, path }) => { selectedMaterial.value = path }
const onProcessSelect  = ({ item, path }) => { selectedProcess.value = path }
const onSurfaceProcessSelect = ({ item, path }) => { selectedSurfaceProcess.value = path }

const showData = () => {
  console.log('材料', JSON.stringify(material.value, null, 2))
  console.log('成型工艺', JSON.stringify(processes.value, null, 2))
  console.log('表面处理后工艺', JSON.stringify(surfaceProcesses.value, null, 2))
  console.log('选中的材料路径', JSON.stringify(selectedMaterial.value, null, 2))
  console.log('选中的工艺路径', JSON.stringify(selectedProcess.value, null, 2))
  console.log('选中的表面处理后工艺路径', JSON.stringify(selectedSurfaceProcess.value, null, 2))
}

// ===== 统一保存处理函数（三个组件的保存按钮共用这一个）
async function handleDictSave(payload) {
  const {type, items} = payload
  try {
    function getXsrfToken() {
      const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
      return match ? decodeURIComponent(match[1]) : '';
    }

    const res = await fetch('/process-dict-tree/sync', { // 和web.php路由保持一致，推荐小写
      method: 'POST',
      headers: {
        'Content-Type': 'application/json',
        'Accept': 'application/json',
        'X-Requested-With': 'XMLHttpRequest',
        'X-XSRF-TOKEN': getXsrfToken()
      },
      credentials: 'same-origin',
      body: JSON.stringify({type, items})
    })

    // 先判断HTTP状态码，非2xx直接抛错
    if (!res.ok) {
      throw new Error(`HTTP ${res.status}`)
    }
    const ret = await res.json()
    if(ret.ok){
      alert(`${type} 字典保存成功`)
      await loadAllDict()
    }else{
      alert('保存失败：' + ret.msg)
    }
  } catch(e){
    console.error(e)
    alert('网络/服务器错误：' + e.message)
  }
}


onMounted(() => {
  loadAllDict().catch(e => {
    console.error(e)
    alert('加载字典失败：' + e.message)
  })
})

</script>
<style scoped>
.cascader-box {
  border: 1px dashed #e923ff;
  transition: height 0.24s ease;  
}
</style>