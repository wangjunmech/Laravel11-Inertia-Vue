<template>
<div class="page-wrap">
  <!-- 筛选区域 -->
  <div class="filter-box">
    <div class="filter-row">
      <div class="item">
        <label>产品名称</label>
        <div class="input-inner">
          <input placeholder="请输入产品名称" />
          <span>🔍</span>
        </div>
      </div>
      <div class="item">
        <label>产品库</label>
        <select>
          <option>默认产品库</option>
        </select>
      </div>
      <div class="item">
        <label>产品类型</label>
        <select>
          <option>全部</option>
        </select>
      </div>
    </div>
    <div class="filter-row">
      <div class="item">
        <label>产品分类</label>
        <select>
          <option>全部</option>
        </select>
      </div>
      <div class="item">
        <label>产品标签</label>
        <select>
          <option>全部</option>
        </select>
      </div>
      <div class="item">
        <label>产品参数</label>
        <div class="select-inner">
          <select>
            <option>编号</option>
          </select>
          <input />
        </div>
      </div>
    </div>
  </div>

  <!-- 产品表格 -->
  <div class="table-box">
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
          <td class="name-cell">
            <div class="img-box"></div>
            <span>创意1-2mm彩色铝线设计</span>
          </td>
          <td>通用型产品</td>
          <td class="tag-col">
            <span class="add-tag">+ 添加</span>
            <div class="tag">家用铝材 <span>×</span></div>
            <div class="tag">柔性铝材 <span>×</span></div>
            <div class="tag">工业铝材 <span>×</span></div>
          </td>
          <td><div class="switch open"></div></td>
          <td><div class="switch"></div></td>
          <td>2025-04-16 10:17:52</td>
          <td><span class="add-tag">+ 添加</span></td>
          <td class="op-cell">
            <a>编辑</a>
            <a>查看</a>
            <a>推广</a>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td class="name-cell">
            <div class="img-box"></div>
            <span>不锈钢板冷拔扁钢方棒</span>
          </td>
          <td>通用型产品</td>
          <td class="tag-col">
            <span class="add-tag">+ 添加</span>
            <div class="tag">家用铝材 <span>×</span></div>
            <div class="tag">热熔铝 <span>×</span></div>
          </td>
          <td><div class="switch open"></div></td>
          <td><div class="switch"></div></td>
          <td>2025-04-16 10:17:52</td>
          <td><span class="add-tag">+ 添加</span></td>
          <td class="op-cell">
            <a>编辑</a>
            <a>查看</a>
            <a>推广</a>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td class="name-cell">
            <div class="img-box"></div>
            <span>手工细铁线</span>
          </td>
          <td>通用型产品</td>
          <td class="tag-col">
            <span class="add-tag">+ 添加</span>
            <div class="tag">柔性铝材 <span>×</span></div>
            <div class="tag">工业铝材 <span>×</span></div>
          </td>
          <td><div class="switch open"></div></td>
          <td><div class="switch"></div></td>
          <td>2025-04-16 10:17:52</td>
          <td><span class="add-tag">+ 添加</span></td>
          <td class="op-cell">
            <a>编辑</a>
            <a>查看</a>
            <a>推广</a>
          </td>
        </tr>
        <tr>
          <td><input type="checkbox"></td>
          <td class="name-cell">
            <div class="img-box"></div>
            <span>彩色不锈钢圆形单孔平盘</span>
          </td>
          <td>通用型产品</td>
          <td class="tag-col">
            <span class="add-tag">+ 添加</span>
            <div class="tag">热熔铝 <span>×</span></div>
          </td>
          <td><div class="switch open"></div></td>
          <td><div class="switch"></div></td>
          <td>2025-04-16 10:17:52</td>
          <td><span class="add-tag">+ 添加</span></td>
          <td class="op-cell">
            <a>编辑</a>
            <a>查看</a>
            <a>推广</a>
          </td>
        </tr>
      </tbody>
    </table>
  </div>

  <!-- 右侧悬浮按钮 -->
  <div class="float-btn-group">
    <div class="float-btn">▦</div>
    <div class="float-btn pink">A</div>
  </div>
</div>
</template>

<script setup></script>

<style scoped>
.page-wrap {
  flex: 1;
  padding: 16px 24px;
  overflow-y: auto;
  background: #f5f7fa;
}
.filter-box {
  background: #fff;
  padding: 16px;
  border-radius: 6px;
  border: 1px solid #e4e7ed;
  margin-bottom: 16px;
}
.filter-row {
  display: flex;
  gap: 30px;
  margin-bottom: 14px;
}
.item {
  display: flex;
  align-items: center;
  gap: 8px;
}
.item label {
  white-space: nowrap;
}
.item input, .item select {
  border: 1px solid #dcdfe6;
  padding: 6px 8px;
  border-radius: 4px;
  min-width: 160px;
}
.input-inner, .select-inner {
  display: flex;
  align-items: center;
  border: 1px solid #dcdfe6;
  border-radius: 4px;
  padding: 0 8px;
}
.input-inner input, .select-inner input {
  border: none;
  outline: none;
  padding: 6px 4px;
}
.table-box {
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
.name-cell {
  display: flex;
  align-items: center;
  gap: 8px;
}
.img-box {
  width: 32px;
  height: 32px;
  background: #eee;
  border-radius: 4px;
}
.tag-col {
  display: flex;
  flex-direction: column;
  gap: 6px;
}
.add-tag {
  border: 1px dashed #c0c4cc;
  padding: 2px 8px;
  border-radius: 3px;
  font-size: 12px;
  color: #909399;
  width: fit-content;
  cursor: pointer;
}
.tag {
  background: #f2f3f5;
  padding: 3px 8px;
  border-radius: 3px;
  font-size: 12px;
  display: flex;
  gap: 6px;
  width: fit-content;
}
.tag span:last-child {
  cursor: pointer;
  color: #999;
}
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
.op-cell a {
  color: #2563eb;
  margin: 0 4px;
  cursor: pointer;
}
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
  box-shadow: 0 2px 10px rgba(0,0,0,0.13);
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