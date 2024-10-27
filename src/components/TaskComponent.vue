<script setup>
defineProps({
    img: String,
    text: String,
    count: String,
    url: String
})
</script>

<template>
    <a :href="url" class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between mb-4">
        <div class="flex items-center gap-4">
            <div class="bg-[#FFFFFF0F] rounded-full p-4">
                <img :src="img" alt="" class="h-[18px]"></div>
            <div class="flex flex-col uppercase" v-html="text"></div>
        </div>
        <p class="flex items-center gap-1 mr-2 text-[#FDD835]">
            <img src="/ayazik/donate.svg" alt="" class="h-6">+{{ count }}
        </p>
    </a>
</template>

<style scoped>

</style>
