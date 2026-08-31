<template>
  <div class="p-1 grid grid-cols-2 gap-1">
    <!-- 左栏：卡板堆箱子 -->
    <div class="border border-slate-300 rounded-xl p-1">
      <div class="text-lg font-medium">卡板堆箱模拟</div>

      <div class="flex ">
        <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-1 rounded-md">
          <input type="color" v-model="bgColor" @input="boxViewer.updateBackground(bgColor)" class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0" />
          <span>背景颜色</span>
        </div>
        <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-1 rounded-md">
          <input type="color" v-model="pColor" @input="boxViewer.buildScene" class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0" />
          <span>箱子颜色</span>
        </div>
      </div>

      <div class="w-full bg-slate-200 mb-2 flex items-center gap-2 rounded-md px-2 border border-slate-400">
        <span class="text-sm whitespace-nowrap">箱子透明度</span>
        <input type="range" min="0.2" max="1" step="0.05" v-model.number="opacity" @input="boxViewer.buildScene" class="flex-1 cursor-pointer" />
        <span class="text-xs w-8 text-right">{{ (opacity * 100).toFixed(0) }}%</span>
      </div>

      <div class="flex flex-wrap m-1 border border-black items-center rounded p-1">
        <span class="font-medium text-sm">卡板尺寸(mm)</span>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">长</label><input type="number" v-model.number="palletLength" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">宽</label><input type="number" v-model.number="palletWidth" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">垫高</label><input type="number" v-model.number="palletHeight" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">最大堆高</label><input type="number" v-model.number="maxStackHeight" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
      </div>

      <div class="flex flex-wrap gap-3 m-1 border border-black items-center rounded p-1">
        <span class="font-medium text-sm">箱子尺寸(mm)</span>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">长</label><input type="number" v-model.number="boxLength" min="1" step="1" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">宽</label><input type="number" v-model.number="boxWidth" min="1" step="1" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">高</label><input type="number" v-model.number="boxHeight" min="1" step="1" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">箱数</label><input type="number" v-model.number="boxCount" min="1" step="1" class="w-24 m-1 border rounded px-2 py-1" /></div>
      </div>

      <div class="flex flex-wrap gap-3 mb-3 text-sm bg-slate-100 rounded p-2 border border-slate-300">
        <div>方向：<span class="font-medium">{{ boxViewer.layout.value.rotated ? '已旋转90°' : '默认' }}</span></div>
        <div>每层：<span class="font-medium">{{ boxViewer.perLayer.value }}</span> 个</div>
        <div>可堆：<span class="font-medium">{{ boxViewer.maxLayers.value }}</span> 层</div>
        <div>可堆箱数：<span class="font-medium">{{ boxViewer.capacity.value }}</span> 个</div>
        <div>实际摆放：<span class="font-medium">{{ boxViewer.placedCount.value }}</span> / {{ boxViewer.capacity.value }}（堆箱数）</div>
        <div>利用率：<span class="font-medium">{{ boxViewer.utilization.value }}%</span></div>
      </div>

      <div :ref="el => (boxViewer.viewerRef.value = el)" class="w-full h-80 bg-slate-200 rounded-xl overflow-hidden relative"></div>
    </div>

    <!-- 右栏：卡板堆货柜 -->
    <div class="border border-slate-300 rounded-xl p-1">
      <div class="text-lg font-medium m-1">集装箱装载模拟</div>
      <div class="flex flex-wrap gap-1 mb-1">
        <span>选择货柜尺寸规格：</span>
        <button
          v-for="preset in containerPresets"
          :key="preset.name"
          @click="applyContainerPreset(preset)"
            class="px-3 py-1 text-sm bg-slate-300 hover:bg-slate-400 rounded-md"
            :class="selectedPresetName === preset.name
                ? 'bg-slate-400 border-2 border-red-500'
                : 'bg-slate-300 hover:bg-slate-400 border-transparent'"
        >
          {{ preset.name }}
        </button>
      </div>

      <div class="flex gap-1 m-1">
        <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-1 rounded-md">
          <input type="color" v-model="containerBgColor" @input="containerViewer.updateBackground(containerBgColor)" class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0" />
          <span>背景颜色</span>
        </div>
        <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-1 rounded-md">
          <input type="color" v-model="palletColor" @input="containerViewer.buildScene" class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0" />
          <span>卡板颜色</span>
        </div>
      </div>

      <div class="w-full bg-slate-200 mb-2 flex items-center gap-1 rounded-md px-2 border border-slate-400">
        <span class="text-sm whitespace-nowrap">卡板透明度</span>
        <input type="range" min="0.2" max="1" step="0.05" v-model.number="palletOpacity" @input="containerViewer.buildScene" class="flex-1 cursor-pointer" />
        <span class="text-xs w-8 text-right">{{ (palletOpacity * 100).toFixed(0) }}%</span>
      </div>

      <div class="flex flex-wrap m-1 border border-black items-center rounded px-2">
        <span class="font-medium text-sm">货柜内尺寸(mm)</span>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">长</label><input type="number" v-model.number="containerLength" min="1" step="10" class="w-24 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">宽</label><input type="number" v-model.number="containerWidth" min="1" step="10" class="w-24 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">高</label><input type="number" v-model.number="containerHeight" min="1" step="10" class="w-24 m-1 border rounded px-2 py-1" /></div>
      </div>

      <div class="flex flex-wrap m-1 border border-black items-center rounded px-2">
        <span class="font-medium text-sm">卡板尺寸(mm)</span>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">长</label><input type="number" v-model.number="cPalletLength" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">宽</label><input type="number" v-model.number="cPalletWidth" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">总高(含货)</label><input type="number" v-model.number="cPalletHeight" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" /></div>
        <div class="flex items-center"><label class="text-sm text-gray-600 mr-1">卡板数</label><input type="number" v-model.number="cPalletCount" min="1" step="1" class="w-16 m-1 border rounded px-2 py-1" /></div>
      </div>

      <div class="flex flex-wrap gap-3 mb-3 text-sm bg-slate-100 rounded p-2 border border-slate-300">
        <div>方向：<span class="font-medium">{{ containerViewer.layout.value.rotated ? '已旋转90°' : '默认' }}</span></div>
        <div>可放：<span class="font-medium">{{ containerViewer.perLayer.value }}</span> 板</div>
        <div>可堆：<span class="font-medium">{{ containerViewer.maxLayers.value }}</span> 层</div>
        <div>堆叠容量：<span class="font-medium">{{ containerViewer.capacity.value }}</span> 板</div>
        <div>实际摆放：<span class="font-medium">{{ containerViewer.placedCount.value }}</span> / {{ containerViewer.capacity.value}}（装板数）</div>
        <div>利用率：<span class="font-medium">{{ containerViewer.utilization.value }}%</span></div>
      </div>

      <div :ref="el => (containerViewer.viewerRef.value = el)" class="w-full h-80 bg-slate-200 rounded-xl overflow-hidden relative"></div>
    </div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import * as THREE from 'three'
import { OrbitControls } from 'three/addons/controls/OrbitControls.js'
const selectedPresetName = ref(null) // 记录当前选中的标准货柜规格
function safe(v) {
  const n = Number(v)
  return n > 0 ? n : 1
}

function createStackingViewer({ outerLength, outerWidth, outerBaseHeight, outerMaxStackHeight, itemLength, itemWidth, itemHeight, itemCount, itemColor, itemOpacity, bgColor, outerColor }) {
  const viewerRef = ref(null)
  let scene, camera, renderer, controls
  let itemGroup = null
  let outerMesh = null
  let boundaryLines = null
  let animationId = null

  const layout = computed(() => {
    const ol = safe(outerLength.value)
    const ow = safe(outerWidth.value)
    const il = safe(itemLength.value)
    const iw = safe(itemWidth.value)

    const colsA = Math.floor(ol / il)
    const rowsA = Math.floor(ow / iw)
    const countA = colsA * rowsA

    const colsB = Math.floor(ol / iw)
    const rowsB = Math.floor(ow / il)
    const countB = colsB * rowsB

    if (countB > countA) {
      return { cols: colsB, rows: rowsB, footprintX: iw, footprintZ: il, rotated: true }
    }
    return { cols: colsA, rows: rowsA, footprintX: il, footprintZ: iw, rotated: false }
  })

  const perLayer = computed(() => layout.value.cols * layout.value.rows)
  const maxLayers = computed(() => Math.floor(safe(outerMaxStackHeight.value) / safe(itemHeight.value)))
  const capacity = computed(() => perLayer.value * maxLayers.value)

  const placements = computed(() => {
    const { cols, rows, footprintX, footprintZ, rotated } = layout.value
    const layers = maxLayers.value
    const list = []
    if (cols === 0 || rows === 0 || layers === 0) return list
    const target = Math.max(0, Math.floor(itemCount.value))
    let placed = 0
    for (let layer = 0; layer < layers && placed < target; layer++) {
      for (let r = 0; r < rows && placed < target; r++) {
        for (let c = 0; c < cols && placed < target; c++) {
          list.push({ x: c * footprintX, y: layer * itemHeight.value, z: r * footprintZ, rotated })
          placed++
        }
      }
    }
    return list
  })

  const placedCount = computed(() => placements.value.length)

  const utilization = computed(() => {
    const outerVolume = safe(outerLength.value) * safe(outerWidth.value) * safe(outerMaxStackHeight.value)
    if (outerVolume === 0) return '0.0'
    const itemVolume = safe(itemLength.value) * safe(itemWidth.value) * safe(itemHeight.value)
    return ((placedCount.value * itemVolume / outerVolume) * 100).toFixed(1)
  })

  function updateBackground(colorHex) {
    if (!scene) return
    scene.background = new THREE.Color(colorHex)
  }

  function clearScene() {
    if (itemGroup) {
      itemGroup.traverse((child) => {
        if (child.isMesh || child.isLineSegments) {
          child.geometry.dispose()
          child.material.dispose()
        }
      })
      scene.remove(itemGroup)
      itemGroup = null
    }
    if (outerMesh) {
      outerMesh.geometry.dispose()
      outerMesh.material.dispose()
      scene.remove(outerMesh)
      outerMesh = null
    }
    if (boundaryLines) {
      boundaryLines.geometry.dispose()
      boundaryLines.material.dispose()
      scene.remove(boundaryLines)
      boundaryLines = null
    }
  }

  function buildScene() {
    if (!scene) return
    clearScene()

    const ol = safe(outerLength.value)
    const ow = safe(outerWidth.value)
    const obh = safe(outerBaseHeight.value)

    const outerGeo = new THREE.BoxGeometry(ol, obh, ow)
    const outerMat = new THREE.MeshPhongMaterial({ color: outerColor, transparent: true, opacity: 0.35, side: THREE.DoubleSide })
    outerMesh = new THREE.Mesh(outerGeo, outerMat)
    outerMesh.position.set(0, -obh / 2, 0)
    scene.add(outerMesh)

    const maxH = safe(outerMaxStackHeight.value)
    const boundaryGeo = new THREE.BoxGeometry(ol, maxH, ow)
    const boundaryEdges = new THREE.EdgesGeometry(boundaryGeo)
    const boundaryMat = new THREE.LineDashedMaterial({ color: 0xd85a30, dashSize: 20, gapSize: 12 })
    boundaryLines = new THREE.LineSegments(boundaryEdges, boundaryMat)
    boundaryLines.position.set(0, maxH / 2, 0)
    boundaryLines.computeLineDistances()
    scene.add(boundaryLines)

    const il = safe(itemLength.value)
    const iw = safe(itemWidth.value)
    const ih = safe(itemHeight.value)
    const { footprintX, footprintZ, rotated } = layout.value

    const group = new THREE.Group()
    const itemGeo = new THREE.BoxGeometry(il, ih, iw)
    const edgesGeo = new THREE.EdgesGeometry(itemGeo)

    placements.value.forEach((p) => {
      const material = new THREE.MeshPhongMaterial({ color: itemColor.value, transparent: true, opacity: itemOpacity.value, side: THREE.DoubleSide })
      const mesh = new THREE.Mesh(itemGeo, material)
      if (rotated) mesh.rotation.y = Math.PI / 2
      mesh.position.set(p.x + footprintX / 2 - ol / 2, p.y + ih / 2, p.z + footprintZ / 2 - ow / 2)
      group.add(mesh)

      const edgeMat = new THREE.LineBasicMaterial({ color: 0x1a3a5c })
      const edges = new THREE.LineSegments(edgesGeo, edgeMat)
      if (rotated) edges.rotation.y = Math.PI / 2
      edges.position.copy(mesh.position)
      group.add(edges)
    })

    itemGroup = group
    scene.add(itemGroup)

    const maxDim = Math.max(ol, ow, maxH)
    const dist = maxDim * 1.6 + 200
    camera.position.set(dist * 0.7, dist * 0.6, dist * 0.9)
    controls.target.set(0, maxH / 4, 0)
    controls.update()
  }

  function initThree() {
    const dom = viewerRef.value
    if (!dom) return
    const w = dom.clientWidth || 400
    const h = dom.clientHeight || 400

    scene = new THREE.Scene()
    scene.background = new THREE.Color(bgColor.value)
    camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 40000)
    renderer = new THREE.WebGLRenderer({ antialias: true })
    renderer.setSize(w, h)
    renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
    dom.appendChild(renderer.domElement)

    scene.add(new THREE.AmbientLight(0xffffff, 0.6))
    const dirLight = new THREE.DirectionalLight(0xffffff, 0.7)
    dirLight.position.set(500, 800, 500)
    scene.add(dirLight)

    controls = new OrbitControls(camera, renderer.domElement)
    controls.enableDamping = true
    controls.dampingFactor = 0.08

    buildScene()
    animate()
  }

  function animate() {
    animationId = requestAnimationFrame(animate)
    controls.update()
    renderer.render(scene, camera)
  }

  function resizeHandler() {
    if (!viewerRef.value || !renderer) return
    const w = viewerRef.value.clientWidth
    const h = viewerRef.value.clientHeight
    if (w === 0 || h === 0) return
    camera.aspect = w / h
    camera.updateProjectionMatrix()
    renderer.setSize(w, h)
  }

  function dispose() {
    cancelAnimationFrame(animationId)
    clearScene()
    if (renderer) {
      renderer.dispose()
      if (renderer.domElement?.parentNode) renderer.domElement.parentNode.removeChild(renderer.domElement)
    }
  }

  return { viewerRef, layout, perLayer, maxLayers, capacity, placements, placedCount, utilization, updateBackground, buildScene, initThree, resizeHandler, dispose }
}

const bgColor = ref('#f0f0f0')
const pColor = ref('#4a90d9')
const opacity = ref(0.85)

const palletLength = ref(1200)
const palletWidth = ref(1000)
const palletHeight = ref(150)
const maxStackHeight = ref(1500)

const boxLength = ref(300)
const boxWidth = ref(250)
const boxHeight = ref(200)
const boxCount = ref(30)

const boxViewer = createStackingViewer({
  outerLength: palletLength, outerWidth: palletWidth, outerBaseHeight: palletHeight, outerMaxStackHeight: maxStackHeight,
  itemLength: boxLength, itemWidth: boxWidth, itemHeight: boxHeight, itemCount: boxCount,
  itemColor: pColor, itemOpacity: opacity, bgColor: bgColor, outerColor: 0x9c7a4f
})

const containerBgColor = ref('#eef2f5')
const palletColor = ref('#c96a3a')
const palletOpacity = ref(0.85)

const containerLength = ref(12032)
const containerWidth = ref(2352)
const containerHeight = ref(2698)
const containerFloorThickness = ref(30)

const cPalletLength = ref(1200)
const cPalletWidth = ref(1000)
const cPalletHeight = ref(1500)
const cPalletCount = ref(20)

const containerPresets = [
  { name: '20GP', length: 5898, width: 2352, height: 2393 },
  { name: '40GP', length: 12032, width: 2352, height: 2393 },
  { name: '40HC', length: 12032, width: 2352, height: 2698 },
  { name: '45HC', length: 13556, width: 2352, height: 2698 }
]

function applyContainerPreset(preset) {
  containerLength.value = preset.length
  containerWidth.value = preset.width
  containerHeight.value = preset.height
  selectedPresetName.value = preset.name
}

const containerViewer = createStackingViewer({
  outerLength: containerLength, outerWidth: containerWidth, outerBaseHeight: containerFloorThickness, outerMaxStackHeight: containerHeight,
  itemLength: cPalletLength, itemWidth: cPalletWidth, itemHeight: cPalletHeight, itemCount: cPalletCount,
  itemColor: palletColor, itemOpacity: palletOpacity, bgColor: containerBgColor, outerColor: 0x888888
})

watch([palletLength, palletWidth, palletHeight, maxStackHeight, boxLength, boxWidth, boxHeight, boxCount], () => {
  boxViewer.buildScene()
})

watch([containerLength, containerWidth, containerFloorThickness, containerHeight, cPalletLength, cPalletWidth, cPalletHeight, cPalletCount], () => {
  containerViewer.buildScene()
})

function globalResizeHandler() {
  boxViewer.resizeHandler()
  containerViewer.resizeHandler()
}

onMounted(async () => {
  await nextTick()
  boxViewer.initThree()
  containerViewer.initThree()
  window.addEventListener('resize', globalResizeHandler)
})

onUnmounted(() => {
  window.removeEventListener('resize', globalResizeHandler)
  boxViewer.dispose()
  containerViewer.dispose()
})
</script>