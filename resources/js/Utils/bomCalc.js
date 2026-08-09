import { cloneDeep } from 'lodash'

/**
 * 创建BOM使用，http://localhost:8000/bom/edit/5
 * 1. 嵌套树形 扁平化一维数组（虚拟滚动必备）
 * @param {Array} tree 原始嵌套树形
 * @param {Number} depth 层级深度
 * @returns 一维数组
 */
/**
 * 树形扁平化 + 生成全局序号 + 分级BOM层级编码
 */
export function flattenTree(tree, depth = 0, arr = [], parentCode = '') {
    tree.forEach((node, index) => {
        // 1. 生成分级层级编码
        let levelCode
        if (parentCode === '') {
            // 一级根节点：index从0开始，+1 → 1,2,3...
            levelCode = String(index + 1)
        } else {
            // 子节点：父编码.当前序号  例：1.1  1.1.2
            levelCode = `${parentCode}.${index + 1}`
        }

        node.depth = depth
        node.levelCode = levelCode // 分级层级编码 存入节点
        arr.push(node)

        // 递归遍历子节点，把当前层级编码作为父编码传入
        if (node.children && node.children.length > 0) {
            flattenTree(node.children, depth + 1, arr, levelCode)
        }
    })

    // 遍历扁平化数组，追加全局流水序号
    arr.forEach((item, idx) => {
        item.rowIndex = idx + 1
    })

    return arr
}

/**
 * 2. 逐层向上归集总成本（BOM核心计算）
 * 原材料：小计=用量*(1+损耗率/100)*单价
 * 组件总成：小计=所有子节点成本总和
 */
export function calcTotalCost(tree) {
    let total = 0
    tree.forEach(node => {
        // 叶子节点 原材料计算自身成本
        if (!node.children || node.children.length === 0) {
            const realQty = Number(node.qty) * (1 + Number(node.loss_rate) / 100)
            const price = node.material?.price ?? 0
            node.subtotal = parseFloat((realQty * price).toFixed(4))
        } else {
            // 父组件累加所有子节点成本
            const childSum = calcTotalCost(node.children)
            node.subtotal = parseFloat(childSum.toFixed(4))
        }
        total += node.subtotal
    })
    return parseFloat(total.toFixed(2))
}

/**
 * 3. 对比两棵BOM树形差异（版本对比）
 */
export function diffBomTree(oldTree, newTree) {
    // 可自行扩展：标记新增/删除/修改字段
    return []
}