import { defineStore } from 'pinia'

export const useFileStore = defineStore('file', {
    state: () => ({
        currentParentId: 0,
        fileList: [
            { id: 1, name: '新建文件夹(1)', type: 'folder', parentId: 0 },
            { id: 2, name: '新建文件夹(2)', type: 'folder', parentId: 0 },
            { id: 3, name: '新建文件夹(3)', type: 'folder', parentId: 0 },
            { id: 4, name: '新建文件夹(4)', type: 'folder', parentId: 0 },
            { id: 5, name: '新建文件夹(5)', type: 'folder', parentId: 0 },
            { id: 6, name: '新建文件夹(6)', type: 'folder', parentId: 0 },
        ],
        selectedIds: [],
        contextMenu: {
            show: false,
            x: 0,
            y: 0,
            targetItem: null
        },
        tempMoveSingleItem: null,
        page: 1,
        pageSize: 100,
        total: 0,
        searchKey: ''
    }),
    getters: {
        rawFilterList(state) {
            let list = state.fileList.filter(item => item.parentId === state.currentParentId)
            if (state.searchKey) {
                list = list.filter(item => item.name.includes(state.searchKey))
            }
            return list
        },
        filterFileList(state) {
            const allList = this.rawFilterList
            const start = (state.page - 1) * state.pageSize
            return allList.slice(start, start + state.pageSize)
        },
        listTotal(state) {
            return this.rawFilterList.length
        },
        breadcrumbList(state) {
            const pathArr = []
            let currId = state.currentParentId
            const allFolder = state.fileList.filter(f => f.type === 'folder')
            while (true) {
                if (currId === 0) {
                    pathArr.unshift({ id: 0, name: '全部' })
                    break
                }
                const findFolder = allFolder.find(f => f.id === currId)
                if (!findFolder) {
                    pathArr.unshift({ id: 0, name: '全部' })
                    break
                }
                pathArr.unshift({
                    id: findFolder.id,
                    name: findFolder.name
                })
                currId = findFolder.parentId
            }
            return pathArr
        },
        folderTree(state) {
            const allFolders = state.fileList.filter(item => item.type === 'folder')
            const buildChildren = (parentId) => {
                return allFolders
                    .filter(f => f.parentId === parentId)
                    .map(node => ({
                        id: node.id,
                        label: node.name,
                        parentId: node.parentId,
                        children: buildChildren(node.id)
                    }))
            }
            return [
                {
                    id: 0,
                    label: '全部',
                    parentId: null,
                    children: buildChildren(0)
                }
            ]
        }
    },
    actions: {
        selectFolder(id) {
            this.currentParentId = id
            this.selectedIds = []
            this.page = 1
            this.searchKey = ''
            this.total = this.listTotal
        },
        addFolder(name) {
            const newId = Date.now()
            this.fileList.push({
                id: newId,
                name,
                type: 'folder',
                parentId: this.currentParentId
            })
            this.total = this.listTotal
        },
        deleteSelected() {
            const getAllChildIds = (pid) => {
                let ids = []
                this.fileList.forEach(f => {
                    if (f.parentId === pid && f.type === 'folder') {
                        ids.push(f.id, ...getAllChildIds(f.id))
                    }
                })
                return ids
            }
            let removeIds = []
            this.selectedIds.forEach(id => {
                removeIds.push(id, ...getAllChildIds(id))
            })
            this.fileList = this.fileList.filter(item => !removeIds.includes(item.id))
            this.selectedIds = []
            this.closeContextMenu()
            if (!this.fileList.some(f => f.id === this.currentParentId)) {
                this.currentParentId = 0
            }
            this.total = this.listTotal
        },
        openContextMenu(e, item) {
            e.preventDefault()
            this.contextMenu = {
                show: true,
                x: e.clientX,
                y: e.clientY,
                targetItem: item
            }
        },
        closeContextMenu() {
            this.contextMenu.show = false
        },
        moveItem(targetParentId) {
            const targetItem = this.contextMenu.targetItem
            if (!targetItem) return
            if (targetParentId === targetItem.id) return
            const isChildFolder = (parentId, checkId) => {
                let flag = false
                this.fileList.forEach(f => {
                    if (f.parentId === parentId && f.type === 'folder') {
                        if (f.id === checkId || isChildFolder(f.id, checkId)) flag = true
                    }
                })
                return flag
            }
            if (isChildFolder(targetItem.id, targetParentId)) return
            const idx = this.fileList.findIndex(i => i.id === targetItem.id)
            if (idx > -1) this.fileList[idx].parentId = targetParentId
            this.closeContextMenu()
            if (!this.fileList.some(f => f.id === this.currentParentId)) {
                this.currentParentId = 0
            }
            this.total = this.listTotal
        }
    }
})