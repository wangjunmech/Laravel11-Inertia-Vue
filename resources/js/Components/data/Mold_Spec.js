export const steelsData = [
    // e26 一级：基础材料大类 pid=0
    { id: 1, area: '预硬钢材', pid: 0 },
    { id: 2, area: '热处理钢材', pid: 0 },
]

export const processesData = [
    // e26 一级：基础材料大类 pid=0
    { id: 1, area: '注塑', pid: 0 },
    { id: 2, area: '吹塑', pid: 0 },
]

export const surfaceProcessesData = [
    // e26 一级：基础材料大类 pid=0
    { id: 1, area: '金属材料', pid: 0 },
    { id: 2, area: '高分子塑料', pid: 0 },
]

// 【可选】统一打包导出，一次性全部导入
export default {
    steelsData,
    processesData,
    surfaceProcessesData
}