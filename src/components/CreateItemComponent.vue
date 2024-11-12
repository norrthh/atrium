<script setup>
import {ref} from "vue";
import InputZ from "./InputZ.vue";
import axios from "axios";

const items = ref([
    {
        id: 1,
        name: 'MERCEDES BENZ E63S',
        img: '/ayazik/car.png'
    },
    {
        id: 2,
        name: 'MERCEDES BENZ E63S',
        img: '/ayazik/car.png'
    },
]);

const emit = defineEmits(['callMainFunction']);
const actualPage = ref('home');
const addForm = ref({
    idItem: '',
    name: '',
    img: '',
});

const editForm = ref({
    idItem: '',
    name: '',
    img: '',
    id: '',
});

const uploadStatus = ref({
    addItemImage: 'Выберите изображение',
    editItemImage: 'Выберите изображение'
});

const changePage = (page) => {
    console.log(actualPage.value, page)
    if (actualPage.value !== page) {
        actualPage.value = page;
    } else {
        emit('callMainFunction', 'admin', null);
    }
};

const editFormButton = (data) => {
    actualPage.value = 'editItem';
    editForm.value = data
}

async function uploadFile(event, target, statusKey) {
    uploadStatus.value[statusKey] = 'Загрузка...';

    const formData = new FormData();
    formData.append('file', event.target.files[0]);

    try {
        const response = await axios.post('https://api.atrium-bot.ru/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        target.img = response.data.url;
        uploadStatus.value[statusKey] = 'Файл загружен';
    } catch (error) {
        console.error("Ошибка загрузки:", error);
        uploadStatus.value[statusKey] = 'Ошибка при загрузке';
    }
}

function createItem() {
    if (addForm.value.idItem && addForm.value.name && addForm.value.img) {
        axios.post('https://api.atrium-bot.ru/api/items/add', {
            idItem: addForm.value.idItem,
            name: addForm.value.name,
            img: addForm.value.img
        }).then(res => {
            items.value = res.data
        })

        addForm.value = {idItem: '', name: '', img: ''};
        uploadStatus.value.addItemImage = 'Выберите изображение';
        changePage('home'); // Возвращаемся на страницу home
    } else {
        alert("Заполните все поля и загрузите изображение.");
    }
}

function editItem() {
    const item = items.value.find(i => i.id === editForm.value.id);
    if (item) {
        axios.post('https://api.atrium-bot.ru/api/items/add', {
            id: editForm.value.id,
            idItem: editForm.value.idItem,
            name: editForm.value.name,
            img: editForm.value.img
        }).then(res => {
            items.value = res.data
        })
        changePage('home');
    } else {
        alert("Предмет не найден.");
    }
}

const deleteItem = () => {
    axios.post('https://api.atrium-bot.ru/api/items/add', {
        id: editForm.value.id,
    }).then(res => {
        items.value = res.data
    })
    changePage('home');
}
</script>

<template>
    <header class="flex justify-between w-full">
        <img src="/ayazik/back.svg" alt="" class="h-16 text-[#979795]"
             @click="changePage('home')">
    </header>

    <div class="flex font-black flex-col w-full gap-8" v-if="actualPage === 'home'">
        <div class="flex flex-col w-full gap-8 items-center">
            <h1 class="text-4xl text-center">УПРАВЛЕНИЕ ПРЕДМЕТАМИ</h1>
        </div>

        <div v-for="(item, i) in items" :key="i">
            <div class="flex gap-4 items-center p-4 rounded-full text-white justify-between rat1"
                 @click="editFormButton(item)">
                <div class="flex items-center gap-4">
                    <h2 class="font-black uppercase" style="font-size: 21px; font-weight: 800; line-height: 20px">ID:
                        {{ item.id }} </h2>
                </div>
                <p class="flex items-center gap-1 mr-2 ">
                    <div class="rounded-full overflow-hidden">
                        <img :src="item.img" alt="" class="h-12">
                    </div>
                    <h2 class="font-black uppercase max-w-[120px]"
                        style="font-size: 21px; font-weight: 800; line-height: 20px">{{ item.name }}</h2>
                </p>
            </div>
        </div>

        <button
            @click="changePage('addItem')"
            class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
            <p class="text-lg font-black text-center uppercase">Добавить предмет</p>
        </button>
    </div>

    <div class="flex font-black flex-col w-full gap-8" v-if="actualPage === 'addItem'">
        <div class="flex flex-col w-full gap-8 items-center">
            <h1 class="text-4xl text-center">Добавление предмета</h1>
        </div>

        <div>
            <InputZ placeholder="Например, 30" label="Введите ID предмета:" v-model="addForm.idItem"/>
            <InputZ placeholder="Например, MERCEDES BENZ" label="Введите название предмета:" v-model="addForm.name"/>

            <div class="flex items-center justify-center w-full mt-4">
                <label class="flex flex-col items-center justify-center w-full h-64 rounded-lg cursor-pointer"
                       style="border: 1.61px solid #FFFFFF8F">
                    <input type="file" @change="e => uploadFile(e, addForm, 'addItemImage')" class="hidden"/>
                    <p class="text-white">{{ uploadStatus.addItemImage }}</p>
                </label>
            </div>

            <button
                @click="createItem"
                class="flex bg-green-500 text-white items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                <p class="text-lg font-black text-center uppercase">Создать предмет</p>
            </button>
        </div>
    </div>

    <div class="flex font-black flex-col w-full gap-8" v-if="actualPage === 'editItem'">
        <div class="flex flex-col w-full gap-8 items-center">
            <h1 class="text-4xl text-center">Редактирование предмета</h1>
        </div>

        <div>
            <InputZ placeholder="ID предмета" label="Введите ID предмета для редактирования:"
                    v-model="editForm.idItem"/>
            <InputZ placeholder="Новое название" label="Введите новое название предмета:" v-model="editForm.name"/>

            <div class="flex items-center justify-center w-full mt-3">
                <label class="flex flex-col items-center justify-center w-full h-64 rounded-lg cursor-pointer"
                       style="border: 1.61px solid #FFFFFF8F">
                    <input type="file" @change="e => uploadFile(e, editForm, 'editItemImage')" class="hidden"/>
                    <p class="text-white">{{ uploadStatus.editItemImage }}</p>
                </label>
            </div>

            <button
                @click="editItem"
                class="flex bg-blue-500 text-white items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                <p class="text-lg font-black text-center uppercase">Сохранить изменения</p>
            </button>

            <button
                @click="deleteItem"
                class="flex bg-red-500 text-white items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                <p class="text-lg font-black text-center uppercase">Удалить предмет</p>
            </button>
        </div>
    </div>
</template>
