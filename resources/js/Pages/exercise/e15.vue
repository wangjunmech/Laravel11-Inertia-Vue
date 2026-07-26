<template>
  <div
    class="w-full h-[700px] relative overflow-hidden"
    ref="containerRef"
    :style="{ backgroundColor: dynamicBg }"
    @contextmenu.prevent="openContextMenu($event)"
  >
    <!-- 颜色选择器 -->
    <div class="absolute top-2 left-2 z-50 flex items-center gap-2 bg-slate-200 p-2 rounded shadow">
      <span class="text-sm text-gray-600">背景色</span>
      <input type="color" v-model="dynamicBg" class="w-8 h-8 rounded-full cursor-pointer border-0" />
      <button
        @click="resetZoom"
        class="bg-blue-500 text-white px-2 py-1 rounded text-xs hover:bg-blue-600"
      >
        重置视图
      </button>
      <div>节点：{{ nodes.length }}</div>
      <div>连线：{{ links.length }}</div>
    </div>

    <!-- 右侧节点详情面板 -->
    <div
      v-if="selectedNode"
      class="absolute top-2 right-2 z-50 bg-white p-3 rounded shadow min-w-[160px]"
    >
      <h4 class="font-bold text-gray-800 mb-1">{{ selectedNode.name }}</h4>
      <p class="text-xs text-gray-500 mb-2">引用次数：{{ selectedNode.linkCount }}</p>
      <button
        @click="openNote"
        class="bg-green-500 text-white w-full px-2 py-1 rounded text-xs hover:bg-green-600 mb-1"
      >
        打开笔记
      </button>
    </div>

    <!-- 右键菜单：紧贴光标 + 优化样式 -->
    <div
      v-if="showContextMenu"
      data-context-menu
      class="absolute z-50 w-36 bg-white rounded shadow-md py-1 text-sm border border-gray-100"
      :style="{
        left: contextMenuX + 'px',
        top: contextMenuY + 'px',
        transform: 'translate(-155px, -110px)' // 右键菜单偏移你原有配置保留
      }"
    >
      <div
        class="px-3 py-1.5 hover:bg-gray-100 cursor-pointer text-gray-700"
        @click="handleResetClick"
      >
        重置视图
      </div>
      <div
        class="px-3 py-1.5 hover:bg-gray-100 cursor-pointer text-gray-700"
        @click="clearSelected"
      >
        取消选中
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick, watch } from 'vue'
import * as d3 from 'd3'

const containerRef = ref(null)
let svg = null
let g = null
let simulation = null
let zoom = null

// 背景色 + localStorage
const dynamicBg = ref(localStorage.getItem('graph_bg') || '#fafafa')
watch(dynamicBg, (val) => localStorage.setItem('graph_bg', val))

let currentK = 1; // 新增：用于记录当前的缩放比例

// 右键菜单变量
const showContextMenu = ref(false)
const contextMenuX = ref(0)
const contextMenuY = ref(0)
// 先在函数顶部定义固定最小值常量
const selectedNodeMinRadius = 20
// 图谱数据
const nodes = ref([
  { id: 1, name: 'Vue3', group: '前端', linkCount: 3 },
  { id: 2, name: 'Vite', group: '前端', linkCount: 2 },
  { id: 3, name: 'Pinia', group: '前端', linkCount: 2 },
  { id: 4, name: 'JavaScript', group: '基础', linkCount: 4 },
  { id: 5, name: 'CSS', group: '基础', linkCount: 1 },
  { id: 6, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 7, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 8, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 9, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 10, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 11, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 12, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 13, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
  { id: 14, name: 'Obsidian', group: '工具', linkCount: 1 },
])
const links = ref([
  { source: 1, target: 2 },
  { source: 1, target: 3 },
  { source: 1, target: 4 },
  { source: 2, target: 4 },
  { source: 3, target: 4 },
  { source: 5, target: 4 },
])

const selectedNode = ref(null)
const colorMap = { '前端': '#42b983', '基础': '#3498db', '工具': '#e67e22' }

let resizeTimer = null
// 全局点击监听具名函数（用于正确绑定+解绑）
let docClickHandler = null

// 打开右键菜单
function openContextMenu(e) {
  contextMenuX.value = e.clientX
  contextMenuY.value = e.clientY
  showContextMenu.value = true
}

// 关闭右键菜单
function closeContextMenu() {
  showContextMenu.value = false
}

// 右键重置点击处理函数（解决动画被打断问题）
function handleResetClick() {
  resetZoom()
  // 延迟关闭菜单，避免DOM销毁打断D3缩放过渡动画
  setTimeout(closeContextMenu, 160)
}

// 取消选中节点+清除闪烁红圈
function clearSelected() {
  selectedNode.value = null
  g.selectAll('.selected-ring').remove()
  closeContextMenu()
}

// 重置视图核心逻辑
function resetZoom() {
  const el = containerRef.value
  const width = el.clientWidth
  const height = el.clientHeight
  const allNodes = nodes.value
  if (!allNodes.length) return

  const xs = allNodes.map(d => d.x)
  const ys = allNodes.map(d => d.y)
  const minX = Math.min(...xs)
  const maxX = Math.max(...xs)
  const minY = Math.min(...ys)
  const maxY = Math.max(...ys)
  const cx = (minX + maxX) / 2
  const cy = (minY + maxY) / 2
  const rx = (maxX - minX) || 1
  const ry = (maxY - minY) || 1
  const scale = 0.8 / Math.max(rx / width, ry / height)

  svg.transition().duration(500).call(
    zoom.transform,
    d3.zoomIdentity
      .translate(width / 2, height / 2)
      .scale(scale)
      .translate(-cx, -cy)
  )
}

// 初始化图谱
function initChart() {
  const el = containerRef.value
  if (!el) return
  const width = el.clientWidth
  const height = el.clientHeight

  d3.select(el).selectAll('svg').remove()
  if (simulation) simulation.stop()

  svg = d3.select(el).append('svg').attr('width', width).attr('height', height)
  g = svg.append('g')

// 修改 zoom 定义部分
zoom = d3.zoom().scaleExtent([0.1, 4]).on('zoom', (event) => {
    currentK = event.transform.k;
    g.attr('transform', event.transform);
    
    // 新增：如果选中了节点，缩放时强制同步更新红圈大小
    if (selectedNode.value) {
        const baseRadius = 6 + selectedNode.value.linkCount + 6;
        const effectiveRadius = Math.max(baseRadius, selectedNodeMinRadius / currentK);
        g.select('.selected-ring').attr('r', effectiveRadius);
    }
})
  svg.call(zoom)

  simulation = d3.forceSimulation(nodes.value)
    .force('link', d3.forceLink(links.value).id(d => d.id).distance(100))
    .force('charge', d3.forceManyBody().strength(-300))
    .force('center', d3.forceCenter(width / 2, height / 2))
    .force('collision', d3.forceCollide().radius(30))

  const link = g.append('g')
    .selectAll('line')
    .data(links.value)
    .enter().append('line')
    .attr('stroke', '#ccc')
    .attr('stroke-opacity', 0.6)
    .attr('stroke-width', 1.5)

  // 红圈图层、节点图层分层
  const ringGroup = g.append('g')
  const nodeGroup = g.append('g')

  const node = nodeGroup.selectAll('circle')
    .data(nodes.value)
    .enter().append('circle')
    .attr('r', d => 6 + d.linkCount)
    .attr('fill', d => colorMap[d.group] || '#999')
    .call(d3.drag().on('start', dragstarted).on('drag', dragged).on('end', dragended))

  // 节点点击，生成红色闪烁外圈
  node.on('click', (e, d) => {
    e.stopPropagation();
    selectedNode.value = d
    ringGroup.selectAll('.selected-ring').remove()
    // 关键：这里计算时就使用 currentK
    const baseRadius = 6 + d.linkCount + 6;
    const initialRadius = Math.max(baseRadius, selectedNodeMinRadius / currentK);
    const ring = ringGroup.append('circle')
      .attr('class', 'selected-ring')
      .attr('cx', d.x)
      .attr('cy', d.y)
      .attr('r', baseRadius + 6)
      .attr('fill', 'none')
      .attr('stroke', '#ef4444')
      .attr('stroke-width', 2)
    function blinkLoop() {
      ring.transition().duration(100).attr('stroke-opacity', 0.2)
        .transition().duration(100).attr('stroke-opacity', 1)
        .on('end', blinkLoop)
    }
    blinkLoop()
  })

  node.on('mouseover', (e, hn) => link.attr('stroke-opacity', l => l.source.id === hn.id || l.target.id === hn.id ? 1 : 0.1))
  node.on('mouseout', () => link.attr('stroke-opacity', 0.6))

  const text = g.append('g')
    .selectAll('text')
    .data(nodes.value)
    .enter().append('text')
    .text(d => d.name)
    .attr('font-size', 12)
    .attr('dx', 12).attr('dy', 4)

  simulation.on('tick', () => {
    link.attr('x1', d => d.source.x).attr('y1', d => d.source.y).attr('x2', d => d.target.x).attr('y2', d => d.target.y)
    node.attr('cx', d => d.x).attr('cy', d => d.y)
    text.attr('x', d => d.x).attr('y', d => d.y)
    // 拖拽同步红圈位置
if (selectedNode.value) {
  const baseRadius = 6 + selectedNode.value.linkCount + 6;
  // 新增：动态计算半径，除以 currentK 实现“反向补偿”
  const effectiveRadius = Math.max(baseRadius, selectedNodeMinRadius / currentK);
  
  ringGroup.select('.selected-ring')
    .attr('cx', selectedNode.value.x)
    .attr('cy', selectedNode.value.y)
    .attr('r', effectiveRadius); // 应用新半径
}
  })

  setTimeout(() => resetZoom(), 300)
}

// 拖拽事件
function dragstarted(event, d) { if (!event.active) simulation.alphaTarget(0.3).restart(); d.fx = d.x; d.fy = d.y }
function dragged(event, d) { d.fx = event.x; d.fy = event.y }
function dragended(event, d) { if (!event.active) simulation.alphaTarget(0); d.fx = null; d.fy = null }
function openNote() { alert(`打开笔记：${selectedNode.value.name}`) }

onMounted(() => {
  nextTick(() => {
    initChart()
    svg?.on('click', () => { closeContextMenu(); clearSelected() })
  })
  // 定义全局点击判断函数
  docClickHandler = (e) => {
    const menuDom = document.querySelector('[data-context-menu]')
    if (menuDom && menuDom.contains(e.target)) return
    closeContextMenu()
  }
  document.addEventListener('click', docClickHandler)
})

onUnmounted(() => {
  clearTimeout(resizeTimer)
  simulation?.stop()
  // 精准解绑同一个监听函数，杜绝残留
  if (docClickHandler) {
    document.removeEventListener('click', docClickHandler)
  }
})

window.addEventListener('resize', () => {
  clearTimeout(resizeTimer)
  resizeTimer = setTimeout(() => { initChart(); setTimeout(resetZoom, 200) }, 200)
})
</script>