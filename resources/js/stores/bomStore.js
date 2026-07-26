import { defineStore } from 'pinia'
import { cloneDeep } from 'lodash'

export const useBomStore = defineStore('bom', {
    state: () => ({
        bomTree: [],          // 当前编辑树形草稿
        originTree: [],       // 后端原始数据（对比差异用）
        materialList: [],     // 全局物料档案列表
        curVersionId: null,   // 当前编辑版本ID
        isEdited: false       // 是否存在未保存修改
    }),
    actions: {
        // 初始化页面数据
        initData(tree, versionId, materials) {
            this.bomTree = cloneDeep(tree)
            this.originTree = cloneDeep(tree)
            this.curVersionId = versionId
            this.materialList = materials
            this.isEdited = false
        },

        // ✅ 正确：直接写在 actions 的第一层级
        updateTree(newTree) {
            if (JSON.stringify(this.bomTree) !== JSON.stringify(newTree)) {
                this.bomTree = newTree
                this.isEdited = true
            } else {
                this.bomTree = newTree
            }
        },

        // 清空缓存
        reset() {
            this.$reset()
        }
    },
    persist: true // 开启localStorage持久化，刷新页面草稿不丢失
})