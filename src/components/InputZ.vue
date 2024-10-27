<template>
    <div class="modal-input" style="padding: 0">
        <p class="p">{{ label }}</p>
        <input type="text"  :placeholder="placeholder" v-model="inputValue" />
    </div>
</template>

<script setup>
import { defineProps, defineEmits, ref, watch } from 'vue';

const props = defineProps({
    label: String,
    placeholder: String,
    modelValue: String
});

const emit = defineEmits(['update:modelValue']);
const inputValue = ref(props.modelValue);

watch(inputValue, (newValue) => {
    emit('update:modelValue', newValue);
});
</script>

<style scoped>

</style>
