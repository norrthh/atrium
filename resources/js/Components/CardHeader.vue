<script setup>
    defineProps({
        value: {
            type: String
        }
    })
</script>

<template>
   <div class="grid grid-cols-2 gap-4" v-if="value.countActivate">
      <div class="count">
         <h1>{{ value.price }}</h1>
         <img src="/img/Donate.svg" alt="">
      </div>
      <div class="count">
         <h1>{{ value.count - value.countActivate }} шт.</h1>
      </div>
   </div>

   <div v-else class="count" style="margin: 0 0 10px auto; max-width: 120px;">
      <h1>{{ value.price }}</h1>
      <img src="/img/Donate.svg" alt="">
   </div>
</template>
<style scoped>
    .count {
        max-width: 120px;
        width: 100%;
        height: 45px;
        border-radius: 50px;
        border: 1.97px solid #FFFFFF0D;
        padding-left: 15px;
        font-size: 15px;
        display: flex;
        align-items: center;
        gap: 8px;
        margin-left: auto;
    }

    .count h1 {
        font-size: 23px;
        font-weight: 700;
        text-align: center;
    }
</style>
