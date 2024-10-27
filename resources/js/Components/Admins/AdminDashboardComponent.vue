<script setup>
import {ref} from "vue";
import Korobka from "@/Components/Admins/Event/Korobka.vue";

let selectPage = ref()

const changePage = (page) => {
    selectPage.value = page
}

</script>

<template>
    <section class="flex flex-col w-full gap-12" v-if="!selectPage">
        <div class="flex flex-col w-full gap-2 items-center">
            <h1 class="text-3xl font-black">УПРАВЛЕНИЕ КОНКУРСАМИ</h1>
        </div>

        <div class="flex flex-col gap-4 w-full font-black">
            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('korobka')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">КОРОБКА С СЮРПРИЗОМ</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>

            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('bonus')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">РУЛЕТКА</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>

            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('bonus')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">УГАДАЙ ГДЕ ПРИЗ</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>

            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('bonus')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">ПРОМОКОДЫ</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>
        </div>
    </section>

    <Korobka v-if="selectPage === 'korobka'" />
</template>

<style scoped>

</style>
