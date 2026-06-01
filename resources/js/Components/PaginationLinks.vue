<script setup>
defineProps({
    paginator: Object,
    showResNum: {
    type: Boolean,    // 类型是布尔值
    default: true    // 默认值
  }
});

const makeLabel = (label) => {
  if (label.includes("Previous")) {
    return "<<";
  } else if (label.includes("Next")) {
    return ">>";
  } else {
    return label;
  }
};
</script>

<template>
    <div class="flex justify-between items-start m-5">
        <div class="flex items-lef"></div>
        <div class="flex items-center rounded-md overflow-hidden shadow-lg">
            <div v-for="(link, i) in paginator.links" :key="i">
                <component
                    :is="link.url ? 'Link' : 'span'"
                    :href="link.url"
                    v-html="makeLabel(link.label)"
                    class="border-x border-slate-50 w-12 h-12 grid place-items-center bg-white dark:bg-slate-900 dark:border-slate-800"
                    :class="{                        
                        'font-bold text-red-700 dark:text-indigo-400 bg-blue-200 dark:bg-green-100':link.active,
                        'hover:bg-yellow-300 dark:hover:bg-slate-500': link.url,
                        'text-black-300 bg-slate-400': !link.url,
                    }"
                />
            </div>
        </div>
        <div 
        v-if="showResNum"
        class="bg-slate-300 flex items-center rounded-md overflow-hidden shadow-lg h-12 p-2">            
                📖 {{ paginator.from }} to {{ paginator.to }} of
                {{ paginator.total }} results
        </div>
    </div>
</template>
