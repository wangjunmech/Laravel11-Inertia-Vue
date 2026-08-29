<template>
  <div class="p-4">
    <div class="text-lg font-medium mb-3">ThreeJS 3D文件读取器</div>
    <div class="grid grid-cols-[1fr_2fr_1fr] gap-2">
      <!-- 左侧拖拽区 -->
      <div
        class="h-60 bg-slate-300 rounded-xl p-2 flex flex-col items-center justify-center border-2 border-dashed border-slate-400 cursor-pointer transition-colors"
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
      <div ref="viewerRef" class="h-60 bg-slate-300 rounded-xl overflow-hidden relative">
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
      <div class="h-60 bg-slate-300 rounded-xl p-2 overflow-auto">
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
              <span class="text-gray-600">三角面片：</span>
              <span>{{ info.faceCount.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">顶点数：</span>
              <span>{{ info.vertexCount.toLocaleString() }}</span>
            </div>
            <div class="flex justify-between">
              <span class="text-gray-600">零部件：</span>
              <span>{{ info.partCount }}</span>
            </div>
          </div>
        </template>
      </div>
    </div>
    <!-- 视图功能菜单 -->
    <div>
        <!-- 父flex：justify‑content‑center，让内部一整组居中 -->
        <div class="flex justify-center  bg-slate-200">
            <div class="w-1/6 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
                <input
                    type="color"
                    v-model="bgColor"
                    @input="updateBackgroundColor(bgColor)"
                    class="m-1 w-8 h-8 rounded cursor-pointer border-0 flex-shrink-0"
                />
                <span>点击修改背景色</span>
            </div>

            <div class="w-1/6 bg-slate-400 m-1 cursor-pointer flex items-center gap-2 rounded-md">
                <input
                    type="checkbox"
                    v-model="showGrid"
                    @change="toggleGrid(showGrid)"
                    class="m-2 cursor-pointer"
                />
                <span class="text-sm">网格显示</span>

            </div>
            <div class="w-1/6 bg-slate-400 m-1">dd</div>
        </div>
    </div>

  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted, nextTick } from 'vue'
import * as THREE from 'three'
import { OrbitControls } from 'three/addons/controls/OrbitControls.js'

const viewerRef = ref(null)
const fileInput = ref(null)
const info = ref({
    x: 0,
    y: 0,
    z: 0,
    volume: 0,
    faceCount: 0,
    vertexCount: 0,
    partCount: 0
})

const loading = ref(false)
const loadingProgress = ref('初始化引擎...')
const isDragging = ref(false)
const bgColor = ref('#f0f0f0') // 和 initThree() 里默认背景色保持一致
const showGrid = ref(true) // 默认显示网格
const modelLoaded = ref(false)
const currentFileName = ref('')
const currentFileExt = ref('')

// ThreeJS 相关变量
let scene, camera, renderer, controls
let currentGroup = null
let occtInstance = null
let gridHelper = null 

function updateBackgroundColor(colorHex) {
    console.log('Color*******')
  if (!scene) return
  scene.background = new THREE.Color(colorHex)
}
function toggleGrid(visible) {
  if (!gridHelper) return
  gridHelper.visible = visible
}
// ---------------- occt-import-js 初始化（使用 CDN） ----------------
async function loadOcctFromCDN() {
  return new Promise((resolve, reject) => {
    if (window.occtimportjs) {
      resolve(window.occtimportjs)
      return
    }
    
    const script = document.createElement('script')
    script.src = 'https://cdn.jsdelivr.net/npm/occt-import-js@0.0.11/dist/occt-import-js.js'
    script.onload = () => {
      if (window.occtimportjs) {
        resolve(window.occtimportjs)
      } else {
        reject(new Error('occtimportjs 加载失败'))
      }
    }
    script.onerror = () => {
      reject(new Error('CDN 加载失败，请检查网络连接'))
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

  camera = new THREE.PerspectiveCamera(45, width / height, 0.1, 10000)
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

  controls = new OrbitControls(camera, renderer.domElement)
  controls.enableDamping = true
  controls.dampingFactor = 0.08
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
  
  camera.aspect = w / h
  camera.updateProjectionMatrix()
  renderer.setSize(w, h)
}

// ---------------- 清理模型 ----------------
function clearModel() {
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
    x: 0, y: 0, z: 0, volume: 0, 
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
      volume: size.x * size.y * size.z * 0.5,
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
      const distance = maxDim * 1.8 + 50
      camera.position.set(distance * 0.6, distance * 0.6, distance * 0.8)
      controls.target.set(0, 0, 0)
      controls.update()
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
  
  try {
    await initOcct()
    console.log('✅ 3D 引擎已预加载')
  } catch (error) {
    console.warn('⚠️ 引擎预加载失败，将在导入文件时重试')
  }
})

onUnmounted(() => {
  window.removeEventListener('resize', resizeHandler)
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