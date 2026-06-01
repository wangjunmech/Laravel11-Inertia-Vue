<script setup>
import {ref} from 'vue'
import { useForm, usePage } from '@inertiajs/vue3'
import { useAppStore } from '../stores/app.js'
const appStore = useAppStore();

const props = defineProps({
    user: Object,
});
const form = useForm({ role: props.user.role })
const submit = () => {
    if (confirm(`Change ${props.user.name}'s role to ${form.role}?`)) {
        
        // //如果确认提交，使用inertia的form.put方法把选项的参数提交到后台处理
        // form.put(route("admin.role", props.user.id));
        // // 使用pina的方法修改全局变量,
        // appStore.setShowFlag(1)
        // 按上面的写法有bug,就是数据还未提交成功就把全局状态改为显示，当时显示的还是修改前的form.role

        // ✅ Inertia 提交后，再修改状态（关键修复）
        form.put(route("admin.role", props.user.id), {
            onSuccess: () => {
                // 提交成功后才显示提示
                appStore.setShowFlag(1);
            }
        });


    } else {
        //如果取消提交，恢复选项卡原来的值
        form.role = props.user.role;
    }
};
</script>
<template>
    <div class="flex items-center gap-3">
        <form @change="submit" class="flex items-center gap-2">
            <label for="roles" class="sr-only">Roles:</label>
            <select
                v-model="form.role"
                name="roles"
                class="text-slate-800 bg-slate-200 text-xs py-1 border-0 outline-0 rounded-lg"
            >
            <!-- 注意option选项中value的值要区分大小写与数据库中的对应，否则不能正常显示绑定默认值 -->
                <option value="admin">Admin</option>
                <option value="general">General</option>
                <option value="suspended">Suspended</option>

            </select>

        </form>
    </div>
</template>