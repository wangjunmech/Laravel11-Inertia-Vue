<template>
  <div
    class="bg-[#4d2c2c] p-5 outline-none"
    tabindex="0"
    @keydown="onKeyDown"
    @keyup="onKeyUp"
    ref="keyWrapRef"
  >
    <div class="flex gap-4">
      <!-- 左侧大容器 包含整行F区+下方三栏键盘 -->
      <div class="flex-1 bg-[#4b7c9d] p-4 rounded-md">
        <!-- 第一行通栏：ESC F1-F12 PRTSC LOCK PAUSE 一整条不拆分 -->
        <div class="flex items-end gap-1 mb-1.5">
          <div
            class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0 mr-6"
            style="width:62px"
            :class="pressedKeys.includes('Escape') ? 'bg-white text-[#222]' : activeKeys.includes('Escape') ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
          >ESC</div>
          <div v-for="k in rowEscF12.slice(1)" :key="k.code"
            class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0 mr-1"
            style="width:48px"
            :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
          >{{k.label}}</div>
          <div class="flex items-end gap-1 mb-1.5"></div>
          <div v-for="k in rowPrint" :key="k.code"
            class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0"
            style="width:46px"
            :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
          >{{k.label}}</div>
        </div>

        <!-- 下方三栏并行：左主键盘｜中编辑红圈方块｜右数字小键盘 -->
        <div class="flex items-start gap-3">
          <!-- 左：主键盘独立竖块 5行完整对齐 -->
          <div class="flex flex-col gap-1.5 shrink-0">
            <!-- 数字+退格行 -->
            <div class="flex gap-1 items-end">
              <div v-for="k in rowNumBack" :key="k.code"
                class="h-12 border border-[#222] rounded flex flex-col items-center justify-center text-xs shrink-0 whitespace-pre-line leading-tight"
                :style="{width:k.w||'48px'}"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <!-- Tab字母行 -->
            <div class="flex gap-1 items-end">
              <div v-for="k in rowTabQ" :key="k.code"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0"
                :style="{width:k.w||'48px'}"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <!-- Caps字母回车行 -->
            <div class="flex gap-1 items-end">
              <div v-for="k in rowCapsA" :key="k.code"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0"
                :style="{width:k.w||'48px'}"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <!-- Shift行 -->
            <div class="flex gap-1 items-end">
              <div v-for="k in rowShiftZ" :key="k.code"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0"
                :style="{width:k.w||'48px'}"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <!-- Ctrl/Alt/空格底行 -->
            <div class="flex gap-1 items-end">
              <div v-for="k in rowCtrlSpace" :key="k.code"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs shrink-0"
                :style="{width:k.w||'48px'}"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
          </div>

          <!-- 中间：红圈编辑键独立方块 严格匹配网站高度对应 -->
          <div class="flex flex-col gap-1.5 w-[146px] shrink-0">
            <div class="flex gap-1">
              <div v-for="k in rowInsPgUp" :key="k.code" style="width:48px"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <div class="flex gap-1">
              <div v-for="k in rowDelPgDn" :key="k.code" style="width:48px"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <!-- 空行 对齐主键盘Caps行高度 -->
            <div class="h-12"></div>
            <!-- 上箭头单行居中 -->
            <div class="flex justify-center">
              <div v-for="k in rowArrow" :key="k.code" style="width:48px"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
            <!-- 底行左右下箭头 -->
            <div class="flex gap-1 justify-center">
              <div v-for="k in rowDirBottom" :key="k.code" style="width:48px"
                class="h-12 border border-[#222] rounded flex items-center justify-center text-xs"
                :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
              >{{k.label}}</div>
            </div>
          </div>

          <!-- 右侧数字小键盘：固定4列定宽网格，禁止自适应拉伸 -->
          <div class="grid gap-1 shrink-0" style="grid-template-columns: 48px 48px 48px 48px;">
            <!-- 第1行 -->
            <div
              v-for="k in rowNumTop"
              :key="k.code"
              class="w-[48px] h-12 border border-[#222] rounded flex items-center justify-center text-xs select-none whitespace-pre-line leading-tight"
              :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >{{ k.label }}</div>

            <!-- 第2行 7 8 9 -->
            <div
              v-for="k in rowNumMid.filter(i=>i.code !== 'NumpadAdd')"
              :key="k.code"
              class="w-[48px] h-12 border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >{{ k.label }}</div>

            <!-- 加号：纵向跨2行 -->
            <div
              class="row-span-2 w-[48px] h-full border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes('NumpadAdd') ? 'bg-white text-[#222]' : activeKeys.includes('NumpadAdd') ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >+</div>

            <!-- 第3行 4 5 6 -->
            <div
              v-for="k in rowNumBot3"
              :key="k.code"
              class="w-[48px] h-12 border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >{{ k.label }}</div>

            <!-- 第4行 1 2 3 -->
            <div
              v-for="k in rowNumBot2.filter(i=>i.code !== 'NumpadEnter')"
              :key="k.code"
              class="w-[48px] h-12 border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >{{ k.label }}</div>

            <!-- 小回车：纵向跨2行 -->
            <div
              class="row-span-2 w-[48px] h-full border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes('NumpadEnter') ? 'bg-white text-[#222]' : activeKeys.includes('NumpadEnter') ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >ENTER</div>

            <!-- 第5行 0（跨2列）、小数点 -->
            <div
              class="col-span-2 w-[97px] h-12 border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes('Numpad0') ? 'bg-white text-[#222]' : activeKeys.includes('Numpad0') ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >0</div>
            <div
              v-for="k in rowNumBot1.filter(i=>i.code !== 'Numpad0')"
              :key="k.code"
              class="w-[48px] h-12 border border-[#222] rounded flex items-center justify-center text-xs select-none"
              :class="pressedKeys.includes(k.code) ? 'bg-white text-[#222]' : activeKeys.includes(k.code) ? 'bg-black text-white' : 'bg-[#444] text-[#eee]'"
            >{{ k.label }}</div>
          </div>
        </div>

        <!-- 底部状态栏 -->
        <div class="mt-4 flex justify-between items-center">
          <div class="flex gap-[18px] items-center text-[#eee] text-sm">
            <span class="flex items-center gap-1.5">
              <span class="w-[18px] h-[18px] border border-[#222] bg-white"></span>
              Pressed(已按过)
            </span>
            <span class="flex items-center gap-1.5">
              <span class="w-[18px] h-[18px] border border-[#222] bg-black"></span>
              Active(当前按住)
            </span>
            <span class="flex items-center gap-1.5">
              <span class="w-[18px] h-[18px] border border-[#222] bg-[#444]"></span>
              Never(从未按下)
            </span>
            <button
              @click="resetAll"
              class="ml-2.5 px-3.5 py-1.5 bg-[#222] text-white border border-[#666] rounded cursor-pointer text-sm"
            >重置</button>
            <div id="sounds" class="flex items-center gap-1.5 cursor-pointer select-none bg-green-400 rounded-full p-1 pr-4" @click="toggleSound">
              <span class="text-lg">{{ soundOn ? '🔊' : '🔇' }}</span>
              <span class="text-sm">按键声音</span>
            </div>
          </div>
          <div class="text-white text-right">
            <div>APM <span class="text-[26px] font-bold ml-2">{{ apm }}</span></div>
            <div class="text-sm opacity-80">实时每分钟触发次数</div>
            <div class="w-[180px] h-1.5 bg-[#222] rounded mt-1 overflow-hidden">
              <div class="h-full bg-white transition-all duration-200" :style="{width: apmPercent + '%'}"></div>
            </div>
          </div>
        </div>
      </div>

      <!-- 右侧日志面板 带自动纵向滚动 -->
      <div class="w-[320px] max-h-[440px] flex flex-col bg-[#555] p-4 rounded-md text-white">
        <div class="flex-1 overflow-y-auto mb-3">
          <div class="flex items-center mb-1.5 text-sm" v-for="(item, idx) in logList" :key="idx">
            <span class="w-[110px] opacity-80">{{ item.time }}</span>
            <span class="min-w-[46px] bg-[#222] text-center py-0.5 mx-2 rounded">{{ item.keyName }}</span>
            <span>{{ item.type }}</span>
          </div>
        </div>
        <p class="text-sm opacity-75 mb-2.5 leading-relaxed">
          右 Shift、右侧 Win 特殊修饰键事件兼容已处理
        </p>
      </div>
    </div>
    <div class="min-h-[300px] flex flex-col"></div>
  </div>
</template>

<script setup>
import { ref, reactive, onMounted, computed } from 'vue'

const keyWrapRef = ref(null)
// 当前按住的按键
const activeKeys = reactive([])
// 永久记录：所有按过一次的按键
const pressedKeys = reactive([])
const logList = ref([])
const pressCount = ref(0)
const startTime = ref(Date.now())

// 按键音效开关
const soundOn = ref(true)
const keyAudio = new Audio('/storage/sounds/key.wav')
keyAudio.volume = 0.3

// 切换音效开关
const toggleSound = () => {
  soundOn.value = !soundOn.value
}

// APM计算
const apm = computed(() => {
  const durationMin = (Date.now() - startTime.value) / 60000
  if (durationMin <= 0) return 0
  return Math.round(pressCount.value / durationMin)
})
const apmPercent = computed(() => Math.min(apm.value / 300 * 100, 100))

// 按键定义数组
const rowEscF12 = [
  { label: 'ESC', code: 'Escape', w: '48px' },
  { label: 'F1', code: 'F1' },{ label: 'F2', code: 'F2' },{ label: 'F3', code: 'F3' },{ label: 'F4', code: 'F4' },
  { label: 'F5', code: 'F5' },{ label: 'F6', code: 'F6' },{ label: 'F7', code: 'F7' },{ label: 'F8', code: 'F8' },
  { label: 'F9', code: 'F9' },{ label: 'F10', code: 'F10' },{ label: 'F11', code: 'F11' },{ label: 'F12', code: 'F12' },
]
const rowPrint = [
  { label: 'PRTSC', code: 'PrintScreen', w: '36px' },
  { label: 'LOCK', code: 'ScrollLock', w: '36px' },
  { label: 'PAUSE', code: 'Pause', w: '36px' },
]
const rowNumBack = [
  { label: '～\n`', code: 'Backquote' },
  { label: '!\n1', code: 'Digit1' },
  { label: '@\n2', code: 'Digit2' },
  { label: '#\n3', code: 'Digit3' },
  { label: '$\n4', code: 'Digit4' },
  { label: '%\n5', code: 'Digit5' },
  { label: '^\n6', code: 'Digit6' },
  { label: '&\n7', code: 'Digit7' },
  { label: '*\n8', code: 'Digit8' },
  { label: '(\n9', code: 'Digit9' },
  { label: ')\n0', code: 'Digit0' },
  { label: '_\n-', code: 'Minus' },
  { label: '=\n+', code: 'Equal' },
  { label: 'BACKSPACE', code: 'Backspace', w: '78px' },
]
const rowInsPgUp = [
  { label: 'INS', code: 'Insert' },{ label: 'HOME', code: 'Home' },{ label: 'PGUP', code: 'PageUp' }
]
const rowNumTop = [
  { label: 'NUM\nLOCK', code: 'NumLock' },
  { label: '/', code: 'NumpadDivide' },
  { label: '*', code: 'NumpadMultiply' },
  { label: '-', code: 'NumpadSubtract' },
]
const rowTabQ = [
  { label: 'TAB', code: 'Tab', w: '62px' },
  { label: 'Q', code: 'KeyQ' },{ label: 'W', code: 'KeyW' },{ label: 'E', code: 'KeyE' },
  { label: 'R', code: 'KeyR' },{ label: 'T', code: 'KeyT' },{ label: 'Y', code: 'KeyY' },
  { label: 'U', code: 'KeyU' },{ label: 'I', code: 'KeyI' },{ label: 'O', code: 'KeyO' },
  { label: 'P', code: 'KeyP' },{ label: '[ {', code: 'BracketLeft' },{ label: '] }', code: 'BracketRight' },
  { label: '\\ |', code: 'Backslash', w: '64px' },
]
const rowDelPgDn = [
  { label: 'DEL', code: 'Delete' },{ label: 'END', code: 'End' },{ label: 'PGDN', code: 'PageDown' }
]
const rowNumMid = [
  { label: '7', code: 'Numpad7' },{ label: '8', code: 'Numpad8' },{ label: '9', code: 'Numpad9' },
  { label: '+', code: 'NumpadAdd' },
]
const rowCapsA = [
  { label: 'CAPS LOCK', code: 'CapsLock', w: '74px' },
  { label: 'A', code: 'KeyA' },{ label: 'S', code: 'KeyS' },{ label: 'D', code: 'KeyD' },
  { label: 'F', code: 'KeyF' },{ label: 'G', code: 'KeyG' },{ label: 'H', code: 'KeyH' },
  { label: 'J', code: 'KeyJ' },{ label: 'K', code: 'KeyK' },{ label: 'L', code: 'KeyL' },
  { label: '; :', code: 'Semicolon' },{ label: "' \"", code: 'Quote' },
  { label: 'ENTER', code: 'Enter', w: '104px' },
]
const rowNumBot3 = [
  { label: '4', code: 'Numpad4' },{ label: '5', code: 'Numpad5' },{ label: '6', code: 'Numpad6' }
]
const rowShiftZ = [
  { label: 'SHIFT', code: 'ShiftLeft', w: '115px' },
  { label: 'Z', code: 'KeyZ' },{ label: 'X', code: 'KeyX' },{ label: 'C', code: 'KeyC' },
  { label: 'V', code: 'KeyV' },{ label: 'B', code: 'KeyB' },{ label: 'N', code: 'KeyN' },
  { label: 'M', code: 'KeyM' },{ label: ', <', code: 'Comma' },{ label: '. >', code: 'Period' },
  { label: '/ ?', code: 'Slash' },
  { label: 'SHIFT', code: 'ShiftRight', w: '115px' },
]
const rowArrow = [
  { label: '↑', code: 'ArrowUp' }
]
const rowNumBot2 = [
  { label: '1', code: 'Numpad1' },{ label: '2', code: 'Numpad2' },{ label: '3', code: 'Numpad3' },
  { label: 'ENTER', code: 'NumpadEnter' },
]
const rowCtrlSpace = [
  { label: 'CTRL', code: 'ControlLeft', w: '60px' },
  { label: 'Win', code: 'MetaLeft' },
  { label: 'ALT', code: 'AltLeft' },
  { label: '', code: 'Space', w: '362px' },
  { label: 'ALT', code: 'AltRight' },
  { label: 'Win', code: 'MetaRight' },
  { label: 'MENU', code: 'ContextMenu', w: '52px' },
  { label: 'CTRL', code: 'ControlRight', w: '60px' },
]
const rowDirBottom = [
  { label: '←', code: 'ArrowLeft' },
  { label: '↓', code: 'ArrowDown' },
  { label: '→', code: 'ArrowRight' },
]
const rowNumBot1 = [
  { label: '0', code: 'Numpad0' },
  { label: '.', code: 'NumpadDecimal' },
]

const formatTime = () => {
  const d = new Date()
  return `${d.getHours()}:${String(d.getMinutes()).padStart(2,'0')}:${String(d.getSeconds()).padStart(2,'0')}.${String(d.getMilliseconds()).slice(0,1)}`
}

const onKeyDown = (e) => {
  e.preventDefault()
  // 按住瞬时状态
  if (!activeKeys.includes(e.code)) {
    activeKeys.push(e.code)
    pressCount.value++
  }
  // 永久标记：从未按过就存入
  if (!pressedKeys.includes(e.code)) {
    pressedKeys.push(e.code)
  }

  let code = e.key.charCodeAt(0) ?? ''
  logList.value.push({
    time: formatTime(),
    keyName: e.key,
    type: `ASCII: ${code}`
  })
  if (logList.value.length > 20) logList.value.shift()

  // 播放按键音
  if (soundOn.value) {
    keyAudio.currentTime = 0
    keyAudio.play().catch(err => {
      console.log('音频需要页面初次交互后播放', err)
    })
  }
}

const onKeyUp = (e) => {
  // 松开只移除瞬时按住状态，永久记录保留不变
  const idx = activeKeys.indexOf(e.code)
  if (idx > -1) {
    activeKeys.splice(idx, 1)
  }
  if (logList.value.length > 20) logList.value.shift();
}

// 重置：清空按住 + 所有已按记录
const resetAll = () => {
  activeKeys.length = 0
  pressedKeys.length = 0
  logList.value = []
  pressCount.value = 0
  startTime.value = Date.now()
}

onMounted(() => {
  keyWrapRef.value.focus()
})
</script>