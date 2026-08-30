<script setup>
import { router} from '@inertiajs/vue3'

// 🔥 只用这一个方法，所有按钮共享！
const goTo = (path) => {
  router.visit(`/exercise/${path}`)
}
</script>
<template>
<div class="p-6 mx-auto max-w-screen-x1">
<!-- <div class="p-6 mx-auto max-w-screen-lg"> -->
  <div class="flex items-center h-[40px] p-1 bg-slate-500 rounded-lg shadow">
    <h2 class="text-2xl font-bold text-gray-800">Exercise List</h2>
  </div>
  <!-- 卡片列表：自动换行 + 间距均匀 + 响应式 -->
    <div >
      <div class="item">
      <Link :href="route('exercise.dragDarkLight')"
      >1--dragDarkLight-拖拽遮蔽图片</Link></div>

      <div class="item">
        <button @click="goTo('e1')">
          1--Recursive Tree--递归树
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e2')">
          2--Import Recursive Tree Data--递归树数据导入
        </button>
      </div>
      

      <div class="item">
        <button @click="goTo('e3')">
          3--Excel import export--处理excel
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e4')">
          4--Image snippet 图片上传浏览
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e5')">
          5--table line selection--多选+Shift确认框
        </button>
      </div>    

      <div class="item">
        <button @click="goTo('e6')">
          6--popup menu（right-click menu options）--右键菜单
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e7')">
          7--layout frame--布局样例
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e8')">
          8--Sys layout frame example--系统布局
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e9')">
          9--Breadcrumb 面包屑导航,逐级选择下拉菜单
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e10')">
          10--展示：悬浮横向级联菜单:hover 右侧自动弹出下级
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e11')">
          11--展示：悬浮纵向级联菜单:hover 下方弹出下级+自定义折叠宽度
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e12')">
          12--右键创建：悬浮纵向级联菜单:hover 下方弹出下级,增加添加兄弟和子元素功能（全局重复限定）
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e13')">
          13--Pina控制不显示导航：文件管理器- npm install vue@3 element-plus pinia @element-plus/icons-vue
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e14')">
          14--Pina控制不显示导航：文件管理器+左拖拽
        </button>
      </div>

      <div class="item">
        <button @click="goTo('e15')">
          15--D3.js--仿Obsidian关系图谱
        </button>
      </div>   

      <div class="item">
        <button @click="goTo('e16')">
          16--流程图：npm install d3@7 dagre-d3
        </button>
      </div>

      <div class="item">
        <a href="https://app.diagrams.net/" >
          --开源流程图：app.diagrams.net
        </a>
      </div>
      <div class="item">
        <button @click="goTo('e9999')">
          36进制：数位字母代码升级器
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e17')">
          17--键盘测试ASCII码+按键音效
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e18')">
          18--网站快捷导航（已扩展）
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e19')">
          19--快捷键 shortcut key
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e20')">
          20--客户编号生成器
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e21')">
          21--BOM物料表
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e22')">
          22--项目编号生成器
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e23')">
          23--制程产品编号生成器
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e24')">
          24--模具编号生成器
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e25')">
          25--产品检验标准书
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e26')">
          26--产品工艺流程表
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e27')">
          27--input输入过滤,过滤特殊字符
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e28')">
          28--3D-Tool : Model Info自定义文本提取器
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e29')">
          29--3D图形实时预览器--卡板堆放箱子预览
        </button>
      </div>
      <div class="item">
        <button @click="goTo('e30')">
          30--ThreeJS读取3D图形
        </button>
      </div>

    
    </div>
  </div>
</template>
<style scoped>
.item {
  background-color: rgb(219, 223, 219);
  border:lightpink solid 1px;
  border-radius: 6px;
  margin-bottom: 6px;
 
}
</style>