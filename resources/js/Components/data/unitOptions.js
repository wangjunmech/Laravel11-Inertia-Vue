export const unitGroupList = [
    {
        label: 'qty.数量类',
        key: 'num',
        children: [
            { label: 'PCS(个)', key: 'pcs' },
            { label: 'SET(套)', key: 'set' },
            { label: 'Pair(对 / 双)', key: 'pair' },
            { label: 'Assy(组件)', key: 'ass' },
        ]
    },
    {
        label: 'weight.重量类',
        key: 'weight',
        children: [
            { label: 'KG(千克)', key: 'kg' },
            { label: 'G(克)', key: 'g' },
            { label: 'T(吨)', key: 't' },
        ]
    },
    {
        label: 'length.长度类',
        key: 'length',
        children: [
            { label: 'M(米)', key: 'm' },
            { label: 'CM(厘米)', key: 'cm' },
            { label: 'MM(毫米)', key: 'mm' },
        ]
    },
    {
        label: 'area.面积类',
        key: 'area',
        children: [
            { label: '㎡(平方米)', key: 'm2' },
            { label: 'c㎡(平方厘米)', key: 'cm2' },
        ]
    }
]