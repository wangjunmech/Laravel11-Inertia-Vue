<script setup>
import { ref } from "vue";

const emit = defineEmits(['image'])
const props = defineProps({
    listingImage: String,
});
// 如果有现有图片，设置预览为现有图片，否则为null
const currentImage = props.listingImage ? `/storage/${props.listingImage}` : null;
const preview = ref(currentImage);
const oversizedImage = ref(false);
const showRevertBtn = ref(false);

const imageSelected = (e) => {
    preview.value = URL.createObjectURL(e.target.files[0]);
    // 3Mb in bytes 控制图片大小不能超过3Mb
    oversizedImage.value = e.target.files[0].size > 3145728;
    showRevertBtn.value=true;
    emit('image', e.target.files[0])
};
const revertImageChange = () => {
    //取消显示图片撤销按钮
    showRevertBtn.value = false;
    //设置预览图片为当前图片（如果有），否则为默认图片
    preview.value = currentImage;
    //设置图片大小超标变量为false
    oversizedImage.value = false;
    emit('image', null);
}

</script>

<template>
    <div>
        <span
            class="block text-sm font-medium text-slate-700 dark:text-slate-300"
            :class="{'!text-red-500' : oversizedImage}"
        >
            {{
                oversizedImage
                    ? "The selected image exceeds 3Mb"
                    : "Image (Max size 3Mb)"
            }}
        </span>

        <label
            for="image"
            class="relative block rounded-md mt-1 bg-slate-300 h-[140px] overflow-hidden cursor-pointer border-slate-300 border"
            :class="{'!border-red-500' : oversizedImage}"
        >
            <img
                :src="preview ?? '/storage/images/listing/default.jpg'"
                class="object-cover object-center h-full w-full"
                alt=""
            />
            <button
                class="absolute top-2 right-2 bg-red-500/75 w-8 h-8 rounded-full grid place-items-center text-slate-700"
                v-if="showRevertBtn"
                @click.prevent="revertImageChange"
                type="button"

            ><i class="fa-solid fa-rotate-left"></i></button>
        </label>

        <input
            @input="imageSelected"
            type="file"
            name="image"
            id="image"
            hidden
        />
    </div>
</template>
