<script setup>
import { computed, ref } from "vue";
import { switchTheme } from "../theme";
import NavLink from "../Components/NavLink.vue";
import { useForm, Link, usePage } from "@inertiajs/vue3";

const page = usePage();
// const name = computed(() => page.props.auth.user.name);//老是页面显示不正常，计算属性为为user如下面一样，使用v-if="user"来判断，用v-if="user.name"来判断也老是显示不正常
const show = ref(false);
// const user = computed(() => page.props.auth.user);
const user = computed(() => page.props?.auth?.user ?? null);
// let show = false;
</script>

<template>
    <!-- overlay 控制点击弹出的登出下拉菜单弹出后点击页面其它区域隐藏菜单的功能-->
    <!-- <div v-show="show" @click="show = false" class="fixed inset-0 z-40 bg-red-300">
</div>
 -->
    <div v-show="show" @click="show = false" class="fixed inset-0 z-40"></div>
    <header class="bg-slate-800 text-white">
        <nav
            class="p-6 mx-auto max-w-screen-lg flex items-center justify-between"
        >
            <NavLink routeName="home" componentName="Home">Home</NavLink>
            <NavLink routeName="exercise.index">Exercise</NavLink>
            <NavLink routeName="approvedbtn1" componentName="c1">email can V1</NavLink>
            <NavLink routeName="approvedbtn2" componentName="c2">email can V2</NavLink>

            <div class="flex items-center space-x-6">
                <div v-if="user" class="relative flex items-center gap-3">                
                    <div
                        @click="show = !show"
                        class="flex items-center gap-2 px-3 py-1 rounded-lg hover:bg-slate-700 cursor-pointer"
                        :class="{ 'bg-slate-700': show }"
                        >
                        <p>{{ user.name }}</p>
                        <i class="fa-solid fa-angle-down"></i>
                        </div>
                        
                        <Link
                                v-if="user.role === 'admin'"
                                :href="route('admin.index')"
                                class="hover:bg-slate-700 w-6 h-6 grid place-items-center rounded-full hover:outline outline-1 outline-white"
                            >
                                <i class="fa-solid fa-lock"></i>
                        </Link>
                            <!--登出下拉菜单 -->
                                <div
                                    v-show="show"
                                    @click="show = false"
                                    class="absolute z-50 top-8 right-0 bg-slate-800 text-white rounded-lg border-slate-300 border overflow-hidden w-48 p-0 m-0">
                                    <Link
                                        :href="route('profile.edit')"
                                        class="block w-full px-6 py-1 hover:bg-slate-700 text-left"
                                        >Profile</Link>
                                    <Link
                                        :href="route('listing.create')"
                                        class="block w-full px-6 py-1 hover:bg-slate-700 text-left"
                                        >Create Listing</Link>
                                    <Link
                                        :href="route('dashboard')"
                                        class="block w-full px-6 py-1 hover:bg-slate-700 text-left"
                                        >Dashboard</Link>

                                    <Link
                                        :href="route('logout')"
                                        method="post"
                                        as="button"
                                        class="block w-full px-6 py-1 hover:bg-slate-700 text-left"
                                        >Logout</Link>
                                </div>
                </div>
                    

                <div v-else class="space-x-6">
                        <NavLink routeName="login" componentName="Auth/Login"
                            >Login</NavLink
                        >
                        <NavLink routeName="register" componentName="Auth/Register"
                            >Register</NavLink
                        >
                </div>

                <button
                    @click="switchTheme"
                    class="hover:bg-slate-700 w-6 h-6 grid place-items-center rounded-full hover:outline outline-1 outline-white"
                >
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>
            </div>
        </nav>
    </header>

    <main class="p-6 mx-auto max-w-screen-lg">
        <slot />
    </main>
</template>
