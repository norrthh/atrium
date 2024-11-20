<script setup>
import {onMounted, ref} from "vue";
import ActivityComponent from "@/Components/ActivityComponent.vue";
import LoaderComponent from "@/Components/LoaderComponent.vue";
import CardHeader from "@/Components/CardHeader.vue";
import axios from "axios";
import InputZ from "@/Components/InputZ.vue";

const props = defineProps({
   page: {
      type: String,
      default: ''
   }
});

const selectPage = ref('home'),
   viewHeader = ref(0);
const selectedBonusType = ref('daily');
const user = ref(),
   bearer_token = ref(),
   activity = ref(),
   lastActivity = ref(),
   settings = ref();

const bonus = ref(),
   tasks = ref(),
   timeLeft = ref()

const loadedSite = ref(false),
   loadedPage = ref(true)

const withdraw = ref(),
   withdrawMe = ref()

let shopItems = ref()
let inventoryItems = ref()
const changePage = (page, isHeader = null) => {
   viewHeader.value = isHeader ? 1 : 0;

   if (page === 'home') {
      console.log(500)
      handleHomeNavigation();
      return;
   }

   isShop.value = false;
   selectPage.value = page;

   // Карта страниц с их обработчиками
   const pageHandlers = {
      rating: () => loadPage('/api/activity/now', activity),
      last_activity: () => loadPage('/api/activity/last', lastActivity),
      bonus: () =>
         loadPage('/api/bonus/coins', bonus, (data) =>
            startTimer(data.time.hours, data.time.minutes, data.time.seconds)
         ),
      bonus_subscription: () => loadPage('/api/tasks', tasks),
      withdraw: () => loadPage('/api/withdraw/', withdraw),
      withdraw_me: () => loadPage('/api/withdraw/me', withdrawMe),
      unique_car: () => loadShopPage(1),
      unique_skin: () => loadShopPage(2),
      unique_item: () => loadShopPage(3),
      unique_value: () => loadShopPage(4),
      setting: () => loadPage('/api/setting', settings),
      auction_shop: () => loadPage('/api/auction', auctions),
      items: () => loadPage('/api/inventory', inventoryItems),
   };

   if (pageHandlers[page]) {
      loadedPage.value = false;
      pageHandlers[page]();
   }
};
const handleHomeNavigation = () => {
   if (["korobka", "roulette", "prizes", "rebus", "promocode"].includes(selectPage.value)) {
      selectPage.value = 'eventZ';
   } else if (selectPage.value === 'eventZ') {
      selectPage.value = 'admin';
   } else {
      selectPage.value = 'home';
   }
};

const loadPage = (url, targetRef, callback = null) => {
   axios.post(url).then((res) => {
      targetRef.value = res.data;
      if (callback) callback(res.data);
      loadedPage.value = true;

      if (url === '/api/auction') {
         console.log(res.data.data)
         auctions.value = res.data.data.map(auction => ({
            ...auction,
            timer: startTimer2(auction.time, auction.created_at) // Добавляем таймер
         }));
      }
   });
};

let shopPage = ref();

const loadShopPage = (type) => {
   shopPage.value = type
   isShop.value = true;
   shopItems.value = {}
   axios.post('/api/shop/', {type}).then((res) => {
      shopItems.value = res.data;
      loadedPage.value = true;
   });
};

const getBonusActivity = (bonus) => {
   axios.post('/api/bonus/getCoins', {}).then(res => {
      console.log(user.value.coin, res.data.coin)
      user.value.coin = Number(user.value.coin) + Number(res.data.coin);

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
         clearInterval(timerInterval);
         timeLeft.value = {
            hours: null,
            minutes: null,
            seconds: null
         };
      }

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

   if (tagName === 'main') {
      if (isCoupon.value || isBoust.value || isCheckNotification.value || isBuyItem.value || isBuyAuction.value || isWithdrawModal.value || isTransferShow.value) {
         document.body.removeAttribute("style");
         isCoupon.value = false
         isBoust.value = false
         isCheckNotification.value = false
         isBuyAuction.value = false
         isBuyItem.value = false
         isTransferShow.value = false
         isWithdrawModal.value = false
      }
   }
}

const whithdrawClose = () => {
   isWithdrawModal.value = false
}

let inputCoupon = ref(),
   responseCoupon = ref(),
   isCoupon = ref(false);

const showCoupon = () => {
   document.body.setAttribute("style", "overflow: hidden;");
   window.scrollTo({
      top: 0,
   });

   responseCoupon.value = {}
   isCoupon.value = true
}

const buttonCoupon = () => {
   responseCoupon.value = {}
   axios.post('/api/promocode/', {
      'code': inputCoupon.value
   }).then(res => {
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
   window.scrollTo({
      top: 0,
   });

   isBoust.value = true
}

let userProfile = ref()

const telegramData = window.Telegram.WebApp

if (!telegramData) {
   const telegramData = ref({
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
   })
}

axios.post('/api/auth/', {
   'telegram_id': telegramData.value.initDataUnsafe.user.id,
   // 'avatar_telegram': userProfile.value.photo_base,
   'nickname': telegramData.value.initDataUnsafe.user.first_name + ' ' + telegramData.value.initDataUnsafe.user.last_name
}).then(res => {
   loadedSite.value = true
   user.value = res.data.user
   bearer_token.value = res.data.token
   localStorage.setItem('bearer', res.data.token)

   axios.defaults.headers.common["Authorization"] = "Bearer " + res.data.token;

   if (res.data.notification.status) {
      checkNotification()
      isCheckNotification.value = true
      notification.value = res.data.notification.data
      axios.post('/api/notification/ready')
   }
})


onMounted(() => {
   if (props.page) {
      selectPage.value = props.page;
   }
   document.addEventListener('click', handleClick);
});

let isCheckNotification = ref(false),
   notification = ref()

let checkNotification = () => {
   document.body.setAttribute("style", "overflow: hidden;");
   window.scrollTo({
      top: 0,
   });

   return true
}

let isBuyItem = ref(false),
   buyItem = ref(),
   responseBuyItem = ref();

let buyItemModal = (item) => {
   buyItem.value = item
   isBuyItem.value = true

   document.body.setAttribute("style", "overflow: hidden;");
   window.scrollTo({
      top: 0,
   });

   responseBuyItem.value = {}
}
let buyItemButton = () => {
   axios.post('/api/shop/buyItem', {
      'id': buyItem.value.id,
   }).then(res => {
      responseBuyItem.value = res.data
      if (res.data.status) {
         user.value.coin = Number(user.value.coin) - Number(buyItem.value.price)
         loadShopPage(shopPage.value)
      }
   })
}
let isBuyAuction = ref(false),
   auctionItem = ref(),
   responseAuctionItem = ref();

let auctionItemModal = (item) => {
   if (item.timer.formattedTime !== 'Время истекло') {
      auctionItem.value = item
      isBuyAuction.value = true

      document.body.setAttribute("style", "overflow: hidden;");
      window.scrollTo({
         top: 0,
      });

      responseAuctionItem.value = {}
   }
}

let auctionInput = ref()
let auctionItemButton = () => {
   if (!auctionInput.value) {
      responseAuctionItem.value = {
         statis: false,
         message: 'Заполните поле'
      }
      return true
   }
   axios.post('/api/auction/buy', {
      'id': auctionItem.value.id,
      'price': auctionInput.value
   }).then(res => {
      responseAuctionItem.value = res.data

      if (res.data.status) {
         loadPage('/api/auction', auctions)
      }
   })
}

const auctions = ref()

const formatTime = (ms) => {
   const hours = Math.floor(ms / (1000 * 60 * 60));
   const minutes = Math.floor((ms % (1000 * 60 * 60)) / (1000 * 60));
   const seconds = Math.floor((ms % (1000 * 60)) / 1000);
   return `${String(hours).padStart(2, '0')}:${String(minutes).padStart(2, '0')}:${String(seconds).padStart(2, '0')}`;
};
const startTimer2 = (time, createdAt) => {
   const endTime = new Date(createdAt).getTime() + time * 60 * 60 * 1000; // Время окончания таймера
   const remainingTime = ref(endTime - new Date().getTime()); // Остаточное время в миллисекундах
   const formattedTime = ref('');
   const timerExpired = ref(false); // Флаг для отслеживания истекшего времени

   let timerInterval;

   const updateTimer = () => {
      remainingTime.value = endTime - new Date().getTime();

      // Проверка на истекшее время
      if (remainingTime.value <= 0) {
         formattedTime.value = 'Время истекло';
         clearInterval(timerInterval); // Остановить таймер, если время истекло
         timerExpired.value = true;
      } else {
         formattedTime.value = formatTime(remainingTime.value);
      }
   };

   updateTimer(); // Сразу обновить таймер при старте
   timerInterval = setInterval(updateTimer, 1000); // Обновлять каждую секунду

   return {formattedTime, timerExpired};
};


let isWithdrawModal = ref(false),
   responseWithdrawItem = ref({}),
   inventorySelectWithdrawItem = ref();

const withdrawButtonModal = (item) => {
   inventorySelectWithdrawItem.value = item
   isWithdrawModal.value = true

   document.body.setAttribute("style", "overflow: hidden;");
   window.scrollTo({
      top: 0,
   });

   responseWithdrawItem.value = {}
}

const inventoryWithdraw = () => {
   axios.post('/api/inventory/withdraw', {
      'id': inventorySelectWithdrawItem.value.id,
   }).then(res => {
      responseWithdrawItem.value = res.data
   })
}
const selectedCouponItems = ref([])

function selectPromocodeItem(itemObject, data, selectedItems, maxSelect) {
   // Проверяем, выбран ли уже этот элемент
   const isAlreadySelectedIndex = selectedItems.findIndex(
      (selected) => selected.id === itemObject.id
   );

   if (isAlreadySelectedIndex !== -1) {
      // Если элемент уже выбран, удаляем его
      selectedItems.splice(isAlreadySelectedIndex, 1);

      // Убираем класс выделения
      data.forEach((block) => {
         if (block.id === itemObject.id) {
            block.selected = false;
         }
      });

      console.log("Элемент удалён. Текущие выбранные элементы:", selectedItems);
      return;
   }

   // Проверяем, не превышен ли лимит выбранных элементов
   if (selectedItems.length >= maxSelect) {
      console.error("Лимит выбора превышен!");
      return;
   }

   // Добавляем элемент в массив выбранных
   selectedItems.push(itemObject);

   // Добавляем класс выделения
   data.forEach((block) => {
      if (block.id === itemObject.id) {
         block.selected = true;
      }
   });

   console.log("Элемент добавлен. Текущие выбранные элементы:", selectedItems);
}

const buttonSaveCoupon = () => {
   if (selectedCouponItems.value.length < responseCoupon.value.select) {
      return false;
   }
   axios.post('/api/promocode/activate', {
      'items': selectedCouponItems.value,
      'promo_id': responseCoupon.value.promo_id
   })

   isCoupon.value = false
}

let isTransferShow = ref(false),
   transferType = ref(1),
   transferInput = ref(),
   transferGenerateCode = ref(),
   transferResponse = ref();

const transferModal = (type) => {
   isTransferShow.value = true
   transferType.value = type

   if (!transferGenerateCode.value) {
      transferCode()
   }

   transferResponse.value = {}
};
const transferCode = () => {
   axios.post('/api/transfer/code', {
      from: 'vkontakte',
      to: 'telegram',
   }).then(res => {
      transferGenerateCode.value = res.data.code
   })
}

const transferActivateCode = () => {
   axios.post('/api/transfer/activate', {
      code: transferInput.value,
      social: 'telegram'
   }).then(res => {
      transferResponse.value = res.data
   })
}

const transferCopyToClipboard = () => {
   const input = document.createElement('input');
   input.value = transferGenerateCode.value;
   document.body.appendChild(input);
   input.select();
   document.execCommand('copy');
   document.body.removeChild(input);

   transferResponse.value = {
      message: 'Код скопирован в буфер обмена!'
   }
};
</script>
<template>
   <div class="bg-black overflow-hidden" id="body">
      <main
         class="max-w-screen text-white mx-auto overflow-hidden z-10 bg-no-repeat bg-cover select-none min-h-screen bg-cover"
         id="main"
         :class="
                {
                    'bg_blure': !isShop && selectPage !== 'auction_shop',
                    'unique_car': selectPage === 'unique_car',
                    'unique_skin': selectPage === 'unique_skin',
                    'unique_value': selectPage === 'unique_value',
                    'unique_item': selectPage === 'unique_item',
                    'auction_shop': selectPage === 'auction_shop',
                }"
      >
         <div
            :style="{'opacity': loadedSite && !isCoupon && !isBoust && !isCheckNotification && !isBuyItem && !isBuyAuction && !isWithdrawModal && !isTransferShow ? 1 : 0.05}"
            :class="{'pointer-events-none': isCoupon || isBoust || isCheckNotification || isBuyItem || isBuyAuction || isWithdrawModal || isTransferShow}"
            id="miniBody">
            <div class=" relative mx-auto p-2 flex justify-center flex-col gap-12 items-center an-opacity">

               <header class="flex justify-between w-full" v-if="!viewHeader">
                  <img src="/ayazik/logoFrame.svg" alt="" class="h-16 text-[#979795] mx-auto"
                       v-if="selectPage === 'home'">
                  <img src="/ayazik/back.svg" alt="" class="h-16 text-[#979795]" v-if="selectPage !== 'home'"
                       @click="changePage(
                                 selectPage === 'withdraw' || selectPage === 'auction_shop' || selectPage === 'shop'
                                 ? 'cataloge'
                                 : (isShop)
                                    ? 'shop'
                                    : 'home'
                                 )">
               </header>
               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'home' && user">
                  <div class="flex gap-4 w-fit items-center">
                     <img :src="user?.avatar" alt="avatar" class="w-[70px] h-[70px]  rounded-full">
                     <div class="text-white flex flex-col font-bold">
                        <h1 class="uppercase text-2xl">{{ telegramData.initDataUnsafe.user.first_name + ' ' + telegramData.initDataUnsafe.user.last_name}}</h1>
                        <div class="uppercaseflex gap-3 text-lg flex">
                           <p class="opacity-50 uppercase">ВАШ БАЛАНС:</p>
                           <p class="flex items-center gap-2">
                              <img src="/ayazik/donate.svg" alt="" class="h-6">
                              <span style="color: #fddc4e" class="uppercase">{{ user.coin }}</span>
                           </p>
                        </div>
                        <div class="uppercaseflex gap-3 text-lg flex">
                           <p class="opacity-50 uppercase">Билетов:</p>
                           <p class="flex items-center gap-2">
                              <img src="/ayazik/bilet.svg" alt="" class="h-6">
                              <span style="color: #f3a418" class="uppercase">0</span> <!-- bilet data !-->
                           </p>
                        </div>
                     </div>
                  </div>


                  <div class="flex flex-col gap-4 w-full font-black">
                     <a href="https://m.vk.com/app5748831_-33903796"
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/help.svg" alt=""
                                                                             class="h-[18px]"></div>
                           <h2 class="uppercase">Получить подарок 🎁 </h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </a>

                     <div class="flex justify-between gap-4 text-white">

                        <div
                           class="flex gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] bgb_one"
                           @click="changePage('rating')">
                           <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4">
                              <img src="/ayazik/top.svg" alt="" class="h-[18px]">
                           </div>

                           <h3 class="">ТОП АКТИВНЫХ <br> ИГРОКОВ</h3>
                        </div>

                        <div
                           class="flex gap-6 flex-col p-4 w-6/12 rounded-[28px] border border-[#2F2D2D] bgb_two"
                           @click="changePage('cataloge')">
                           <div class="bg-[#FFFFFF0F] w-fit rounded-full p-4">
                              <img src="/ayazik/cart.svg" alt="" class="h-[18px]">
                           </div>

                           <h3 class="">МАГАЗИН ИГРОВЫХ <br> ТОВАРОВ</h3>
                        </div>
                     </div>
                     <div
                        class="flex gap-4 items-center p-4 rounded-full text-white justify-between"
                        @click="changePage('bonus')">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/gift.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">БОНУСНЫЕ МОНЕТЫ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>

                     <div
                        class="flex gap-4 items-center p-4 rounded-full text-white justify-between"
                        @click="changePage('items')">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/icons/items.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">ХРАНИЛИЩЕ ПРЕДМЕТОВ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>

                     <div
                        class="flex gap-4 items-center p-4 rounded-full text-white justify-between"
                        @click="showCoupon">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/promo.svg" alt=""
                                   class="h-[18px]"></div>
                           <h2 class="">ВВЕСТИ ПРОМОКОД</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>

                     <div
                        class="flex gap-4 items-center p-4 rounded-full text-white justify-between"
                        @click="changePage('setting')">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/icons/settings.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">НАСТРОЙКИ</h2>
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

                     <div v-if="user && user?.user?.vkontakte_id == 582127671 ||  user?.user?.vkontakte_id == 217199523"
                          class="flex gap-4 items-center p-4 rounded-full text-white justify-between"
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
                           <div v-if="tasks" v-for="(item, i) in tasks['data']" :key="i">
                              <a :href="item.href"
                                 class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between mb-4">
                                 <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full p-4">
                                       <img
                                          :src="(item.icon == 'VK' ? '/ayazik/icons/VK.svg' : '/ayazik/icons/telegram.svg')"
                                          alt=""></div>
                                    <div class="flex flex-col uppercase">
                                       <span>{{ item.task }}</span>
                                       <span style="color: #FFFFFFA3;">{{ item.description }}</span>
                                    </div>
                                 </div>
                                 <p class="flex items-center gap-1 mr-2 text-[#FDD835]">
                                    <img :src="item.items[0].item_details.icon" alt=""
                                         class="h-6">+{{ item.items[0].count }}
                                 </p>
                              </a>
                           </div>
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
               <section class="flex font-black flex-col w-full gap-8" v-if="selectPage === 'cataloge'">
                  <div class="flex flex-col w-full gap-8 items-center">
                     <h1 class="text-4xl  text-center">МАГАЗИН ИГРОВЫХ ТОВАРОВ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник
                        с 00:00 до 23:00</p>
                  </div>

                  <div
                     class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between rat7"
                     @click="changePage('auction_shop')">
                     <div class="flex items-center gap-4">
                        <div class="bg-[#FFFFFF0F] rounded-full p-4">
                           <img src="/ayazik/icons/auction.svg" alt="" class="h-[18px]">
                        </div>
                        <h2>АУКЦИОН ТОВАРОВ</h2>
                     </div>
                     <img src="/ayazik/arrow-right.svg" alt="">
                  </div>

                  <div
                     class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between rat8"
                     @click="changePage('shop')">
                     <div class="flex items-center gap-4">
                        <div class="bg-[#FFFFFF0F] rounded-full p-4">
                           <img src="/ayazik/icons/shop.svg" alt="" class="h-[18px]">
                        </div>
                        <h2>МАГАЗИН ТОВАРОВ</h2>
                     </div>
                     <img src="/ayazik/arrow-right.svg" alt="">
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
               <section class="flex font-black flex-col w-full gap-8" v-if="selectPage === 'auction_shop'">
                  <div class="flex flex-col w-full gap-8 items-center">
                     <h1 class="text-4xl  text-center">АУКЦИОН ТОВАРОВ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник
                        с 00:00 до 23:00</p>
                  </div>

                  <div class="unique_car_cards" v-if="auctions">
                     <div class="cardsGood" v-for="shopItem in auctions">
                        <div @click="auctionItemModal(shopItem)">
                           <h1 class="cardName">{{ shopItem.item.name }}</h1>
                           <div :class="shopItem.item.skin ? 'absolute bottom-0 left-0 right-0' : ''">
                              <img src="/ayazik/icons/time.svg" class="absolute right-0 top-[12px]"
                                   v-if="shopItem.item_type === 1"/>
                              <img :src="shopItem.item.icon" alt="" class="photo mx-auto"
                                   :class="shopItem.item.skin ? 'h-[156px]' : 'pt-[20px]'">
                           </div>

                           <div class="flex gap-3 absolute bottom-[10px] max-w-[500px] left-0 right-0 mx-auto">
                              <div class="count">
                                 <h1 class="p">ПОСЛЕДНЯЯ СТАВКА:</h1>
                                 <h1>{{ shopItem.start_price }}</h1>
                                 <img src="/img/Donate.svg" alt="">
                              </div>
                              <div class="count">
                                 <h1>{{ shopItem.timer.formattedTime }}</h1>
                              </div>
                           </div>
                        </div>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>
               <section class="flex font-black flex-col w-full gap-8" v-if="selectPage === 'shop'">
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
                                 v-for="(withD, index) in withdraw['data']">
                                 <div class="flex items-center gap-4">
                                    <div class="bg-[#FFFFFF0F] rounded-full border overflow-hidden"><img
                                       :src="withD.user.avatar" alt="" class="h-12"></div>
                                    <h2 class="font-black uppercase w-2/12">{{ withD.user.nickname }}</h2>
                                 </div>
                                 <p class="flex items-center mr-2 gap-3 mr-3 font-bold">
                                    <img :src="withD.item.icon" alt="" class="h-12">
                                    <span class="text-end max-w-[90px] ml-auto"
                                          v-html="withD.item.name"></span>
                                 </p>
                              </div>
                           </div>
                        </div>
                        <LoaderComponent v-else/>
                     </div>

                     <div v-if="selectPage === 'withdraw_me'" class="flex flex-col gap-4">
                        <div v-if="loadedPage && withdrawMe">
                           <div
                              class="flex gap-4 items-center bg-[#FFFFFF0F] p-2 rounded-full text-white mb-3 justify-between"
                              v-for="withD in withdrawMe['data']">
                              <p class="flex items-center mr-2 gap-3 mr-3 font-bold">
                                 <img :src="withD.item.icon" alt="" class="h-12">
                                 <span class="w-[90px] ml-auto" v-html="withD.item.name"></span>
                              </p>
                              <h2 class="font-black uppercase">
                                            <span v-if="withD.status === 1"
                                                  style="color: #FDD835">На рассмотрение</span>
                                 <span v-if="withD.status === 3" style="color: #FF2530">Отклонён</span>
                                 <span v-if="withD.status === 2" style="color: #47C840">Одобрен</span>
                              </h2>
                           </div>
                        </div>
                        <LoaderComponent v-else/>
                     </div>
                  </div>
               </section>

               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'items'">
                  <div class="flex flex-col w-full gap-2 items-center">
                     <h1 class="text-3xl font-black">ХРАНИЛИЩЕ ПРЕДМЕТОВ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник с 00:00 до 23:00</p>
                  </div>

                  <div v-if="loadedPage && inventoryItems">
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-2 rounded-full text-white mb-3 justify-between"
                        v-for="withD in inventoryItems['data']" @click="withdrawButtonModal(withD)">
                        <p class="flex items-center mr-2 gap-3 mr-3 font-bold">
                           <img :src="withD.item.icon" alt="" class="h-12">
                           <span class="w-[90px] ml-auto" v-html="withD.item.name"></span>
                        </p>
                        <button class="withdrawButton">Вывести</button>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>

               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_car'">
                  <div class="flex flex-col w-full gap-2 items-center">
                     <h1 class="text-3xl font-black">УНИКАЛЬНЫЙ ТРАНСПОРТ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник с 00:00 до 23:00</p>
                  </div>

                  <div class="unique_car_cards cardsBuy" v-if="shopItems">
                     <div class="cards" v-for="shopItem in shopItems['data']">
                        <div @click="buyItemModal(shopItem)">
                           <CardHeader :value="shopItem"/>
                           <div class="relative">
                              <img src="/ayazik/icons/time.svg" class="absolute right-0 top-[12px]"
                                   v-if="shopItem.item_type === 1"/>
                              <img :src="shopItem.item.icon" alt="" class="photo">
                           </div>
                           <h1 class="cardName">{{ shopItem.item.name }}</h1>
                        </div>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>
               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_skin'">
                  <div class="flex flex-col w-full gap-2 items-center">
                     <h1 class="text-3xl font-black">УНИКАЛЬНЫЙ СКИНЫ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник с 00:00 до 23:00</p>
                  </div>

                  <div class="unique_skin_cards cardsBuy" v-if="shopItems">
                     <div class="cards" v-for="shopItem in shopItems['data']" @click="buyItemModal(shopItem)">
                        <CardHeader :value="shopItem"/>
                        <div class="relative">
                           <img src="/ayazik/icons/time.svg" class="absolute right-0 top-[12px]"
                                v-if="shopItem.item_type === 1"/>
                           <img :src="shopItem.item.icon" alt="" class="photo">
                        </div>
                        <h1 class="cardName">{{ shopItem.item.name }}</h1>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>
               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_item'">
                  <div class="flex flex-col w-full gap-2 items-center">
                     <h1 class="text-3xl font-black">УНИКАЛЬНЫЕ ПРЕДМЕТЫ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник с 00:00 до 23:00</p>
                  </div>

                  <div class="unique_car_cards cardsBuy" v-if="shopItems">
                     <div class="cards" v-for="shopItem in shopItems['data']" @click="buyItemModal(shopItem)">
                        <CardHeader :value="shopItem"/>
                        <div class="relative">
                           <img src="/ayazik/icons/time.svg" class="absolute right-0 top-[12px]"
                                v-if="shopItem.item_type === 1"/>
                           <img :src="shopItem.item.icon" alt="" class="photo">
                        </div>
                        <h1 class="cardName">{{ shopItem.item.name }}</h1>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>
               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'unique_value'">
                  <div class="flex flex-col w-full gap-2 items-center">
                     <h1 class="text-3xl font-black">УНИКАЛЬНЫЕ ПРЕДМЕТЫ</h1>
                     <p class="text-lg opacity-30 font-bold text-center">Все выводы товаров происходят каждый
                        понедельник с 00:00 до 23:00</p>
                  </div>

                  <div class="unique_value_cards cardsBuy" v-if="shopItems">
                     <div class="cards" v-for="shopItem in shopItems['data']" @click="buyItemModal(shopItem)">
                        <CardHeader :value="shopItem"/>
                        <div class="relative">
                           <img src="/ayazik/icons/time.svg" class="absolute right-0 top-[12px]"
                                v-if="shopItem.item_type === 1"/>
                           <img :src="shopItem.item.icon" alt="" class="photo">
                        </div>
                        <h1 class="cardName">{{ shopItem.item.name }}</h1>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>
               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'setting'">
                  <div class="flex flex-col w-full gap-2 items-center">
                     <h1 class="text-3xl font-black">НАСТРОЙКИ</h1>
                  </div>

                  <div v-if="settings">
                     <div style="border: 1px dashed #FFFFFF26; padding: 26px">
                        <div class="flex items-center">
                           <p class="p" style="opacity: 1">Игровой логин:</p>
                           <p class="p ml-auto" style="opacity: 1">{{ user.nickname ?? 'Не привязан' }}</p>
                        </div>
                        <div class="flex items-center">
                           <p class="p" style="opacity: 1">Дата регистрации:</p>
                           <p class="p ml-auto" style="opacity: 1">
                              {{ new Date(user.created_at).toISOString().slice(0, 10) }}</p>
                        </div>
                        <div class="flex items-center">
                           <p class="p" style="opacity: 1">Лайков:</p>
                           <p class="p ml-auto" style="opacity: 1">{{ settings.reaction.like }} шт</p>
                        </div>
                        <div class="flex items-center">
                           <p class="p" style="opacity: 1">Комментариев:</p>
                           <p class="p ml-auto" style="opacity: 1">{{ settings.reaction.comment }} шт</p>
                        </div>
                     </div>

                     <div v-if="user.telegram_id === '' || user.vkontakte_id === ''"
                          class="flex gap-4 font-black items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between mt-4"
                          @click="transferModal(1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img
                                 src='/ayazik/icons/telegram.svg'
                                 alt="">
                           </div>
                           <div class="flex flex-col uppercase">
                              <span>ПРИВЯЗАТЬ ТЕЛЕГРАМ АККАУНТ</span>
                              <span style="color: #FFFFFFA3;">НЕ ПРИВЯЗАН</span>
                           </div>
                        </div>
                     </div>
                     <div v-if="user.telegram_id === '' || user.vkontakte_id === ''"
                          class="flex gap-4 font-black items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between mt-4"
                          @click="transferModal(2)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img
                                 src='/ayazik/icons/VK.svg'
                                 alt="">
                           </div>
                           <div class="flex flex-col uppercase">
                              <span>ПРИВЯЗАТЬ ВК АККАУНТ</span>
                              <span style="color: #FFFFFFA3;">НЕ ПРИВЯЗАН</span>
                           </div>
                        </div>
                     </div>
                  </div>
                  <LoaderComponent v-else/>
               </section>
               <section class="flex flex-col w-full gap-12" v-if="selectPage === 'admin'">
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
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminItems', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                             class="h-[18px]"></div>
                           <h2 class="">УПРАВЛЕНИЕ ПРЕДМЕТАМИ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminTask', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                             class="h-[18px]"></div>
                           <h2 class="">УПРАВЛЕНИЕ ПОДПИСЧИКАМИ [логика не сделано пока]</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminNotification', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/gift.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">УПРАВЛЕНИЕ УВЕДОМЛЕНИЯМИ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminShop', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/gift.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">УПРАВЛЕНИЕ МАГАЗИНОМ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminAuction', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/gift.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">УПРАВЛЕНИЕ АУКЦИОНОМ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminMailing', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/gift.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">УПРАВЛЕНИЕ РАССЫЛКИ [логика не сделано пока]</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminPromocode', 1)">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4">
                              <img src="/ayazik/gift.svg" alt="" class="h-[18px]">
                           </div>
                           <h2 class="">УПРАВЛЕНИЕ ПРОМОКОДАМИ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>

                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('adminWithdraw')">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                             class="h-[18px]"></div>
                           <h2 class="">ВЫВОД ИГРОВЫХ ТОВАРОВ</h2>
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
                     <div
                        class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between"
                        @click="changePage('promocode')">
                        <div class="flex items-center gap-4">
                           <div class="bg-[#FFFFFF0F] rounded-full p-4"><img src="/ayazik/gift.svg" alt=""
                                                                             class="h-[18px]"></div>
                           <h2 class="">ПРОМОКОДЫ</h2>
                        </div>
                        <img src="/ayazik/arrow-right.svg" alt="">
                     </div>
                  </div>
               </section>

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
      </main>

      <div id="portal-target"></div>
      <div class="modal-promo" v-if="isBuyAuction">
         <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-title">
               <div class="content">
                  ВЫ ХОТИТЕ СДЕЛАТЬ СТАВКУ:
               </div>
            </div>

            <div class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between">
               <div class="mx-auto flex items-center gap-2">
                  <img :src="auctionItem.item.icon" alt="" style="height: 50px">
                  <p class="w-[110px]" style="font-weight: 800">{{ auctionItem.item.name }}</p>
               </div>
            </div>

            <p class="footer" v-if="auctionItem.item_type == 1">
                    <span
                       style="opacity: 65%">Внимание, временный предмет! В случае покупки, предмет будет выдан на </span>
               <span style="color: #fff !important;"> {{ auctionItem.item_count }} часов</span>.
            </p>

            <InputZ v-model="auctionInput" placeholder="мин. 5 000"
                    label="Введите сумму ставки, она должна быть больше последней на 10 монет:"/>

            <button
               @click="auctionItemButton"
               class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
               <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
               <p class="text-lg font-black text-center uppercase">Применить</p>
            </button>

            <p class="text-red-500 font-medium text-center mt-3 text-xm" v-if="!responseAuctionItem.status">
               {{ responseAuctionItem.message }}
            </p>

            <p class="text-green-500 font-medium text-center mt-3 text-xm" v-if="responseAuctionItem.status">
               {{ responseAuctionItem.message }}
            </p>
         </div>
      </div>
      <div class="modal-promo" v-if="isCoupon">
         <div class="modal-content"
              :style="responseCoupon.status === 200 ? 'background-repeat: no-repeat; background-size: cover; border-top-left-radius: 30px; border-top-right-radius: 30px;' : ''"
              :class="responseCoupon.status ? 'unique_value' : ''">
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
                  <span v-if="responseCoupon.status !== 200">ввести промокод</span>
                  <span v-else>промокод применён!</span>
               </div>
            </div>

            <div v-if="responseCoupon.status === 200">
               <div class="grid gris-cols-2 gap-4 cardsBuy" style="overflow: inherit; height: 100%">
                  <div class="cards" v-for="shopItem in responseCoupon.item"
                       @click="selectPromocodeItem(shopItem, responseCoupon.item, selectedCouponItems, responseCoupon.select)"
                       :class="{ selected: shopItem.selected }"
                       style="height: 190px"
                  >
                     <div class="relative">
                        <img src="/ayazik/icons/time.svg" class="absolute right-0 top-[12px]"
                             v-if="shopItem.item_type === 1"/>
                        <img :src="shopItem.item.icon" alt="" class="mx-auto"
                             :class="shopItem.item.skin ? 'h-[159px]' : ''">
                     </div>
                     <h1 class="promo_title">{{ shopItem.item.name }}</h1>
                  </div>
               </div>

               <button
                  @click="buttonSaveCoupon"
                  class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                  <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
                  <p class="text-lg font-black text-center uppercase">
                     {{
                        selectedCouponItems.length >= responseCoupon.select ? 'Сохранить' : (selectedCouponItems.length === 0 ? 'Выберите награду' : `Выберите ещё ${responseCoupon.select - selectedCouponItems.length} награду`)
                     }}
                  </p>
               </button>
            </div>

            <div v-else>
               <div class="modal-input">
                  <p>Введите ваш промокод</p>
                  <input type="text" id="large-input"
                         :style="{'border': responseCoupon.status && responseCoupon.status === 200 ? '1px solid green' : responseCoupon.status ? '1px solid #EA3F3F' : ''}"
                         placeholder="Например, 1aA2-3bB4" v-model="inputCoupon">
               </div>

               <p class="text-[#EA3F3F]" style="font-weight: 700; font-size: 14px;"
                  v-if="responseCoupon.status !== 200">{{ responseCoupon.message }}</p>
               <p class="text-green-500" style="font-weight: 700; font-size: 14px;" v-else>
                  {{ responseCoupon.message }}</p>

               <button
                  @click="buttonCoupon"
                  class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                  <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
                  <p class="text-lg font-black text-center uppercase">Применить</p>
               </button>
            </div>
         </div>
      </div>

      <div class="modal-promo" v-if="isTransferShow">
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
                  <span>привязка аккаунта</span>
               </div>
            </div>

            <div v-if="transferType === 1">
               <div class="modal-input">
                  <p class="p">Введите сгенерированный код в приложение {{ user.telegram_id === '' ? 'Telegram' : 'VK'}}</p>
                  <input type="text" id="large-input"
                         placeholder="Например, 1aA2-3bB4"
                         v-model="transferGenerateCode"
                         readonly
                         @click="transferCopyToClipboard"
                  >
                  <p class="text-green-500 font-black text-xm" v-if="transferResponse">{{
                        transferResponse.message
                     }}</p>
               </div>

               <button
                  @click="transferCode"
                  class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                  <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
                  <p class="text-lg font-black text-center uppercase">
                     СГЕНЕРИРОВАТЬ НОВЫЙ КОД
                  </p>
               </button>
            </div>
            <div v-else>
               <div class="modal-input">
                  <p class="p">Вставьте код, который вы получили в приложении {{ user.telegram_id === '' ? 'Telegram' : 'VK'}}</p>
                  <input type="text" id="large-input"
                         placeholder="Например, 1aA2-3bB4"
                         v-model="transferInput"
                  >
                  <p class="text-green-500 font-black text-xm" v-if="transferResponse && transferResponse?.status">
                     {{ transferResponse.message }}</p>
                  <p class="text-red-500 font-black text-xm" v-if="transferResponse && !transferResponse?.status">
                     {{ transferResponse.message }}</p>
               </div>

               <button
                  @click="transferActivateCode"
                  class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                  <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
                  <p class="text-lg font-black text-center uppercase">
                     перейти
                  </p>
               </button>
            </div>
         </div>
      </div>

      <div class="modal-promo" v-if="isWithdrawModal">
         <div class="modal-content" v-if="!responseWithdrawItem.message">
            <div class="modal-header"></div>
            <div class="modal-title">
               <div class="content">
                  ВЫ ХОТИТЕ вывести:
               </div>
            </div>

            <div class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-center">
               <img :src="inventorySelectWithdrawItem.item.icon" alt="" style="height: 50px">
               <p class="w-[110px]" style="font-weight: 800">{{ inventorySelectWithdrawItem.item.name }}</p>
            </div>

            <button
               @click="inventoryWithdraw"
               class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
               <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
               <p class="text-lg font-black text-center uppercase">Применить</p>
            </button>

            <p class="text-red-500 font-medium text-center mt-3 text-xm" v-if="!responseWithdrawItem.status">
               {{ responseWithdrawItem.message }}
            </p>

            <p class="text-green-500 font-medium text-center mt-3 text-xm" v-if="responseWithdrawItem.status">
               {{ responseWithdrawItem.message }}
            </p>
         </div>
         <div class="modal-content" v-else>
            <div class="modal-header"></div>
            <div class="modal-title">
               <div>
                  <img :src="responseWithdrawItem.icon"/>
               </div>
               <div class="content">{{ responseWithdrawItem.title }}</div>
            </div>

            <p class="p" style="padding-top: 15px">{{ responseWithdrawItem.message }}</p>

            <button
               class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]"
               @click="whithdrawClose()">
               <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
               <p class="text-lg font-black text-center uppercase">хорошо</p>
            </button>
         </div>
      </div>
      <div class="modal-promo" v-if="isBuyItem">
         <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-title">
               <div class="content">
                  ВЫ ХОТИТЕ приобрести:
               </div>
            </div>

            <div class="flex gap-4 items-center bg-[#FFFFFF0F] p-4 rounded-full text-white justify-between">
               <div class="flex items-center gap-2">
                  <img :src="buyItem.item.icon" alt="" style="height: 50px">
                  <p class="w-[110px]" style="font-weight: 800">{{ buyItem.item.name }}</p>
               </div>
               <p class="flex items-center gap-2">
                  <span style="color: #fddc4e" class="uppercase">{{ buyItem.price }}</span>
                  <img src="/ayazik/donate.svg" alt="" class="h-6">
               </p>
            </div>

            <p class="footer" v-if="buyItem.item_type == 1">
                    <span
                       style="opacity: 65%">Внимание, временный предмет! В случае покупки, предмет будет выдан на</span>
               <span style="color: #fff !important;">{{ buyItem.item_count }} часов</span>.
            </p>

            <button
               @click="buyItemButton()"
               class="flex bg-[#47C840] text-white items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
               <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
               <p class="text-lg font-black text-center uppercase">ПРИОБРЕСТИ</p>
            </button>

            <p class="text-green-500 font-medium text-center mt-3 text-xm" v-if="responseBuyItem.status">
               {{ responseBuyItem.message }}
            </p>
         </div>
      </div>
      <div class="modal-promo" v-if="isCheckNotification">
         <div class="modal-content">
            <div class="modal-header"></div>
            <div class="modal-title justify-center">
               <div class="content text-center">
                  {{ notification.description }}
               </div>
            </div>

            <img :src="notification.image" alt="" class="h-[340px]"/>
            <div class="notification mt-3">
               <p class="p mb-3">Получите за выполнение:</p>

               <div class="prize">
                  <div v-for="item in notification.items" class="item mb-2">
                     <div class="item__circle"></div>
                     <img :src="item.item_details.icon" alt="">
                     <p>{{ item.item_details.name }} x {{ item.count }} штук</p>
                  </div>
               </div>
            </div>

            <a :href="notification.href"
               class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
               <!--                <img src="/ayazik/group.svg" alt="" class="w-6">-->
               <p class="text-lg font-black text-center uppercase">перейти</p>
            </a>
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

               <a href="https://vk.com/atriumru"
                  class="flex bg-white text-black items-center gap-4 justify-center w-full py-6 rounded-3xl mt-[20px]">
                  <p class="text-lg font-black text-center uppercase">подписаться на vk Donut</p>
               </a>
            </div>
         </div>
      </div>
   </div>
</template>

<style scoped>

.active-item_nav {
   opacity: 1;
}

.unique_car {
   background-image: url("/public/img/unique_car.png");
}

.unique_skin {
   background-image: url("/public/img/unique_skin.png");
}

.unique_item {
   background-image: url("/public/img/unique_item.png");
}

.unique_value {
   background-image: url("/public/img/unique_value.png");
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

.modal-content .footer {
   font-family: Akrobat;
   font-size: 20px;
   font-weight: 500;
   margin-top: 20px;
   margin-bottom: 20px;
   line-height: 25px;
}

.modal-content .footer span {
   color: #fff !important;
}

.count {
   height: 45px;
   border-radius: 50px;
   border: 1.97px solid #FFFFFF0D;
   padding-left: 15px;
   font-size: 15px;
   display: flex;
   align-items: center;
   gap: 8px;
   margin: auto;
   justify-content: end;
   padding-right: 15px;
}

.count h1 {
   font-size: 23px;
   font-weight: 700;
   text-align: center;
}

.promo_title {
   font-size: 23px;
   font-weight: 800;
   position: absolute;
   bottom: 7px;
   left: 0;
   right: 0;
   margin: auto;
   text-align: center;
   max-width: 150px;
   line-height: 25px;
}

.cards.selected {
   background: #6c696940;
}
</style>
