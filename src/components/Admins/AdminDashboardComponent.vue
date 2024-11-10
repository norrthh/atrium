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

</template>

<style scoped>

</style>
