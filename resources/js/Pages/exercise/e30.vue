<template>
  <div class="p-4">
    <div class="text-lg font-medium mb-3">ThreeJS 3D文件查看器</div>
    <div class="h-[70vh] grid grid-cols-[1fr_2fr_1fr] gap-1">
      <!-- 左侧拖拽区 -->
      <div
        class="bg-slate-300 rounded-xl p-2 flex flex-col items-center justify-center border-2 border-dashed border-slate-400 cursor-pointer transition-colors"
        :class="{ 'bg-blue-100 border-blue-500': isDragging }"
        @dragover.prevent="handleDragOver"
        @dragenter="isDragging = true"
        @dragleave="isDragging = false"
        @drop.prevent="handleFileDrop"
        @click="triggerFileInput"
      >
        <div v-if="!loading" class="text-center">
          <div class="text-lg">📁 拖拽或点击上传</div>
          <div class="text-sm text-gray-600 mt-1">
            支持格式：STEP, STP, IGS, IGES, BREP
          </div>
          <div class="text-xs text-gray-500 mt-1">
            文件大小建议 &lt; 50MB
          </div>
        </div>
        <div v-else class="text-center">
          <div class="animate-spin text-3xl">⏳</div>
          <div class="mt-2 font-medium">正在加载模型...</div>
          <div class="text-sm text-gray-600 mt-1">{{ loadingProgress }}</div>
        </div>
        <input
          ref="fileInput"
          type="file"
          accept=".step,.stp,.igs,.iges,.brep"
          class="hidden"
          @change="handleFileSelect"
        />
      </div>

      <!-- 中间ThreeJS视图容器 -->
      <div ref="viewerRef" class="bg-slate-300 rounded-xl overflow-hidden relative">
        <div 
          v-if="loading" 
          class="absolute inset-0 flex items-center justify-center bg-black/10 backdrop-blur-sm"
        >
          <div class="bg-white/90 rounded-lg p-4 shadow-lg text-center">
            <div class="animate-spin text-3xl">⏳</div>
            <div class="mt-2 font-medium">解析中...</div>
          </div>
        </div>
      </div>

      <!-- 右侧信息面板 -->
      <div class="bg-slate-300 rounded-xl p-2 overflow-auto">
        <div class="font-medium mb-2">📊 模型信息</div>
        <div v-if="!modelLoaded" class="text-gray-500 text-sm">
          暂无模型数据
        </div>
        <template v-else>
          <div class="space-y-1 text-sm">
            <div class="flex justify-between">
              <span class="text-gray-600">文件名：</span>
              <span class="font-mono">{{ currentFileName }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">格式：</span>
              <span class="font-mono">{{ currentFileExt.toUpperCase() }}</span>
            </div>
            <hr class="my-2" />
            <div class="flex justify-between">
              <span class="text-gray-600">X 尺寸：</span>
              <span>{{ info.x.toFixed(2) }} mm</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Y 尺寸：</span>
              <span>{{ info.y.toFixed(2) }} mm</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">Z 尺寸：</span>
              <span>{{ info.z.toFixed(2) }} mm</span>
            </div>
            <hr class="my-2" />
            <div class="flex justify-between">
              <span class="text-gray-600">轮廓体积（L*W*H）：</span>
              <span>{{info.volume}} cm³</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">实体体积：</span>
              <span>{{info.realVolume}} cm³</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">三角面片：</span>
              <span>{{ info.faceCount.toLocaleString() }} </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">顶点数：</span>
              <span>{{ info.vertexCount.toLocaleString() }} </span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">零部件：</span>
              <span>{{ info.partCount }}</span>
            </div>
              <div v-if="selectedFaceInfo" class="mt-2 pt-2 border-t border-slate-400">
                <div class="font-medium mb-1 text-yellow-700">🔶 选中曲面</div>
                <div class="flex justify-between">
                  <span class="text-gray-600">所属零件：</span>
                  <span>{{ selectedFaceInfo.partName }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">曲面序号：</span>
                  <span>{{ selectedFaceInfo.faceIndex }} / {{ selectedFaceInfo.totalFaces }}</span>
                </div>
                <div class="flex justify-between">
                  <span class="text-gray-600">三角面片数：</span>
                  <span>{{ selectedFaceInfo.triangleCount }}</span>
                </div>
              </div>
            <div class="flex w-24 p-2 cursor-pointer bg-red-300 hover:bg-blue-300 rounded-full" @click="copyInfo">📋复制信息</div><div v-if="showCopyTip" class="animate-blink bg-yellow-200 m-6 rounded-lg p-2">✅️已复制 !</div>
          </div>
        </template>
      </div>
    </div>
    <!-- 视图功能菜单 -->
    <div>
        <!-- 父flex：justify‑content‑center，让内部一整组居中 -->
        <div class="flex justify-center rounded-lg mt-2 bg-slate-200">
            <div class="w-24 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
                <input
                    type="color"
                    v-model="bgColor"
                    @input="updateBackgroundColor(bgColor)"
                    class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0"
                />
                <span>背景色</span>
            </div>
            <div class="w-24 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
                <input
                    type="color"
                    v-model="pColor"
                    @input="updatePartColor(pColor)"
                    class="m-1 w-1/4 h-8 rounded cursor-pointer border-0 flex-shrink-0"
                />
                <span>产品色</span>
            </div>

            <div class="w-28 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
                <input
                    type="checkbox"
                    v-model="showGrid"
                    @change="toggleGrid(showGrid)"
                    class="m-2 cursor-pointer"
                />
                <span>网格显示</span>

            </div>
            <div 
            @click=setInitView
            class="w-24 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md justify-center">
                默认视图
            </div>
            <div 
              @click="showViewMenu = !showViewMenu"
              class="w-24 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md justify-center relative">
                🧊视图方向
                
                <!-- 下拉菜单 -->
                <div 
                  v-if="showViewMenu"
                  class="absolute bottom-full left-0 mb-1 bg-blue-200 rounded-md shadow-lg z-20 w-24 py-1"
                >
                <!-- 视图方向的菜单改为向上弹出:只需要改下拉菜单那个 div 的定位 class，把 top-full 改成 bottom-full，mt-1 改成 mb-1： -->
                  <div class="px-1 py-1 hover:bg-slate-100 cursor-pointer text-sm" @click.stop="setViewDirection('top')">俯视 Top</div>
                  <div class="px-1 py-1 hover:bg-slate-100 cursor-pointer text-sm" @click.stop="setViewDirection('bottom')">仰视 Bottom</div>
                  <div class="px-1 py-1 hover:bg-slate-100 cursor-pointer text-sm" @click.stop="setViewDirection('front')">前视 Front</div>
                  <div class="px-1 py-1 hover:bg-slate-100 cursor-pointer text-sm" @click.stop="setViewDirection('back')">后视 Back</div>
                  <div class="px-1 py-1 hover:bg-slate-100 cursor-pointer text-sm" @click.stop="setViewDirection('left')">左视 Left</div>
                  <div class="px-1 py-1 hover:bg-slate-100 cursor-pointer text-sm" @click.stop="setViewDirection('right')">右视 Right</div>
                </div>
            </div>
        </div>
        <hr>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import * as THREE from 'three'
// import { OrbitControls } from 'three/addons/controls/OrbitControls.js'
import { TrackballControls } from 'three/addons/controls/TrackballControls.js'

const viewerRef = ref(null)
const fileInput = ref(null)
const info = ref({
    x: 0,
    y: 0,
    z: 0,
    volume: 0,
    realVolume: 0,
    faceCount: 0,
    vertexCount: 0,
    partCount: 0
})

const loading = ref(false)
const loadingProgress = ref('初始化引擎...')
const isDragging = ref(false)
const bgColor = ref('#f0f0f0') // 和 initThree() 里默认背景色保持一致
const pColor = ref(null)
const showGrid = ref(true) // 默认显示网格
const showViewMenu = ref(false) // 控制"视图方向"下拉菜单显示/隐藏
const selectedFaceInfo = ref(null) // 当前选中曲面的信息，用于面板显示
let currentViewDistance = 300 // 默认值，模型加载后会更新
const modelLoaded = ref(false)
const currentFileName = ref('')
const currentFileExt = ref('')
const showCopyTip = ref(false)
let copyTipTimer = null // 保存定时器引用，防止重复点击时计时器冲突
// ThreeJS 相关变量
let scene, camera, renderer, controls
let currentGroup = null
let occtInstance = null
let gridHelper = null 
const raycaster = new THREE.Raycaster()
const mouseNDC = new THREE.Vector2()
let selectedFaceMesh = null // 当前高亮曲面的覆盖网格
let frustumSize = 300 // 决定正交相机能看到的世界宽度/高度，模型加载后会自动调整
let initialCameraPosition = new THREE.Vector3(100, 80, 120) // 默认值，和 initThree() 里初始设置一致
let initialControlsTarget = new THREE.Vector3(0, 0, 0)

/**
 * 复制文本到剪贴板
 * @param {string} text
 */
async function copyToClipboard(text) {
  if (!text) return
  try {
    await navigator.clipboard.writeText(text)
    console.log("✅已复制剪贴板：", text)
    showCopyTip.value = true

    // 如果之前的计时器还没到时间，先清掉，避免连续点击时提前消失
    if (copyTipTimer) {
      clearTimeout(copyTipTimer)
    }
    copyTipTimer = setTimeout(() => {
      showCopyTip.value = false
      copyTipTimer = null
    }, 3000)
  } catch (err) {
    console.error("❌复制失败", err)
  }
}
function copyInfo() {
  if (!modelLoaded.value) {
    console.warn('⚠️ 尚未加载模型，无信息可复制')
    return
  }

  const text = [
    `文件名：${currentFileName.value}`,
    `格式：${currentFileExt.value.toUpperCase()}`,
    `X 尺寸：${info.value.x.toFixed(2)} mm`,
    `Y 尺寸：${info.value.y.toFixed(2)} mm`,
    `Z 尺寸：${info.value.z.toFixed(2)} mm`,
    `轮廓体积（L*W*H）：${(info.value.volume)} cm³`,
    `实体体积：${(info.value.realVolume)} cm³`,
    `三角面片：${Math.round(info.value.faceCount).toLocaleString()}`,
    `顶点数：${info.value.vertexCount.toLocaleString()}`,
    `零部件：${info.value.partCount}`
  ].join('\n')

  copyToClipboard(text)
}
//背景颜色
function updateBackgroundColor(colorHex) {
    console.log('Color*******')
  if (!scene) return
  scene.background = new THREE.Color(colorHex)
}
// 3D图形颜色 
function updatePartColor(colorHex) {
  if (!currentGroup) return
  
  currentGroup.traverse((child) => {
    if (child.isMesh) {
      if (Array.isArray(child.material)) {
        child.material.forEach(m => m.color.set(colorHex))
      } else {
        child.material.color.set(colorHex)
      }
    }
  })
}
//网格显示
function toggleGrid(visible) {
  if (!gridHelper) return
  gridHelper.visible = visible
}
//恢复初始视图
function setInitView() {
  if (!camera || !controls) return
  
  camera.position.copy(initialCameraPosition)
  controls.target.copy(initialControlsTarget)
  camera.updateProjectionMatrix()
  controls.update()
}

//计算网格体积
function computeMeshVolume(geometry) {
  const posAttr = geometry.attributes.position
  const index = geometry.index
  
  let volume = 0
  
  const getTriangleVolume = (p1, p2, p3) => {
    // 有符号四面体体积（原点到三角形）
    return p1.dot(p2.clone().cross(p3)) / 6.0
  }
  
  const v1 = new THREE.Vector3()
  const v2 = new THREE.Vector3()
  const v3 = new THREE.Vector3()
  
  if (index) {
    for (let i = 0; i < index.count; i += 3) {
      const a = index.getX(i)
      const b = index.getX(i + 1)
      const c = index.getX(i + 2)
      v1.fromBufferAttribute(posAttr, a)
      v2.fromBufferAttribute(posAttr, b)
      v3.fromBufferAttribute(posAttr, c)
      volume += getTriangleVolume(v1, v2, v3)
    }
  } else {
    for (let i = 0; i < posAttr.count; i += 3) {
      v1.fromBufferAttribute(posAttr, i)
      v2.fromBufferAttribute(posAttr, i + 1)
      v3.fromBufferAttribute(posAttr, i + 2)
      volume += getTriangleVolume(v1, v2, v3)
    }
  }
  
  return Math.abs(volume)
}

//设置视图方向
function setViewDirection(direction) {
  if (!camera || !controls) return
  
  const distance = currentViewDistance
  const target = controls.target.clone() // 保持当前观察目标点不变（一般是模型中心 0,0,0）
  
  const positions = {
    top:    { x: 0, y: distance, z: 0.0001 }, // z 给个极小偏移，避免 lookAt 时和 up 向量共线导致画面异常
    bottom: { x: 0, y: -distance, z: 0.0001 },
    front:  { x: 0, y: 0, z: distance },
    back:   { x: 0, y: 0, z: -distance },
    left:   { x: -distance, y: 0, z: 0 },
    right:  { x: distance, y: 0, z: 0 }
  }
  
  const pos = positions[direction]
  if (!pos) return
  
  camera.position.set(target.x + pos.x, target.y + pos.y, target.z + pos.z)
  camera.up.set(0, 1, 0) // 重置上方向，避免之前 Trackball 自由旋转后视角"歪"了
  camera.lookAt(target)
  controls.update()
  
  showViewMenu.value = false // 选完自动收起菜单
}

//曲面选择
function onCanvasClick(event) {
  if (!currentGroup || !camera || !renderer) return

  const rect = renderer.domElement.getBoundingClientRect()
  mouseNDC.x = ((event.clientX - rect.left) / rect.width) * 2 - 1
  mouseNDC.y = -((event.clientY - rect.top) / rect.height) * 2 + 1

  raycaster.setFromCamera(mouseNDC, camera)

  // 只拾取真实零件网格，排除边线(LineSegments)和已有的高亮覆盖层
  const pickables = []
  currentGroup.traverse((child) => {
    if (child.isMesh && child !== selectedFaceMesh) {
      pickables.push(child)
    }
  })

  const intersects = raycaster.intersectObjects(pickables, false)

  if (intersects.length === 0) {
    clearFaceSelection()
    return
  }

  const hit = intersects[0]
  const mesh = hit.object
  const triangleIndex = hit.faceIndex // Three.js 返回的三角形序号
  const brepFaces = mesh.userData.brepFaces

  if (!brepFaces || brepFaces.length === 0) {
    console.warn('该零件无曲面拓扑数据，无法精确选择单个曲面')
    clearFaceSelection()
    return
  }

  // 根据三角形序号反查所属曲面区间
  let faceRangeStart = 0
  let faceRangeEnd = (mesh.geometry.index.count / 3) - 1
  let faceIdx = -1

  for (let i = 0; i < brepFaces.length; i++) {
    const start = brepFaces[i].first
    const end = i + 1 < brepFaces.length ? brepFaces[i + 1].first - 1 : faceRangeEnd
    if (triangleIndex >= start && triangleIndex <= end) {
      faceRangeStart = start
      faceRangeEnd = end
      faceIdx = i
      break
    }
  }

  if (faceIdx === -1) return

  highlightFace(mesh, faceRangeStart, faceRangeEnd)

  selectedFaceInfo.value = {
    partName: mesh.userData.partName,
    faceIndex: faceIdx + 1,
    totalFaces: brepFaces.length,
    triangleCount: faceRangeEnd - faceRangeStart + 1
  }
}
//清除高亮曲面
function clearFaceSelection() {
  if (selectedFaceMesh) {
    selectedFaceMesh.geometry.dispose()
    selectedFaceMesh.material.dispose()
    selectedFaceMesh.parent?.remove(selectedFaceMesh)
    selectedFaceMesh = null
  }
  selectedFaceInfo.value = null
}
//高亮曲面
function highlightFace(mesh, triStart, triEnd) {
  clearFaceSelection()

  const sourceGeom = mesh.geometry
  const sourceIndexArr = sourceGeom.index.array
  const subIndices = sourceIndexArr.slice(triStart * 3, (triEnd + 1) * 3)

  const highlightGeom = new THREE.BufferGeometry()
  highlightGeom.setAttribute('position', sourceGeom.attributes.position) // 共用顶点数据
  highlightGeom.setIndex(new THREE.BufferAttribute(new Uint32Array(subIndices), 1))
  highlightGeom.computeVertexNormals()

  const highlightMaterial = new THREE.MeshBasicMaterial({
    color: 0xffff00,
    transparent: true,
    opacity: 0.6,
    side: THREE.DoubleSide,
    depthTest: true,
    polygonOffset: true,      // 避免和原表面 z-fighting 闪烁
    polygonOffsetFactor: -1,
    polygonOffsetUnits: -1
  })

  selectedFaceMesh = new THREE.Mesh(highlightGeom, highlightMaterial)
  mesh.add(selectedFaceMesh) // 挂在原 mesh 下面，跟随其一起变换
}
// ---------------- occt-import-js 初始化 ----------------
async function loadOcctFromCDN() {
  return new Promise((resolve, reject) => {
    if (window.occtimportjs) {
      resolve(window.occtimportjs)
      return
    }
    
    const script = document.createElement('script')
    // script.src = 'https://cdn.jsdelivr.net/npm/occt-import-js@0.0.11/dist/occt-import-js.js'
    script.src = '/occt-import-js/occt-import-js.js'
    /**注意使用本地occt-import-js需要先npm安装，然后把dist目录复制到public目录下并改名为occt-import-js目录，注意改名时中杠横线输入错误也是不行的 */
    script.onload = () => {
      if (window.occtimportjs) {
        resolve(window.occtimportjs)
      } else {
        reject(new Error('occtimportjs 加载失败'))
      }
    }
    script.onerror = () => {
      reject(new Error('CDN 加载或本地加载occt-import-js失败，请检查网络连接或本地是否存在occt-import-js.js'))
    }
    document.head.appendChild(script)
  })
}

async function initOcct() {
  if (occtInstance) return occtInstance
  
  try {
    loadingProgress.value = '加载 3D 引擎...'
    const occtModule = await loadOcctFromCDN()
    occtInstance = await occtModule()
    console.log('✅ occt-import-js 初始化成功')
    return occtInstance
  } catch (error) {
    console.error('❌ occt-import-js 初始化失败:', error)
    throw new Error('3D 引擎加载失败，请刷新页面重试')
  }
}

// ---------------- ThreeJS 初始化 ----------------
function initThree() {
  const dom = viewerRef.value
  if (!dom) return

  const width = dom.clientWidth || 400
  const height = dom.clientHeight || 240

  scene = new THREE.Scene()
  scene.background = new THREE.Color(0xf0f0f0)

  const aspect = width / height
  camera = new THREE.OrthographicCamera(
    (frustumSize * aspect) / -2,
    (frustumSize * aspect) / 2,
    frustumSize / 2,
    frustumSize / -2,
    0.1,
    10000
  )
  camera.position.set(100, 80, 120)
  camera.lookAt(0, 0, 0)

  renderer = new THREE.WebGLRenderer({ 
    antialias: true,
    alpha: false
  })
  renderer.setSize(width, height)
  renderer.setPixelRatio(Math.min(window.devicePixelRatio, 2))
  dom.appendChild(renderer.domElement)

  const ambientLight = new THREE.AmbientLight(0xffffff, 0.5)
  scene.add(ambientLight)

  const mainLight = new THREE.DirectionalLight(0xffffff, 0.8)
  mainLight.position.set(100, 200, 100)
  scene.add(mainLight)

  const fillLight = new THREE.DirectionalLight(0x8888ff, 0.3)
  fillLight.position.set(-100, 50, -100)
  scene.add(fillLight)

  const backLight = new THREE.DirectionalLight(0xff8888, 0.2)
  backLight.position.set(-50, -100, -150)
  scene.add(backLight)

  gridHelper = new THREE.GridHelper(200, 20, 0x888888, 0x444444)
  gridHelper.position.y = -0.01
  scene.add(gridHelper)

controls = new TrackballControls(camera, renderer.domElement)
controls.rotateSpeed = 3.0      // 旋转速度，可按手感调整
controls.zoomSpeed = 1.2
controls.panSpeed = 0.8
controls.noZoom = false
controls.noPan = false
controls.staticMoving = false   // false 表示有惯性阻尼；true 则立即停止
controls.dynamicDampingFactor = 0.1
controls.target.set(0, 0, 0)
controls.update()

  animate()
}

function animate() {
  requestAnimationFrame(animate)
  controls.update()
  renderer.render(scene, camera)
}

function resizeHandler() {
  if (!viewerRef.value || !renderer) return
  const w = viewerRef.value.clientWidth
  const h = viewerRef.value.clientHeight
  if (w === 0 || h === 0) return
  
  const aspect = w / h
  camera.left = (frustumSize * aspect) / -2
  camera.right = (frustumSize * aspect) / 2
  camera.top = frustumSize / 2
  camera.bottom = frustumSize / -2
  camera.updateProjectionMatrix()
  
  renderer.setSize(w, h)
  controls.handleResize()
}
// ---------------- 清理模型 ----------------
function clearModel() {
  clearFaceSelection()
  if (currentGroup) {
    currentGroup.traverse((child) => {
      if (child.isMesh) {
        if (child.geometry) child.geometry.dispose()
        if (child.material) {
          if (Array.isArray(child.material)) {
            child.material.forEach(m => m.dispose())
          } else {
            child.material.dispose()
          }
        }
      }
    })
    scene.remove(currentGroup)
    currentGroup = null
  }
  
  modelLoaded.value = false
  currentFileName.value = ''
  currentFileExt.value = ''
  info.value = {
    x: 0, y: 0, z: 0, volume: 0, realVolume: 0,
    faceCount: 0, vertexCount: 0, partCount: 0
  }
}

// ---------------- 生成随机颜色 ----------------
function getRandomColor() {
  const hue = Math.random()
  return new THREE.Color().setHSL(hue, 0.6, 0.5)
}

// ---------------- 加载模型（修复版 - 带详细调试） ----------------
async function loadModelFromFile(file) {
  loading.value = true
  loadingProgress.value = '初始化引擎...'
  
  try {
    // 1. 初始化 occt
    const occt = await initOcct()
    
    // 2. 读取文件
    loadingProgress.value = '读取文件...'
    const arrayBuffer = await file.arrayBuffer()
    const fileData = new Uint8Array(arrayBuffer)
    
    console.log(`📄 文件大小: ${(fileData.length / 1024).toFixed(2)} KB`)
    
    // 3. 解析文件
    loadingProgress.value = '解析 3D 模型...'
    const ext = file.name.split('.').pop().toLowerCase()
    
    let result = null
    
    // 尝试不同的解析方法
    try {
      // 方法1: 直接调用对应的读取函数
      if (['step', 'stp'].includes(ext)) {
        result = occt.ReadStepFile(fileData)
      } else if (['iges', 'igs'].includes(ext)) {
        result = occt.ReadIgesFile(fileData)
      } else if (['brep'].includes(ext)) {
        result = occt.ReadBrepFile(fileData)
      } else {
        // 尝试自动检测
        try {
          result = occt.ReadStepFile(fileData)
        } catch (e) {
          try {
            result = occt.ReadIgesFile(fileData)
          } catch (e2) {
            result = occt.ReadBrepFile(fileData)
          }
        }
      }
    } catch (parseError) {
      console.error('解析错误:', parseError)
      throw new Error(`解析文件失败: ${parseError.message || '未知错误'}`)
    }
    
    // 4. 调试输出 - 查看返回的数据结构
    console.log('📊 解析结果类型:', typeof result)
    console.log('📊 解析结果:', result)
    
    // 检查 result 是否为空
    if (!result) {
      throw new Error('解析结果为空，文件可能已损坏或格式不正确')
    }
    
    // 5. 提取网格数据 - 支持多种返回格式
    let meshes = []
    let success = false
    
    // 尝试不同的数据结构
    if (Array.isArray(result)) {
      // 直接返回数组
      meshes = result
      success = meshes.length > 0
      console.log('✅ 结果是一个数组，长度:', meshes.length)
    } else if (result.meshes && Array.isArray(result.meshes)) {
      // 有 meshes 字段
      meshes = result.meshes
      success = result.success !== false && meshes.length > 0
      console.log('✅ 结果包含 meshes 字段，长度:', meshes.length)
    } else if (result.mesh && Array.isArray(result.mesh)) {
      // 有 mesh 字段
      meshes = result.mesh
      success = meshes.length > 0
      console.log('✅ 结果包含 mesh 字段，长度:', meshes.length)
    } else if (result.data && Array.isArray(result.data)) {
      // 有 data 字段
      meshes = result.data
      success = meshes.length > 0
      console.log('✅ 结果包含 data 字段，长度:', meshes.length)
    } else if (typeof result === 'object') {
      // 尝试将对象转换为数组
      const keys = Object.keys(result)
      console.log('📊 结果对象的键:', keys)
      
      // 检查是否有包含网格数据的键
      const possibleKeys = ['meshes', 'mesh', 'data', 'parts', 'part', 'geometries']
      for (const key of possibleKeys) {
        if (result[key] && Array.isArray(result[key])) {
          meshes = result[key]
          success = meshes.length > 0
          console.log(`✅ 找到网格数据在键 "${key}"，长度:`, meshes.length)
          break
        }
      }
      
      // 如果还是没找到，尝试检查每个值
      if (!success) {
        for (const key of keys) {
          const value = result[key]
          if (value && typeof value === 'object') {
            // 检查是否有 positions 或 vertices
            if (value.positions || value.vertices || value.points) {
              meshes.push(value)
              success = true
              console.log(`✅ 在键 "${key}" 找到网格数据`)
            }
          }
        }
      }
    }
    
    // 6. 如果还是没有网格数据，尝试用不同的方式解析
    if (!success || meshes.length === 0) {
      console.warn('⚠️ 标准解析失败，尝试备用方法...')
      
      // 尝试使用 readFile 方法（如果存在）
      if (typeof occt.readFile === 'function') {
        try {
          const altResult = occt.readFile(fileData, ext)
          console.log('🔄 备用解析结果:', altResult)
          
          if (altResult && altResult.meshes && altResult.meshes.length > 0) {
            meshes = altResult.meshes
            success = true
            console.log('✅ 备用解析成功')
          }
        } catch (altError) {
          console.warn('备用解析也失败:', altError)
        }
      }
    }
    
    // 7. 最终检查
    if (!success || meshes.length === 0) {
      console.error('❌ 未能提取任何网格数据')
      console.error('原始结果:', result)
      throw new Error('无法从文件中提取网格数据。文件可能不包含 3D 几何体或格式不受支持。')
    }
    
    console.log(`✅ 成功提取 ${meshes.length} 个网格`)
    
    loadingProgress.value = '构建 3D 模型...'
    
    // 8. 清理旧模型
    clearModel()
    
    // 9. 创建组
    const group = new THREE.Group()
    let totalFaces = 0
    let totalVertices = 0
    let validMeshCount = 0
    let totalVolume = 0
    // 10. 遍历所有网格
    meshes.forEach((meshData, index) => {
  let positions = null
  let indices = null
  let normals = null

  // occt-import-js 的真实结构：attributes.position.array / attributes.normal.array
  if (meshData.attributes?.position?.array) {
    positions = meshData.attributes.position.array
  } else if (meshData.positions) {
    positions = meshData.positions
  } else if (meshData.vertices) {
    positions = meshData.vertices
  } else if (meshData.points) {
    positions = meshData.points
  } else if (meshData.coordinates) {
    positions = meshData.coordinates
  } else if (Array.isArray(meshData)) {
    positions = meshData
  }

  if (!positions || positions.length < 3) {
    console.warn(`⚠️ 跳过网格 ${index}: 没有有效的顶点数据`)
    return
  }

  if (meshData.index?.array) {
    indices = meshData.index.array
  } else if (meshData.indices) {
    indices = meshData.indices
  } else if (meshData.triangles) {
    indices = meshData.triangles
  } else if (meshData.faces) {
    indices = meshData.faces
  }

  if (meshData.attributes?.normal?.array) {
    normals = meshData.attributes.normal.array
  } else if (meshData.normals) {
    normals = meshData.normals
  } else if (meshData.vertexNormals) {
    normals = meshData.vertexNormals
  }
      
      try {
        const geometry = new THREE.BufferGeometry()
        
        // 处理顶点数据
        const posArray = positions.length > 0 && typeof positions[0] === 'number' 
          ? new Float32Array(positions) 
          : new Float32Array(positions.flat ? positions.flat() : positions)
        
        geometry.setAttribute('position', new THREE.BufferAttribute(posArray, 3))
        
        // 处理索引
        if (indices && indices.length > 0) {
          const indicesArray = new Uint32Array(indices)
          geometry.setIndex(new THREE.BufferAttribute(indicesArray, 1))
        }
        
        // 处理法线
        if (normals && normals.length > 0) {
          const normalsArray = new Float32Array(normals)
          geometry.setAttribute('normal', new THREE.BufferAttribute(normalsArray, 3))
        } else {
          geometry.computeVertexNormals()
        }
        
        // 统计信息
        const posAttr = geometry.attributes.position
        const vertexCount = posAttr.count
        totalVertices += vertexCount
        
        if (geometry.index) {
          totalFaces += geometry.index.count / 3
        } else {
          totalFaces += vertexCount / 3
        }
        // 累加网格体积
        totalVolume += computeMeshVolume(geometry)
        
        // 材质
        const color = getRandomColor()
        const material = new THREE.MeshPhongMaterial({
          color: color,
          shininess: 40,
          specular: new THREE.Color(0x333333),
          flatShading: false,
          side: THREE.DoubleSide
        })
        
        const mesh = new THREE.Mesh(geometry, material)
        mesh.castShadow = true
        mesh.receiveShadow = true
        mesh.userData.brepFaces = meshData.brep_faces || null
        mesh.userData.partName = meshData.name || `零件${index + 1}`
          group.add(mesh)
                // 添加黑色边线
            const edgesGeometry = new THREE.EdgesGeometry(geometry, 15) // 第二个参数是角度阈值(度),超过这个角度才算"边"
            const edgesMaterial = new THREE.LineBasicMaterial({ 
            color: 0x000000,
            linewidth: 1 // 注意:大多数浏览器 WebGL 不支持 linewidth > 1,后面会给替代方案
            })
            const edges = new THREE.LineSegments(edgesGeometry, edgesMaterial)
            mesh.add(edges) // 挂在 mesh 下面,这样清理时会跟着 mesh 一起被 traverse 到
  
        validMeshCount++
        
      } catch (meshError) {
        console.warn(`⚠️ 处理网格 ${index} 失败:`, meshError)
      }
    })
    
    // 11. 检查是否有有效的网格
    if (validMeshCount === 0) {
      throw new Error('没有有效的网格数据可以显示')
    }
    
    // 12. 计算包围盒并居中
    group.updateMatrixWorld(true)
    const box = new THREE.Box3().setFromObject(group)
    const size = box.getSize(new THREE.Vector3())
    const center = box.getCenter(new THREE.Vector3())
    group.position.sub(center)
    
    // 13. 添加到场景
    currentGroup = group
    scene.add(group)
    
    // 14. 更新信息
    info.value = {
      x: size.x,
      y: size.y,
      z: size.z,
      
      volume: (size.x * size.y * size.z/1000).toFixed(1),  // 轮廓体积：包围盒估算
      realVolume: (totalVolume/1000).toFixed(1),           // 体积：真实网格体积
      faceCount: Math.round(totalFaces),
      vertexCount: totalVertices,
      partCount: validMeshCount
    }
    
    currentFileName.value = file.name
    currentFileExt.value = ext
    modelLoaded.value = true
    
// 15. 调整视角
const maxDim = Math.max(size.x, size.y, size.z)
if (maxDim > 0 && isFinite(maxDim)) {
  // 正交相机：用 frustumSize 控制模型占屏幕的比例，留一些边距（*1.5）
  frustumSize = maxDim * 1.5
  
  const aspect = camera.right !== camera.left 
    ? (camera.right - camera.left) / (camera.top - camera.bottom) 
    : 1
  camera.left = (frustumSize * aspect) / -2
  camera.right = (frustumSize * aspect) / 2
  camera.top = frustumSize / 2
  camera.bottom = frustumSize / -2
  camera.updateProjectionMatrix()
  
  // 相机位置只需要保证在 near/far 范围内、且方向合适即可，距离数值本身不影响大小
  const distance = maxDim * 3 + 100
  const camX = distance * 0.6
  const camY = distance * 0.6
  const camZ = distance * 0.8
  
  camera.position.set(camX, camY, camZ)
  controls.target.set(0, 0, 0)
  controls.update()
  
  initialCameraPosition.set(camX, camY, camZ)
  initialControlsTarget.set(0, 0, 0)
  currentViewDistance = distance
}
    
    console.log(`✅ 模型加载成功: ${file.name}`)
    console.log(`   - 零件数: ${validMeshCount}`)
    console.log(`   - 面片数: ${Math.round(totalFaces)}`)
    console.log(`   - 尺寸: ${size.x.toFixed(2)} x ${size.y.toFixed(2)} x ${size.z.toFixed(2)}`)
    
  } catch (error) {
    console.error('❌ 加载模型失败:', error)
    let errorMessage = error.message || '未知错误'
    
    if (errorMessage.includes('memory')) {
      errorMessage = '文件过大，内存不足。请尝试更小的文件。'
    } else if (errorMessage.includes('format') || errorMessage.includes('不支持')) {
      errorMessage = '不支持的文件格式或文件已损坏。\n请确保文件是有效的 STEP/IGES 格式。'
    } else if (errorMessage.includes('network') || errorMessage.includes('CDN')) {
      errorMessage = '网络连接问题，请检查网络后重试。'
    }
    
    alert(`加载失败: ${errorMessage}`)
  } finally {
    loading.value = false
    loadingProgress.value = ''
  }
}

// ---------------- 文件处理 ----------------
function handleDragOver(e) {
  e.preventDefault()
  isDragging.value = true
}

async function handleFileDrop(e) {
  e.preventDefault()
  isDragging.value = false
  
  const files = e.dataTransfer.files
  if (files.length === 0) return
  await validateAndLoadFile(files[0])
}

function triggerFileInput() {
  if (!loading.value) {
    fileInput.value?.click()
  }
}

async function handleFileSelect(e) {
  const files = e.target.files
  if (files.length === 0) return
  await validateAndLoadFile(files[0])
  e.target.value = ''
}

async function validateAndLoadFile(file) {
  const ext = file.name.split('.').pop().toLowerCase()
  const validExts = ['step', 'stp', 'iges', 'igs', 'brep']
  
  if (!validExts.includes(ext)) {
    alert(`不支持 ${ext} 格式\n\n支持格式: ${validExts.join(', ').toUpperCase()}`)
    return
  }
  
  const maxSize = 50 * 1024 * 1024
  if (file.size > maxSize) {
    alert(`文件过大 (${(file.size / 1024 / 1024).toFixed(1)}MB)\n请选择小于 50MB 的文件`)
    return
  }
  
  if (file.size === 0) {
    alert('文件为空，请选择有效的 3D 文件')
    return
  }
  
  await loadModelFromFile(file)
}

// ---------------- 生命周期 ----------------
onMounted(async () => {
  await nextTick()
  initThree()
  window.addEventListener('resize', resizeHandler)
  renderer.domElement.addEventListener('click', onCanvasClick)
  try {
    await initOcct()
    console.log('✅ 3D 引擎已预加载')
  } catch (error) {
    console.warn('⚠️ 引擎预加载失败，将在导入文件时重试')
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeHandler)
  renderer?.domElement.removeEventListener('click', onCanvasClick)
  clearModel()
  if (renderer) {
    renderer.dispose()
    if (renderer.domElement?.parentNode) {
      renderer.domElement.parentNode.removeChild(renderer.domElement)
    }
  }
  occtInstance = null
})
</script>

<style scoped>
.cursor-pointer {
  cursor: pointer;
}

.animate-spin {
  animation: spin 1s linear infinite;
}

@keyframes spin {
  from { transform: rotate(0deg); }
  to { transform: rotate(360deg); }
}

.transition-colors {
  transition: all 0.3s ease;
}

.overflow-auto::-webkit-scrollbar {
  width: 4px;
}

.overflow-auto::-webkit-scrollbar-track {
  background: transparent;
}

.overflow-auto::-webkit-scrollbar-thumb {
  background: rgba(0,0,0,0.2);
  border-radius: 2px;
}
</style>