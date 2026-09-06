<template>
  <!-- 默认提示文字 -->
  <CtxMenuWrapper :menu-list="menuOptions" @select="menuSelect">
    <div class="box w-full h-10 flex items-center justify-center bg-red-300">在此区域点击鼠标右键弹出菜单</div>
  </CtxMenuWrapper>

  <!-- 自定义tip文字 -->
   自定义tip文字tip-text=XXX
  <CtxMenuWrapper :menu-list="menuOptions" tip-text="XXX" @select="menuSelect">
    <button class="btn bg-red-300 rounded-full p-3">testtestestes</button>
  </CtxMenuWrapper>



  <!-- 关闭tip提示:show-helper-tip="false": -->
<p>关闭右键提醒:show-helper-tip="false"</p>
  <CtxMenuWrapper :menu-list="menuOptions" :show-helper-tip="false" @select="menuSelect">
    <span class="text-5xl">🪟</span>
  </CtxMenuWrapper>
  <!-- 关闭tip提示:show-helper-tip="false": -->
<p>打开右键提醒</p>
  <CtxMenuWrapper :menu-list="menuOptions" :show-helper-tip="true" @select="menuSelect">
    <span class="text-5xl">🪟</span>
  </CtxMenuWrapper>
  <!-- 关闭tip提示:show-helper-tip="false": -->
<p>关闭右键提醒:show-helper-tip="false"</p>
  <CtxMenuWrapper :menu-list="menuOptions" :show-helper-tip="false" @select="menuSelect">
    <span class="text-5xl">🪟</span>
  </CtxMenuWrapper>
  <!-- 关闭tip提示:show-helper-tip="false": -->
<p>打开右键提醒</p>
  <CtxMenuWrapper :menu-list="menuOptions" :show-helper-tip="true" @select="menuSelect">
    <span class="text-5xl">🪟</span>
  </CtxMenuWrapper>

</template>

<script setup>
import { ref } from 'vue'
import CtxMenuWrapper from '@/Components/CtxMenuWrapper.vue'

const menuOptions = ref([
  { label: '复制', command: 'copy' },
  { label: '粘贴', command: 'paste' },
  { label: '删除', command: 'delete' },
  { label: '重命名', command: 'rename' },
  { label: '想干啥', command: 'test' }
])

const menuSelect = (item) => {
  console.log(item.command) // 'copy'
  switch(item.command){
    case 'copy':
      copy() // 需要你手动调用这个函数，不会自动跑
      break
    case 'paste':
      paste()
      break
    case 'delete':
      deleteItem()
      break
    case 'rename':
      rename()
      break
    case 'test':
      test()    
      break
  }
}

function copy() {
  console.log('********************copy')
}
function paste() {
  console.log('********************paste')
}
function deleteItem() {
  console.log('********************delete')
}
function rename() {
  console.log('********************rename')
}
function test() {
  console.log('********************test')
}


</script>
