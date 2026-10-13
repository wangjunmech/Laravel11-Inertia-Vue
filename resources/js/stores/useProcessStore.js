import { defineStore } from 'pinia'

export const useProcessStore = defineStore('process', {
    state: () => ({
        // 产品分类树
        categoryTree: [
            { id: 'c1', label: '注塑产品', children: [{ id: 'c1-1', label: '外壳产品' }] },
            { id: 'c2', label: '机加工产品', children: [] }
        ],
        // 工艺素材模板（拖拽源，拖入画布会生成实例，不要修改模板）
        materialList: [
            { id: 'm1', type: 'injection', name: '注塑成型', params: { temp: '220', pressure: '80' } },
            { id: 'm2', type: 'drill', name: '钻孔加工', params: { speed: '3000', depth: '5' } },
            { id: 'm3', type: 'polish', name: '表面打磨', params: { grit: '800', time: '120' } }
        ],
        // G6画布数据
        graphData: {
            nodes: [],
            edges: []
        },
        // 当前选中节点
        currentNode: null
    }),
    actions: {
        setGraphData(data) {
            this.graphData = JSON.parse(JSON.stringify(data))
        },
        getGraphData() {
            return JSON.parse(JSON.stringify(this.graphData))
        },
        setCurrentNode(node) {
            this.currentNode = node
        },
        clearCanvas() {
            this.graphData = { nodes: [], edges: [] }
            this.currentNode = null
        }
    }
})
