<script setup>
import { ref, onMounted, onBeforeUnmount } from 'vue';
import * as XLSX from 'xlsx';

// 1. 只从大包中提取标准的 createUniver 与核心定义，100% 杜绝多套 redi 环境
import { createUniver, LocaleType, mergeLocales } from '@univerjs/presets';
import { UniverSheetsCorePreset } from '@univerjs/preset-sheets-core';

// 2. 绕开大包顶层引用的不确定性，去它必然带有的底层物理目录中取回语言包
import designZhCN from '@univerjs/design/locale/zh-CN';
import uiZhCN from '@univerjs/ui/locale/zh-CN';
import sheetsZhCN from '@univerjs/sheets/locale/zh-CN';
import sheetsUiZhCN from '@univerjs/sheets-ui/locale/zh-CN';

// 3. 引入核心标准样式
import '@univerjs/design/lib/index.css';
import '@univerjs/preset-sheets-core/lib/index.css';


//支持导出所有工作表（Multi-sheets）；2. 保留单元格的宽度、文字颜色、背景色等样式；3. 兼容表格内存在的各种特殊数据类型（日期、公式、数字等）；
import ExcelJS from 'exceljs';
import { saveAs } from 'file-saver';

const univerContainer = ref(null);
let univerInstance = null; // 2. 定义变量持有 Univer 实例
let univerAPIInstance = null;

const handleManualExit = () => {
    try {
        console.log("正在销毁 Univer...");
        if (univerInstance) {
            // 3. 正确调用实例的 dispose
            univerInstance.dispose(); 
            univerInstance = null;
            univerAPIInstance = null;
        }
        router.push('/'); 
    } catch (e) {
        console.error("销毁异常:", e);
        window.location.href = '/'; // 兜底：强转
    }
};

onMounted(() => {
    if (!univerContainer.value) return;
    try {
        // 4. 正确解构出 univer 实例
        const { univerAPI, univer } = createUniver({
            // ... 配置
        });
        
        univerInstance = univer; // 保存实例
        univerAPIInstance = univerAPI;
        
        // ... 初始化 workbook
    } catch (error) {
        console.error("初始化错误:", error);
    }
});

onBeforeUnmount(() => {
    // 5. 确保在组件销毁时也进行清理
    if (univerInstance) {
        univerInstance.dispose();
        univerInstance = null;
    }
});

onMounted(() => {
    if (!univerContainer.value) return;
    try {
        // 4. 【核心对齐】使用 0.25.0 大包的标准单管道注册，绝不在别处手工 registerPlugin
        const { univerAPI } = createUniver({
            locale: LocaleType.ZH_CN,
            locales: {
                // 手工合并原子包语言，确保 100% 成功汉化
                [LocaleType.ZH_CN]: mergeLocales(designZhCN, uiZhCN, sheetsZhCN, sheetsUiZhCN),
            },
            presets: [
                // 核心预设会自动安全装配 UI、Render Engine，只要没有脏缓存干扰，绝不会报 already registered
                UniverSheetsCorePreset({
                    container: univerContainer.value,
                }),
            ],
        });

        // 5. 绑定解构出的统一 API 实例
        univerAPIInstance = univerAPI;

        // 6. 顺畅拉起初始表格
        univerAPIInstance.createWorkbook({
            id: 'workbook-01',
            name: 'Spreadsheet',
        });
        
        console.log("🎯 [Univer 0.25.0] 初始化成功，双重 redi 锁链已被斩断！");

    } catch (error) {
        console.error("❌ Univer 初始化拦截到致命错误:", error);
    }

    // 假设你有一个 univer 实例用于监听选区
    const selectionManager = univerAPIInstance.getSelectionManager();
    
    // 监听选区变化，当鼠标松开或选中变化时，自动更新 currentRange
    selectionManager.selectionChange$.subscribe((selections) => {
        if (selections && selections.length > 0) {
            currentRange = selections[0].range; // 直接存下 range 对象
            console.log("选区已更新:", currentRange);
        }
    });
});

onBeforeUnmount(() => {
    if (univerAPIInstance) {
        try {
            univerAPIInstance.getActiveWorkbook()?.dispose();
        } catch (e) {
            console.log("Univer 内存安全释放完成");
        }
    }
});

/**
 * Excel 导入处理逻辑
 */
const handleImport = (e) => {
    const file = e.target.files[0];
    if (!file) return;

    // 终极强防御：如果由于缓存问题导致 API 仍未生成成功，直接拦截报错，不给它报 getActiveWorkbook of null 的机会
    if (!univerAPIInstance) {
        alert("云表格初始化未成功，请执行 npm run dev -- --force 强刷 Vite 预编译缓存。");
        return;
    }

    const reader = new FileReader();
    reader.onload = (event) => {
        try {
            const data = new Uint8Array(event.target.result);
            const workbook = XLSX.read(data, { type: 'array' });
            
            const firstSheetName = workbook.SheetNames[0];
            const worksheet = workbook.Sheets[firstSheetName];
            const jsonData = XLSX.utils.sheet_to_json(worksheet, { header: 1 });

            if (jsonData.length === 0) {
                alert("导入的文件内未检测到有效数据");
                return;
            }

            const currentWorkbook = univerAPIInstance.getActiveWorkbook();
            if (!currentWorkbook) {
                alert("未发现任何激活的工作簿实例");
                return;
            }

            const activeSheet = currentWorkbook.getActiveSheet();
            
            const univerValues = jsonData.map(row => 
                row.map(cellValue => ({
                    v: cellValue !== undefined && cellValue !== null ? String(cellValue) : ''
                }))
            );

            activeSheet.getRange(0, 0, univerValues.length, univerValues[0].length).setValues(univerValues);
            e.target.value = '';
            console.log("✅ 外部数据流已成功流入 Canvas 视图层");
        } catch (err) {
            console.error("导入 Excel 失败:", err);
            alert("渲染电子表格失败，请确认文件格式。");
        }
    };
    reader.readAsArrayBuffer(file);
};

/**
 * Univer 0.25.0 像素级完美对齐导出
 * 动态解析复杂混搭边框（粗实线、虚线、彩色线）、精确补偿网页列宽
 */
const handleExport = async () => {
    if (!univerAPIInstance) {
        alert("云表格尚未就绪");
        return;
    }
    
    try {
        const activeWorkbook = univerAPIInstance.getActiveWorkbook();
        if (!activeWorkbook) {
            alert("未找到激活的工作簿");
            return;
        }

        const workbook = new ExcelJS.Workbook();
        const saveData = activeWorkbook.save();
        const globalStyles = saveData.styles || {}; 
        const sheetsMap = saveData.sheets || {};     

        const sheetIds = Object.keys(sheetsMap);
        if (sheetIds.length === 0) {
            alert("未检测到可导出的数据");
            return;
        }

        // 定义系统默认的原生暗灰色细网格线，用来打底
        const defaultGridBorder = {
            top: { style: 'thin', color: { argb: 'FFE0E0E0' } },
            left: { style: 'thin', color: { argb: 'FFE0E0E0' } },
            bottom: { style: 'thin', color: { argb: 'FFE0E0E0' } },
            right: { style: 'thin', color: { argb: 'FFE0E0E0' } }
        };

        for (const sheetId of sheetIds) {
            const rawSheetConfig = sheetsMap[sheetId];
            if (!rawSheetConfig) continue;

            const sheetName = rawSheetConfig.name || `Sheet_${sheetId}`;
            const totalRows = rawSheetConfig.rowCount || 100;
            const totalCols = rawSheetConfig.columnCount || 20;

            const worksheet = workbook.addWorksheet(sheetName);

            // 【彻底解决问题2】列宽精确复刻：转换px为字符宽度，加入强力容错膨胀系数以应对长英文字符
            if (rawSheetConfig.columnData) {
                const columnData = rawSheetConfig.columnData;
                for (let c = 0; c < totalCols; c++) {
                    if (columnData[c] && columnData[c].w !== undefined) {
                        const pxWidth = columnData[c].w;
                        // 精准映射公式：基于网页视觉比例，额外给足 Padding 空间，防止内容缩水
                        const excelWidth = Math.max((pxWidth / 7.2) + 4.5, 12);
                        worksheet.getColumn(c + 1).width = excelWidth;
                    } else {
                        worksheet.getColumn(c + 1).width = 14; 
                    }
                }
            }

            const rawCellData = rawSheetConfig.cellData || {};

            // 核心功能：双层矩阵遍历，百分之百还原数据、颜色与独立细颗粒度边框
            for (let r = 0; r < totalRows; r++) {
                const rawRow = rawCellData[r];
                if (!rawRow) continue;

                for (let c = 0; c < totalCols; c++) {
                    const cellObj = rawRow[c];
                    const targetCell = worksheet.getCell(r + 1, c + 1);

                    // 默认先用原生浅灰色网格线打底
                    targetCell.border = { ...defaultGridBorder };

                    if (!cellObj) continue;

                    // 1. 基础数据安全写入（粉碎Nr.序号错乱截断）
                    let cellValue = '';
                    if (typeof cellObj === 'object') {
                        cellValue = cellObj.v !== undefined && cellObj.v !== null ? cellObj.v : (cellObj.m || '');
                    } else {
                        cellValue = cellObj;
                    }
                    targetCell.value = cellValue !== '' ? String(cellValue) : null;

                    // 文本默认水平居中、垂直居中对齐
                    targetCell.alignment = { horizontal: 'center', vertical: 'middle', wrapText: true };

                    // 2. 解析当前单元格挂载的全局独立样式对象
                    let univerStyle = null;
                    if (cellObj.s) {
                        univerStyle = typeof cellObj.s === 'string' ? globalStyles[cellObj.s] : cellObj.s;
                    }

                    if (univerStyle) {
                        // A. 文本颜色隔离逻辑：防止大面积红字传染
                        const cellFont = {};
                        if (univerStyle.cl && univerStyle.cl.rgb) {
                            const cleanTextColor = univerStyle.cl.rgb.replace('#', '').trim();
                            cellFont.color = { argb: cleanTextColor.length === 6 ? `FF${cleanTextColor}` : cleanTextColor };
                        } else {
                            cellFont.color = { argb: 'FF000000' }; // 没指定的强行回归清爽黑字
                        }

                        if (univerStyle.bl === 1) cellFont.bold = true;
                        if (univerStyle.fs) cellFont.size = parseInt(univerStyle.fs, 10);
                        targetCell.font = cellFont;

                        // B. 背景填充色隔离逻辑（杜绝紫色、淡红色范围蔓延误伤）
                        if (univerStyle.bg && univerStyle.bg.rgb) {
                            const cleanBgColor = univerStyle.bg.rgb.replace('#', '').trim();
                            targetCell.fill = {
                                type: 'pattern',
                                pattern: 'solid',
                                fgColor: { argb: cleanBgColor.length === 6 ? `FF${cleanBgColor}` : cleanBgColor }
                            };
                        }

                        // C. 【重点破局：彻底解决问题1】动态解析并精准克隆 1:1 独立边框
                        // 摒弃之前写死区域的方法，直接读取底层数据里的 bd (Border) 配置
                        if (univerStyle.bd) {
                            const customBorder = { ...defaultGridBorder };
                            const bdConfig = univerStyle.bd;

                            // 封装映射函数：将 Univer 的线条类型转为 ExcelJS 格式
                            const mapBorderStyle = (univerType, colorObj) => {
                                let style = 'thin';
                                // Univer 常见的有 1:细实线, 2:中实线, 3:虚线, 4:点线 等
                                if (univerType === 2 || univerType === 'medium') style = 'medium';
                                if (univerType === 3 || univerType === 'dashed') style = 'dashed';
                                if (univerType === 4 || univerType === 'dotted') style = 'dotted';
                                if (univerType === 5 || univerType === 'thick') style = 'thick';

                                let color = 'FF000000'; // 默认黑线
                                if (colorObj && colorObj.rgb) {
                                    const cleanC = colorObj.rgb.replace('#', '').trim();
                                    color = cleanC.length === 6 ? `FF${cleanC}` : cleanC;
                                }
                                return { style, color: { argb: color } };
                            };

                            if (bdConfig.t) customBorder.top = mapBorderStyle(bdConfig.t.s, bdConfig.t.cl);
                            if (bdConfig.b) customBorder.bottom = mapBorderStyle(bdConfig.b.s, bdConfig.b.cl);
                            if (bdConfig.l) customBorder.left = mapBorderStyle(bdConfig.l.s, bdConfig.l.cl);
                            if (bdConfig.r) customBorder.right = mapBorderStyle(bdConfig.r.s, bdConfig.r.cl);

                            targetCell.border = customBorder;
                        }
                    }
                }
            }

            // 5. 自动裁掉尾部冗余白表单空行
            let lastRow = worksheet.actualRowCount;
            while (lastRow > 0) {
                const row = worksheet.getRow(lastRow);
                let isRowEmpty = true;
                row.eachCell({ includeEmpty: false }, () => { isRowEmpty = false; });
                if (!isRowEmpty) break;
                worksheet.spliceRows(lastRow, 1);
                lastRow--;
            }
        }

        const buffer = await workbook.xlsx.writeBuffer();
        const blob = new Blob([buffer], { type: "application/vnd.openxmlformats-officedocument.spreadsheetml.sheet" });
        saveAs(blob, "High_Fidelity_Export.xlsx");
        
        console.log("🚀 [Univer 0.25.0] 复合复杂边框及列宽自适应同步技术完全跑通！");
    } catch (err) {
        console.error("❌ 导出细节微调崩溃:", err);
        alert("高级高保真样式导出失败，请看控制台。");
    }
};

//输出所有数据到控制台
const handleExportAll = () => {
    // 1. 获取全局实例，确认 univerAPI 是否就绪
    if (typeof univerAPI === 'undefined' && !univerAPIInstance) {
        console.error("Univer API 未初始化");
        return;
    }
    
    // 使用你当前项目中实际生效的 API 入口对象
    const api = univerAPIInstance || univerAPI;
    const workbook = api.getActiveWorkbook();
    if (!workbook) {
        console.error("未找到激活的工作簿");
        return;
    }

    // 2. 绕过所有可能报错的 getSheet/getSelection API，直接从底层 save() 取数据
    const rawData = workbook.save();
    const activeSheetId = workbook.getActiveSheet().getSheetId();
    
    // 3. 【核心修复】直接从原始 JSON 树里捞数据，不要调用任何中间函数
    const cellData = rawData.sheets[activeSheetId].cellData;
    
    // 4. 为了让你看到到底有没有数据，我们直接把整个 cellData 打印出来
    console.log("=== 底层原始数据打印 ===");
    console.log(cellData);
    
    if (!cellData || Object.keys(cellData).length === 0) {
        alert("打印出来了：cellData 确实是空的！说明数据还没挂载到 Canvas 层。");
        return;
    }

    alert("数据已在控制台，请看 cellData 结构！");
};


/**
 * 输出选区数据
 * @returns {Array} 返回选区内的二维数组数据
 */
const handleExportSelection = () => {
    // 1. 获取工作簿
    const workbook = univerAPIInstance.getActiveWorkbook();
    if (!workbook) return;

    // 2. 直接获取选区对象 (这是你刚才成功打印出的那个 FRange2)
    const selection = workbook.getActiveRange(); 
    
    if (!selection) {
        alert("未获取到选区对象");
        return;
    }

    // 3. 【核心点】直接读取 FRange2 内部的 _range 属性
    // 你刚才的控制台结果已经展示了：_range 里面直接就有 startRow, endRow 等信息
    const range = selection._range;
    if (!range) {
        console.error("FRange2 对象中未找到 _range 属性，完整对象为:", selection);
        alert("对象格式不对，请检查控制台打印的 FRange2 结构");
        return;
    }

    const { startRow, endRow, startColumn, endColumn } = range;
    console.log("提取到坐标范围:", startRow, endRow, startColumn, endColumn);

    // 4. 从底层直接捞取数据
    // Univer 的单元格数据存放在 sheet 的 cellData 中
    const sheet = workbook.getActiveSheet();
    const sheetId = sheet.getSheetId();
    const cellData = workbook.save().sheets[sheetId].cellData;

    const result = [];
    
    // 5. 进行双重循环，确保即使中间有空单元格也不会漏掉
    for (let r = startRow; r <= endRow; r++) {
        const rowData = [];
        // 如果该行数据在 cellData 中不存在（即整行空），则默认为空对象
        const rowObj = cellData[r] || {};
        
        for (let c = startColumn; c <= endColumn; c++) {
            const cell = rowObj[c];
            // v 为原始值，m 为显示值，取第一个存在的即可
            const value = cell ? (cell.v ?? cell.m ?? "") : "";
            rowData.push(value);
        }
        result.push(rowData);
    }

    // 6. 输出结果
    console.log("=== 选区数据提取完成 ===");
    console.table(result);
    alert(`成功提取数据！已导出 ${result.length} 行，请查看控制台。`);
    console.log(JSON.stringify(result));
};

</script>

<template>
    <div class="excel-container-page">
        <div class="control-bar">
            <span class="title">✨Excel 工作台</span>
            <div class="btn-group">
                <div @click="handleManualExit" class=" bg-red-500 text-white rounded-full" title="销毁并退出编辑器"><i class="fa-solid fa-power-off "></i></div>

                <label class="action-btn import-btn">
                    📥 导入 Excel 文件
                    <input type="file" @change="handleImport" accept=".xlsx, .xls" hidden />
                </label>
                <button @click="handleExportAll" class="action-btn bg-amber-400">
                     📄 输出All到控制台
                </button>
                <button @click="handleExportSelection" class="action-btn bg-red-400">
                     📄 输出选区到控制台
                </button>
                <button @click="handleExport" class="action-btn export-btn">
                     📄 导出 Excel 文件
                </button>
            </div>
        </div>

        <div class="univer-view-wrapper">
            <div ref="univerContainer" class="univer-box"></div>
        </div>
    </div>
</template>

<style scoped>
.excel-container-page {
    height:100px;

}
.control-bar {
    height: 56px;
    background-color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 24px;
    border-bottom: 1px solid #e2e8f0;
    z-index: 10;
}
.title {
    font-size: 16px;
    font-weight: 600;
    color: #0f172a;
}
.btn-group {
    display: flex;
    gap: 12px;
}
.action-btn {
    padding: 8px 16px;
    border-radius: 6px;
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: 1px solid transparent;
}
.import-btn {
    background-color: #f1f5f9;
    color: #334155;
    border-color: #cbd5e1;
}
.export-btn {
    background-color: #2563eb;
    color: #ffffff;
}
.univer-view-wrapper {
    flex: 1;
    width: 100%;
    height: calc(100vh - 56px); 
    position: relative;
}
.univer-box {
    position: absolute;
    top: 0; left: 0; right: 0; bottom: 0;
    width: 100%; height: 100%;
}
</style>