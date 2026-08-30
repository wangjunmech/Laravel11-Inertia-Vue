<template>
  <div class="p-4">
    <div class="text-lg font-medium mb-3">卡板堆箱自动装箱预览</div>

    <div class="flex gap-2 mb-2">
      <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
        <input
          type="color"
          v-model="bgColor"
          @input="updateBackgroundColor(bgColor)"
          class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0"
        />
        <span>背景颜色</span>
      </div>
      <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
        <input
          type="color"
          v-model="pColor"
          @input="rebuildScene"
          class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0"
        />
        <span>箱子颜色</span>
      </div>
      <div class="w-60 bg-slate-200 m-1 flex items-center gap-2 rounded-md px-2 border border-slate-400">
        <span class="text-sm whitespace-nowrap">箱子透明度</span>
        <input
          type="range"
          min="0.2"
          max="1"
          step="0.05"
          v-model.number="opacity"
          @input="rebuildScene"
          class="flex-1 cursor-pointer"
        />
        <span class="text-xs w-8 text-right">{{ (opacity * 100).toFixed(0) }}%</span>
      </div>
    </div>

    <div class="flex gap-3 mb-2 border border-black items-center rounded p-2">
      <span class="font-medium text-sm">卡板尺寸 (mm)</span>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">长</label>
        <input type="number" v-model.number="palletLength" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">宽</label>
        <input type="number" v-model.number="palletWidth" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">垫高</label>
        <input type="number" v-model.number="palletHeight" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">最大堆高（不含卡板高度）</label>
        <input type="number" v-model.number="maxStackHeight" min="1" step="10" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
    </div>

    <div class="flex gap-3 mb-3 border border-black items-center rounded p-2">
      <span class="font-medium text-sm">箱子尺寸 (mm)</span>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">长</label>
        <input type="number" v-model.number="boxLength" min="1" step="1" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">宽</label>
        <input type="number" v-model.number="boxWidth" min="1" step="1" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">高</label>
        <input type="number" v-model.number="boxHeight" min="1" step="1" class="w-20 m-1 border rounded px-2 py-1" />
      </div>
      <div class="flex items-center">
        <label class="text-sm text-gray-600 mr-1">期望数量</label>
        <input type="number" v-model.number="boxCount" min="1" step="1" class="w-24 m-1 border rounded px-2 py-1" />
      </div>
    </div>

    <div class="flex gap-4 mb-3 text-sm bg-slate-100 rounded p-2 border border-slate-300">
      <div>摆放方向：<span class="font-medium">{{ layout.rotated ? '已旋转90°（利用率更优）' : '默认方向' }}</span></div>
      <div>每层可放：<span class="font-medium">{{ perLayer }}</span> 个</div>
      <div>可堆层数：<span class="font-medium">{{ maxLayers }}</span> 层</div>
      <div>实际摆放：<span class="font-medium">{{ placedCount }}</span> / {{ boxCount }}</div>
      <div v-if="placedCount < boxCount" class="text-red-600">
        ⚠ 剩余 {{ boxCount - placedCount }} 个超出卡板容量
      </div>
      <div>体积利用率：<span class="font-medium">{{ utilization }}%</span></div>
    </div>

    <div
      ref="viewerRef"
      class="w-full h-96 bg-slate-200 rounded-xl overflow-hidden relative"
    ></div>
  </div>
</template>

<script setup>
import { ref, computed, watch, onMounted, onUnmounted, nextTick } from 'vue'
import * as THREE from 'three'
import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

const viewerRef = ref(null)

const palletLength = ref(1200)
const palletWidth = ref(1000)
const palletHeight = ref(150)
const maxStackHeight = ref(1500)

const boxLength = ref(300)
const boxWidth = ref(250)
const boxHeight = ref(200)
const boxCount = ref(30)

const bgColor = ref('#f0f0f0')
const pColor = ref('#4a90d9')
const opacity = ref(0.85)

let scene, camera, renderer, controls
let boxGroup = null
let palletMesh = null
let boundaryLines = null

function safe(v) {
  const n = Number(v)
  return n > 0 ? n : 1
}

const layout = computed(() => {
  const pl = safe(palletLength.value)
  const pw = safe(palletWidth.value)
  const bl = safe(boxLength.value)
  const bw = safe(boxWidth.value)

  const colsA = Math.floor(pl / bl)
  const rowsA = Math.floor(pw / bw)
  const countA = colsA * rowsA

  const colsB = Math.floor(pl / bw)
  const rowsB = Math.floor(pw / bl)
  const countB = colsB * rowsB

  if (countB > countA) {
    return { cols: colsB, rows: rowsB, footprintX: bw, footprintZ: bl, rotated: true }
  }
  return { cols: colsA, rows: rowsA, footprintX: bl, footprintZ: bw, rotated: false }
})

const perLayer = computed(() => layout.value.cols * layout.value.rows)

const maxLayers = computed(() => {
  return Math.floor(safe(maxStackHeight.value) / safe(boxHeight.value))
})

const placements = computed(() => {
  const { cols, rows, footprintX, footprintZ, rotated } = layout.value
  const layers = maxLayers.value

  const list = []
  if (cols === 0 || rows === 0 || layers === 0) return list

  const target = Math.max(0, Math.floor(boxCount.value))
  let placed = 0

  for (let layer = 0; layer < layers && placed < target; layer++) {
    for (let r = 0; r < rows && placed < target; r++) {
      for (let c = 0; c < cols && placed < target; c++) {
        list.push({
          x: c * footprintX,
          y: layer * boxHeight.value,
          z: r * footprintZ,
          rotated
        })
        placed++
      }
    }
  }
  return list
})

const placedCount = computed(() => placements.value.length)

const utilization = computed(() => {
  const palletVolume = safe(palletLength.value) * safe(palletWidth.value) * safe(maxStackHeight.value)
  if (palletVolume === 0) return '0.0'
  const boxVolume = safe(boxLength.value) * safe(boxWidth.value) * safe(boxHeight.value)
  const used = placedCount.value * boxVolume
  return ((used / palletVolume) * 100).toFixed(1)
})

function updateBackgroundColor(colorHex) {
  if (!scene) return
  scene.background = new THREE.Color(colorHex)
}

function clearGroup() {
  if (boxGroup) {
    boxGroup.traverse((child) => {
      if (child.isMesh) {
        child.geometry.dispose()
        child.material.dispose()
      }
      if (child.isLineSegments) {
        child.geometry.dispose()
        child.material.dispose()
      }
    })
    scene.remove(boxGroup)
    boxGroup = null
  }
  if (palletMesh) {
    palletMesh.geometry.dispose()
    palletMesh.material.dispose()
    scene.remove(palletMesh)
    palletMesh = null
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
  clearGroup()

  const pl = safe(palletLength.value)
  const pw = safe(palletWidth.value)
  const ph = safe(palletHeight.value)

  const palletGeo = new THREE.BoxGeometry(pl, ph, pw)
  const palletMat = new THREE.MeshPhongMaterial({ color: 0x9c7a4f })
  palletMesh = new THREE.Mesh(palletGeo, palletMat)
  palletMesh.position.set(0, -ph / 2, 0)
  scene.add(palletMesh)

  const boundaryGeo = new THREE.BoxGeometry(pl, safe(maxStackHeight.value), pw)
  const boundaryEdges = new THREE.EdgesGeometry(boundaryGeo)
  const boundaryMat = new THREE.LineDashedMaterial({
    color: 0xd85a30,
    dashSize: 20,
    gapSize: 12,
    linewidth: 1
  })
  boundaryLines = new THREE.LineSegments(boundaryEdges, boundaryMat)
  boundaryLines.position.set(0, safe(maxStackHeight.value) / 2, 0)
  boundaryLines.computeLineDistances()
  scene.add(boundaryLines)

  const bl = safe(boxLength.value)
  const bw = safe(boxWidth.value)
  const bh = safe(boxHeight.value)
  const { footprintX, footprintZ, rotated } = layout.value

  const group = new THREE.Group()

  const boxGeo = new THREE.BoxGeometry(bl, bh, bw)
  const edgesGeo = new THREE.EdgesGeometry(boxGeo)

  placements.value.forEach((p) => {
    const material = new THREE.MeshPhongMaterial({
      color: pColor.value,
      transparent: true,
      opacity: opacity.value,
      side: THREE.DoubleSide
    })
    const mesh = new THREE.Mesh(boxGeo, material)
    if (rotated) mesh.rotation.y = Math.PI / 2
    mesh.position.set(
      p.x + footprintX / 2 - pl / 2,
      p.y + bh / 2,
      p.z + footprintZ / 2 - pw / 2
    )
    group.add(mesh)

    const edgeMat = new THREE.LineBasicMaterial({ color: 0x1a3a5c })
    const edges = new THREE.LineSegments(edgesGeo, edgeMat)
    if (rotated) edges.rotation.y = Math.PI / 2
    edges.position.copy(mesh.position)
    group.add(edges)
  })

  boxGroup = group
  scene.add(boxGroup)

  const maxDim = Math.max(pl, pw, safe(maxStackHeight.value))
  const dist = maxDim * 1.6 + 200
  camera.position.set(dist * 0.7, dist * 0.6, dist * 0.9)
  controls.target.set(0, safe(maxStackHeight.value) / 4, 0)
  controls.update()
}

function rebuildScene() {
  buildScene()
}

function initThree() {
  const dom = viewerRef.value
  if (!dom) return

  const w = dom.clientWidth || 400
  const h = dom.clientHeight || 400

  scene = new THREE.Scene()
  scene.background = new THREE.Color(bgColor.value)

  camera = new THREE.PerspectiveCamera(45, w / h, 0.1, 20000)

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

let animationId = null
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

watch(
  [palletLength, palletWidth, palletHeight, maxStackHeight, boxLength, boxWidth, boxHeight, boxCount],
  () => {
    buildScene()
  }
)

onMounted(async () => {
  await nextTick()
  initThree()
  window.addEventListener('resize', resizeHandler)
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeHandler)
  cancelAnimationFrame(animationId)
  clearGroup()
  if (renderer) {
    renderer.dispose()
    if (renderer.domElement?.parentNode) {
      renderer.domElement.parentNode.removeChild(renderer.domElement)
    }
  }
})
</script>