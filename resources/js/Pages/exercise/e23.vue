<template>
  <div class="tree-container ">
    <div class="tree-header mb-6">
      <h2 class="text-2xl font-bold">制程产品编号生成器</h2>
    </div>
    <div>规则数位说明：       
      <span class="r1" :class="{ active: hoverClass === 'r1' }">(2位)字母</span>+ 
      <span class="r2" :class="{ active: hoverClass === 'r2' }"> (N位)字母</span>+ 
      <span class="r3" :class="{ active: hoverClass === 'r3' }"> (N位)数字</span>+ 
      <span class="r4" :class="{ active: hoverClass === 'r4' }"> (1或2位)字母</span>+ 
      <span class="r5" :class="{ active: hoverClass === 'r5' }"> (N位)数字</span>+ 
      <span class="r6" :class="{ active: hoverClass === 'r6' }">  （S*）</span>+ 
      <span class="r7" :class="{ active: hoverClass === 'r7' }"> （C*）</span>+ 
      <span class="r7" :class="{ active: hoverClass === 'r8' }"> (6位)数字</span>+ 
      <span class="r8" :class="{ active: hoverClass === 'r9' }"> （F**）</span>+ 
      <span class="r9" :class="{ active: hoverClass === 'r10' }">F</span></div>
<div>
  编码示例，光标解释：
  <span 
    class="r1"
    @mouseenter="hoverClass = 'r1'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r1' }"
  >PL</span>-
  <span 
    class="r2"
    @mouseenter="hoverClass = 'r2'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r2' }"
  >LLDPE</span>-
  <span 
    class="r3"
    @mouseenter="hoverClass = 'r3'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r3' }"
  >7CM</span>-
  <span 
    class="r4"
    @mouseenter="hoverClass = 'r4'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r4' }"
  >26G</span>-
  <span 
    class="r5"
    @mouseenter="hoverClass = 'r5'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r5' }"
  >T4</span>-
  <span 
    class="r6"
    @mouseenter="hoverClass = 'r6'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r6' }"
  >S2</span>-
  <span 
    class="r7"
    @mouseenter="hoverClass = 'r7'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r7' }"
  >C3</span>-
  <span 
    class="r8"
    @mouseenter="hoverClass = 'r8'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r8' }"
  >000001</span>-
  <span 
    class="r9"
    @mouseenter="hoverClass = 'r9'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r9' }"
  >FV2</span>-
  <span 
    class="r10"
    @mouseenter="hoverClass = 'r10'"
    @mouseleave="hoverClass = ''"
    :class="{ active: hoverClass === 'r10' }"
  >F</span>
</div>

<div>
  规则数位说明：
  <span class="r1" :class="{ active: hoverClass === 'r1' }">主材料分类</span>-
  <span class="r2" :class="{ active: hoverClass === 'r2' }">子材料分类</span>-
  <span class="r3" :class="{ active: hoverClass === 'r3' }">最大尺寸加单位</span>-
  <span class="r4" :class="{ active: hoverClass === 'r4' }">重量加单位</span>-
  <span class="r5 cursor-pointer" :class="{ active: hoverClass === 'r5' }" @click="openTolModal = true" >公差级别</span>-
  <span class="r6" :class="{ active: hoverClass === 'r6' }"  @click="openRoughModal = true">表面粗糙度要求</span>-
  <span class="r7" :class="{ active: hoverClass === 'r7' }">颜色色差级别要求</span>-
  <span class="r8" :class="{ active: hoverClass === 'r8' }">6位序列号</span>-
  <span class="r8" :class="{ active: hoverClass === 'r9' }">防火等级</span>-
  <span class="r9" :class="{ active: hoverClass === 'r10' }">食品级要求</span>
</div>
    <!-- 表单区域 -->
    <div class="space-y-4">
      <!-- 材料两级联动选择（下拉末尾增加添加分类选项） -->
      <div class="flex flex-wrap gap-4 items-center">
        <div class="flex flex-col gap-1">
          <label>材料基础分类选择：</label>
          <select
            v-model="selectedMainMaterialCode"
            class="border p-2 rounded min-w-56"
            @change="handleMainMaterialChange"
          >
            <option value="">请选择产品材料大类</option>
            <option
              v-for="m in materialCategory"
              :key="m.mcid"
              :value="m.mcode"
            >
             {{ m.mcid }} {{ m.mcateCn }} ......【{{ m.mdesc }}】
            </option>
            <!-- 末尾虚拟选项：添加分类 -->
            <option value="_add_category_main">+ 添加分类</option>
          </select>
        </div>

        <div class="flex flex-col gap-1">
          <label>材料子类别选择：</label>
          <select
            v-model="selectedSubMaterialCode"
            class="border p-2 rounded min-w-48"
            @change="handleSubMaterialChange"
          >
            <option value="">请先选择下方材料子类</option>
            <option
              v-for="sub in filterSubMaterialList"
              :key="sub.msid"
              :value="sub.mcateEn"
            >
             {{ sub.msid+'...' }} {{ sub.mcateCn }} ({{ sub.msdesc }})
            </option>
            <!-- 末尾虚拟选项：添加分类 -->
            <option value="_add_category_sub">+ 添加分类</option>
          </select>
        </div>
      </div>

      <!-- 产品尺寸区域 -->
 <!-- 产品尺寸区域 -->
<div class="flex flex-wrap gap-3 items-center">
  <span>产品外形尺寸（单位mm）, 向上取整不要小数：</span>
  <div class="flex items-center gap-1">
    <label>长</label>
    <input
      v-model.number="length"
      type="number"
      class="border p-2 rounded w-24"
      placeholder="长度"
      min="0"
      @blur="handleCeilNumber('length')"
    >
  </div>
  <div class="flex items-center gap-1">
    <label>宽</label>
    <input
      v-model.number="width"
      type="number"
      class="border p-2 rounded w-24"
      placeholder="宽度"
      min="0"
      @blur="handleCeilNumber('width')"
    >
  </div>
  <div class="flex items-center gap-1">
    <label>高</label>
    <input
      v-model.number="height"
      type="number"
      class="border p-2 rounded w-24"
      placeholder="高度"
      min="0"
      @blur="handleCeilNumber('height')"
    >
  </div>
  <button
    type="button"
    @click="openSizeModal = true"
    class="bg-blue-400 text-white rounded-lg p-2 px-4"
    title="打开剪贴板粘贴尺寸浮动窗口"
  >
    快速粘贴填写器
  </button>
</div>

      <!-- 产品重量 -->
      <div class="flex items-center gap-3">
        <span>产品重量（单位g）：</span>
        <input
          v-model.number="weight"
          type="number"
          class="border p-2 rounded w-24"
          placeholder="重量"
          min="0"
          @blur="handleCeilNumber('weight')"
        >
        >
      </div>

      <!-- 尺寸精度公差 -->
      <div class="flex items-center  gap-1">
        <label>最小公差要求,如±‌0.02填写0.02，+0/-0.02也填写0.02, 单位mm：</label>
        <input
          v-model="selectedToleranceUnit"
          type="text"
          class="border p-2 rounded w-48"
          placeholder="请输入尺寸公差要求"
        >
        <button
          type="button"
          @click="openTolModal = true"
          class="bg-blue-400 text-white rounded-lg p-2 px-4"
          title="打开公差等级参考窗口"
        >
          公司自定义公差等级参考
        </button>
        <span class="text-xs text-gray-500">换算：1mm=1000μm，1μm=1000nm</span>
      </div>

      <!-- 表面粗糙度&光泽 -->
      <div class="flex gap-3 items-center">
        <div class="flex items-center  gap-1">
          <label>表面粗糙及光泽单位：</label>
          <select v-model="selectedRoughnessUnit" class="border p-2 rounded min-w-48">
            <option value="">请选择表面粗糙度单位</option>
            <option
              v-for="item in surfaceCodeSet"
              :key="item.sId"
              :value="item.scode"
            >
              {{item.sId+': '}}{{ 'Ra '+item.ra }} ({{ item.unit }})
            </option>
             <!-- {"sId":"1","ra":"0.012","unit":"µm"}, -->
          </select>
        </div>
        <button
          type="button"
          @click="openRoughModal = true"
          class="bg-blue-400 text-white rounded-lg p-2 px-4"
          title="打开粗糙度参考浮动窗口"
        >
          粗糙度参考
        </button>
        <a
          href="https://astropak.com/surface-roughness-average-ra/"
          target="_blank"
          class="text-blue-600 underline text-sm"
        >
          行业粗糙度参考链接
        </a>
      </div>

      <!-- 外观颜色色差等级 -->
      <div class="flex items-center gap-1">
        <label>外观颜色色差等级：</label>
        <select v-model="selectedColorLevel" class="border p-2 rounded max-w-480">
          <option value="">请选择外观色差标准</option>
          <option
            v-for="color in colorCodeSet"
            :key="color.cId"
            :value="color.clevel"
          >
           {{color.cId+': '}} {{ color.clevel }} - {{ color.cdesc }}
          </option>
        </select>
      </div>
      <!-- 防火阻燃等级 -->
      <div class="flex items-center gap-1">
        <label>防火阻燃等级：</label>
        <select v-model="selectedFireResistanceLevel" class="border p-2 rounded max-w-480">
          <option value="">请选择防火阻燃等级</option>
          <option
            v-for="fire in fireCodeSet"
            :key="fire.fId"
            :value="fire.fcode"
          >
           {{ fire.fId+': ' }} {{ fire.flevel }} - {{ fire.cdesc }}
          </option>
        </select>
      </div>
      <div class="flex items-center gap-3"><label>食品接触材料，有食品级要求：</label>
        <input type="checkbox" v-model="isFoodSafe" class="form-checkbox">      
      </div>


      <!-- 生成料号 & 查重 -->
      <div class="flex items-center gap-3 mt-4 pt-4 border-t">
        <label class="font-semibold">自动生成产品编号：</label>
        <input
          v-model="partNumber"
          type="text"
          readonly
          class="border p-2 rounded w-80 bg-gray-50"
          placeholder="填写全部参数后自动生成料号"
        >
        <button
          type="button"
          @click="CheckPartNumberExistence"
          class="bg-amber-400 rounded-lg p-2 px-4"
        >
          检查产品编号是否重复
        </button>
      </div>
    </div>

    <!-- 弹窗1：快速粘贴填写器（带自动滚动条） -->
<div v-if="openSizeModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="openSizeModal = false">
  <!-- 外层：弹性垂直布局 + 最大屏幕高度限制 -->
  <div class="bg-white rounded-lg w-[580px] max-h-[80vh] shadow-xl relative flex flex-col" @click.stop>
    <button @click="openSizeModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-black text-xl z-10">×</button>

    <!-- 可滚动主体区域，所有工具内容放这里 -->
    <div class="p-5 overflow-y-auto flex-1 min-h-0">
      <!-- 快速粘贴填写器 工具1 -->
      <div class="bg-blue-200 p-2 rounded-lg m-2">
        <h3 class="text-lg font-bold mb-4">剪贴板尺寸粘贴：工具1，填写或粘贴符合格式的文本</h3>
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">支持格式示例：</p>
          <ul class="text-sm text-gray-600 list-disc pl-5">
            <li>100,50,20</li>
            <li>100 50 20</li>
            <li>100*50*20</li>
          </ul>
        </div>
        <textarea
          v-model="clipboardText"
          rows="4"
          class="w-full border rounded"
          placeholder="粘贴复制好的长宽高数据到这里,或按上面示例的格式填写..."
        ></textarea>
      </div>

      <!-- 快速粘贴填写器 工具2 -->
      <div class="bg-yellow-200 p-2 rounded-lg m-2">
        <h3 class="text-lg font-bold mb-4">剪贴板尺寸粘贴：工具2，支持3D-Tool的CopyInfo</h3>
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">支持3D-Tool的CopyInfo：</p>
        </div>
        <textarea
          v-model="clipboardText2"
          rows="4"
          class="w-full border rounded"
          placeholder="粘贴复制好的3D-Tool详情文本..."
        ></textarea>
      </div>

      <!-- 快速粘贴填写器 工具3 -->
      <div class="bg-green-200 p-2 rounded-lg m-2">
        <h3 class="text-lg font-bold mb-4">剪贴板尺寸粘贴：工具3，支持Sw的CopyInfo</h3>
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">支持Sw的CopyInfo：</p>
        </div>
        <textarea
          v-model="clipboardText3"
          rows="4"
          class="w-full border rounded"
          placeholder="粘贴复制好的XXX详情文本..."
        ></textarea>
      </div>

      <!-- 快速粘贴填写器 工具4 -->
      <div class="bg-orange-200 p-2 rounded-lg m-2">
        <h3 class="text-lg font-bold mb-4">剪贴板尺寸粘贴：工具4，支持Creo的CopyInfo</h3>
        <div class="mb-4">
          <p class="text-sm text-gray-600 mb-2">支持creo的CopyInfo：</p>
        </div>
        <textarea
          v-model="clipboardText4"
          rows="4"
          class="w-full border rounded"
          placeholder="粘贴复制好的XXX详情文本..."
        ></textarea>
      </div>
    </div>



        <!-- // 解析填写器中的数值按钮 -->
        <div class="flex justify-end gap-2">
          <button @click="openSizeModal = false" class="px-4 py-2 border rounded">取消</button>
          <button @click="SizeFromClipboard" class="px-4 py-2 bg-blue-500 text-white rounded">自动填充长宽高</button>
        </div>
      </div>

    </div>

    <!-- 弹窗2：粗糙度参考说明 -->
    <div v-if="openRoughModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="openRoughModal = false">
      <div class="bg-white rounded-lg w-[720px] p-5 shadow-xl relative" @click.stop>
        <button @click="openRoughModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-black text-xl">×</button>
        <h3 class="text-lg font-bold mb-4">表面粗糙度 Ra 等级参考</h3>
        <div class="text-sm space-y-3 text-gray-700">
          <div class="astro-chart-wrap overflow-x-auto my-6">
            <table class="astro-chart w-full text-sm text-gray-800">
              <thead>
                <tr><th>Ra (µin)</th><th style="background-color: lightsalmon;">Ra (µm)</th><th>RMS (µin)</th><th>Rz (µm)</th><th>Grit #</th><th style="background-color: transparent;">Typical Process / Application</th></tr>
              </thead>
              <tbody>
                <tr><td>2000</td><td>50</td><td>2200</td><td>200</td><td>—</td><td>Hot-rolled, sand-cast surfaces</td></tr>
                <tr><td>1000</td><td>25</td><td>1100</td><td>100</td><td>—</td><td>Rough machining, flame cutting</td></tr>
                <tr><td>500</td><td>12.5</td><td>550</td><td>50</td><td>—</td><td>Heavy turning, milling</td></tr>
                <tr><td>250</td><td>6.30</td><td>275</td><td>25</td><td>60</td><td>Standard machining, #1 finish (HRAP)</td></tr>
                <tr class="benchmark"><td>125</td><td>3.20</td><td>137.5</td><td>13</td><td>—</td><td>"As-machined" CNC default</td></tr>
                <tr><td>71</td><td>1.80</td><td>78</td><td>7</td><td>80</td><td class="empty">—</td></tr>
                <tr><td>63</td><td>1.60</td><td>64.3</td><td>6.3</td><td>—</td><td style="background-color: transparent;">Fine machining</td></tr>
                <tr><td>52</td><td>1.32</td><td>58</td><td>5</td><td>120</td><td class="empty">—</td></tr>
                <tr><td>42</td><td>1.06</td><td>46</td><td>4</td><td>150</td><td class="empty">—</td></tr>
                <tr class="benchmark"><td>19 – 32</td><td>0.48 – 0.80</td><td>21 – 33</td><td>1.9 – 3.2</td><td>180 – 220</td><td>#4 brushed, dairy/sanitary range</td></tr>
                <tr><td>16</td><td>0.40</td><td>17.6</td><td>1.6</td><td>—</td><td>Honing, lapping, fine polishing</td></tr>
                <tr class="benchmark"><td>15</td><td>0.38</td><td>17</td><td>1.5</td><td>240</td><td>ASME BPE SF4 (bio-pharma EP)</td></tr>
                <tr><td>12</td><td>0.30</td><td>14</td><td>1.2</td><td>320</td><td class="empty">—</td></tr>
                <tr><td>9</td><td>0.23</td><td>10</td><td>0.9</td><td>400</td><td>Fine lap, mirror prep</td></tr>
                <tr><td>8</td><td>0.20</td><td>8.8</td><td>0.8</td><td>—</td><td class="empty">—</td></tr>
                <tr><td>4</td><td>0.10</td><td style="background-color: transparent;">4.4</td><td>0.4</td><td>500</td><td class="empty">—</td></tr>
                <tr><td>2</td><td>0.05</td><td>2.2</td><td>0.2</td><td>—</td><td>Optical / mirror finish</td></tr>
                <tr class="benchmark"><td>1</td><td>0.025</td><td>1.1</td><td>0.1</td><td>—</td><td>#8 buffed mirror, lapped</td></tr>
              </tbody>
            </table>
        </div>
        </div>
        <div class="flex justify-end mt-6">
          <button @click="openRoughModal = false" class="px-4 py-2 bg-blue-500 text-white rounded">关闭</button>
        </div>
      </div>
    </div>

    <!-- 弹窗3：添加材料分类（左右分栏布局） -->
    <div v-if="openCategoryModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="closeCategoryModal">
      <div class="bg-white rounded-lg w-[900px] max-h-[80vh] shadow-xl relative flex flex-col" @click.stop>
        <button @click="closeCategoryModal" class="absolute top-3 right-3 text-gray-400 hover:text-black text-xl z-10">×</button>
        <h3 class="text-lg font-bold p-4 border-b">材料分类管理（新增大类/子分类）</h3>

        <!-- 左右分栏主体 -->
        <div class="flex flex-1 overflow-hidden">
          <!-- 左侧：一级主材料大类列表，绑定滚动ref -->
          <div ref="leftListScrollRef" class="w-1/2 border-r p-4 overflow-y-auto">
            <h4 class="font-semibold mb-3">现有一级材料大类</h4>
            <div class="space-y-2 mb-6">
              <div
                v-for="item in materialCategory"
                :key="item.mcid"
                @click="selectedModalMainMcid = item.mcid"
                class="p-2 border rounded cursor-pointer hover:bg-slate-100"
                :class="{ 'bg-blue-100 border-blue-400': selectedModalMainMcid === item.mcid }"
              >
                <div class="font-medium">{{ item.mcateCn }} ({{ item.mcode }})</div>
                <div class="text-xs text-gray-500">{{ item.mdesc }}</div>
              </div>
            </div>

            <!-- 新增一级大类表单 -->
            <div class="border-t pt-4">
              <h4 class="font-semibold mb-3">新增一级材料大类</h4>
              <div class="space-y-2">
                <div class="flex gap-2 items-center">
                  <label class="w-20 shrink-0">英文编码:</label>
                  <input v-model="newMainForm.mcode" class="border p-1.5 rounded flex-1" placeholder="如 PL、ME" maxlength="2">
                </div>
                <div class="flex gap-2 items-center">
                  <label class="w-20 shrink-0">中文名称:</label>
                  <input v-model="newMainForm.mcateCn" class="border p-1.5 rounded flex-1" placeholder="塑料">
                </div>
                <div class="flex gap-2 items-center">
                  <label class="w-20 shrink-0">英文名称:</label>
                  <input v-model="newMainForm.mcateEn" class="border p-1.5 rounded flex-1" placeholder="Plastic">
                </div>
                <div class="flex gap-2 items-center">
                  <label class="w-20 shrink-0">描述说明:</label>
                  <input v-model="newMainForm.mdesc" class="border p-1.5 rounded flex-1" placeholder="材料备注说明">
                </div>
                <button @click="submitAddMainCategory" class="w-full bg-green-500 text-white py-2 rounded mt-2">
                  提交新增一级大类
                </button>
              </div>
            </div>
          </div>

          <!-- 右侧：选中大类对应的子分类列表 + 新增子分类 -->
          <div class="w-1/2 p-4 overflow-y-auto">
            <!-- 动态显示选中大类名称（对应截图红色标注） -->
            <h4 class="font-semibold mb-3">
              子分类管理
              <span v-if="currentSelectMainName" class="text-blue-600">（归属：{{ currentSelectMainName }}）</span>
            </h4>
            <div v-if="!selectedModalMainMcid" class="text-gray-400 py-8 text-center">
              请先在左侧选择一个一级材料大类
            </div>
            <div v-else>
              <!-- 当前大类已有子分类 -->
              <div class="mb-4">
                <p class="text-sm mb-2 text-gray-600">当前大类下已有子分类：</p>
                <div class="space-y-1 max-h-40 overflow-y-auto border rounded p-2 mb-4">
                  <div
                    v-for="sub in modalFilterSubList"
                    :key="sub.msid"
                    class="text-sm p-1.5 bg-slate-50 rounded"
                  >
                    {{ sub.msid +'. '}}{{ sub.mcateCn }} ({{ sub.mcateEn }}) - {{ sub.msdesc }}
                  </div>
                </div>
              </div>

              <!-- 新增子分类表单 -->
              <div class="border-t pt-4">
                <h4 class="font-semibold mb-3">新增子分类（归属左侧选中大类）</h4>
                <div class="space-y-2">
                  <div class="flex gap-2 items-center">
                    <label class="w-20 shrink-0">中文名称:</label>
                    <input v-model="newSubForm.mcateCn" class="border p-1.5 rounded flex-1" placeholder="材料中文名称">
                  </div>
                  <div class="flex gap-2 items-center">
                    <label class="w-20 shrink-0">英文编码:</label>
                    <input v-model="newSubForm.mcateEn" class="border p-1.5 rounded flex-1" placeholder="材料英文编码">
                  </div>
                  <div class="flex gap-2 items-center">
                    <label class="w-20 shrink-0">描述:</label>
                    <input v-model="newSubForm.msdesc" class="border p-1.5 rounded flex-1" placeholder="材料描述说明">
                  </div>
                  <button @click="submitAddSubCategory" class="w-full bg-blue-500 text-white py-2 rounded mt-2">
                    提交新增子分类
                  </button>
                </div>
              </div>
            </div>
          </div>
        </div>

        <!-- 底部关闭按钮 -->
        <div class="p-4 border-t flex justify-end">
          <button @click="closeCategoryModal" class="px-6 py-2 border rounded">关闭</button>
        </div>
      </div>
    </div>

    
    <!-- 弹窗4：公差等级参考说明 -->
    <div v-if="openTolModal" class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center" @click.self="openTolModal = false">
      <div class="bg-white rounded-lg w-[720px] p-5 shadow-xl relative" @click.stop>
        <button @click="openTolModal = false" class="absolute top-3 right-3 text-gray-400 hover:text-black text-xl">×</button>
        <h3 class="text-lg font-bold mb-4">公司自定义公差等级参考</h3>
        <div class="text-sm space-y-3 text-gray-700">
          <div class="astro-chart-wrap overflow-x-auto my-6">
            <table class="astro-chart w-full text-sm text-gray-800">
              <thead>
                <!-- "tId":"1","tcode":"T1","tnum":"±0.001mm","desc" -->
                <tr><th>序号</th><th>公差等级</th><th>尺寸范围(mm)</th><th>公差应用说明</th></tr>
              </thead>
              <tbody>
                <tr v-for="it in tolCodeSet" :key="it.tId" class="benchmark">
                  <td>{{ it.tId }}</td>
                  <td>{{ it.tcode }}</td>
                  <td>{{ it.tnum }}</td>
                  <td>{{ it.desc }}</td>
                </tr>      
                <!-- 可以继续添加更多行 -->
              </tbody>
            </table>
            <p>
              <a href="https://www.engineersedge.com/international_tol.htm" target="_blank" class="text-blue-500 hover:underline">国际公差 International Tolerance (IT) Grades Table Chart ISO 286-1</a>
            </p>
              <p>
              <a href="https://www.machiningdoctor.com/calculators/tolerances/fit-2/?fitid=9" target="_blank" class="text-blue-500 hover:underline">常用基孔制间隙配合H7/g6 Clearance Fit (Per ISO 286)</a>
            </p>
            </div>
        </div>
        <div class="flex justify-end mt-6">
          <button @click="openTolModal = false" class="px-4 py-2 bg-blue-500 text-white rounded">关闭</button>
        </div>
      </div>
    </div>
  </div>
</template>

<script setup>
import { ref, watch, computed, nextTick } from 'vue'
import { router } from '@inertiajs/vue3'

// ====================== 全局弹窗开关 ======================
const openSizeModal = ref(false)
const openRoughModal = ref(false)
const openTolModal = ref(false)
const openCategoryModal = ref(false)
const clipboardText = ref('')
// 【增量新增】工具2文本绑定变量
const clipboardText2 = ref('')
// 左侧列表滚动容器ref
const leftListScrollRef = ref(null)
// 标记弹窗打开来源：main 一级下拉 / sub 子分类下拉
const openFromType = ref('')
const hoverClass = ref('')
// 分类弹窗专用变量
const selectedModalMainMcid = ref('')
// 新增一级大类表单
const newMainForm = ref({
  mcode: '',
  mcateCn: '',
  mcateEn: '',
  mdesc: ''
})
// 新增子分类表单
const newSubForm = ref({
  mcateCn: '',
  mcateEn: '',
  msdesc: ''
})

// ====================== 数据源定义 ======================
// 一级材料大类
const materialCategory = ref([
  {"mcid":"1","mcode":"PL","mcateCn":"塑料","mcateEn":"Plastic","mdesc":"纯塑料制品不含胶袋薄膜等"},
  {"mcid":"2","mcode":"ME","mcateCn":"金属","mcateEn":"Metal","mdesc":"纯金属制品"},
  {"mcid":"3","mcode":"GL","mcateCn":"玻璃","mcateEn":"Glass","mdesc":"玻璃制品"},
  {"mcid":"4","mcode":"CE","mcateCn":"陶瓷","mcateEn":"Ceramic","mdesc":"陶瓷"},
  {"mcid":"5","mcode":"SI","mcateCn":"硅橡胶","mcateEn":"Silicone & Rubber","mdesc":"硅胶或橡胶制品"},
  {"mcid":"6","mcode":"WO","mcateCn":"竹木","mcateEn":"Wood","mdesc":"竹木"},
  {"mcid":"7","mcode":"CA","mcateCn":"线材绳索","mcateEn":"Cable","mdesc":"电线，绳索"},
  {"mcid":"8","mcode":"FM","mcateCn":"磁铁","mcateEn":"Ferromagnet","mdesc":"普通磁铁，特殊磁铁"},
  {"mcid":"9","mcode":"BA","mcateCn":"电池","mcateEn":"Battery","mdesc":"圆柱电池，聚合物电池"},
  {"mcid":"10","mcode":"PC","mcateCn":"电路板, 电子标签","mcateEn":"PCBA","mdesc":"各类电路板，电子元器件"},
  {"mcid":"11","mcode":"LI","mcateCn":"胶水及其它液体流体","mcateEn":"Liquid&Glue&Grease","mdesc":"螺丝胶，粘接胶，酒精，白电油,润滑油，润滑脂等"},
  {"mcid":"12","mcode":"PM","mcateCn":"包装材料","mcateEn":"Packaging","mdesc":"纸箱、胶袋、胶箱、托盘、卡板，木箱"},
  {"mcid":"13","mcode":"SM","mcateCn":"标准合成件零件","mcateEn":"Standard Mixed components","mdesc":"马达、电源接口、开关、传感器，油缸气缸,等"},
  {"mcid":"14","mcode":"SC","mcateCn":"标准材料及零配件","mcateEn":"Standard components","mdesc":"轴承，螺丝，垫圈等"},
  {"mcid":"15","mcode":"CC","mcateCn":"通用耗材","mcateEn":"common consumable material","mdesc":"标签贴纸/麦拉纸/铜版纸"}
]);

// 材料子分类（通过mcid关联一级大类）
const matSubCategory = ref([
  { "mcid": "1", "msid": "1", "mcateCn": "LDPE（低密度聚乙烯）", "mcateEn": "LDPE", "msdesc": "" },
  { "mcid": "1", "msid": "2", "mcateCn": "LLDPE（线性低密度聚乙烯）", "mcateEn": "LLDPE", "msdesc": "" },
  { "mcid": "1", "msid": "3", "mcateCn": "HDPE（高密度聚乙烯）", "mcateEn": "HDPE", "msdesc": "" },
  { "mcid": "1", "msid": "4", "mcateCn": "POE（聚烯烃弹性体）", "mcateEn": "POE", "msdesc": "" },
  { "mcid": "1", "msid": "5", "mcateCn": "PP（聚丙烯）", "mcateEn": "PP", "msdesc": "" },
  { "mcid": "1", "msid": "6", "mcateCn": "ABS（丙烯腈丁二烯苯乙烯）", "mcateEn": "ABS", "msdesc": "常用于家电外壳等" },
  { "mcid": "1", "msid": "7", "mcateCn": "MBS（甲基丙烯酸甲酯-丁二烯-苯乙烯三元共聚物，透明ABS）", "mcateEn": "MBS", "msdesc": "" },
  { "mcid": "1", "msid": "8", "mcateCn": "PVC（聚氯乙烯，软弹性体常用于玩具）", "mcateEn": "PVC", "msdesc": "" },
  { "mcid": "1", "msid": "9", "mcateCn": "K(Q)胶（苯乙烯 - 丁二烯嵌段共聚物,SBC又称丁苯透明抗冲树脂）", "mcateEn": "K(Q)", "msdesc": "" },
  { "mcid": "1", "msid": "10", "mcateCn": "EVA（乙烯-醋酸乙烯共聚物，弹性体）", "mcateEn": "EVA", "msdesc": "" },
  { "mcid": "1", "msid": "11", "mcateCn": "TPE（热塑性弹性体）", "mcateEn": "TPE", "msdesc": "" },
  { "mcid": "1", "msid": "12", "mcateCn": "TPR（热塑性弹性体）", "mcateEn": "TPR", "msdesc": "" },
  { "mcid": "1", "msid": "13", "mcateCn": "TPU（热塑性弹性体）", "mcateEn": "TPU", "msdesc": "" },
  { "mcid": "1", "msid": "14", "mcateCn": "GPPS（聚苯乙烯）", "mcateEn": "GPPS", "msdesc": "" },
  { "mcid": "1", "msid": "15", "mcateCn": "HIPS（高抗冲击聚苯乙烯）", "mcateEn": "HIPS", "msdesc": "" },
  { "mcid": "1", "msid": "16", "mcateCn": "PC（聚碳酸酯）", "mcateEn": "PC", "msdesc": "高透明抗冲击塑料" },
  { "mcid": "1", "msid": "17", "mcateCn": "PA6（聚酰胺/尼龙）", "mcateEn": "PA6", "msdesc": "" },
  { "mcid": "1", "msid": "18", "mcateCn": "PA66（聚酰胺/尼龙）", "mcateEn": "PA66", "msdesc": "" },
  { "mcid": "1", "msid": "19", "mcateCn": "PA46（聚酰胺/高温耐磨尼龙）", "mcateEn": "PA46", "msdesc": "" },
  { "mcid": "1", "msid": "20", "mcateCn": "PPA（聚邻苯二甲酰胺,耐高温尼龙）", "mcateEn": "PPA", "msdesc": "" },
  { "mcid": "1", "msid": "21", "mcateCn": "POM（聚甲醛）", "mcateEn": "POM", "msdesc": "" },
  { "mcid": "1", "msid": "22", "mcateCn": "PMMA（亚克力）", "mcateEn": "PMMA", "msdesc": "" },
  { "mcid": "1", "msid": "23", "mcateCn": "PBT（热塑性聚酯）", "mcateEn": "PBT", "msdesc": "" },
  { "mcid": "1", "msid": "24", "mcateCn": "PET（热塑性聚酯）", "mcateEn": "PET", "msdesc": "" },
  { "mcid": "1", "msid": "25", "mcateCn": "PES（聚醚砜熔融温度高320-355℃）", "mcateEn": "PES", "msdesc": "" },
  { "mcid": "1", "msid": "26", "mcateCn": "PEI（聚醚酰亚胺，熔融温度340-400℃）", "mcateEn": "PEI", "msdesc": "" },
  { "mcid": "1", "msid": "27", "mcateCn": "COC（环烯烃共聚物，高透明）", "mcateEn": "COC", "msdesc": "" },
  { "mcid": "1", "msid": "28", "mcateCn": "SAN /AS/ASA（丙烯腈-苯乙烯共聚物）", "mcateEn": "SAN/AS/ASA", "msdesc": "" },
  { "mcid": "1", "msid": "29", "mcateCn": "LCP（液晶聚合物）", "mcateEn": "LCP", "msdesc": "" },
  { "mcid": "1", "msid": "30", "mcateCn": "PTFE（聚四氟乙烯）", "mcateEn": "PTFE", "msdesc": "" },
  { "mcid": "1", "msid": "31", "mcateCn": "PFA（可溶性聚四氟乙烯）", "mcateEn": "PFA", "msdesc": "" },
  { "mcid": "1", "msid": "32", "mcateCn": "FEP（聚全氟乙丙烯）", "mcateEn": "FEP", "msdesc": "" },
  { "mcid": "1", "msid": "33", "mcateCn": "ETFE（乙烯-四氟乙烯共聚物,简称F40，俗称软玻璃）", "mcateEn": "ETFE", "msdesc": "" },
  { "mcid": "1", "msid": "34", "mcateCn": "PCTFE（聚三氟氯乙烯）", "mcateEn": "PCTFE", "msdesc": "" },
  { "mcid": "1", "msid": "35", "mcateCn": "PVDF（聚偏氟乙烯）", "mcateEn": "PVDF", "msdesc": "" },
  { "mcid": "1", "msid": "36", "mcateCn": "PVF（聚氟乙烯）", "mcateEn": "PVF", "msdesc": "" },
  { "mcid": "1", "msid": "37", "mcateCn": "PPS（聚苯硫醚）", "mcateEn": "PPS", "msdesc": "" },
  { "mcid": "1", "msid": "38", "mcateCn": "PPO（聚苯醚）", "mcateEn": "PPO", "msdesc": "" },
  { "mcid": "1", "msid": "39", "mcateCn": "PSU（聚砜）", "mcateEn": "PSU", "msdesc": "" },
  { "mcid": "1", "msid": "40", "mcateCn": "PEEK（聚醚醚酮）", "mcateEn": "PEEK", "msdesc": "" },
  { "mcid": "1", "msid": "41", "mcateCn": "*PF（热固：酚醛树脂PF）", "mcateEn": "PF", "msdesc": "" },
  { "mcid": "1", "msid": "42", "mcateCn": "*UF（热固：脲醛树脂UF）", "mcateEn": "UF", "msdesc": "" },
  { "mcid": "1", "msid": "43", "mcateCn": "*MF（热固：三聚氰胺－甲醛树脂MF）", "mcateEn": "MF", "msdesc": "" },
  { "mcid": "1", "msid": "44", "mcateCn": "*UPR（热固：不饱和聚酯树脂UPR）", "mcateEn": "UPR", "msdesc": "" },
  { "mcid": "1", "msid": "45", "mcateCn": "*EP（热固：环氧树脂EP）", "mcateEn": "EP", "msdesc": "" },
  { "mcid": "1", "msid": "46", "mcateCn": "*SI（热固：有机硅树脂SI）", "mcateEn": "SI", "msdesc": "" },
]);

// 尺寸单位列表,以mm为基础，根据产品最大尺寸超过的范围，自动生成对应的尺寸单位（mm, μm, nm）的代码
const SizeUnitList = ref([
  {"tolId":"1","uEn":"M","uCn":"米","ratio":"0.001"},
  {"tolId":"2","uEn":"DM","uCn":"分米","ratio":"0.01"},
  {"tolId":"3","uEn":"CM","uCn":"厘米","ratio":"0.1"},
  {"tolId":"4","uEn":"MM","uCn":"毫米","ratio":"1"},
  {"tolId":"5","uEn":"μm","uCn":"微米","ratio":"1000"},
  {"tolId":"6","uEn":"nm","uCn":"纳米","ratio":"1000"}
]);

//精度等级分类表
const tolCodeSet = ref([
  {"tId":"1","tcode":"T1","tnum":"±0.001mm","desc":"超高精密（微米级，量具光学件）"},
  {"tId":"2","tcode":"T2","tnum":"±0.005mm","desc":"高精密（5μm，精密模具芯子）"},
  {"tId":"3","tcode":"T3","tnum":"±0.01mm","desc":"一丝精密（定位、插件关键尺寸）"},
  {"tId":"4","tcode":"T4","tnum":"±0.02mm","desc":"两丝精密（模具型腔、精密机加件）"},
  {"tId":"5","tcode":"T5","tnum":"±0.05mm","desc":"常规精密（通用轴孔标准配合）"},
  {"tId":"6","tcode":"T6","tnum":"±0.1mm","desc":"标准间隙配合（可拆卸传动件）"},
  {"tId":"7","tcode":"T7","tnum":"±0.2mm","desc":"普通装配（箱体、支架结构件）"},
  {"tId":"8","tcode":"T8","tnum":"±0.5mm","desc":"宽松装配（钣金、次要组装尺寸）"},
  {"tId":"9","tcode":"T9","tnum":"±1.0mm","desc":"粗定位尺寸（外观底座、非功能面）"}
]);

// 表面粗糙度等级分类9级
const surfaceCodeSet = ref([
  {"sId":"1","scode":"S1","ra":"0.012以下","unit":"光学镜面，对材料及模具材质要求高以及检查需要专业仪器设备"},
  {"sId":"2","scode":"S2","ra":"0.025-0.05","unit":"精细镜面，目视50cm距离不能看到加工刀痕或划痕"},
  {"sId":"3","scode":"S3","ra":"0.05-0.2","unit":"普通镜面，目视50cm距离可有轻微划痕"},
  {"sId":"4","scode":"S4","ra":"0.2-0.8","unit":"精磨光面，不可见加工刀痕，普通慢走丝表面"},
  {"sId":"5","scode":"S5","ra":"0.8-3.2","unit":"中等光面，精加工轻微刀痕"},
  {"sId":"6","scode":"S6","ra":"3.2-6.3","unit":"普通光面，精加工刀痕明显，普通快走丝表面"},
  {"sId":"7","scode":"S7","ra":"6.3-12.5","unit":"精细粗面，加工刀痕明显或精细铸造表面"},
  {"sId":"8","scode":"S8","ra":"12.5-50","unit":"中等粗面，加工明显，粗车、粗刨、粗铣、钻孔"},
  {"sId":"9","scode":"S9","ra":"50-100","unit":"普通粗面，普通铸件表面粗糙"},
]);

// 外观色差等级，C1-C9代表逐渐降低难度
const colorCodeSet = ref([
  {"cId":"1","clevel":"C1","cdesc":"复合色（金属/珠光/渐变），色差仪测量Delta E≤0.5"},
  {"cId":"2","clevel":"C2","cdesc":"复合色（金属/珠光/渐变），色差仪测量Delta E≤1.5"},
  {"cId":"3","clevel":"C3","cdesc":"复合色（金属/珠光/渐变），色差仪测量Delta E≤5.0"},
  {"cId":"4","clevel":"C4","cdesc":"纯色，色差仪测量Delta E≤0.5"},
  {"cId":"5","clevel":"C5","cdesc":"纯色，色差仪测量Delta E≤1.0"},
  {"cId":"6","clevel":"C6","cdesc":"纯色，色差仪测量Delta E≤2.0"},
  {"cId":"7","clevel":"C7","cdesc":"目视验收，色差仪测量Delta E≤5.0"},
  {"cId":"8","clevel":"C8","cdesc":"目视验收，色差仪测量Delta E≤10.0"},
  {"cId":"9","clevel":"C9","cdesc":"目视验收，色差仪测量Delta E≤20.0"}
]);

// 防火阻燃等级UL 94 Flammability Ratings
const fireCodeSet = ref([
  {"fId":"1","fcode":"FHB","flevel":"HB","cdesc":"Slow horizontal burn; lowest safety level."},
  {"fId":"2","fcode":"FV2","flevel":"V-2","cdesc":"Vertical burn stops within 30 seconds; allows flaming drips"},
  {"fId":"3","fcode":"FV1","flevel":"V-1","cdesc":" Vertical burn stops within 30 seconds; no flaming drips"},
  {"fId":"4","fcode":"FV0","flevel":"V-0","cdesc":"Vertical burn stops within 10 seconds; no flaming drips"},
  {"fId":"5","fcode":"F5B","flevel":"5VB","cdesc":"Severe vertical burn test; stops in 60 seconds; allows burn-through holes"},
  {"fId":"6","fcode":"F5A","flevel":"5VA","cdesc":"Highest rating; stops in 60 seconds; no burn-through holes allowed"}
]);

// ====================== 表单响应式变量 ======================
const selectedMainMaterialCode = ref('')
const selectedSubMaterialCode = ref('')
const length = ref(null)
const width = ref(null)
const height = ref(null)
const weight = ref(null)
const selectedToleranceUnit = ref('')
const selectedRoughnessUnit = ref('')
const selectedColorLevel = ref('')
const partNumber = ref('')
const selectedFireResistanceLevel = ref('')
const isFoodSafe = ref(false)

// 输入框失焦自动向上取整（兼容长宽高+重量）
const handleCeilNumber = (field) => {
  let val
  // 区分四个字段
  if (field === 'length') val = length.value
  if (field === 'width') val = width.value
  if (field === 'height') val = height.value
  if (field === 'weight') val = weight.value

  // 空值、非数字不处理
  if (val === null || val === undefined || isNaN(val)) return
  // 向上取整 Math.ceil
  const ceilVal = Math.ceil(val)

  // 赋值回对应变量
  if (field === 'length') length.value = ceilVal
  if (field === 'width') width.value = ceilVal
  if (field === 'height') height.value = ceilVal
  if (field === 'weight') weight.value = ceilVal
}
// ====================== 计算属性 ======================
// 页面主下拉子材质过滤
const filterSubMaterialList = computed(() => {
  if (!selectedMainMaterialCode.value) return []
  const targetMain = materialCategory.value.find(item => item.mcode === selectedMainMaterialCode.value)
  if (!targetMain) return []
  return matSubCategory.value.filter(sub => sub.mcid === targetMain.mcid)
})

// 分类弹窗右侧子分类过滤（根据左侧选中mcid）
const modalFilterSubList = computed(() => {
  if (!selectedModalMainMcid.value) return []
  return matSubCategory.value.filter(sub => sub.mcid === selectedModalMainMcid.value)
})

// 获取当前选中大类中文名称（用于右侧标题展示）
const currentSelectMainName = computed(() => {
  if (!selectedModalMainMcid.value) return ''
  const findItem = materialCategory.value.find(m => m.mcid === selectedModalMainMcid.value)
  return findItem ? findItem.mcateCn : ''
})

// 根据输入公差数值匹配对应T等级编码
const getToleranceTCode = (inputVal) => {
  if (!inputVal || isNaN(Number(inputVal))) return ''
  const inputNum = Number(inputVal)
  // 把tolCodeSet转成带纯数字公差值的数组
  const list = tolCodeSet.value.map(item => {
    const num = parseFloat(item.tnum.replace(/[±mm]/g, ''))
    return { ...item, val: num }
  })
  // 筛选所有公差基准 ≤ 输入值的等级
  const matchArr = list.filter(i => i.val <= inputNum)
  if (matchArr.length === 0) return ''
  // 按公差从小到大排序，取最接近输入值的一档
  matchArr.sort((a, b) => a.val - b.val)
  return matchArr.at(-1).tcode
}

// 切换主大类清空子选择
watch(selectedMainMaterialCode, () => {
  selectedSubMaterialCode.value = ''
})

// 监听弹窗打开，执行滚动/选中逻辑
watch(openCategoryModal, async (val) => {
  if (val) {
    await nextTick()
    // 从一级下拉打开：滚动到底部新增大类区域
    if (openFromType.value === 'main') {
      if (leftListScrollRef.value) {
        leftListScrollRef.value.scrollTop = leftListScrollRef.value.scrollHeight
      }
    }
    // 从子分类下拉打开：自动选中当前已选主材料
    if (openFromType.value === 'sub' && selectedMainMaterialCode.value) {
      const findMain = materialCategory.value.find(m => m.mcode === selectedMainMaterialCode.value)
      if (findMain) {
        selectedModalMainMcid.value = findMain.mcid
      }
    }
  }
})

// 监听表单自动生成料号
watch(
  [
    selectedMainMaterialCode,
    selectedSubMaterialCode,
    length,
    width,
    height,
    weight,
    selectedToleranceUnit,
    selectedRoughnessUnit,
    selectedColorLevel,
    selectedFireResistanceLevel,
    isFoodSafe
  ],
  () => generatePartNumber(),
  { deep: true }
)


// ====================== 下拉切换事件 ======================
// 一级材料下拉 change
const handleMainMaterialChange = () => {
  if (selectedMainMaterialCode.value === '_add_category_main') {
    openFromType.value = 'main'
    openCategoryModal.value = true
    selectedMainMaterialCode.value = ''
  }
}

// 子材料下拉 change
const handleSubMaterialChange = () => {
  if (selectedSubMaterialCode.value === '_add_category_sub') {
    openFromType.value = 'sub'
    openCategoryModal.value = true
    selectedSubMaterialCode.value = ''
  }
}

// 根据重量返回带单位的字符串
const formatWeightUnit = (weightVal) => {
  let num, unitEn;
  if (!weightVal || isNaN(weightVal)) return ''
    if (weightVal >= 1000) {
        num = Math.ceil(weightVal / 1000);
        unitEn = 'KG';
      } else {
        num = weightVal;
        unitEn = 'G';
  }
  return `${num}${unitEn}`
}
// 根据最大毫米尺寸自动换算单位并返回格式化字符串
const formatMaxSizeUnit = (maxMm) => {
  let num, unitEn;
  if (maxMm >= 1000) {
    num = Math.ceil(maxMm / 1000);
    unitEn = 'M';
  } else if (maxMm >= 100) {
    num = Math.ceil(maxMm / 100);
    unitEn = 'DM';
  } else if (maxMm >= 10) {
    num = Math.ceil(maxMm / 10);
    unitEn = 'CM';
  } else {
    num = maxMm;
    unitEn = 'MM';
  }
  return `${num}${unitEn}`;
};

// ====================== 业务方法 ======================
// 生成料号
const generatePartNumber = () => {
  const segments = []
  if (selectedMainMaterialCode.value) segments.push(selectedMainMaterialCode.value)
  if (selectedSubMaterialCode.value) segments.push(selectedSubMaterialCode.value)

  // 处理长宽高：过滤有效数字、从大到小排序
  if (length.value && width.value && height.value) {
    // 转数字并过滤空值
    const sizeArr = [Number(length.value), Number(width.value), Number(height.value)]
      .filter(num => !isNaN(num) && num > 0)
    // 长宽高从大到小排序
    sizeArr.sort((a, b) => b - a)
    // 长宽高最大值
    const maxVal = sizeArr[0]
    // 中间值
    const midVal = sizeArr[1]
    // 最小值
    const minVal = sizeArr[2]
    // 拼接 大*中*小

    const maxSizeCode = formatMaxSizeUnit(maxVal)
    segments.push(maxSizeCode)
  }

  // if (weight.value) segments.push(`${weight.value}g`)
  if (weight.value) segments.push(formatWeightUnit(weight.value))
  // 公差：自动转换为T1/T2…编码，不再直接填入数字
  if (selectedToleranceUnit.value) {
    const tCode = getToleranceTCode(selectedToleranceUnit.value)
    if (tCode) segments.push(tCode)
  }
  if (selectedRoughnessUnit.value) segments.push(selectedRoughnessUnit.value)
  if (selectedColorLevel.value) segments.push(selectedColorLevel.value)
  if (selectedFireResistanceLevel.value) segments.push(selectedFireResistanceLevel.value)
  if (isFoodSafe.value) segments.push('F')
  
  partNumber.value = segments.join('-')//编号组成连接符
}

// 尺寸粘贴解析【增量修改，新增工具2解析逻辑】
const SizeFromClipboard = () => {
  // 优先解析工具2（3D-Tool CopyInfo文本）
  const tool2Text = clipboardText2.value.trim()
  if (tool2Text) {
    // 正则匹配 X[mm]: / Y[mm]: / Z[mm]: 后面的数字
    const regX = /X\[mm\]:\s*([\d.]+)/
    const regY = /Y\[mm\]:\s*([\d.]+)/
    const regZ = /Z\[mm\]:\s*([\d.]+)/
    const matchX = tool2Text.match(regX)
    const matchY = tool2Text.match(regY)
    const matchZ = tool2Text.match(regZ)

    if (matchX && matchY && matchZ) {
      length.value = Math.ceil(Number(matchX[1]))
      width.value = Math.ceil(Number(matchY[1]))
      height.value = Math.ceil(Number(matchZ[1]))
      // 清空两个输入框文本
      clipboardText.value = ''
      clipboardText2.value = ''
      openSizeModal.value = false
      alert('3D-Tool尺寸解析完成，已自动填充长宽高！')
      return
    } else {
      alert('工具2文本未识别到完整X/Y/Z尺寸，请检查粘贴内容！')
      return
    }
  }

  // 工具2无内容，走原有工具1解析逻辑
  const clipText = clipboardText.value.trim()
  if (!clipText) {
    alert('请在工具1或工具2粘贴尺寸数据！')
    return
  }
  const nums = clipText.replace(/[ ,*、]/g, ' ').trim().split(/\s+/).map(Number)
  if (nums.length >= 3 && !isNaN(nums[0]) && !isNaN(nums[1]) && !isNaN(nums[2])) {
    length.value = Math.ceil(nums[0])
    width.value = Math.ceil(nums[1])
    height.value = Math.ceil(nums[2])
    clipboardText.value = ''
    clipboardText2.value = ''
    openSizeModal.value = false
  } else {
    alert('工具1识别失败，请输入三组数字长、宽、高')
  }
}

// 料号查重
const CheckPartNumberExistence = () => {
  if (!partNumber.value) {
    alert('请先完善参数生成产品编号')
    return
  }
  router.get('/api/part-number/check', {
    number: partNumber.value
  }, {
    preserveScroll: true,
    onSuccess: (res) => {
      if (res.exists) alert(`警告：编号 ${partNumber.value} 已存在系统中！`)
      else alert(`编号 ${partNumber.value} 可用，无重复`)
    },
    onError: (err) => alert('校验接口请求失败：' + JSON.stringify(err))
  })
}

// 关闭分类弹窗，清空所有状态
const closeCategoryModal = () => {
  openCategoryModal.value = false
  openFromType.value = ''
  selectedModalMainMcid.value = ''
  newMainForm.value = { mcode: '', mcateCn: '', mcateEn: '', mdesc: '' }
  newSubForm.value = { mcateCn: '', mcateEn: '', msdesc: '' }
}

// 提交新增一级大类（前端模拟新增）
const submitAddMainCategory = () => {
  const { mcode, mcateCn, mcateEn, mdesc } = newMainForm.value
  if (!mcode || !mcateCn || !mcateEn) {
    alert('编码、中文名称、英文名称不能为空！')
    return
  }
  const newMcid = String(Math.max(...materialCategory.value.map(i => Number(i.mcid))) + 1)
  materialCategory.value.push({
    mcid: newMcid,
    mcode: mcode.toUpperCase(),
    mcateCn,
    mcateEn,
    mdesc
  })
  alert('一级材料大类新增成功！')
  newMainForm.value = { mcode: '', mcateCn: '', mcateEn: '', mdesc: '' }
}

// 提交新增子分类
const submitAddSubCategory = () => {
  if (!selectedModalMainMcid.value) {
    alert('请先在左侧选择归属的一级材料大类！')
    return
  }
  const { mcateCn, mcateEn, msdesc } = newSubForm.value
  if (!mcateCn || !mcateEn) {
    alert('子分类中文名称、英文编码不能为空！')
    return
  }
  const newMsid = String(Math.max(...matSubCategory.value.map(i => Number(i.msid))) + 1)
  matSubCategory.value.push({
    mcid: selectedModalMainMcid.value,
    msid: newMsid,
    mcateCn,
    mcateEn,
    msdesc
  })
  alert('子分类新增成功！')
  newSubForm.value = { mcateCn: '', mcateEn: '', msdesc: '' }
}
</script>

<style scoped>
.tree-container {
  box-sizing: border-box;
}
input::-webkit-outer-spin-button,
input::-webkit-inner-spin-button {
  -webkit-appearance: none;
  margin: 0;
}
input[type="number"] {
  -moz-appearance: textfield;
}


/* 表格基础合并边框 */
.astro-chart {
  @apply border-collapse;
}

/* 所有单元格统一边框+内边距 */
.astro-chart th,
.astro-chart td {
  @apply border border-gray-300 px-3 py-2;
}

/* 表头样式：灰底加粗 */
.astro-chart th {
  @apply bg-gray-100 font-semibold;
}

/* 前五列数值居中，最后一列左对齐 */
.astro-chart td:not(:last-child) {
  @apply text-center;
}

/* benchmark 基准行浅蓝高亮 */
.astro-chart .benchmark {
  @apply bg-sky-50;
}

.active {
  background-color: #ff5804ee;
}
</style>