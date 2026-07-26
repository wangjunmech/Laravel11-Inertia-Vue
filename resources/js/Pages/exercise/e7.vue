<template>
<div class="admin-layout-wrap">
  <!-- 1. 最左侧主导航栏【可拖拽宽度】 -->
  <div class="left-main-sidebar" :style="{width: `${mainSideWidth}px`}">
    <div class="logo-box">
      <div class="logo-icon"></div>
    </div>
    <div class="menu-list">
      <div class="menu-item">
        <span class="icon">⌂</span>
        <span class="text">概况</span>
      </div>
      <div class="menu-item">
        <span class="icon">📊</span>
        <span class="text">统计</span>
      </div>
      <div class="menu-item">
        <span class="icon">📄</span>
        <span class="text">文章</span>
      </div>
      <div class="menu-item active">
        <span class="icon">📦</span>
        <span class="text">产品</span>
      </div>
      <div class="menu-item">
        <span class="icon">📣</span>
        <span class="text">营销</span>
      </div>
      <div class="menu-item">
        <span class="icon">📝</span>
        <span class="text">表单</span>
      </div>
      <div class="menu-item">
        <span class="icon">💬</span>
        <span class="text">互动</span>
      </div>
      <div class="menu-item">
        <span class="icon">🖼️</span>
        <span class="text">图册</span>
      </div>
      <div class="menu-item">
        <span class="icon">🗄️</span>
        <span class="text">资源库</span>
      </div>
      <div class="menu-item">
        <span class="icon">💱</span>
        <span class="text">交易管理</span>
      </div>
      <div class="menu-item">
        <span class="icon">🔍</span>
        <span class="text">搜索优化</span>
      </div>
      <div class="menu-item">
        <span class="icon">🌐</span>
        <span class="text">域名管理</span>
      </div>
      <div class="menu-item">
        <span class="icon">⚙️</span>
        <span class="text">系统设置</span>
      </div>
    </div>
  </div>

  <!-- 主导航拖拽分割线 -->
  <div class="resizer-main" @mousedown="startDragMain"></div>

  <!-- 2. 中间二级侧边栏【产品子菜单】 -->
  <div class="left-sub-sidebar">
    <div class="back-top-bar">
      <span class="back-icon">←</span>
      <span>返回设计</span>
    </div>
    <div class="sub-menu-list">
      <div class="sub-menu-item active">管理产品</div>
      <div class="sub-menu-item">功能设置</div>
      <div class="sub-menu-item">添加产品</div>
      <div class="sub-menu-item">收货设置</div>
      <div class="sub-menu-item">产品参数</div>
      <div class="sub-menu-item">产品分类</div>
      <div class="sub-menu-item">产品标签</div>
    </div>
  </div>

  <!-- 3. 右侧主内容区域：产品管理页面 -->
  <div class="page-main-content">
    <!-- 面包屑 -->
    <div class="breadcrumb">
      <span>返回概况</span>
      <span>/</span>
      <span class="current">管理产品</span>
    </div>

    <!-- 顶部操作按钮栏 -->
    <div class="btn-bar">
      <div class="left-btns">
        <button class="btn-primary">添加产品</button>
        <div class="dropdown-wrap">
          <button class="btn-default">批量导入 ∨</button>
        </div>
      </div>
      <div class="right-btns">
        <a class="link-text">收起筛选项 ∧</a>
        <button class="btn-tag">AI智能翻译</button>
        <button class="btn-tag">导出数据</button>
        <div class="dropdown-wrap">
          <button class="btn-default">批量操作 ∨</button>
        </div>
        <span class="setting-icon">⚙</span>
      </div>
    </div>

    <!-- 筛选表单区域 -->
    <div class="filter-form">
      <div class="filter-row">
        <div class="filter-item">
          <label>产品名称</label>
          <div class="input-wrap">
            <input placeholder="请输入产品名称" />
            <span class="search-icon">🔍</span>
          </div>
        </div>
        <div class="filter-item">
          <label>产品库</label>
          <select>
            <option>默认产品库</option>
          </select>
        </div>
        <div class="filter-item">
          <label>产品类型</label>
          <select>
            <option>全部</option>
          </select>
        </div>
      </div>
      <div class="filter-row">
        <div class="filter-item">
          <label>产品分类</label>
          <select>
            <option>全部</option>
          </select>
        </div>
        <div class="filter-item">
          <label>产品标签</label>
          <select>
            <option>全部</option>
          </select>
        </div>
        <div class="filter-item">
          <label>产品参数</label>
          <div class="select-input-wrap">
            <select>
              <option>编号</option>
            </select>
            <input />
          </div>
        </div>
      </div>
    </div>

    <!-- 产品表格 -->
    <div class="table-wrap">
      <table class="prod-table">
        <thead>
          <tr>
            <th><input type="checkbox"></th>
            <th>产品名称 ⇅</th>
            <th>产品类型</th>
            <th>分类名称</th>
            <th>上架 ⇅</th>
            <th>置顶 ⇅</th>
            <th>录入时间</th>
            <th>标签</th>
            <th>操作</th>
          </tr>
        </thead>
        <tbody>
          <tr>
            <td><input type="checkbox"></td>
            <td class="prod-name-cell">
              <img class="prod-img" src="" alt="">
              <span>创意1-2mm彩色铝线设计</span>
            </td>
            <td>通用型产品</td>
            <td class="tag-cell">
              <span class="add-tag-btn">+ 添加</span>
              <div class="tag-item">家用铝材 <span>×</span></div>
              <div class="tag-item">柔性铝材 <span>×</span></div>
              <div class="tag-item">工业铝材 <span>×</span></div>
            </td>
            <td><div class="switch open"></div></td>
            <td><div class="switch"></div></td>
            <td>2025-04-16 10:17:52</td>
            <td><span class="add-tag-btn">+ 添加</span></td>
            <td class="operate-cell">
              <a href="#">编辑</a>
              <a href="#">查看</a>
              <a href="#">推广</a>
            </td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td class="prod-name-cell">
              <img class="prod-img" src="" alt="">
              <span>不锈钢板冷拔扁钢方棒</span>
            </td>
            <td>通用型产品</td>
            <td class="tag-cell">
              <span class="add-tag-btn">+ 添加</span>
              <div class="tag-item">家用铝材 <span>×</span></div>
              <div class="tag-item">热熔铝 <span>×</span></div>
            </td>
            <td><div class="switch open"></div></td>
            <td><div class="switch"></div></td>
            <td>2025-04-16 10:17:52</td>
            <td><span class="add-tag-btn">+ 添加</span></td>
            <td class="operate-cell">
              <a href="#">编辑</a>
              <a href="#">查看</a>
              <a href="#">推广</a>
            </td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td class="prod-name-cell">
              <img class="prod-img" src="" alt="">
              <span>手工细铁线</span>
            </td>
            <td>通用型产品</td>
            <td class="tag-cell">
              <span class="add-tag-btn">+ 添加</span>
              <div class="tag-item">柔性铝材 <span>×</span></div>
              <div class="tag-item">工业铝材 <span>×</span></div>
            </td>
            <td><div class="switch open"></div></td>
            <td><div class="switch"></div></td>
            <td>2025-04-16 10:17:52</td>
            <td><span class="add-tag-btn">+ 添加</span></td>
            <td class="operate-cell">
              <a href="#">编辑</a>
              <a href="#">查看</a>
              <a href="#">推广</a>
            </td>
          </tr>
          <tr>
            <td><input type="checkbox"></td>
            <td class="prod-name-cell">
              <img class="prod-img" src="" alt="">
              <span>彩色不锈钢圆形单孔平盘</span>
            </td>
            <td>通用型产品</td>
            <td class="tag-cell">
              <span class="add-tag-btn">+ 添加</span>
              <div class="tag-item">热熔铝 <span>×</span></div>
            </td>
            <td><div class="switch open"></div></td>
            <td><div class="switch"></div></td>
            <td>2025-04-16 10:17:52</td>
            <td><span class="add-tag-btn">+ 添加</span></td>
            <td class="operate-cell">
              <a href="#">编辑</a>
              <a href="#">查看</a>
              <a href="#">推广</a>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 右侧悬浮快捷按钮 -->
    <div class="float-btn-group">
      <div class="float-btn">▦</div>
      <div class="float-btn pink">A</div>
    </div>
  </div>
</div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue'
import { useAppStore } from '../../stores/app.js'// 直接从Store的 app.js 导入pinia
const appStore = useAppStore()
appStore.navShow = 0;//控制头部菜单显示
onUnmounted(() => {
  appStore.navShow = true;
})
// console.log(navShow);
// ========== 左侧主导航拖拽配置（顺滑同步版） ==========
const mainSideWidth = ref(Number(localStorage.getItem('mainSideWidth')) || 180)
const MAIN_MIN = 60
const MAIN_MAX = 300
let dragMain = false
let mainStartX = 0
let mainStartW = 0
let mainRAF = null

const startDragMain = (e) => {
  dragMain = true
  mainStartX = e.clientX
  mainStartW = mainSideWidth.value
  document.body.style.cursor = 'col-resize'
  document.body.style.userSelect = 'none'
}
const moveMain = (e) => {
  if (!dragMain) return
  if (mainRAF) return
  mainRAF = requestAnimationFrame(() => {
    const offset = e.clientX - mainStartX
    let w = mainStartW + offset
    w = Math.max(MAIN_MIN, Math.min(w, MAIN_MAX))
    mainSideWidth.value = w
    mainRAF = null
  })
}
const stopDragMain = () => {
  if (!dragMain) return
  dragMain = false
  cancelAnimationFrame(mainRAF)
  document.body.style.cursor = ''
  document.body.style.userSelect = ''
  localStorage.setItem('mainSideWidth', mainSideWidth.value)
  mainRAF = null
}

// 全局拖拽监听
onMounted(() => {
  document.addEventListener('mousemove', moveMain)
  document.addEventListener('mouseup', stopDragMain)
})
onUnmounted(() => {
  document.removeEventListener('mousemove', moveMain)
  document.removeEventListener('mouseup', stopDragMain)
  cancelAnimationFrame(mainRAF)
})
</script>

<style scoped>
/* 整体布局容器，全屏左右贴边无间隙 */
.admin-layout-wrap {
  display: flex;
  width: 100%;
  height: 100%;
  margin: 0;
  padding: 0;
  overflow: hidden;
  font-size: 14px;
}

/* ---------------- 最左侧主导航栏 ---------------- */
.left-main-sidebar {
  background-color: #191a1f;
  color: #fff;
  flex-shrink: 0;
  overflow-y: auto;
  will-change: width;
}
.logo-box {
  height: 64px;
  display: flex;
  align-items: center;
  justify-content: center;
  border-bottom: 1px solid #2c2d34;
}
.logo-icon {
  width: 36px;
  height: 36px;
  background: linear-gradient(135deg, #2563eb, #10b981);
  border-radius: 6px;
}
.menu-list {
  padding-top: 10px;
}
.menu-item {
  display: flex;
  align-items: center;
  gap: 10px;
  padding: 12px 18px;
  cursor: pointer;
  color: #b8b9c0;
}
.menu-item.active {
  background-color: #2c2d34;
  color: #fff;
  border-right: 3px solid #2563eb;
}
.menu-item:hover:not(.active) {
  background-color: #24252b;
  color: #fff;
}
.icon {
  font-size: 18px;
  width: 22px;
  text-align: center;
}

/* 主导航拖拽分割线 */
.resizer-main {
  width: 4px;
  background: #e4e7ed;
  cursor: col-resize;
  flex-shrink: 0;
  transition: background 0.15s;
}
.resizer-main:hover {
  background: #409eff;
}

/* ---------------- 中间二级侧边栏 ---------------- */
.left-sub-sidebar {
  width: 140px;
  background: #f7f8fa;
  border-right: 1px solid #e4e7ed;
  flex-shrink: 0;
  overflow-y: auto;
}
.back-top-bar {
  height: 64px;
  padding: 0 16px;
  display: flex;
  align-items: center;
  gap: 6px;
  border-bottom: 1px solid #e4e7ed;
  cursor: pointer;
}
.sub-menu-list {
  padding: 10px 0;
}
.sub-menu-item {
  padding: 10px 16px;
  cursor: pointer;
  color: #4e5969;
}
.sub-menu-item.active {
  background: #e8f3ff;
  color: #2563eb;
}
.sub-menu-item:hover:not(.active) {
  background: #eef0f3;
}

/* ---------------- 右侧主内容页面 ---------------- */
.page-main-content {
  flex: 1;
  background: #fff;
  overflow-y: auto;
  padding: 16px 24px;
  background-color: #f5f7fa;
}
.breadcrumb {
  font-size: 15px;
  color: #666;
  margin-bottom: 16px;
}
.breadcrumb .current {
  color: #111;
  font-weight: 500;
  margin-left: 6px;
}

.btn-bar {
  display: flex;
  justify-content: space-between;
  align-items: center;
  margin-bottom: 16px;
}
.left-btns, .right-btns {
  display: flex;
  gap: 10px;
  align-items: center;
}
.btn-primary {
  background: #2563eb;
  color: white;
  border: none;
  padding: 7px 16px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-default {
  border: 1px solid #dcdfe6;
  background: #fff;
  padding: 7px 14px;
  border-radius: 4px;
  cursor: pointer;
}
.btn-tag {
  border: 1px solid #f57224;
  color: #f57224;
  background: #fff;
  padding: 6px 12px;
  border-radius: 4px;
  cursor: pointer;
}
.link-text {
  color: #2563eb;
  cursor: pointer;
}
.setting-icon {
  font-size: 18px;
  cursor: pointer;
}

/* 筛选区域 */
.filter-form {
  background: #fff;
  padding: 16px;
  border-radius: 6px;
  margin-bottom: 16px;
  border: 1px solid #e4e7ed;
}
.filter-row {
  display: flex;
  gap: 30px;
  margin-bottom: 14px;
}
.filter-item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.filter-item label {
  white-space: nowrap;
  color: #333;
}
.filter-item input, .filter-item select {
  border: 1px solid #dcdfe6;
  padding: 6px 8px;
  border-radius: 4px;
  min-width: 160px;
}
.input-wrap, .select-input-wrap {
  display: flex;
  align-items: center;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 0 8px;
}
.input-wrap input {
  border: none;
  outline: none;
  padding: 6px 4px;
  min-width: 140px;
}
.select-input-wrap {
  gap: 4px;
}
.search-icon {
  color: #999;
}

/* 产品表格 */
.table-wrap {
  background: #fff;
  border-radius: 6px;
  border: 1px solid #e4e7ed;
}
.prod-table {
  width: 100%;
  border-collapse: collapse;
}
.prod-table th, .prod-table td {
  padding: 12px 10px;
  text-align: left;
  border-bottom: 1px solid #ebeef5;
}
.prod-table thead tr {
  background: #fafafa;
}
.prod-name-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}
.prod-img {
  width: 32px;
  height: 32px;
  background: #eee;
  border-radius: 4px;
}
.tag-cell {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.add-tag-btn {
  border: 1px dashed #c0c4cc;
  width: fit-content;
  padding: 2px 8px;
  border-radius: 3px;
  font-size: 12px;
  color: #909399;
  cursor: pointer;
}
.tag-item {
  background: #f2f3f5;
  width: fit-content;
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 12px;
  display: flex;
  gap: 6px;
}
.tag-item span:last-child {
  cursor: pointer;
  color: #999;
}
/* 开关样式 */
.switch {
  width: 36px;
  height: 18px;
  background: #dcdfe6;
  border-radius: 99px;
  position: relative;
  cursor: pointer;
}
.switch.open {
  background: #2563eb;
}
.switch::after {
  content: '';
  width: 14px;
  height: 14px;
  background: #fff;
  border-radius: 50%;
  position: absolute;
  top: 2px;
  left: 2px;
  transition: left 0.2s;
}
.switch.open::after {
  left: 20px;
}
.operate-cell a {
  color: #2563eb;
  margin: 0 4px;
  cursor: pointer;
}

/* 右侧悬浮按钮 */
.float-btn-group {
  position: fixed;
  right: 20px;
  bottom: 80px;
  display: flex;
  flex-direction: column;
  gap: 8px;
}
.float-btn {
  width: 40px;
  height: 40px;
  background: #fff;
  border-radius: 50%;
  box-shadow: 0 2px 10px #00000022;
  display: flex;
  align-items: center;
  justify-content: center;
  cursor: pointer;
}
.float-btn.pink {
  background: #f56c9c;
  color: white;
}
</style>