<script setup>
import {onMounted, ref} from "vue";
import {initFlowbite} from "flowbite";
import {TonConnectButton} from "ton-ui-vue";
import ActivityComponent from "@/Components/ActivityComponent.vue";
import LoaderComponent from "@/Components/LoaderComponent.vue";
import TaskComponent from "@/Components/TaskComponent.vue";
import CardHeader from "@/Components/CardHeader.vue";
import AdminDashboardComponent from "@/Components/Admins/AdminDashboardComponent.vue";

// Получение свойств компонента
const props = defineProps({
    page: {
        type: String,
        default: ''
    }
});

const telegramData = {
    "initData": "user=%7B%22id%22%3A891954506%2C%22first_name%22%3A%22%D0%90%D1%8F%D0%B7%22%2C%22last_name%22%3A%22%22%2C%22username%22%3A%22norrthh%22%2C%22language_code%22%3A%22ru%22%2C%22is_premium%22%3Atrue%2C%22allows_write_to_pm%22%3Atrue%7D&chat_instance=-7945443225735177088&chat_type=private&auth_date=1726175825&hash=e75f3598d93782f6c71daff1bb41db674233f3eb55261e254dd7b1c1fedb19fb",
    "initDataUnsafe": {
        "user": {
            "id": 891954506,
            "first_name": "Аяз",
            "last_name": "",
            "username": "norrthh",
            "language_code": "ru",
            "is_premium": true,
            "allows_write_to_pm": true
        },
        "chat_instance": "-7945443225735177088",
        "chat_type": "private",
        "auth_date": "1726175825",
        "hash": "e75f3598d93782f6c71daff1bb41db674233f3eb55261e254dd7b1c1fedb19fb"
    },
    "version": "7.10",
    "platform": "tdesktop",
    "colorScheme": "dark",
    "themeParams": {
        "accent_text_color": "#79e8da",
        "bg_color": "#282e33",
        "bottom_bar_bg_color": "#282e33",
        "button_color": "#3fc1b0",
        "button_text_color": "#ffffff",
        "destructive_text_color": "#f57474",
        "header_bg_color": "#282e33",
        "hint_color": "#82868a",
        "link_color": "#4be1c3",
        "secondary_bg_color": "#313b43",
        "section_bg_color": "#282e33",
        "section_header_text_color": "#4be1c3",
        "section_separator_color": "#242a2e",
        "subtitle_text_color": "#82868a",
        "text_color": "#f5f5f5"
    },
    "isExpanded": true,
    "viewportHeight": 590,
    "viewportStableHeight": 590,
    "isClosingConfirmationEnabled": false,
    "isVerticalSwipesEnabled": true,
    "headerColor": "#282e33",
    "backgroundColor": "#282e33",
    "bottomBarColor": "#282e33",
    "BackButton": {
        "isVisible": false
    },
    "MainButton": {
        "type": "main",
        "text": "Continue",
        "color": "#3fc1b0",
        "textColor": "#ffffff",
        "isVisible": false,
        "isProgressVisible": false,
        "isActive": true,
        "hasShineEffect": false
    },
    "SecondaryButton": {
        "type": "secondary",
        "text": "Cancel",
        "color": "#282e33",
        "textColor": "#3fc1b0",
        "isVisible": false,
        "isProgressVisible": false,
        "isActive": true,
        "hasShineEffect": false,
        "position": "left"
    },
    "SettingsButton": {
        "isVisible": false
    },
    "HapticFeedback": {},
    "CloudStorage": {},
    "BiometricManager": {
        "isInited": false,
        "isBiometricAvailable": false,
        "biometricType": "unknown",
        "isAccessRequested": false,
        "isAccessGranted": false,
        "isBiometricTokenSaved": false,
        "deviceId": ""
    }
}

const active = ref('home');
const selectPage = ref('home');
const selectedBonusType = ref('daily');
const user = ref(),
    bearer_token = ref(),
    activity = ref(),
    lastActivity = ref();

const bonus = ref(),
    tasks = ref(),
    timeLeft = ref()

const loadedSite = ref(false),
    loadedPage = ref(true)

const withdraw = ref(),
    withdrawMe = ref()

const changePage = (page) => {
    isShop.value = false
    selectPage.value = page;
    console.log(page)

    if (page === 'rating') {
        loadedPage.value = false

        if (!activity.value) {
            axios.post('/api/activity/now', {}, {
                headers: {
                    'Authorization': 'Bearer ' + bearer_token.value
                }
            }).then(res => {
                activity.value = res.data
                loadedPage.value = true
            })
        }

        loadedPage.value = true
    }

    if (page === 'last_activity') {
        loadedPage.value = false

        if (!lastActivity.value) {
            axios.post('/api/activity/last', {}, {
                headers: {
                    'Authorization': 'Bearer ' + bearer_token.value
                }
            }).then(res => {
                lastActivity.value = res.data
                loadedPage.value = true
            })
        }

        loadedPage.value = true
    }

    if (page === 'bonus') {
        loadedPage.value = false

        if (!bonus.value) {
            axios.post('/api/bonus/coins', {}, {
                headers: {
                    'Authorization': 'Bearer ' + bearer_token.value
                }
            }).then(res => {
                bonus.value = res.data
                startTimer(res.data.time.hours, res.data.time.minutes, res.data.time.seconds)
                loadedPage.value = true
            })
        }

        loadedPage.value = true
    }

    if (page === 'bonus_subscription') {
        loadedPage.value = false

        if (!tasks.value) {
            axios.post('/api/tasks', {}, {
                headers: {
                    'Authorization': 'Bearer ' + bearer_token.value
                }
            }).then(res => {
                tasks.value = res.data
                loadedPage.value = true
            })
        }

        loadedPage.value = true
    }

    if (page === 'withdraw') {
        loadedPage.value = false

        axios.post('/api/withdraw/all', {}, {
            headers: {
                'Authorization': 'Bearer ' + bearer_token.value
            }
        }).then(res => {
            withdraw.value = res.data
            loadedPage.value = true
        })
    }

    if (page === 'withdraw_me') {
        loadedPage.value = false

        axios.post('/api/withdraw/me', {}, {
            headers: {
                'Authorization': 'Bearer ' + bearer_token.value
            }
        }).then(res => {
            withdrawMe.value = res.data
            loadedPage.value = true
        })
    }

    if (page === 'unique_car') {
        isShop.value = true
    }

    if (page === 'unique_skin') {
        isShop.value = true
    }

    if (page === 'unique_item') {
        // document.body.setAttribute("style", "overflow: hidden;");
        // document.body.style.overflow = "hidden"
        isShop.value = true
    }

    if (page === 'unique_value') {
        // document.body.setAttribute("style", "background-color: lightblue; color: black;");
        // document.body.style.overflow = "hidden"
        isShop.value = true
    }
};

const changeBonusType = (bonusType) => {
    selectedBonusType.value = bonusType;
};
const getBonusActivity = (bonus) => {
    axios.post('/api/bonus/getCoins', {}, {
        headers: {
            'Authorization': 'Bearer ' + bearer_token.value
        }
    }).then(res => {
        bonus.status = false
        bonus.time = {hours: 23, minutes: 59, seconds: 59}

        startTimer(23, 59, 59)
    })
}
const startTimer = (initialHours, initialMinutes, initialSeconds) => {
    let hours = initialHours;
    let minutes = initialMinutes;
    let seconds = initialSeconds;

    const countdown = () => {
        if (seconds > 0) {
            seconds--;
        } else if (minutes > 0) {
            minutes--;
            seconds = 59;
        } else if (hours > 0) {
            hours--;
            minutes = 59;
            seconds = 59;
        } else {
            clearInterval(timerInterval); // Останавливаем таймер, когда время заканчивается
            timeLeft.value = {
                hours: null,
                minutes: null,
                seconds: null
            };
        }

        // Обновляем значения в таймере
        timeLeft.value = {
            hours: hours,
            minutes: minutes,
            seconds: seconds
        };
    };

    const timerInterval = setInterval(countdown, 1000); // Запускаем обновление каждую секунду
};
const handleClick = (event) => {
    const targetElement = event.target;
    const tagName = targetElement.tagName.toLowerCase();
    const className = targetElement.className;
    const elementId = targetElement.id;

    // console.log("tagName:", tagName);
    // console.log("className:", className);
    // console.log("elementId:", elementId);

    if (tagName === 'main') {
        if (isCoupon.value) {
            document.body.removeAttribute("style");
            isCoupon.value = false
        }

        if (isBoust.value) {
            document.body.removeAttribute("style");
            isBoust.value = false
        }
    }
}

let inputCoupon = ref(),
    responseCoupon = ref(),
    isCoupon = ref(false);

const showCoupon = () => {
    document.body.setAttribute("style", "overflow: hidden;");
    window.scrollTo({
        top: 0,
        behavior: 'smooth' // Плавная прокрутка
    });

    responseCoupon.value = {}
    isCoupon.value = true
}

const buttonCoupon = () => {
    responseCoupon.value = {}
    axios.post('api/coupons/insert', {
        'coupon': inputCoupon.value
    }, {
        headers: {
            'Authorization': 'Bearer ' + bearer_token.value
        }
    }).then(res => {
        console.log(res.data)
        responseCoupon.value = res.data
    })
}

let bousts = ref([
    {
        id: 1,
        path: '/img/boust/boust-1.svg',
        name: '<b>ULTIMATE SUBSCRIBE</b> на 30 дней'
    },
    {
        id: 2,
        path: '/img/boust/boust-2.svg',
        name: '20.000 игровой валюты'
    },
    {
        id: 3,
        path: '/img/boust/boust-3.svg',
        name: '5 конкурсных билетиков'
    },
    {
        id: 4,
        path: '/img/boust/boust-4.svg',
        name: '+5 VK каждый день'
    }
])

let isBoust = ref(false)

let isShop = ref(false)

let boustModal = () => {
    document.body.setAttribute("style", "overflow: hidden;");
    isBoust.value = true
}

onMounted(() => {
    initFlowbite();

    if (props.page) {
        selectPage.value = props.page;
    }

    axios.post('/api/auth', {
        'telegram_id': telegramData.initDataUnsafe.user.id
    }).then(res => {
        loadedSite.value = true
        user.value = res.data.user
        bearer_token.value = res.data.token
    })

    document.addEventListener('click', handleClick);
});

</script>


<template>
    <div class="bg-black overflow-hidden" id="body">
        <main
            class="max-w-screen text-white mx-auto relative overflow-hidden z-10 bg-no-repeat bg-cover select-none min-h-screen"
            id="main"
            :class="
                {
                    'bg_blure': !isShop,
                    'unique_car': selectPage === 'unique_car',
                    'unique_skin': selectPage === 'unique_skin',
                    'unique_value': selectPage === 'unique_value',
                    'unique_item': selectPage === 'unique_item',
                    // 'p-2': !isCoupon && !isBoust,
                }"
        >
            <div :style="{'opacity': loadedSite && !isCoupon && !isBoust ? 1 : 0.05}"
                 :class="{'pointer-events-none': isCoupon || isBoust}">

                <div class=" relative mx-auto p-2 flex justify-center flex-col gap-12 items-center an-opacity">

                    <header class="flex justify-between w-full">
                        <img src="/ayazik/logoFrame.svg" alt="" class="h-16 text-[#979795] mx-auto" v-if="selectPage === 'home'">
                        <img src="/ayazik/back.svg" alt="" class="h-16 text-[#979795]" v-if="selectPage !== 'home'" @click="changePage(isShop || selectPage === 'withdraw' ? 'cataloge' : 'home')">
                    </header>

                    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'home' && user">
                        <div class="flex gap-4 w-fit items-center">
                            <img :src="user.avatar ? user.avatar : '/ayazik/no_image.png'" alt="avatar" class="w-[70px] h-[70px]  rounded-full">
                            <div class="text-white flex flex-col font-bold">
                                <h1 class="uppercase text-2xl">{{ user.nickname }}</h1>
                                <div class="uppercaseflex gap-3 text-lg flex">
                                    <p class="opacity-50">ВАШ БАЛАНС:</p>
                                    <p class="flex items-center gap-2"><img src="/ayazik/donate.svg" alt=""
                                                                            class="h-6">{{ user.coin }}</p>
                                </div>
                            </div>
                        </div>


                        <div class="flex flex-col gap-4 w-full font-black">
                            <a href="https://t.me/norrthh"
                               class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/help.svg" alt=""
                                                                                      class="h-[18px]"></div>
                                    <h2>ПОМОЩЬ ПО ПРИЛОЖЕНИЮ</h2>
                                </div>
                                <img src="/ayazik/arrow-right.svg" alt="">
                            </a>

                            <div class="flex justify-between gap-4 text-white">

                                <div
                                    class="flex gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] bgb_one"
                                    @click="changePage('rating')">
                                    <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4"><img src="/ayazik/top.svg" alt=""
                                                                                            class="h-[18px]"></div>

                                    <h3 class="">ТОП АКТИВНЫХ <br> ИГРОКОВ</h3>
                                </div>

                                <div
                                    class="flex gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] bgb_two"
                                    @click="changePage('cataloge')">
                                    <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4"><img src="/ayazik/cart.svg"
                                                                                            alt=""
                                                                                            class="h-[18px]"></div>

                                    <h3 class="">МАГАЗИН ИГРОВЫХ <br> ТОВАРОВ</h3>
                                </div>

                            </div>
                            <div
                                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                                @click="changePage('bonus')">
                                <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                                      class="h-[18px]"></div>
                                    <h2 class="">БОНУСНЫЕ МОНЕТЫ</h2>
                                </div>
                                <img src="/ayazik/arrow-right.svg" alt="">
                            </div>

                            <div
                                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                                @click="showCoupon">
                                <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full p-4">
                                        <img src="/ayazik/promo.svg" alt=""
                                             class="h-[18px]"></div>
                                    <h2 class="">ВВЕСТИ ПРОМОКОД</h2>
                                </div>
                                <img src="/ayazik/arrow-right.svg" alt="">
                            </div>

                            <div @click="boustModal"
                                 class="flex gap-4 items-center bg-black opacity-20 p-4 rounded-full text-white justify-between">
                                <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/secret.svg" alt=""
                                                                                      class="h-[18px]"></div>
                                    <h2 class="">СЕКРЕТНЫЙ БУСТ</h2>
                                </div>
                                <img src="/ayazik/arrow-right.svg" alt="">
                            </div>

                            <div
                                class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                                @click="changePage('admin')">
                                <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full p-4">
                                        <img src="/ayazik/promo.svg" alt=""
                                             class="h-[18px]"></div>
                                    <h2 class="uppercase">Админ панель</h2>
                                </div>
                                <img src="/ayazik/arrow-right.svg" alt="">
                            </div>

                        </div>
                    </section>

                    <section class="flex flex-col w-full gap-12"
                             v-if="selectPage === 'bonus' || selectPage === 'bonus_subscription'">

                        <div class="flex flex-col w-full gap-2 items-center">
                            <h1 class="text-3xl font-black">БОНУСНЫЕ МОНЕТЫ</h1>
                            <p class="text-lg opacity-30 font-bold text-center">Заходите каждый день и забирайте
                                бонусы.</p>
                        </div>

                        <div class="flex flex-col gap-8">

                            <!-- Кнопки выбора типа бонуса -->
                            <div class="grid grid-cols-2 gap-2 justify-between font-black">
                                <button @click="changePage('bonus')"
                                        :class="{'bg-[#FFFFFF1F]': selectPage === 'bonus', 'bg-[#FFFFFF0F]': selectedBonusType !== 'bonus_subscription'}"
                                        class="uppercase px-4 py-6 border border-[#2F2D2D] rounded-2xl">
                                    ежедневный бонус
                                </button>
                                <button @click="changePage('bonus_subscription')"
                                        :class="{'bg-[#FFFFFF1F]': selectPage === 'bonus_subscription', 'bg-[#FFFFFF0F]': selectedBonusType !== 'bonus'}"
                                        class="uppercase px-4 py-6 border border-[#2F2D2D] rounded-2xl">
                                    за подписку
                                </button>
                            </div>

                            <div v-if="selectPage === 'bonus'" class="flex flex-col gap-8">
                                <div v-if="loadedPage && bonus">
                                    <div class="flex justify-between items-center font-bold"
                                         v-if="bonus.status === true">
                                        <p class="opacity-50 text-xl">Заберите свой бонус:</p>
                                        <button class="px-10 py-3 bg-white text-black rounded-xl font-black"
                                                @click="getBonusActivity(bonus)">ЗАБРАТЬ
                                        </button>
                                    </div>

                                    <div class="flex justify-between items-center font-bold" v-else-if="timeLeft">
                                        <p class="opacity-50 text-xl">Можно забрать бонус через:</p>
                                        <span class="px-5 py-2 bg-[#FFFFFF0F] text-white rounded-xl font-black">{{
                                                timeLeft.hours
                                            }}ч. {{ timeLeft.minutes }}м. {{ timeLeft.seconds }}сек.</span>
                                    </div>

                                    <div class="grid grid-cols-3 gap-4 mt-4">
                                        <div v-for="(bon, index) in bonus.coins" :key="index">
                                            <img
                                                :src="`/ayazik/bonuski/${index + 1}.svg`"
                                                alt=""
                                                class="mx-auto border-[#2F2D2D] border"
                                                style="border-radius: 30px"
                                                :class="{
                                                'opacity-30': bonus.status === false ? bonus.day > index  : bonus.day > index + 1,
                                                'border-white': bonus.status === false ? bonus.day === index  : bonus.day - 1 === index
                                            }"
                                            >
                                        </div>
                                    </div>
                                </div>
                                <LoaderComponent v-else></LoaderComponent>
                            </div>

                            <div v-else-if="selectPage === 'bonus_subscription'" class="flex flex-col gap-4">

                                <div v-if="loadedPage && tasks">
                                    <TaskComponent v-for="task in tasks" :count="task.count" :img="task.icon"
                                                   :text="task.title" :url="task.url"></TaskComponent>
                                </div>
                                <LoaderComponent v-else></LoaderComponent>
                            </div>
                        </div>
                    </section>

                    <section class="flex flex-col w-full gap-8"
                             v-if="selectPage === 'rating' || selectPage === 'last_activity'">
                        <div class="flex flex-col w-full gap-8 items-center">
                            <h1 class="text-4xl font-black"><span
                                v-if="selectPage === 'rating'">ТОП АКТИВНЫХ ИГРОКОВ</span>
                                <span v-else>ПОБЕДИТЕЛИ ПРОШЛОЙ НЕДЕЛИ</span></h1>
                            <button @click="changePage('last_activity')" v-if="selectPage === 'rating'"
                                    class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl">
                                <img src="/ayazik/group.svg" alt="" class="w-6">
                                <p class="text-lg font-black text-center">ПОБЕДИТЕЛИ ПРОШЛОЙ НЕДЕЛИ</p>
                            </button>

                            <button @click="changePage('rating')" v-else
                                    class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl">
                                <img src="/ayazik/group.svg" alt="" class="w-6">
                                <p class="text-lg font-black text-center">ТОП АКТИВНЫХ ИГРОКОВ</p>
                            </button>
                        </div>

                        <div class="flex flex-col gap-8" v-if="loadedPage">
                            <div class="flex flex-col gap-4 h-[55vh] overflow-y-auto">
                                <ActivityComponent v-if="selectPage === 'rating' && activity" :activity="activity"/>
                                <LoaderComponent v-else-if="selectPage === 'rating'"/>
                                <ActivityComponent v-if="selectPage === 'last_activity' && lastActivity"
                                                   :activity="lastActivity"/>
                                <LoaderComponent v-else-if="selectPage === 'last_activity'"/>
                            </div>
                        </div>

                        <LoaderComponent v-else/>
                    </section>

                    <section class="flex  font-black  flex-col w-full gap-8" v-if="selectPage === 'cataloge'">
                        <div class="flex flex-col w-full gap-8 items-center">
                            <h1 class="text-4xl  text-center">МАГАЗИН ИГРОВЫХ ТОВАРОВ</h1>
                            <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                                понедельник
                                с 00:00 до 23:00</p>
                        </div>

                        <div class="grid grid-cols-2  gap-4 gap-4 text-white">
                            <div
                                class="flex w-full gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] bgb_two"
                                @click="changePage('unique_car')">

                                <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4">
                                    <img src="/ayazik/car_icon.svg" alt="" class="w-[18px] h-[18px]">
                                </div>
                                <h3 class="">УНИКАЛЬНЫЙ <br> ТРАНСПОРТ</h3>
                            </div>

                            <div
                                class="flex w-full gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] rat4"
                                @click="changePage('unique_skin')">
                                <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4">
                                    <img src="/ayazik/skin_icon.svg" alt="" class="w-[18px] h-[18px]">
                                </div>
                                <h3 class="">УНИКАЛЬНЫЕ <br> СКИНЫ</h3>
                            </div>


                            <div
                                class="flex w-full gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] rat5"
                                @click="changePage('unique_item')">
                                <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4">
                                    <img src="/ayazik/eyes_icon.svg" alt="" class="w-[18px] h-[18px]">
                                </div>
                                <h3 class="">УНИКАЛЬНЫЕ <br> ПРЕДМЕТЫ</h3>
                            </div>

                            <div
                                class="flex w-full gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] rat6"
                                @click="changePage('unique_value')">
                                <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4">
                                    <img src="/ayazik/rur_icon.svg" alt="" class="w-[18px] h-[18px]">
                                </div>
                                <h3 class="">ВИРТУАЛЬНАЯ <br> ВАЛЮТА</h3>
                            </div>
                        </div>

                        <div class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                             @click="changePage('withdraw')">
                            <div class="flex items-center gap-4">
                                <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/help.svg" alt=""
                                                                                  class="h-[18px]"></div>
                                <h2>ВЫВОДЫ ТОВАРОВ</h2>
                            </div>
                            <img src="/ayazik/arrow-right.svg" alt="">
                        </div>
                    </section>

                    <section class="flex flex-col w-full gap-12"
                             v-if="selectPage === 'withdraw' || selectPage === 'withdraw_me'">
                        <div class="flex flex-col w-full gap-2 items-center">
                            <h1 class="text-3xl font-black">ВЫВОДЫ ИГРОВЫХ ТОВАРОВ</h1>
                            <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                                понедельник с 00:00 до 23:00</p>
                        </div>

                        <div class="flex flex-col gap-8">
                            <div class="grid grid-cols-2 gap-2 font-black">
                                <button @click="changePage('withdraw')"
                                        :class="{'bg-[#FFFFFF1F]': selectPage === 'withdraw', 'bg-[#FFFFFF0F]': selectPage !== 'withdraw'}"
                                        class="uppercase px-10 py-6 border border-[#2F2D2D] rounded-2xl">
                                    ВСЕ ВЫВОДЫ
                                </button>
                                <button @click="changePage('withdraw_me')"
                                        :class="{'bg-[#FFFFFF1F]': selectPage === 'withdraw_me', 'bg-[#FFFFFF0F]': selectPage !== 'withdraw_me'}"
                                        class="uppercase px-10 py-6 border border-[#2F2D2D] rounded-2xl">
                                    МОИ ВЫВОДЫ
                                </button>
                            </div>

                            <div v-if="selectPage === 'withdraw'" class="flex flex-col gap-8">
                                <div v-if="loadedPage && withdraw">
                                    <div class="flex flex-col gap-4 overflow-y-auto">
                                        <div
                                            class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                                            v-for="(withD, index) in withdraw">
                                            <div class="flex items-center gap-4">
                                                <div class="bg-[#FFFFFF0F] rounded-full border overflow-hidden"><img
                                                    src="/ayazik/no_image.png" alt="" class="h-12"></div>
                                                <h2 class="font-black uppercase w-2/12">{{ withD.user.nickname }}</h2>
                                            </div>
                                            <p class="flex items-center mr-2 gap-3 mr-3 font-bold">
                                                <img :src="withD.item.icon" alt="" class="h-12">
                                                <span class="text-end w-[90px] ml-auto" v-html="withD.item.name"></span>
                                            </p>
                                        </div>
                                    </div>
                                </div>
                                <LoaderComponent v-else/>
                            </div>

                            <div v-if="selectPage === 'withdraw_me'" class="flex flex-col gap-4">
                                <div v-if="loadedPage && withdrawMe">
                                    <div
                                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-2 rounded-full text-white justify-center mb-3"
                                        v-for="withD in withdrawMe">
                                        <div class="flex items-center gap-4">
                                            <div class="flex flex-col uppercase text-center font-bold"
                                                 v-html="withD.item.name"></div>
                                        </div>
                                        <p class="flex items-center gap-1 mr-2">
                                            <img :src="withD.item.icon" alt="" class="h-10">
                                        </p>
                                    </div>
                                </div>
                                <LoaderComponent v-else/>
                            </div>
                        </div>
                    </section>

                    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_car'">
                        <div class="flex flex-col w-full gap-2 items-center">
                            <h1 class="text-3xl font-black">УНИКАЛЬНЫЙ ТРАНСПОРТ</h1>
                            <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                                понедельник с 00:00 до 23:00</p>
                        </div>

                        <div class="unique_car_cards cardsBuy">
                            <div class="cards" v-for="i in 10">
                                <CardHeader value="10 000"/>
                                <img src="/ayazik/car.png" alt="" class="photo">
                                <h1 class="cardName">ASTON MARTIN VINTAGE</h1>
                            </div>
                        </div>
                    </section>

                    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_skin'">
                        <div class="flex flex-col w-full gap-2 items-center">
                            <h1 class="text-3xl font-black">УНИКАЛЬНЫЙ СКИНЫ</h1>
                            <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                                понедельник с 00:00 до 23:00</p>
                        </div>

                        <div class="unique_skin_cards cardsBuy">
                            <div class="cards" v-for="i in 10">
                                <CardHeader value="10 000"/>
                                <img src="/img/woman.png" alt="" class="photo">
                                <h1 class="cardName">ДЕВУШКА В ЧЁРНОМ ТОПЕ</h1>
                            </div>
                        </div>
                    </section>

                    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_item'">
                        <div class="flex flex-col w-full gap-2 items-center">
                            <h1 class="text-3xl font-black">УНИКАЛЬНЫЕ ПРЕДМЕТЫ</h1>
                            <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                                понедельник с 00:00 до 23:00</p>
                        </div>

                        <div class="unique_car_cards cardsBuy">
                            <div class="cards" v-for="i in 10">
                                <CardHeader value="10 000"/>
                                <img src="/img/krilya.png" alt="" class="photo">
                                <h1 class="cardName">АНГЕЛЬСКИЕ КРЫЛЬЯ</h1>
                            </div>
                        </div>
                    </section>

                    <AdminDashboardComponent v-if="selectPage === 'admin'" />


                    <section class="flex flex-col w-full gap-12" v-if="selectPage === 'dev'">

                        <div class="flex flex-col w-full gap-2 items-center">
                            <h1 class="text-3xl font-black">dev</h1>
                        </div>
                    </section>
                </div>
                <div class="max-w-[350px] flex gap-4 flex-col items-center text-[#383439] mx-auto p-4 ">
                    <hr class="w-full border-[#383439]">
                    <p>Powered by Atrium Online</p>
                </div>
            </div>
            <div role="status" class="absolute -translate-x-1/2 -translate-y-1/2 top-2/4 left-1/2" v-if="!loadedSite">
                <svg aria-hidden="true" class="w-8 h-8 text-gray-200 animate-spin dark:text-gray-600 fill-blue-600"
                     viewBox="0 0 100 101" fill="none" xmlns="http://www.w3.org/2000/svg">
                    <path
                        d="M100 50.5908C100 78.2051 77.6142 100.591 50 100.591C22.3858 100.591 0 78.2051 0 50.5908C0 22.9766 22.3858 0.59082 50 0.59082C77.6142 0.59082 100 22.9766 100 50.5908ZM9.08144 50.5908C9.08144 73.1895 27.4013 91.5094 50 91.5094C72.5987 91.5094 90.9186 73.1895 90.9186 50.5908C90.9186 27.9921 72.5987 9.67226 50 9.67226C27.4013 9.67226 9.08144 27.9921 9.08144 50.5908Z"
                        fill="currentColor"/>
                    <path
                        d="M93.9676 39.0409C96.393 38.4038 97.8624 35.9116 97.0079 33.5539C95.2932 28.8227 92.871 24.3692 89.8167 20.348C85.8452 15.1192 80.8826 10.7238 75.2124 7.41289C69.5422 4.10194 63.2754 1.94025 56.7698 1.05124C51.7666 0.367541 46.6976 0.446843 41.7345 1.27873C39.2613 1.69328 37.813 4.19778 38.4501 6.62326C39.0873 9.04874 41.5694 10.4717 44.0505 10.1071C47.8511 9.54855 51.7191 9.52689 55.5402 10.0491C60.8642 10.7766 65.9928 12.5457 70.6331 15.2552C75.2735 17.9648 79.3347 21.5619 82.5849 25.841C84.9175 28.9121 86.7997 32.2913 88.1811 35.8758C89.083 38.2158 91.5421 39.6781 93.9676 39.0409Z"
                        fill="currentFill"/>
                </svg>
                <span class="sr-only">Loading...</span>
            </div>

            <div class="modal-promo" v-if="isCoupon">
                <div class="modal-content">
                    <div class="modal-header"></div>
                    <div class="modal-title">
                        <div>
                            <svg width="90" height="90" viewBox="0 0 103 103" fill="none"
                                 xmlns="http://www.w3.org/2000/svg">
                                <circle opacity="0.1" cx="51.5" cy="51.5" r="51.5" fill="#D9D9D9"/>
                                <path
                                    d="M54.9145 37.8501H48.0855C41.6471 37.8501 38.4279 37.8501 36.4277 39.819C35.0505 41.1747 34.6216 43.0992 34.488 46.2354C34.4615 46.8573 34.4482 47.1683 34.5659 47.3757C34.6836 47.5832 35.1534 47.8421 36.0931 48.36C37.1366 48.9351 37.842 50.0339 37.842 51.2948C37.842 52.5558 37.1366 53.6546 36.0931 54.2297C35.1534 54.7476 34.6836 55.0064 34.5659 55.214C34.4482 55.4214 34.4615 55.7324 34.488 56.3543C34.6216 59.4904 35.0505 61.415 36.4277 62.7706C38.4279 64.7396 41.6471 64.7396 48.0855 64.7396H54.9145C61.3528 64.7396 64.572 64.7396 66.5722 62.7706C67.9494 61.415 68.3784 59.4904 68.5119 56.3543C68.5385 55.7324 68.5517 55.4214 68.4341 55.214C68.3164 55.0064 67.8465 54.7476 66.9068 54.2297C65.8632 53.6546 65.1578 52.5558 65.1578 51.2948C65.1578 50.0339 65.8632 48.9351 66.9068 48.36C67.8465 47.8421 68.3164 47.5832 68.4341 47.3757C68.5517 47.1683 68.5385 46.8573 68.5119 46.2354C68.3784 43.0992 67.9494 41.1747 66.5722 39.819C64.572 37.8501 61.3528 37.8501 54.9145 37.8501Z"
                                    stroke="white" stroke-width="2.925"/>
                                <path d="M46.1218 55.7762L55.9813 45.9167" stroke="white" stroke-width="2.925"
                                      stroke-linecap="round"/>
                                <path
                                    d="M57.7742 55.7765C57.7742 56.7666 56.9717 57.5692 55.9816 57.5692C54.9915 57.5692 54.189 56.7666 54.189 55.7765C54.189 54.7864 54.9915 53.9839 55.9816 53.9839C56.9717 53.9839 57.7742 54.7864 57.7742 55.7765Z"
                                    fill="white"/>
                                <path
                                    d="M48.8111 46.8134C48.8111 47.8035 48.0085 48.606 47.0185 48.606C46.0284 48.606 45.2258 47.8035 45.2258 46.8134C45.2258 45.8233 46.0284 45.0208 47.0185 45.0208C48.0085 45.0208 48.8111 45.8233 48.8111 46.8134Z"
                                    fill="white"/>
                            </svg>
                        </div>
                        <div class="content">
                            ввести промокод
                        </div>
                    </div>

                    <div class="modal-input">
                        <p>Введите ваш промокод</p>
                        <input type="text" id="large-input" placeholder="Например, 1aA2-3bB4" v-model="inputCoupon">
                    </div>

                    <p class="text-red-500" v-if="!responseCoupon.status">{{ responseCoupon.message }}</p>
                    <p class="text-green-500" v-else>{{ responseCoupon.message }}</p>

                    <button
                        @click="buttonCoupon"
                        class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                        <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
                        <p class="text-lg font-black text-center uppercase">Применить</p>
                    </button>
                </div>
            </div>
            <div class="boust-modal" v-if="isBoust">
                <div class="boust-content">
                    <div class="boust-header"></div>
                    <div class="boust-title">
                        <img src="/img/boust/raketa.svg" alt="">
                        <h1>Секретный <br> БУСТ</h1>
                    </div>

                    <div class="boust-info">
                        <p>Подпишитесь на VK Donut, чтобы получить на открытии следующие бонусы:</p>

                        <div class="boust-ul">
                            <div class="flex items-center mb-[6px]" v-for="boust in bousts">
                                <div class="boust-circle"></div>
                                <img :src="boust.path" width="25" height="25" alt="">
                                <p class="ml-[8px]" v-html="boust.name"></p>
                            </div>
                        </div>

                        <a href="https://t.me/norrthh"
                           class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                            <p class="text-lg font-black text-center uppercase">подписаться на vk Donut</p>
                        </a>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>

<style scoped>

.active-item_nav {
    opacity: 1;
}

.unique_car {
    background-image: url("/img/unique_car.png");
}

.unique_skin {
    background-image: url("/img/unique_skin.png");
}

.unique_item {
    background-image: url("/img/unique_item.png");
}

.unique_value {
    background-image: url("/img/unique_value.png");
}

.modal-promo {
    z-index: 10000;
    background: #242424;
    width: 100%;
    bottom: 0;
    border-top-left-radius: 30px;
    border-top-right-radius: 30px;
    //height: 475px;
    @apply absolute;
}

.modal-header {
    background: #FFFFFF1A;
    height: 10px;
    width: 81px;
    border-radius: 60px;
    margin: auto;
}

.modal-content {
    padding: 25px 45px 50px 42px;
    color: #fff;
}

.modal-title {
    padding-top: 24px;
    display: flex;
    align-items: center;
    gap: 20px;
}

.modal-title .content {
    font-size: 28px;
    font-weight: 800;

    @apply uppercase
}

@media (max-width: 415px) {
    .modal-title .content {
        font-size: 22px;
    }
}

@media (max-width: 360px) {
    .modal-title svg {
        width: 70px;
    }
}
</style>
