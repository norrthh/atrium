<script setup>
import InputZ from "@/components/InputZ.vue";
import axios from 'axios';
import {ref} from "vue";

let data = ref({
    countAttempt: '',
    countMessage: '',
    word: '',
    bg: {
        cover: '',
        success: '',
        fail: ''
    },
    subscribe: '',
    subscribe_mailing: '',
    timeForAttempt: '',
    cumebackPlayer: {
        status: '',
        time: '',
        attempt: '',
        count: ''
    },
    like: {
        status: '',
        count: ''
    },
    repost: {
        status: '',
        count: ''
    },
    text: '',
    states: [],
    attempts: [], // массив для хранения динамически добавляемых блоков
    isMobile: true,
    uploadStatus: { // статус загрузки для каждого блока
        postImage: 'Ожидание загрузки файла',
        successBackground: 'Ожидание загрузки файла',
        failBackground: 'Ожидание загрузки файла'
    },
    type: 5,
    typeActivate: '',
});

let shopItems = () => {
    console.log(300)
    axios.post('http://127.0.0.1:8000/api/items/getEvent').then(res => {
        data.value.states = res.data
    })
}

shopItems()


let nextId = 1;

function addAttempt() {
    data.value.attempts.push({id: nextId++, name: '', count: '', promcoode: '', 'countActivatePromocode': ''});
}

// Функция загрузки файла
async function uploadFile(event, type) {
    data.value.uploadStatus[type] = 'Загрузка...';

    let formData = new FormData();
    formData.append('file', event.target.files[0]);

    try {
        const response = await axios.post('http://127.0.0.1:8000/upload', formData, {
            headers: {
                'Content-Type': 'multipart/form-data'
            }
        });
        data.value.bg[type] = response.data.url;
        data.value.uploadStatus[type] = 'Файл загружен';
    } catch (error) {
        console.error("Ошибка загрузки:", error);
        data.value.uploadStatus[type] = 'Ошибка при загрузке';
    }
}

let createEvent = () => {
    axios.post('http://127.0.0.1:8000/api/vkontakte/event', data.value).then(res => {
        console.log(res)
    });
}

</script>

<template>
    <section class="flex flex-col w-full gap-12">
        <div class="flex flex-col w-full gap-2 items-center">
            <h1 class="text-3xl font-black">ПРОМОКОДЫ</h1>
        </div>

        <div class="flex flex-col gap-4 w-full font-black">
            <div v-if="!data.typeActivate">
                <div class="selectBlock">
                    <p class="p">Тип активации:</p>
                    <select v-model="data.typeActivate"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                        <option value="1">Игроĸи должны либо написать название промокода в ĸомментарии</option>
                        <option value="2">Любой игрок, который напишет что угодно в коментарий</option>
                        <option value="3">В приложение</option>
                    </select>
                </div>
            </div>

            <div v-if="data.typeActivate">
                <p class="mini-title">Настройка призов:</p>

                <!-- Динамически добавляемые блоки -->
                <div v-for="(attempt, index) in data.attempts" :key="index" class="selectBlock">
                    <label for="states" class="sr-only">Выберите приз</label>
                    <select v-model="attempt.name"
                            class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg p-2.5">
                        <option v-for="state in data.states" :key="state.id" :value="state.name">{{ state.name }}</option>
                    </select>
                    <input-z placeholder="Количество, например, 30" label="" v-model="attempt.count"/>
                    <input-z placeholder="Количество активации, например, 30" label="" v-model="attempt.count"/>
                    <input-z v-if="data.typeActivate == 1 || data.typeActivate == 3" placeholder="Название промокода" label="" v-model="attempt.count" />
                </div>

                <button @click="addAttempt"
                        class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                    <p class="text-lg font-black text-center uppercase">добавить приз</p>
                </button>

                <div v-if="data.typeActivate == 1">
                    <p class="mini-title">Настройка игры:</p>

                    <div>
                        <div class="selectBlock">
                            <p class="p">Репост</p>
                            <select v-model="data.repost.status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="1">ДАЕТ ДОПОЛНИТЕЛЬНЫЕ ПОПЫТКИ</option>
                                <option value="0">НЕ ДАЕТ ДОПОЛНИТЕЛЬНЫЕ ПОПЫТКИ</option>
                            </select>
                        </div>

                        <div v-if="data.repost.status === '1'">
                            <input-z placeholder="укажите попытки за репост" v-model="data.repost.count"/>
                        </div>
                    </div>

                    <!-- Поля для загрузки файлов с отображением статуса загрузки -->
                    <div>
                        <p class="p">Настройка фона победы:</p>
                        <div class="flex items-center justify-center w-full">
                            <label class="flex flex-col items-center justify-center w-full h-64 rounded-lg cursor-pointer"
                                   style="border: 1.61px solid #FFFFFF8F">
                                <input type="file" @change="e => uploadFile(e, 'successBackground')" class="hidden"/>
                                <p class="text-white">{{ data.uploadStatus.successBackground }}</p>
                            </label>
                        </div>
                    </div>

                    <p class="mini-title">Правила конкурса:</p>
                    <input-z label="Количество попыток:" placeholder="Например, 3" v-model="data.countAttempt"/>

                    <div>
                        <div class="selectBlock">
                            <p class="p">Репост:</p>
                            <select v-model="data.repost.status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="1">ДАЕТ ДОПОЛНИТЕЛЬНЫЕ ПОПЫТКИ</option>
                                <option value="0">НЕ ДАЕТ ДОПОЛНИТЕЛЬНЫЕ ПОПЫТКИ</option>
                            </select>
                        </div>

                        <div v-if="data.repost.status === '1'">
                            <input-z placeholder="укажите попытки за репост" v-model="data.repost.count"/>
                        </div>
                    </div>

                    <div>
                        <div class="selectBlock">
                            <p class="p">Лайк:</p>
                            <select v-model="data.like.status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="1">ДАЕТ ДОПОЛНИТЕЛЬНЫЕ ПОПЫТКИ</option>
                                <option value="0">НЕ ДАЕТ ДОПОЛНИТЕЛЬНЫЕ ПОПЫТКИ</option>
                            </select>
                        </div>

                        <div v-if="data.like.status === '1'">
                            <input-z placeholder="укажите попытки за лайк" v-model="data.like.count"/>
                        </div>
                    </div>

                    <div class="selectBlock">
                        <p class="p">Подписка:</p>
                        <select v-model="data.subscribe"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="required">требуется</option>
                            <option value="not_required">не требуется</option>
                        </select>
                    </div>

                    <div class="selectBlock">
                        <p class="p">Подписка на рассылку:</p>
                        <select v-model="data.subscribe_mailing"
                                class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                            <option value="required">требуется</option>
                            <option value="not_required">не требуется</option>
                        </select>
                    </div>

                    <input-z placeholder="Время между попытками:" label="например, 30 сек" v-model="data.timeForAttempt"/>

                    <div>
                        <div class="selectBlock">
                            <p class="p">Возвращать игроков в конкурс бонусными попытками:</p>
                            <select v-model="data.cumebackPlayer.status"
                                    class="bg-gray-50 border border-gray-300 text-gray-900 text-sm rounded-e-lg border-s-gray-100 dark:border-s-gray-700 border-s-2 focus:ring-blue-500 focus:border-blue-500 block w-full p-2.5 dark:bg-gray-700 dark:border-gray-600 dark:placeholder-gray-400 dark:text-white dark:focus:ring-blue-500 dark:focus:border-blue-500">
                                <option value="required">требуется</option>
                                <option value="not_required">не требуется</option>
                            </select>
                        </div>

                        <div v-if="data.cumebackPlayer.status === 'required'">
                            <input-z placeholder="через сколько времени, например, 30 сек" v-model="data.cumebackPlayer.time"/>
                            <input-z placeholder="сколько попыток подарить" v-model="data.cumebackPlayer.attempt"/>
                            <input-z placeholder="сколько раз возвращать" v-model="data.cumebackPlayer.count"/>
                        </div>
                    </div>
                </div>

                <div>
                    <p class="mini-title">Пост в соц.сети</p>
                    <textarea id="message" rows="4" v-model="data.text"
                              placeholder="Write your thoughts here..."></textarea>
                    <p class="p2">Доступные переменные: {twist_word} — подставится слово, которое открывает коробку</p>
                </div>

                <div>
                    <p class="p">Картинка для поста</p>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col items-center justify-center w-full h-64 rounded-lg cursor-pointer"
                               style="border: 1.61px solid #FFFFFF8F">
                            <input type="file" @change="e => uploadFile(e, 'postImage')" class="hidden"/>
                            <p class="text-white">{{ data.uploadStatus.postImage }}</p>
                        </label>
                    </div>
                </div>


                <button @click="createEvent"
                        class="data.isMobile ? 'flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px] mobile-button' : 'flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]'">
                    <p class="text-lg font-black text-center uppercase">запустить конкурс</p>
                </button>
            </div>

        </div>
    </section>
</template>

<style scoped>
/* Стили для мобильной кнопки */
.mobile-button {
    font-size: 1rem;
    padding: 0.75rem 1rem;
}
</style>
