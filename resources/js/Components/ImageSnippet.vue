<script setup>
import { ref, computed } from "vue";
import { useForm } from '@inertiajs/vue3';
import { useAppStore } from '../stores/app.js';//pinia store

const appStore = useAppStore();

// 参数说明
//@ :image=listings.image---------------父组件中传过来的图片对象
//@ :title='"title txt"'----------------鼠标移到图片上面停留时弹出显示的文本
//@ :desc='"discrXXX"'------------------图片下方的文本说明
//@ :SizeLevel=6------------------------图片尺寸，最小为1
//@ :lock=0-----------------------------锁定图图片上传，粘贴，描述修改等功能仅保留查看和旋转操作
//@ :path="'exercise.storeImages'" -----图片提交处理路径，web.php中的路由名称
//@ :imageMaxSize=3145728 -----图片大小限制字节数，默认3MB = 3145728 字节

const props = defineProps({
    image: String,
    title: String,
    path: String,
    desc: String,
    imageMaxSize: { type: Number, default: 3 },
    SizeLevel: { type: Number, default: 6 },
    lock: { type: Boolean, default: true },
});

const form = useForm({
  image: null,
});

// 计算尺寸
const imgHeight = computed(() => `${30 * props.SizeLevel}px`);//图片显示高度
const imgWidth = computed(() => `${40 * props.SizeLevel}px`);//图片显示宽

// 状态
const currentImage = props.image ? `/storage/${props.image}` : null;
const preview = ref(currentImage);
const oversizedImage = ref(false);
const showRevertBtn = ref(false);
const rotate = ref(0);
const showImageView = ref(false);
const viewImageUrl = ref('');
const fileInput = ref(null);
const isImageVisible = ref(true);
const scale = ref(1) // 图片缩放比例
const pos = ref({ x: 0, y: 0 })
const isDragging = ref(false)//放大后拖动查看
// 点击图片区域（锁定=查看，未锁定=选择文件）
const selectImage = () => {
  if (props.lock) {
    handleView();
  } else {
    fileInput.value?.click();
  }
};

// 选择图片
const imageSelected = (e) => {
  const file = e.target.files[0];
  if (!file) return;

  preview.value = URL.createObjectURL(file);
  oversizedImage.value = file.size > props.imageMaxSize * 1024*1024 ;
  showRevertBtn.value = true;
  form.image = file;
};

// 重置图片
const revertImageChange = () => {
  showRevertBtn.value = false;
  preview.value = currentImage;
  oversizedImage.value = false;
  rotate.value = 0;
  form.image = null;
};

// 旋转
const handleRotateRight = () => rotate.value += 90;
const handleRotateLeft = () => rotate.value -= 90;
//滚轮缩放
const handleWheel = (e) => {
  e.preventDefault()
  if (e.deltaY < 0) {
    scale.value += 0.1 // 向上滚 → 放大
  } else {
    scale.value = Math.max(0.5, scale.value - 0.1) // 向下滚 → 缩小
  }
}
// 开始拖动
const startDrag = (e) => {
  e.preventDefault()
  isDragging.value = true
}

// 拖动中（绑在遮罩层）
const onDrag = (e) => {
  if (!isDragging.value) return
  pos.value.x += e.movementX
  pos.value.y += e.movementY
}

// 结束拖动
const stopDrag = () => {
  isDragging.value = false
}

// 查看大图
const handleView = () => {
  viewImageUrl.value = preview.value || null;
  showImageView.value = true;
};
const closeView = () => {
    showImageView.value = false
    scale.value = 1 // 关闭预览重置缩放
    pos.value = { x: 0, y: 0 }// 重置拖动坐标
}

// 隐藏/显示图片
const hideView = () => isImageVisible.value = !isImageVisible.value;

// 提交上传
const submitAction = () => {
  if (form.processing) return;
  alert("上传图片中")

  form.patch(route(props.path, form.image), {
    onSuccess: () => {
      appStore.setShowFlag(true);
    },
  });
};

//粘贴图片函数
// 点击粘贴按钮读取剪贴板图片
const pasteImage = async () => {
  // 浏览器兼容性判断
  if (!navigator?.clipboard?.read) {
    alert("当前浏览器不支持读取剪贴板图片，请使用Chrome/Edge，或手动上传图片");
    return;
  }
  try {
    // 读取剪贴板所有内容
    const clipboardItems = await navigator.clipboard.read();
    let targetFile = null;

    // 遍历剪贴板，找到图片
    for (const item of clipboardItems) {
      const imageType = item.types.find(type => type.startsWith("image/"));
      if (!imageType) continue;

      const blob = await item.getType(imageType);
      // 生成File对象，和文件选择拿到的格式统一
      targetFile = new File(
        [blob],
        `clip_${Date.now()}.${imageType.split("/")[1]}`,
        { type: imageType }
      );
      break;
    }

    if (!targetFile) {
      alert("剪贴板里没有图片，请先复制一张图片再点击粘贴");
      return;
    }

    // 复用你已有的图片处理逻辑
    preview.value = URL.createObjectURL(targetFile);
    oversizedImage.value = targetFile.size > props.imageMaxSize * 1024*1024 ;//图片大小
    showRevertBtn.value = true;
    form.image = targetFile;
    rotate.value = 0; // 重置旋转角度
  } catch (err) {
    console.error("粘贴图片失败：", err);
    alert("读取剪贴板失败：\n1.本地http环境需要localhost/127.0.0.1访问；\n2.线上必须HTTPS；\n3.请先复制图片再操作");
  }
};
</script>

<template>
  <form
    id="imageSnippet"
    @submit.prevent="submitAction"
  >
    <div class="relative group">
      <span
        class="block text-sm font-medium text-slate-700 dark:text-slate-300"
        :class="{ '!text-red-500': oversizedImage }"
      >
        {{ oversizedImage ? `The selected image exceeds limit ${props.imageMaxSize} Mb` : `Image (Max size limit ${props.imageMaxSize} Mb)` }}
      </span>

      <!-- 图片盒子外层div -->
      <div
        class="relative block rounded-md mt-1 bg-slate-300 overflow-hidden cursor-pointer border"
        :class="{ '!border-red-500': oversizedImage }"
        :style="{ height: imgHeight, width: imgWidth }"
        @click="handleView"
      >
      <!-- 图片标签绑定路径 注意：src后面的路径是双引号包单引号<=>或单引号包'双引号"../test.jpg"'
       父组件中的图片路径和子组件中的图片路径完全一样
       :src="preview ?? '/storage/images/listing/default.jpg'"
       :src="'/storage/test.jpg'"或:src="'../storage/test.jpg'"
       :src="'/test.jpg'"---------<>>>>public目录
       /test.jpg===../test.jpg ---------<>>>>只加左斜杠或加../
 -->
            <!-- 图片右侧操作菜单--图片上传操作按钮，SizeLevel < 6时在上方显示 -->
<!-- 图片右侧操作菜单--图片上传操作按钮 -->
<div 
  class="absolute right-0 top-0 z-50 gap-3"
  :class="[
    props.SizeLevel < 6 ? 'flex flex-row' : 'flex flex-col'
  ]"
>
  <!-- 粘贴图片按钮 -->
<div 
  class="rounded-sm grid place-items-center border-2 border-transparent border-dashed bg-white/80 cursor-pointer hover:border-red-500 transition-colors"
  title="粘贴图片"
  @click.stop="pasteImage"
>📋</div>

  <!-- 选择图片按钮 -->
  <div 
  class="rounded-sm grid place-items-center border-2 border-transparent border-dashed bg-white/80 cursor-pointer hover:border-red-500 transition-colors"
    title="选择图片"
    @click.stop="selectImage"
  >📁</div>

  <!-- 重置按钮 -->
  <button
    v-if="showRevertBtn && !props.lock"
    title="Revert"
    class="bg-red-500/75 w-8 h-8 rounded-full text-white flex items-center justify-center cursor-pointer"
    @click.stop="revertImageChange"
    type="button"
  >↺</button>

  <!-- 上传提交按钮 -->
  <div
    v-if="showRevertBtn && !props.lock"
    title="Submit image"
    class="bg-blue-500/75 w-8 h-8 rounded-full text-white flex items-center justify-center cursor-pointer"
    @click.stop="submitAction"
  >📤</div>
</div>


        <img
          v-if="isImageVisible"
          :src='preview ?? "../test.jpg"'
          class="object-contain object-center h-full w-full"
          alt=""
          :style="{ transform: `rotate(${rotate}deg)`, transformOrigin: 'center' }"
          :title=props.title
        />
<!-- 测试图片链接 -->

        </div>
        <!-- //单行省略显示文本
        <div 
            class="rounded-b bg-orange-300 text-red-500 flex items-center px-2 truncate"
            :style="{ 
                width: imgWidth,
                height: `${parseInt(imgHeight.value) / 8}px`
            }"
        > -->

      <!-- 左下工具栏，隐藏图片，旋转图片按钮 -->
      <div
        class="flex gap-2 items-center w-full"
        :class="{
          'opacity-0 group-hover:opacity-100 absolute left-0 bottom-[-30px]': SizeLevel < 6,
          'absolute bottom-0 left-0 p-2': SizeLevel >= 6
        }"
      >
        <div class="adjBtn" title="hide view" @click.stop="hideView">👁︎</div>
        <div class="adjBtn" title="rotate right" @click.stop="handleRotateRight">↩</div>
        <div class="adjBtn" title="rotate left" @click.stop="handleRotateLeft">↪</div>


      </div>

      <input ref="fileInput" @input="imageSelected" type="file" hidden />
         </div>
            <div v-if="props.desc && props.desc.trim() !== ''"
            class="rounded-b bg-orange-300 text-red-500 px-2 py-1 break-words whitespace-normal"
            :style="{ 
                width: imgWidth,
                minHeight: `${parseInt(imgHeight.value) / 8}px`
            }"
        >
   
        {{props.desc}}
        </div>
  </form>

  <!-- 大图预览 -->
  <div
    v-if="showImageView"
    class="fixed inset-0 bg-black/90 z-[9999] flex items-center justify-center p-5"
    @click.self="closeView"
  >
    <button
      class="absolute top-5 right-5 text-white text-3xl bg-red-500/75 px-3 py-1 rounded-full"
      @click="closeView"
    >
      ✕
    </button>
        <div class="absolute cursor-pointer top-5 right-[120px] text-white text-3xl bg-red-500/75 px-1 py-1 rounded-full"
     title="rotate left" @click.stop="handleRotateLeft">↪</div>
        <div class="absolute cursor-pointer top-5 right-[70px] text-white text-3xl bg-red-500/75 px-1 py-1 rounded-full"
         title="rotate right" @click.stop="handleRotateRight">↩</div>
    <img
        :src="viewImageUrl"
        class="max-w-full max-h-full object-contain cursor-grab active:cursor-grabbing"
        :style="{
            transform: `translate(${pos.x}px, ${pos.y}px) rotate(${rotate}deg) scale(${scale})`,
            transformOrigin: 'center',
            transition: isDragging ? 'none' : 'transform 0.1s ease'
        }"
        @wheel.prevent="handleWheel"
        @mousedown="startDrag"
        @mousemove="onDrag"
        @mouseup="stopDrag"
        @mouseleave="stopDrag"
    />
  </div>
</template>

<style scoped>
.adjBtn {
  @apply bg-yellow-300 rounded-full w-6 h-6 grid place-items-center cursor-pointer hover:bg-blue-500 transition-colors;
}
</style>