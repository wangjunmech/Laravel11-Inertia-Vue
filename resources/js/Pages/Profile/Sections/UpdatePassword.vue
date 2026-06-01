<script setup>
import Container from "../../../Components/Container.vue";
import Title from "../../../Components/Title.vue";
import InputField from "../../../Components/InputField.vue";
import PrimaryBtn from "../../../Components/PrimaryBtn.vue";
import ErrorMessages from "../../../Components/ErrorMessages.vue";
import SessionMessages from "../../../Components/SessionMessages.vue";
import { router, useForm } from "@inertiajs/vue3";


const form = useForm({
    current_password: "",
    password: "", 
    password_confirmation: "",
});

const submit = () => {
    form.put(route("profile.password"), {
        onSuccess: () => form.reset(),
        onError: () => form.reset(),
        preserveScroll: true,// 保持浏览器窗口滚动位置不变，因为页面长度比较长的情况下填写输入框位置滚动了不利于用户操作，用户体验更好
    });
};
console.log(form);
</script>

<template>
    <Container class="mb-6">
        <div class="mb-6">
            <Title>Update Password</Title>
            <p>Ensure your are using a long, random password to stay secure.</p>
        </div>

        <ErrorMessages :errors="form.errors" />

        <form @submit.prevent="submit" class="space-y-6">
            <InputField
                label="Current Password"
                icon="key"
                class="w-1/2"
                type="password"
                v-model="form.current_password"
            />

            <InputField
                label="New Password"
                icon="key"
                class="w-1/2"
                type="password"
                v-model="form.password"
            />

            <InputField
                label="Confirm New Password"
                icon="key"
                class="w-1/2"
                type="password"
                v-model="form.password_confirmation"
            />


            <p v-if="form.recentlySuccessful" class="text-green-500 font-medium">Saved!</p>

            <PrimaryBtn :disabled="form.processing">Save</PrimaryBtn>
        </form>
    </Container>
</template>
