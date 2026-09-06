<template>
  <div class="org-editor flex flex-col h-screen font-sans" style="font-family:Inter,-apple-system,'PingFang SC','Microsoft YaHei',sans-serif;">
    <!-- toolbar -->
     <!-- 右键上下文菜单 -->
<div
  v-show="contextMenu.show"
  class="context-menu fixed bg-white shadow-lg border border-[#E2E2DE] rounded-md py-1 z-50 min-w-[130px]"
  :style="{ left: contextMenu.x + 'px', top: contextMenu.y + 'px' }"
  @click.stop
>
  <div class="ctx-item px-3 py-1.5 cursor-pointer hover:bg-gray-100 text-[12px]" @click="handleAddChild">＋ 添加下级</div>
  <div class="ctx-item px-3 py-1.5 cursor-pointer hover:bg-gray-100 text-[12px]" @click="handleAddSibling">＋ 添加同级</div>
  <div class="ctx-item px-3 py-1.5 cursor-pointer hover:bg-gray-100 text-[12px] text-[#C04838]" @click="handleDeleteNode">删除节点</div>
</div>
    <div class="toolbar bg-[#848791] text-black flex items-center gap-1 px-3.5 py-2 flex-shrink-0 border-b border-[#343B49] overflow-x-auto">
      <div class="brand font-bold text-sm mr-4.5 text-white whitespace-nowrap">
        组织架构图编辑器
        <small class="block font-normal text-xs text-[#000205] mt-0.5">Vue 3 · AntV G6 · Inertia</small>
      </div>

      <div class="tsep w-px self-stretch bg-[#343B49] mx-1.5"></div>

      <button class="tbtn flex items-center gap-1.5 px-2.5 p-1 rounded-md cursor-pointer whitespace-nowrap transition-colors duration-[120ms] hover:bg-[#7f9cf4] " :style="{
    backgroundColor: btnPrimaryBg,
  }" @click="expandAll">全部展开</button>
      <button class="tbtn flex items-center gap-1.5 px-2.5 p-1 rounded-md cursor-pointer whitespace-nowrap transition-colors duration-[120ms] hover:bg-[#7f9cf4]  " :style="{
    backgroundColor: btnPrimaryBg,
  }" @click="collapseAll">全部折叠</button>

        <div class="flex items-center rounded-full p-1 gap-1.5" :style="{
    backgroundColor: btnPrimaryBg,
  }">
            <button class="tbtn flex items-center rounded-full px-1 cursor-pointer hover:bg-[#7f9cf4] bg-[#f8d072] " @click="zoomBy(-0.1)">－</button>
           <span class="flex items-center ">{{ zoomPct }}%</span>
            <button class="tbtn flex items-center rounded-full px-1 cursor-pointer hover:bg-[#7f9cf4] bg-[#f8d072] " @click="zoomBy(0.1)">＋</button>
        </div>
      
      <button class="tbtn flex items-center gap-1.5 px-2.5 p-1 rounded-md cursor-pointer whitespace-nowrap transition-colors duration-[120ms] hover:bg-[#7f9cf4]  " :style="{
    backgroundColor: btnPrimaryBg,
  }" @click="fitView">适应窗口</button>
      
      <!-- *************测试颜色，颜色调试选择 -->
      <button class="flex p-4 rounded-full bg-[#f7dce3] "></button>

      <div class="spacer flex-1"></div>

      <button v-if="saveUrl" class="tbtn flex items-center gap-1.5 bg-[#5B8DEF] text-white border border-transparent text-xs px-2.5 p-1 rounded-md cursor-pointer whitespace-nowrap transition-colors duration-[120ms] hover:bg-[#4A7BDE] disabled:opacity-35 " :disabled="saving" @click="saveToServer">
        {{ saving ? '保存中…' : '保存到服务器' }}
      </button>
      <button class="tbtn flex items-center gap-1.5 px-2.5 p-1 rounded-md cursor-pointer whitespace-nowrap transition-colors duration-[120ms] hover:bg-[#7f9cf4]  " :style="{
    backgroundColor: btnPrimaryBg,
  }" @click="printJSON">打印JSON</button>
      <button class="tbtn flex items-center gap-1.5 px-2.5 p-1 rounded-md cursor-pointer whitespace-nowrap transition-colors duration-[120ms] hover:bg-[#7f9cf4]  " :style="{
    backgroundColor: btnPrimaryBg,
  }" @click="exportJSON">导出 JSON</button>
    </div>
    <div class="body-row flex flex-1 min-h-0">
            <!-- 左侧部门列表 -->
        <div 
            class="legend-aside flex-shrink-0 bg-white border-r border-[#E2E2DE] overflow-y-auto transition-all duration-200"
            :class="legendCollapsed ? 'w-[44px]' : 'w-[100px]'"
            @click="legendCollapsed = !legendCollapsed"
        >
            <!-- 可点击头部，切换折叠 -->
            <div class="legend-header m-3 mb-2 cursor-pointer select-none">
                <h3 v-if="!legendCollapsed" class="text-[11px] text-[#6B6B66] font-semibold">部门图例</h3>
                <span v-if="legendCollapsed" class="text-[11px] text-[#6B6B66] font-semibold">●</span>
            </div>

            <div class="legend-item flex items-center gap-2 text-[12px] py-1.5 px-3" v-for="d in DEPTS" :key="d.key">
                <span class="dept-dot w-3 h-3 rounded-full flex-shrink-0" :style="{ background: d.color }"></span>
                <span v-if="!legendCollapsed" class="truncate">{{ d.label }}</span>
            </div>
        </div>
        
        <!--中间画布区域 左下提示说明 -->
        <div class="canvas-wrap flex-1 relative min-w-0 bg-[#F5F5F2]" style="background-image:radial-gradient(#DADAD4 1px,transparent 1px);background-size:22px 22px;">
            <div ref="containerRef" class="graph-container w-full h-full"></div>
            <div class="hint absolute left-4 bottom-3.5 text-[11.5px] text-[#8E8E86] bg-white/85 px-2.5 py-1.5 rounded-md border border-[#E2E2DE]">点击节点编辑 · 点击底部圆点折叠/展开 · 拖拽节点可拖到另一节点上重新挂靠</div>
            
        </div>
        
    <!-- 右侧编辑区域 -->
      <div class="side-panel w-[200px] flex-shrink-0 bg-yellow-100 border-l border-[#E2E2DE] p-3 overflow-y-auto">
        <template v-if="selectedId">
          <h2 class="text-sm m-0 mb-1">{{ selectedId === treeData.id ? '根节点' : '节点编辑' }}</h2>
          <p class="sub text-[11.5px] text-[#6B6B66] m-0 mb-4.5">修改内容会实时同步到图上</p>

          <div class="field">
            <label class="block text-[11px] text-[#6B6B66] mb-1.5">姓名</label>
            <input type="text" v-model="formName" class="w-full px-2.25 py-2 text-[13px] border border-[#E2E2DE] rounded-md bg-white focus:outline-none focus:border-[#5B8DEF]">
          </div>
          <div class="field">
            <label class="block text-[11px] text-[#6B6B66] mb-1.5">职位 / 角色</label>
            <input type="text" v-model="formTitle" class="w-full px-2.25 py-2 text-[13px] border border-[#E2E2DE] rounded-md bg-white focus:outline-none focus:border-[#5B8DEF]">
          </div>
          <div class="field">
            <label class="block text-[11px] text-[#6B6B66] mb-1.5">部门分类</label>
            <select v-model="formDept" class="w-full px-4.25 py-2 text-[13px] border border-[#E2E2DE] rounded-md bg-white focus:outline-none focus:border-[#5B8DEF]">
              <option v-for="d in DEPTS" :key="d.key" :value="d.key">{{ d.label }}</option>
            </select>
          </div>

          <div class="meta-row flex justify-between text-[11px] text-[#6B6B66] py-2 border-t border-dashed border-[#E2E2DE] mt-1">
            <span>节点 ID</span><span>{{ selectedId }}</span>
          </div>
          <div class="meta-row flex justify-between text-[11px] text-[#6B6B66] py-2 border-t border-dashed border-[#E2E2DE] mt-1">
            <span>下级人数</span><span>{{ selectedChildrenCount }}</span>
          </div>

          <div class="panel-actions flex flex-col gap-2 mt-4.5">
            <button class="pbtn px-2.5 py-2.25 text-[12.5px] rounded-md border border-[#E2E2DE] bg-white cursor-pointer text-left text-[#1C1C1A] hover:border-[#5B8DEF] hover:text-[#5B8DEF]" @click="addChild">＋ 添加下级节点</button>
            <button class="pbtn px-2.5 py-2.25 text-[12.5px] rounded-md border border-[#E2E2DE] bg-white cursor-pointer text-left text-[#1C1C1A] hover:border-[#5B8DEF] hover:text-[#5B8DEF] disabled:opacity-40 disabled:cursor-not-allowed" :disabled="selectedId === treeData.id" @click="addSibling">＋ 添加同级节点</button>
            <button class="pbtn px-2.5 py-2.25 text-[12.5px] rounded-md border border-[#E2E2DE] bg-white cursor-pointer text-left text-[#1C1C1A] hover:border-[#C0563F] hover:text-[#C0563F] disabled:opacity-40 disabled:cursor-not-allowed" :disabled="selectedId === treeData.id" @click="deleteSelected">删除该节点（含下级）</button>
          </div>
        </template>
        <template v-else>
          <h2 class="text-sm m-0 mb-1">未选中节点</h2>
          <p class="empty-state text-[12.5px] text-[#6B6B66] leading-relaxed mt-2">点击左侧图中的任意卡片进行编辑，或拖拽卡片调整汇报关系。</p>
        </template>


      </div>
    </div>

  </div>
</template>

<script>
// 模块作用域：只在这个组件文件被首次 import 时执行一次。
// G6.registerNode 是全局注册，放进 <script setup> 会导致组件每次实例化都重复注册。
import G6 from '@antv/g6'

const DEPTS = [
  {id:1, key: 'sales',label: '业务销售', color: '#33415C' },
  {id:2, key: 'eng',label: '工程技术', color: '#2E5AAC' },
  {id:3, key: 'ops',label: '生产运营', color: '#B5730A' },
  {id:4, key: 'qd',label: '品质管控', color: '#2E8B74' },
  {id:5, key: 'law',label: '法务法规', color: '#8B3A62' },
  {id:6, key: 'pur',label: '采购跟单', color: '#6B6B66' },
]
const deptColor = key => (DEPTS.find(d => d.key === key) || DEPTS[DEPTS.length - 1]).color
const initials = name => (name || '?').trim().slice(-2)

G6.registerNode('org-card', {
  draw(cfg, group) {
    const w = 200, h = 66
    const color = deptColor(cfg.dept)
    const keyShape = group.addShape('rect', {
      attrs: {
        x: -w / 2, y: -h / 2, width: w, height: h, radius: 8,
        fill: '#fff',
        stroke: cfg.selected ? '#5B8DEF' : '#E2E2DE',
        lineWidth: cfg.selected ? 2 : 1,
        shadowColor: 'rgba(20,20,20,0.08)', shadowBlur: 8, shadowOffsetY: 2,
        cursor: 'pointer',
      },
      name: 'card-rect',
    })
    group.addShape('rect', {
      attrs: { x: -w / 2, y: -h / 2, width: 34, height: h, radius: [8, 0, 0, 8], fill: color },
      name: 'accent-bar',
    })
    group.addShape('circle', {
      attrs: { x: -w / 2 + 62, y: 0, r: 15, fill: color + '22', stroke: color, lineWidth: 1 },
      name: 'avatar-circle',
    })
    group.addShape('text', {
      attrs: { x: -w / 2 + 62, y: 1, text: initials(cfg.name), fontSize: 11, fontWeight: 600,
               fill: color, textAlign: 'center', textBaseline: 'middle' },
      name: 'avatar-text',
    })
    group.addShape('text', {
      attrs: { x: -w / 2 + 86, y: -9, text: cfg.name, fontSize: 13, fontWeight: 600,
               fill: '#1C1C1A', textBaseline: 'middle' },
      name: 'name-text',
    })
    group.addShape('text', {
      attrs: { x: -w / 2 + 86, y: 11, text: cfg.title || '', fontSize: 11,
               fill: '#6B6B66', textBaseline: 'middle' },
      name: 'title-text',
    })
    if (cfg.children && cfg.children.length) {
      group.addShape('circle', {
        attrs: { x: 0, y: h / 2, r: 8, fill: '#fff', stroke: '#C9C9C4', lineWidth: 1, cursor: 'pointer' },
        name: 'collapse-marker',
      })
      group.addShape('text', {
        attrs: { x: 0, y: h / 2 + 1, text: cfg.collapsed ? '+' : '–', fontSize: 13, fontWeight: 700,
                 fill: '#6B6B66', textAlign: 'center', textBaseline: 'middle', cursor: 'pointer' },
        name: 'collapse-marker-text',
      })
    }
    return keyShape
  },
  update(cfg, item) {
    const group = item.getContainer()
    const rect = group.find(el => el.get('name') === 'card-rect')
    if (rect) rect.attr({ stroke: cfg.selected ? '#ff0044' : '#E2E2DE', lineWidth: cfg.selected ? 2 : 1 })
    const accent = group.find(el => el.get('name') === 'accent-bar')
    if (accent) accent.attr('fill', deptColor(cfg.dept))
    const avatarC = group.find(el => el.get('name') === 'avatar-circle')
    if (avatarC) avatarC.attr({ fill: deptColor(cfg.dept) + '22', stroke: deptColor(cfg.dept) })
    const avatarT = group.find(el => el.get('name') === 'avatar-text')
    if (avatarT) avatarT.attr({ text: initials(cfg.name), fill: deptColor(cfg.dept) })
    const nameText = group.find(el => el.get('name') === 'name-text')
    if (nameText) nameText.attr('text', cfg.name)
    const titleText = group.find(el => el.get('name') === 'title-text')
    if (titleText) titleText.attr('text', cfg.title || '')
    const markerText = group.find(el => el.get('name') === 'collapse-marker-text')
    if (markerText) markerText.attr('text', cfg.collapsed ? '+' : '–')
  },
  getAnchorPoints() { return [[0.5, 0], [0.5, 1]] },
}, 'single-node')

export { DEPTS }
</script>

<script setup>
import { ref, watch, onMounted, onBeforeUnmount, nextTick } from 'vue'
import G6 from '@antv/g6'
import { router } from '@inertiajs/vue3'


const props = defineProps({
  // 由 Inertia 页面 / 控制器传入的初始组织架构数据（结构见下方 makeInitialData）
  initialData: { type: Object, default: null },
  // 若提供，"保存到服务器"按钮会出现，点击后 POST 到该地址
  saveUrl: { type: String, default: '' },
})
const emit = defineEmits(['saved'])
// 定义按钮颜色变量
const btnPrimaryBg = ref('#f3f3be')
const btnPrimaryHoverBg = ref('#4A7BDE')
const legendCollapsed = ref(false)
function makeInitialData() {
  return {
    id: 'ceo', name: '王建国', title: '总经理', dept: 'sales', collapsed: false,
    children: [
      { id: 'fin', name: '李芳', title: '财务总监', dept: 'law', collapsed: false, children: [] },
      { id: 'hr',  name: '陈伟', title: '人力资源经理', dept: 'pur', collapsed: false, children: [] },
      { id: 'it',  name: '赵敏', title: '信息技术经理', dept: 'eng', collapsed: false, children: [] },
      { id: 'qa',  name: '刘洋', title: '质量总监', dept: 'qc', collapsed: false, children: [
          { id: 'qa1', name: '孙丽', title: '质量工程师', dept: 'qc', collapsed: false, children: [] },
          { id: 'qa2', name: '周涛', title: '质检主管', dept: 'qc', collapsed: false, children: [] },
        ] },
      { id: 'eng', name: '黄磊', title: '工程总监', dept: 'eng', collapsed: false, children: [
          { id: 'eng1', name: '吴静', title: '研发工程师', dept: 'eng', collapsed: false, children: [] },
        ] },
      { id: 'ops', name: '郑强', title: '生产总监', dept: 'ops', collapsed: false, children: [
          { id: 'ops1', name: '林芳', title: '车间主管', dept: 'ops', collapsed: false, children: [] },
          { id: 'ops2', name: '徐杰', title: '仓储物流主管', dept: 'ops', collapsed: false, children: [] },
        ] },
    ],
  }
}
// ========== 右键菜单状态 ==========
const contextMenu = ref({
  show: false,
  x: 0,
  y: 0,
  nodeId: null // 当前右键的节点ID
})

// 关闭右键菜单
function closeContextMenu() {
  contextMenu.value.show = false
  contextMenu.value.nodeId = null
}

// 右键菜单事件处理：复用原有逻辑，把 selectedId 临时设置为右键节点id
function handleAddChild() {
  selectedId.value = contextMenu.value.nodeId
  addChild()
  closeContextMenu()
}

function handleAddSibling() {
  selectedId.value = contextMenu.value.nodeId
  addSibling()
  closeContextMenu()
}

function handleDeleteNode() {
  selectedId.value = contextMenu.value.nodeId
  deleteSelected()
  closeContextMenu()
}

// 页面全局点击，点击别处关闭右键菜单
document.addEventListener('click', ()=>{
  closeContextMenu()
})

// ---- 纯 JS 树操作，不放进 Vue 的响应式系统，G6 自己接管渲染 ----
function findNode(root, id) {
  if (root.id === id) return root
  for (const c of root.children || []) {
    const found = findNode(c, id)
    if (found) return found
  }
  return null
}
function findParent(root, id, parent = null) {
  if (root.id === id) return parent
  for (const c of root.children || []) {
    const found = findParent(c, id, root)
    if (found) return found
  }
  return null
}
function isDescendant(node, targetId) {
  if (!node.children) return false
  return node.children.some(c => c.id === targetId || isDescendant(c, targetId))
}
function forEachNode(root, fn) {
  fn(root)
  ;(root.children || []).forEach(c => forEachNode(c, fn))
}

const containerRef = ref(null)
const selectedId = ref(null)
const formName = ref('')
const formTitle = ref('')
const formDept = ref('exec')
const zoomPct = ref(100)
const selectedChildrenCount = ref(0)
const saving = ref(false)

let treeData = props.initialData ? JSON.parse(JSON.stringify(props.initialData)) : makeInitialData()
let graph = null
let syncingForm = false

function clearSelectionVisual() {
  if (selectedId.value) {
    const item = graph.findById(selectedId.value)
    if (item) graph.updateItem(item, { selected: false })
  }
}

function selectNode(id) {
  clearSelectionVisual()
  selectedId.value = id
  const item = graph.findById(id)
  if (item) graph.updateItem(item, { selected: true })
  const node = findNode(treeData, id)
  syncingForm = true
  formName.value = node.name
  formTitle.value = node.title
  formDept.value = node.dept
  selectedChildrenCount.value = (node.children || []).length
  nextTick(() => { syncingForm = false })
}

function redraw(refit) {
  graph.changeData(treeData)
  if (refit) graph.fitView(30)
}

function addChild() {
  if (!selectedId.value) return
  const parent = findNode(treeData, selectedId.value)
  const id = 'n' + Math.random().toString(36).slice(2, 8)
  parent.children = parent.children || []
  parent.children.push({ id, name: '新成员', title: '职位待定', dept: parent.dept, collapsed: false, children: [] })
  parent.collapsed = false
  redraw(true)
  selectNode(id)
}

function addSibling() {
  if (!selectedId.value || selectedId.value === treeData.id) return
  const parent = findParent(treeData, selectedId.value)
  if (!parent) return
  const id = 'n' + Math.random().toString(36).slice(2, 8)
  parent.children.push({ id, name: '新成员', title: '职位待定', dept: parent.dept, collapsed: false, children: [] })
  redraw(true)
  selectNode(id)
}

function deleteSelected() {
  if (!selectedId.value || selectedId.value === treeData.id) return
  const parent = findParent(treeData, selectedId.value)
  if (!parent) return
  parent.children = parent.children.filter(c => c.id !== selectedId.value)
  selectedId.value = null
  redraw(true)
}

function expandAll() {
  forEachNode(treeData, n => { n.collapsed = false })
  redraw(false)
  graph.layout()
}
function collapseAll() {
  forEachNode(treeData, n => { if (n.id !== treeData.id) n.collapsed = true })
  redraw(false)
  graph.layout()
}

function zoomBy(delta) {
  const z = Math.min(2, Math.max(0.2, graph.getZoom() + delta))
  graph.zoomTo(z)
  zoomPct.value = Math.round(z * 100)
}
function fitView() {
  graph.fitView(30)
  nextTick(() => { zoomPct.value = Math.round(graph.getZoom() * 100) })
}
function printJSON() {
    console.log(JSON.stringify(treeData))  
}
function exportJSON() {
  const blob = new Blob([JSON.stringify(treeData, null, 2)], { type: 'application/json' })
  const url = URL.createObjectURL(blob)
  const a = document.createElement('a')
  a.href = url; a.download = 'org-chart.json'; a.click()
  URL.revokeObjectURL(url)
}

// 通过 Inertia 把当前组织架构提交回后端
// 需要你在后端定义对应路由/控制器接收 { treeData } 并落库
function saveToServer() {
  if (!props.saveUrl) return
  saving.value = true
  router.post(props.saveUrl, { treeData }, {
    preserveScroll: true,
    onFinish: () => { saving.value = false },
    onSuccess: () => emit('saved', treeData),
  })
}

watch([formName, formTitle, formDept], () => {
  if (syncingForm || !selectedId.value) return
  const node = findNode(treeData, selectedId.value)
  node.name = formName.value
  node.title = formTitle.value
  node.dept = formDept.value
  const item = graph.findById(selectedId.value)
  if (item) graph.updateItem(item, { name: node.name, title: node.title, dept: node.dept })
})

let resizeHandler = null

onMounted(() => {
  const container = containerRef.value
  graph = new G6.TreeGraph({
    container,
    width: container.clientWidth,
    height: container.clientHeight,
    modes: { default: ['drag-canvas', 'zoom-canvas', 'drag-node'] },
    defaultNode: { type: 'org-card', size: [200, 76] },
    defaultEdge: { type: 'cubic-vertical', style: { stroke: '#C9C9C4', lineWidth: 1.5, endArrow: false } },
    layout: {
      type: 'compactBox', direction: 'TB',
      getId: d => d.id,
      getHeight: () => 76,
      getWidth: () => 200,
      getVGap: () => 44,
      getHGap: () => 28,
    },
  })

  graph.data(treeData)
  graph.render()
  graph.fitView(30)
  zoomPct.value = Math.round(graph.getZoom() * 100)

  graph.on('node:click', (evt) => {
    const name = evt.target.get('name')
    const model = evt.item.getModel()
    if (name === 'collapse-marker' || name === 'collapse-marker-text') {
      const node = findNode(treeData, model.id)
      node.collapsed = !node.collapsed
      graph.updateItem(evt.item, { collapsed: node.collapsed })
      graph.layout()
      return
    }
    selectNode(model.id)
  })

// 节点右键菜单
graph.on('node:contextmenu', (evt) => {
  evt.preventDefault() // 阻止浏览器原生右键菜单
  const model = evt.item.getModel()

  // 打开菜单，鼠标画布坐标转页面页面坐标
//   const point = graph.getClientPointByCanvas(evt.x, evt.y)
    contextMenu.value.x = evt.clientX;
    contextMenu.value.y = evt.clientY;
    contextMenu.value.show = true;
    contextMenu.value.nodeId = model.id 
  // 同时选中该节点，和点击行为保持一致
  selectNode(model.id)
})

// 画布空白处右键，关闭菜单
graph.on('canvas:contextmenu', (evt)=>{
  evt.preventDefault()
  closeContextMenu()
})

  graph.on('canvas:click', () => {
    clearSelectionVisual()
    selectedId.value = null
  })

  graph.on('wheelzoom', () => { zoomPct.value = Math.round(graph.getZoom() * 100) })

  graph.on('node:dragend', (evt) => {
    const draggedId = evt.item.getModel().id
    if (draggedId === treeData.id) { graph.layout(); return }
    const point = { x: evt.x, y: evt.y }
    let target = null
    graph.getNodes().forEach(n => {
      if (n.getModel().id === draggedId) return
      const bbox = n.getBBox()
      if (point.x >= bbox.minX && point.x <= bbox.maxX && point.y >= bbox.minY && point.y <= bbox.maxY) target = n
    })
    const draggedNode = findNode(treeData, draggedId)
    if (!target || isDescendant(draggedNode, target.getModel().id) || target.getModel().id === draggedId) {
      graph.layout()
      return
    }
    const targetId = target.getModel().id
    const oldParent = findParent(treeData, draggedId)
    oldParent.children = oldParent.children.filter(c => c.id !== draggedId)
    const newParent = findNode(treeData, targetId)
    newParent.children = newParent.children || []
    newParent.children.push(draggedNode)
    newParent.collapsed = false
    redraw(true)
  })
  resizeHandler = () => {
    graph.changeSize(container.clientWidth, container.clientHeight)
    graph.fitView(30)
  }
  window.addEventListener('resize', resizeHandler)
})

onBeforeUnmount(() => {
  if (resizeHandler) window.removeEventListener('resize', resizeHandler)
  if (graph) graph.destroy()
})
</script>

<style scoped>


</style>