// utils/excel.js 完整修正后代码
import * as XLSX from 'xlsx'
import { flattenTree } from './bomCalc'

// 导出BOM为Excel
export function exportBomExcel(treeData, fileName = 'BOM物料清单.xlsx') {
    const flatList = flattenTree(cloneDeep(treeData))
    const exportArr = flatList.map(item => {
        return {
            层级深度: item.depth + 1,
            物料编码: item.material?.code ?? '',
            物料名称: item.material?.name ?? '',
            规格: item.material?.spec ?? '',
            单位: item.material?.unit ?? '',
            单台用量: item.qty,
            损耗率: item.loss_rate, // 删掉%符号
            单价: item.material?.price ?? 0,
            小计成本: item.subtotal
        }
    })
    const sheet = XLSX.utils.json_to_sheet(exportArr)
    const book = XLSX.utils.book_new()
    XLSX.utils.book_append_sheet(book, sheet, 'BOM清单')
    XLSX.writeFile(book, fileName)
}