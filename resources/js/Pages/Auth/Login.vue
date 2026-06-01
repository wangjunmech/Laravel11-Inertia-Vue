<script setup>
import Container from "../../Components/Container.vue";
import Title from "../../Components/Title.vue";
import TextLink from "../../Components/TextLink.vue";
import InputField from "../../Components/InputField.vue";
import PrimaryBtn from "../../Components/PrimaryBtn.vue";
import { Head, useForm } from "@inertiajs/vue3";
import CheckBox from "../../Components/CheckBox.vue";
import ErrorMessages from "../../Components/ErrorMessages.vue";
import SessionMessages from "../../Components/SessionMessages.vue";

const form =useForm({
    email: "",
    password: "",
    remember:null,
})

defineProps({ status: String });// 用户重置密码成功后转到登录页面，从服务器端传递过来的status消息显示在登录页面上

const submit = () => {
    form.post(route("login"), {
        onFinish: () => form.reset("password"),
    });
};
</script>

<template>
    <Head title="-Login"/>
    <Container class="w-1/2">
        <div class="mb-8 text-center">
        <Title>Login to your account</Title>
            <p>
                Need an account?
                <TextLink routeName="register" label="Register" />
            </p>
        </div>
        <!-- 错误处理,如果要集中处理，把ErrorMessages组件打开并把错误绑定过去 -->
        <ErrorMessages :errors="form.errors" />
        <!-- //用户重置密码成功后转到登录页面，从服务器端传递过来的status消息 -->
        <session-messages :status="status"/>


        <form @submit.prevent="submit" class="space-y-6">

            <InputField
                label="Email"             
                icon="at"
                v-model="form.email"
            /><p class="text-sm text-red-500">{{ form.errors.email }}</p>     

            <InputField
                label="Password"
                type="password"
                icon="key"
                v-model="form.password"
            /><p class="text-sm text-red-500">{{ form.errors.password }}</p>     
            <div class="flex items-center">
                <CheckBox name="remember" v-model="form.remember">Remember me</CheckBox>                    
                <TextLink routeName="password.request" label="Forgot your password?" class="ml-auto text-sm" />
            </div>
            <PrimaryBtn :disabled="form.processing">Login</PrimaryBtn>
        </form>
    </Container>
</template>