import { defineStore } from 'pinia'

export const useAppStore = defineStore('app', {
    state: () => ({
        showFlag: false // 建议默认 false，避免首次加载就显示
    }),
    actions: {
        setShowFlag(val) {
            this.showFlag = val
        }
    }
})