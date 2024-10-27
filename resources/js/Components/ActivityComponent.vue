<script setup>
    defineProps({
        activity: {
            type: Object
        }
    })
</script>

<template>
    <div v-if="activity.length > 0">
        <div class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between rat1" v-for="(activity, i) in activity">
            <div class="flex items-center gap-4">
                <h2 class="font-black uppercase">#{{ i + 1 }} </h2>
                <div class="bg-[#FFFFFF0F] rounded-full border overflow-hidden">
                    <img src="/ayazik/no_image.png" alt="" class="h-12">
                </div>
                <h2 class="font-black uppercase">{{ activity.nickname }}</h2>
            </div>
            <p class="flex items-center gap-1 mr-2 text-[#FDD835]">
                <img src="/ayazik/donate.svg" alt="" class="h-6">+{{ activity.coin }}
            </p>
        </div>
    </div>
    <div v-else>
        <p class="text-center text-2xl">Ничего не найдено</p>
    </div>
</template>

<style scoped>

</style>
