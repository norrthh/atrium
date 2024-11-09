<script setup>
import {ref} from "vue";
import Korobka from "@/components/Admins/Event/Korobka.vue";
import Roulette from "./Event/Roulette.vue";
import Prizes from "./Event/Prizes.vue";
import Promocode from "./Event/Promocode.vue";
import Rebus from "./Event/Rebus.vue";

const selectPage = ref('DEFAULT')

const changePage = (page) => {
    selectPage.value = page
    console.log("selectPage:", selectPage.value, typeof selectPage.value);
}

</script>

<template>
    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'DEFAULT'">
        <div class="flex flex-col w-full gap-2 items-center">
            <h1 class="text-3xl font-black">ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
        </div>

        <div class="flex flex-col gap-4 w-full font-black">
            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('eventZ')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">УПРАВЛЕНИЕ МЕРОПРИЯТИЯМИ</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>
        </div>
    </section>

    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'eventZ'">
        <div class="flex flex-col w-full gap-2 items-center">
            <h1 class="text-3xl font-black">ПАНЕЛЬ АДМИНИСТРАТОРА</h1>
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
                @click="changePage('roulette')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">РУЛЕТКА</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>
            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('prizes')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">УГАДАЙ ГДЕ ПРИЗ</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>
            <div
                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                @click="changePage('rebus')">
                <div class="flex items-center gap-4">
                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                      class="h-[18px]"></div>
                    <h2 class="">РЕБУСЫ</h2>
                </div>
                <img src="/ayazik/arrow-right.svg" alt="">
            </div>
        </div>
    </section>

    <Korobka v-if="selectPage === 'korobka'"/>
    <Roulette v-if="selectPage === 'roulette'"/>
    <Prizes v-if="selectPage === 'prizes'"/>
    <Rebus v-if="selectPage === 'rebus'"/>
</template>

<style scoped>

</style>
