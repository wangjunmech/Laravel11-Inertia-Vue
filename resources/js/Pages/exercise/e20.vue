<template>
  <div class="p-4 space-y-4">
    <div class="text-xl font-semibold">客户编号生成器</div>
    <div>
      编号规则：客户所在<a href="https://github.com/lukes/ISO-3166-Countries-with-Regional-Codes" class="text-indigo-500 underline">国家代码</a>(2位)+业务员代码(3位)+年份(2位)+序号(3位)
    </div>

    <!-- 选择区域 -->
    <div class="">
        <div>
        <select v-model="selectedCountryCode" class="border p-2 rounded">
            <option value="">请选择国家</option>
            <option
            v-for="c in countryList"
            :key="c['alpha-2']"
            :value="c['alpha-2']"
            >
            {{ c.name }} ({{ c['alpha-2'] }})
            </option>
        </select>

        <select v-model="selectedSalesCode" class="border p-2 rounded">
            <option value="">请选择业务员</option>
            <option
            v-for="k in salesCode"
            :key="k.sn"
            :value="k.sn"
            >
            业务员{{ k.sn }} 编码：{{ k.empcode }}
            </option>
        </select>
        </div>
      <div class="">
        <input
          v-model="customerNumber"
          type="text"
          id="customerNumber"
          readonly
          class="border p-2 rounded w-60"
          placeholder="选择国家和业务员自动生成"
        >
        <button
          type="button"
          @click="submitCustomer"
          class="bg-amber-400 rounded-lg p-2 px-4"
        >
          刷新编号
        </button>
      </div>
    </div>

    <!-- 国家列表表格 -->
    <div>
      <h3 class="text-lg font-medium mb-2">国家列表</h3>
      <table class="border border-gray-300 w-full border-collapse">
        <thead>
          <tr>
            <th class="border border-gray-300 p-2 bg-gray-100 w-[180px]">国家名称</th>
            <th class="border border-gray-300 p-2 bg-gray-100">二位代码</th>
            <th class="border border-gray-300 p-2 bg-gray-100">三位代码</th>
            <th class="border border-gray-300 p-2 bg-gray-100">国家数字编码</th>
            <th class="border border-gray-300 p-2 bg-gray-100">所属大洲</th>
            <th class="border border-gray-300 p-2 bg-gray-100">次区域</th>
          </tr>
        </thead>
        <tbody>
          <tr v-for="item in countryList" :key="item['alpha-2']">
            <td class="border border-gray-300 p-2">{{ item.name }}</td>
            <td class="border border-gray-300 p-2">{{ item['alpha-2'] }}</td>
            <td class="border border-gray-300 p-2">{{ item['alpha-3'] }}</td>
            <td class="border border-gray-300 p-2">{{ item['country-code'] }}</td>
            <td class="border border-gray-300 p-2">{{ item.region }}</td>
            <td class="border border-gray-300 p-2">{{ item['sub-region'] }}</td>
          </tr>
        </tbody>
      </table>
    </div>
  </div>
</template>

<script setup>
// 修复：补充watch导入
import { ref, watch } from 'vue'

// 测试国家数据（增加中国方便测试）
const countryList = ref(
    [{"name":"Afghanistan","alpha-2":"AF","alpha-3":"AFG","country-code":"004","iso_3166-2":"ISO 3166-2:AF","region":"Asia","sub-region":"Southern Asia","intermediate-region":"","region-code":"142","sub-region-code":"034","intermediate-region-code":""},{"name":"Åland Islands","alpha-2":"AX","alpha-3":"ALA","country-code":"248","iso_3166-2":"ISO 3166-2:AX","region":"Europe","sub-region":"Northern Europe","intermediate-region":"","region-code":"150","sub-region-code":"154","intermediate-region-code":""},{"name":"Albania","alpha-2":"AL","alpha-3":"ALB","country-code":"008","iso_3166-2":"ISO 3166-2:AL","region":"Europe","sub-region":"Southern Europe","intermediate-region":"","region-code":"150","sub-region-code":"039","intermediate-region-code":""},{"name":"Algeria","alpha-2":"DZ","alpha-3":"DZA","country-code":"012","iso_3166-2":"ISO 3166-2:DZ","region":"Africa","sub-region":"Northern Africa","intermediate-region":"","region-code":"002","sub-region-code":"015","intermediate-region-code":""},{"name":"American Samoa","alpha-2":"AS","alpha-3":"ASM","country-code":"016","iso_3166-2":"ISO 3166-2:AS","region":"Oceania","sub-region":"Polynesia","intermediate-region":"","region-code":"009","sub-region-code":"061","intermediate-region-code":""},{"name":"Andorra","alpha-2":"AD","alpha-3":"AND","country-code":"020","iso_3166-2":"ISO 3166-2:AD","region":"Europe","sub-region":"Southern Europe","intermediate-region":"","region-code":"150","sub-region-code":"039","intermediate-region-code":""},{"name":"Angola","alpha-2":"AO","alpha-3":"AGO","country-code":"024","iso_3166-2":"ISO 3166-2:AO","region":"Africa","sub-region":"Sub-Saharan Africa","intermediate-region":"Middle Africa","region-code":"002","sub-region-code":"202","intermediate-region-code":"017"},{"name":"Anguilla","alpha-2":"AI","alpha-3":"AIA","country-code":"660","iso_3166-2":"ISO 3166-2:AI","region":"Americas","sub-region":"Latin America and the Caribbean","intermediate-region":"Caribbean","region-code":"019","sub-region-code":"419","intermediate-region-code":"029"}
]);


const salesCode = ref([
  {"sn":"001","empcode":"a00001"},
  {"sn":"002","empcode":"a00002"},
  {"sn":"003","empcode":"a00003"},
  {"sn":"004","empcode":"a00004"},
  {"sn":"005","empcode":"a00005"},
  {"sn":"006","empcode":"a00006"},
  {"sn":"007","empcode":"a00007"},
  {"sn":"008","empcode":"a00008"},
  {"sn":"009","empcode":"a00009"},
  {"sn":"010","empcode":"a00010"}
]);

// 绑定下拉值
const selectedCountryCode = ref('')
const selectedSalesCode = ref('')
// 最终客户编号
const customerNumber = ref('')
// 自增序号
const serialNum = ref(1)
// 当前年份
// const currentYear = new Date().getFullYear()//获取4位当前年份
const currentYear = new Date().getFullYear().toString().slice(-2)//获取2位当前年份
// 封装生成编号逻辑
function generateCode() {
  // 修复：清空赋值为空字符串
  if (!selectedCountryCode.value || !selectedSalesCode.value) {
    customerNumber.value = ''
    return
  }
  // 序号固定3位，不足前面补0
  const seq = String(serialNum.value).padStart(3, '0')
  // 拼接规则：2位国家码 + 业务员编码 + 年份 + 序号
  customerNumber.value = `${selectedCountryCode.value}${selectedSalesCode.value}${currentYear}${seq}`
}

// 监听两个下拉，任意切换自动生成
watch([selectedCountryCode, selectedSalesCode], () => {
  generateCode()
})

// 按钮手动刷新
const submitCustomer = () => {
  if (!selectedCountryCode.value || !selectedSalesCode.value) {
    alert('请同时选择国家和业务员！')
    return
  }
  serialNum.value++
  generateCode()
}
</script>

<style scoped>
a {
  @apply text-indigo-500 hover:text-indigo-600 font-medium no-underline dark:text-indigo-400;
}
</style>