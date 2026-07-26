import { defineStore } from 'pinia'

export const useAppStore = defineStore('app', {
    state: () => ({
        showFlag: false, // 建议默认 false，避免首次加载就显示,子组件角色选择下拉菜单选择后弹出修改消息，控制sessionMessage的显示，弹出的消息点击后消失
        navShow: true
    }),
    actions: {
        setShowFlag(val) {
            this.showFlag = val
        },
        setNavShow(val) {
            this.showFlag = val
        }
    }
})