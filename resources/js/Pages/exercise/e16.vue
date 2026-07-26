<template>
  <div class="flowchart-container">
    <!-- 使用 ref 绑定 SVG 元素 -->
    <svg ref="svgRef" class="flowchart-svg">
      <g ref="innerRef"></g>
    </svg>
  </div>
</template>

<script setup>
import { ref, onMounted } from 'vue';
import * as d3 from 'd3';
import dagreD3 from 'dagre-d3';

// 获取 DOM 引用
const svgRef = ref(null);
const innerRef = ref(null);

onMounted(() => {
  if (!svgRef.value || !innerRef.value) return;

  // 1. 初始化 Dagre 有向图
  const g = new dagreD3.graphlib.Graph().setGraph({
    rankdir: 'TB',     // 布局方向：TB(从上到下), LR(从左到右)
    nodesep: 50,       // 同层节点之间的间距
    ranksep: 60,       // 层与层之间的间距
    edgesep: 10,       // 连线之间的间距
  });

  // 2. 添加节点 (Node)
  // 可以根据业务需求通过 props 传入这些数据进行动态渲染
  g.setNode('start', { label: '开始', shape: 'ellipse', class: 'node-start' });
  g.setNode('process1', { label: '数据处理', shape: 'rect', class: 'node-process' });
  g.setNode('condition', { label: '是否合格？', shape: 'diamond', class: 'node-condition' });
  g.setNode('end', { label: '结束', shape: 'ellipse', class: 'node-end' });

  // 3. 添加连线 (Edge)
  g.setEdge('start', 'process1', { label: '触发', arrowhead: 'normal' });
  g.setEdge('process1', 'condition', { arrowhead: 'normal' });
  g.setEdge('condition', 'end', { label: '是', arrowhead: 'normal' });
  // 回路连线示例
  g.setEdge('condition', 'process1', { label: '否', arrowhead: 'normal' });

  // 4. 使用 D3 选择元素并配置缩放/平移 (Zoom)
  const svg = d3.select(svgRef.value);
  const inner = d3.select(innerRef.value);

  const zoom = d3.zoom().on('zoom', (event) => {
    inner.attr('transform', event.transform);
  });
  svg.call(zoom);

  // 5. 执行渲染
  const render = new dagreD3.render();
  render(inner, g);

  // 6. 居中对齐图表
  const initialScale = 0.9;
  const svgWidth = svgRef.value.clientWidth || 800;
  const svgHeight = svgRef.value.clientHeight || 500;
  // 获取 dagre 计算出来的图表总宽高
  const graphWidth = g.graph().width;
  const graphHeight = g.graph().height;

  // 计算居中坐标
  const xCenter = (svgWidth - graphWidth * initialScale) / 2;
  const yCenter = (svgHeight - graphHeight * initialScale) / 2;

  // 应用初始变换
  svg.call(
    zoom.transform,
    d3.zoomIdentity.translate(xCenter, yCenter).scale(initialScale)
  );
});
</script>

<style scoped>
.flowchart-container {
  width: 100%;
  height: 500px;
  border: 1px solid #e0e0e0;
  border-radius: 8px;
  background-color: #f9f9f9;
  overflow: hidden;
}

.flowchart-svg {
  width: 100%;
  height: 100%;
}

/* 深度选择器：由于 D3 动态生成的 DOM 不带 Vue 的 scoped 属性，必须用 :deep */
:deep(.node rect),
:deep(.node ellipse),
:deep(.node diamond) {
  stroke: #409eff;
  fill: #fff;
  stroke-width: 2px;
}

/* 针对不同类型的节点定制样式 */
:deep(.node-start ellipse) {
  fill: #f0f9eb;
  stroke: #67c23a;
}
:deep(.node-end ellipse) {
  fill: #fef0f0;
  stroke: #f56c6c;
}

:deep(.edgePath path) {
  stroke: #909399;
  stroke-width: 2px;
  fill: none;
}

/* 箭头颜色 */
:deep(.edgePath marker) {
  fill: #909399;
}

/* 连线文字样式 */
:deep(.edgeLabel text) {
  fill: #606266;
  font-size: 12px;
}
</style>
