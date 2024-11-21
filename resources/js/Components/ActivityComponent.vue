<script setup>
import { computed } from 'vue';

// Используем props для получения данных
const props = defineProps({
   activity: {
      type: Array,
      default: () => []
   }
})

// Вычисляемое свойство для сортировки массива по убыванию coin
const sortedActivities = computed(() => {
   return [...props.activity].sort((a, b) => b.coin - a.coin);
})
</script>

<template>
   <div v-if="sortedActivities.length > 0" v-for="(activity, i) in sortedActivities" :key="i">
      <div v-if="activity.username" class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between" :class="{'rat1': i === 0, 'rat2': i === 1, 'rat3': i === 2, 'rat4': i > 2}">
         <div class="flex items-center gap-4">
            <h2 class="font-black uppercase">#{{ i + 1 }} </h2>
            <div class="rounded-full overflow-hidden">
               <img :src="activity.avatar ? activity.avatar : '/ayazik/no_image.png'" alt="" class="h-12">
            </div>
            <h2 class="font-black uppercase">{{ activity.username }}</h2>
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
