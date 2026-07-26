<template>
  <div>
    <div></div>
    <div style="background: yellow; padding: 10px;">
    <a href="https://www.emojiall.com/">  ❌</a>调试信息：Shift状态 = {{ isShiftPressed }} | 列表长度 = {{ selectedIdList.length }}| 列表{{ JSON.stringify(selectedIdList) }}
    </div>

    <div>
        <div>
        <a @click="deleteLines"  class="rounded-full w-1/4 bg-red-300 p-1 my-2 mx-1 inline-block cursor-pointer" >  ❌ Delete seleted items</a>
        </div>

    <table class="w-full border-collapse border border-slate-300">
        <thead>
            <!-- 表格头 -->
            <tr>
                <!-- 表头复选框 -->
                <th class="border border-slate-300 text-left w-28">
                    <!-- <input type="checkbox" :checked="isAllSelected" @change="toggleSelectAll($event.target.checked)" /> -->
                    <span  @click="toggleSelectAll(!isAllSelected)" class="cursor-pointer" title="全选/取消">☑️</span>
                    <span  @click="selectReverse" class="cursor-pointer border-slate-600 bg-purple-300 rounded-full px-2 border-2 inline-flex ">✔反选</span>
                </th>
                <!-- 表头其它列循环出来 -->
                <th v-for="c in tableColumns"
                    :key="c.id"
                    class="border border-slate-300 text-left"
                    >{{ c.label }}{{ c.id }}</th>

            </tr>
        </thead>
        <tbody>
        <!-- 表格数据区 -->
        <template v-for="(subArr, outerIdx) in props.data ?? []" :key="outerIdx">
            <tr v-for="item in subArr" 
                :key="item.id" 
                class="border-b border-slate-200"
                :class="{ 'bg-sky-100': selectedIdList.includes(item.id) }"
                >
                <!-- 固定复选框首列不动 -->
                <td class="border border-slate-300 p-2">
                    <CheckBox
                    :name="'check_' + item.id"
                    :is-checked="selectedIdList.includes(item.id)"
                    @change="(newChecked) => checkboxChange(item, newChecked)"
                    />
                </td>
                <!-- 自动循环剩余单元格 -->
                <td
                    v-for="col in tableColumns"
                    :key="col.field"
                    class="border border-slate-300 p-2"
                >
                    {{ item[col.field] }}
                </td>
            </tr>
        </template>
        </tbody>
        </table>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, nextTick, onMounted, onUnmounted } from 'vue';
// 引入checkbox组件
import CheckBox from './CheckBox.vue';

//定义props属性用于接收父组件传递:data="tabData"表格数据过来
const props = defineProps({ data: Array });
//定义 emit属性用于向父组件传递选择后的数据
const emit = defineEmits(['update:modelValue','update:data']);////声明两个数据通道。绑定名为modelValue自动传递数据给父组件的v-model绑定的方法，update:data绑定到父组件的标签中的@update:table-data="updateTabData"

// 响应式状态声明
const selectedIdList = ref([]);//存放选择的数据的id
const lastClickRowId = ref(null);//存放上次选择的checkbox的id
const isShiftPressed = ref(false); // 关键：全局记录 Shift 状态,按下shift键时为真

const allFlatRows = computed(() => (props.data ?? []).flat());//把二维嵌套数组 props.data 拍平成一维平铺数组，同时做空值容错，方便统一遍历、查找行下标、实现 Shift 区间多选逻辑

//表格字段提取处理
const tableColumns = computed(() => {
  const flatArr = (props.data ?? []).flat()
  if (!flatArr.length) return []
  const fieldKeys = Object.keys(flatArr[0])
  // 定义要排除的字段名数组
    const excludeKeys = [];//空数组默认显示所有的列
    // const excludeKeys = ['time', 'palindrome'];//不想显示某些列就写把列名写到数组中
  return fieldKeys
    .filter(key => !excludeKeys.includes(key)) // 先剔除不需要的列
    .map(key => ({
      label: key,
      field: key // 建议补上field，方便单元格取值 item[c.field]
    }))
})

// 是否全选计算出真假值
const isAllSelected = computed(() =>
    allFlatRows.value.length > 0 && 
    allFlatRows.value.every(i => selectedIdList.value.includes(i.id))
);//遍历数组每一个元素，执行回调判断，只有所有元素回调都返回 true，整体结果才为 true

// 全局监听 Shift 键
const handleKeyDown = (e) => { if (e.key === 'Shift') isShiftPressed.value = true; };
const handleKeyUp = (e) => { if (e.key === 'Shift') isShiftPressed.value = false; };


//删除选中的数据更新到父组件
function deleteLines() {
    const del = confirm('Are you sure to delete selected lines?')
    if (!del) return
    const idsToDelete = selectedIdList.value
    console.log('待删除ID：', idsToDelete)
    if (idsToDelete.length === 0) {
        alert('请先勾选要删除的行')
        return
    }
    // 1. 过滤数据
    const newData = props.data.map(subArr => {
        return subArr.filter(row => !idsToDelete.includes(row.id))
    }).filter(subArr => subArr.length > 0);

    // 2. 重置选中状态
    selectedIdList.value = [];
    lastClickRowId.value = null;

    // 3. 必须确保发送完整数据给父组件，并更新 ID 列表同步给父组件
    emit('update:table-data', newData); // 触发更新父组件的 tabData
    emit('update:modelValue', []); // 触发更新父组件的选中项列表为空，//绑定名为modelValue自动传递数据给父组件的v-model绑定的方法
}

//挂载监听shift键
onMounted(() => {
    // console.log('挂载监听器....................')
    window.addEventListener('keydown', handleKeyDown);
    window.addEventListener('keyup', handleKeyUp);
});
//销毁挂载监听shift键
onUnmounted(() => {
    // console.log('销毁监听器....................')
    window.removeEventListener('keydown', handleKeyDown);
    window.removeEventListener('keyup', handleKeyUp);
});
// // 全选：将所有行ID存入数组-----toggleSelectAll替代
// const selectAll = () => {
//   selectedIdList.value = allFlatRows.value.map(i => i.id);
//   lastClickRowId.value = null; // 重置连选起点
// };

// 反选：遍历所有行，已选则取消，未选则选中
const selectReverse = () => {
    const allIds = allFlatRows.value.map(i => i.id);//获取全部数据的id
    // 发送数据到父组件
        // console.log(allIds);
    const allRows = allFlatRows.value;//获取表格全部行数据
        // console.log(allRows);
    // 1. 更新内部 ID 列表
    selectedIdList.value = allIds.filter(id => !selectedIdList.value.includes(id));//从数据中过滤出被选中的数据的id
    // console.log(selectedIdList.value);
    lastClickRowId.value = null;//最近一次选中的id
    // console.log(lastClickRowId.value);
    
    // 2. 根据最新选择的ID列表，从allRows中筛选出完整数据对象
    const fullData = allRows.filter(row => selectedIdList.value.includes(row.id));
    
    // 3. 发送数据给父组件
    emit('update:modelValue', fullData);//绑定名为modelValue自动传递数据给父组件的v-model绑定的方法
};

// // 取消选择：清空数组-----toggleSelectAll替代
// const deselect = () => {
//   selectedIdList.value = [];
//   lastClickRowId.value = null;
// };


// 根据传入真假值来处理全选或取消全选：
const toggleSelectAll = (checked) => {
    // 1. 更新内部状态，
    selectedIdList.value = checked ? allFlatRows.value.map(i => i.id) : [];//如果传入真就把所有行数据的id列表给过去更新已选择id列表的数组
    // 根据最新的 ID 列表，从全量数据中过滤出完整对象
    const fullData = checked ? allFlatRows.value : []; 
    emit('update:modelValue', fullData);//向父组件传送全部的数据，//绑定名为modelValue自动传递数据给父组件的v-model绑定的方法
};

//复选框改变状态时处理数据,传入当前行数据currentItem和复选框选择状态真或假，每次的复选框选择都触发此方法
const checkboxChange = async (currentItem, newChecked) => {
        // console.log(currentItem);
        // console.log('---newChecked');
    const allRows = allFlatRows.value;//所有行数据
    const currentIndex = allRows.findIndex(row => row.id === currentItem.id);//当前行id的索引，注意不要跟数据id搞混淆
    //   console.log(currentIndex+']]]]]]]]]]]')
    //使用... ES6 展开运算符（扩展运算符）对选中ID数组做浅拷贝，生成一个全新数组 nextList，和原数组不再共用内存地址
    let nextList = [...selectedIdList.value];
        // console.log('--------------------]')
        // console.log(selectedIdList.value)
        // console.log(nextList)

    // 直接使用全局的 isShiftPressed状态来处理数据
    if (isShiftPressed.value && lastClickRowId.value !== null) {
        // console.log('按shift.......时的复选框选择走这里')
        //如果按下了Shift键并且有上次选择的id索引值（如果上次选择的是第1条则索引为0），下面进行多选计算数据
        const lastIndex = allRows.findIndex(row => row.id === lastClickRowId.value);//最后一次选择的复选框索引
            // console.log(lastIndex+']]]]]]]]]')
        const start = Math.min(lastIndex, currentIndex);//多选列表开始索引值
        const end = Math.max(lastIndex, currentIndex);//多选列表结尾索引值
        const rangeIds = allRows.slice(start, end + 1).map(r => r.id);//根据开始和结尾索引位置计算出多选行的id范围
            // console.log(rangeIds+'..........>>>>>>')
        if (newChecked) {
            // console.log('newChecked...........')            
            rangeIds.forEach(id => { if (!nextList.includes(id)) nextList.push(id); });
        } else {
            // console.log('没有newChecked...........')
            //如果当前复选框没有选中，范围处理为取消选中，例如已全选，再把第一个取消选择，按住shift再点后面某一范围时会取消这一范围的选中
            nextList = nextList.filter(id => !rangeIds.includes(id));
        }
    } else {
        // console.log('没按shift时的复选框选择走这里')
        if (newChecked) {
            if (!nextList.includes(currentItem.id)) nextList.push(currentItem.id);
            } else {
            nextList = nextList.filter(id => id !== currentItem.id);
        }
    }

    selectedIdList.value = nextList;
    lastClickRowId.value = currentItem.id;
    // 【关键】将选中项的完整数据传给父组件
    const selectedFullData = allFlatRows.value.filter(item => nextList.includes(item.id));
    emit('update:modelValue', selectedFullData);//绑定名为modelValue自动传递数据给父组件的v-model绑定的方法
    await nextTick();
};




</script>