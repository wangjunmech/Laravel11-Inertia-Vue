<template>
  <div class="container">
    <div class="text-xl p-2">
      注塑模具规格书
      <span class="cursor-pointer text-blue-500 hover:underline" @click="showData">打印当前数据到控制台</span>
    </div>
    <div class="">
      <div class="cascader-box rounded-lg relative p-2">产品基础信息及要求：      
        <div class="flex flex-col gap-3  p-4">
  <div class="flex flex-row items-center">
    <label class="">产品编号：</label>
    <input type="text" name="fname" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
    <input type="text" name="fname" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品名称：</label>
    <input type="text" name="product_name" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">胶料类别：</label>
    <input type="text" name="rubber_type" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">胶料牌号：</label>
    <input type="text" name="rubber_mark" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">是否有提供指定胶料的物性表：</label>
    <input type="text" name="has_material_datasheet" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品用途：</label>
    <input type="text" name="product_usage" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">外观等级：</label>
    <input type="text" name="appearance_grade" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">表面要求：</label>
    <input type="text" name="surface_req" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品颜色：</label>
    <input type="text" name="product_color" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">是否多款颜色换色注塑：</label>
    <input type="text" name="multi_color_mold" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">热咀与换色优先级：</label>
    <input type="text" name="hot_tip_color_priority" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品重量：</label>
    <input type="text" name="product_weight" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">最高精度公差要求：</label>
    <input type="text" name="tolerance_high" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">最大尺寸的公差要求：</label>
    <input type="text" name="tolerance_max_size" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品外形尺寸：</label>
    <input type="text" name="product_dimension" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">水口去除方式：</label>
    <input type="text" name="gate_remove_method" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">是否换镶件出相似特征产品：</label>
    <input type="text" name="insert_mold_switch" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">是否有预埋件成型：</label>
    <input type="text" name="has_inserts" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品取出方式：</label>
    <input type="text" name="eject_method" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品包装要求：</label>
    <input type="text" name="pack_req" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">日产量要求：</label>
    <input type="text" name="daily_output" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">年产量要求：</label>
    <input type="text" name="year_output" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品2D图：</label>
    <input type="text" name="drawing_2d" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">产品3D图：</label>
    <input type="text" name="drawing_3d" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
  <div class="flex flex-row items-center">
    <label class="">是否有参考样品且说明哪些内容参考：</label>
    <input type="text" name="sample_ref" class="border border-gray-300 rounded px-2 py-1.5 flex-1">
  </div>
</div>
   
        <hr class="mb-6">
      </div>
      </div>
    <div class="">
      <div class="cascader-box rounded-lg relative p-2">
        <CascaderMenu
          v-model="material"
          dict-type="material"
          title="模具钢材选择："
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
const material = ref([])
material.value =  [
    // e26 一级：基础材料大类 pid=0
    { id: 1, area: '金属材料', pid: 0 },
    { id: 2, area: '高分子塑料', pid: 0 },
]

const processes = ref([])
processes.value = [
    // e26 一级：基础材料大类 pid=0
    { id: 1, area: '金属材料', pid: 0 },
    { id: 2, area: '高分子塑料', pid: 0 },
]

const surfaceProcesses = ref([])
surfaceProcesses.value = [
    // e26 一级：基础材料大类 pid=0
    { id: 1, area: '金属材料', pid: 0 },
    { id: 2, area: '高分子塑料', pid: 0 },
]

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
// async function loadAllDict() {
//   const opts = { headers: { Accept: 'application/json' }, credentials: 'same-origin' }
//   const [matRes, procRes, surfRes] = await Promise.all([
//     fetch('/process-dict-tree/material', opts),
//     fetch('/process-dict-tree/process', opts),
//     fetch('/process-dict-tree/surface_process', opts),
//   ])
//   if (!matRes.ok || !procRes.ok || !surfRes.ok) {
//     throw new Error('加载字典失败')
//   }
//   const [m, p, s] = await Promise.all([matRes.json(), procRes.json(), surfRes.json()])
//   material.value = Array.isArray(m) ? m : []
//   processes.value = Array.isArray(p) ? p : []
//   surfaceProcesses.value = Array.isArray(s) ? s : []
// }

const onMaterialSelect = ({ item, path }) => { selectedMaterial.value = path }
const onProcessSelect  = ({ item, path }) => { selectedProcess.value = path }
const onSurfaceProcessSelect = ({ item, path }) => { selectedSurfaceProcess.value = path }

const showData = () => {
  console.log('材料', JSON.stringify(material.value, null, 2))
  console.log('成型工艺', JSON.stringify(processes.value, null, 2))
 }

// ===== 统一保存处理函数（三个组件的保存按钮共用这一个）
async function handleDictSave(payload) {
  const {type, items} = payload
  try {
    function getXsrfToken() {
      const match = document.cookie.match(/XSRF-TOKEN=([^;]+)/);
      return match ? decodeURIComponent(match[1]) : '';
    }

    // const res = await fetch('/process-dict-tree/sync', { // 和web.php路由保持一致，推荐小写
    //   method: 'POST',
    //   headers: {
    //     'Content-Type': 'application/json',
    //     'Accept': 'application/json',
    //     'X-Requested-With': 'XMLHttpRequest',
    //     'X-XSRF-TOKEN': getXsrfToken()
    //   },
    //   credentials: 'same-origin',
    //   body: JSON.stringify({type, items})
    // })

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