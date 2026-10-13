<template>
  <div class="container">
    <h2>条码/二维码扫码</h2>
    <!-- 视频预览窗口 -->
    <video ref="videoRef" autoplay playsinline style="max-width:100%;border:1px solid #ccc;"></video>
    <div style="margin:1rem 0;">
      <button @click="startScan" class="btn bg-slate-500 rounded p-1 mx-2">开始扫码</button>
      <button @click="stopScan" class="btn bg-red-300 rounded p-1 mx-2">停止扫码</button>
    </div>
    <div>
      <label>扫码结果：</label>
      <input v-model="result" type="text" style="width:300px;" />
    </div>
  </div>
</template>

<script setup>
import { ref, onUnmounted } from 'vue'
import { BrowserMultiFormatReader } from '@zxing/library'

const videoRef = ref(null)
const result = ref('')
let codeReader = null

// 启动扫码
const startScan = async () => {
  if(codeReader) codeReader.reset()
  codeReader = new BrowserMultiFormatReader()
  try {
    // 获取所有摄像头
    const devices = await codeReader.getVideoInputDevices()
    if(!devices.length) {
      alert("未找到摄像头设备")
      return
    }
    // 优先后置摄像头（手机）
    const backCam = devices.find(item => item.label.toLowerCase().includes('back')) || devices[0]

    await codeReader.decodeFromVideoDevice(
      backCam.deviceId,
      videoRef.value,
      (res, err) => {
        if(res) {
          // 识别成功
          result.value = res.text
          console.log('扫码内容：', res.text)
          // 这里可以直接调用接口提交 this.$inertia.post(...)
        }
      }
    )
  } catch (err) {
    console.error(err)
    alert("摄像头启动失败，HTTPS环境才允许手机摄像头")
  }
}

// 停止扫码
const stopScan = () => {
  if(codeReader) {
    codeReader.reset()
    codeReader = null
  }
}

// 组件销毁，释放摄像头
onUnmounted(() => {
  stopScan()
})
</script>