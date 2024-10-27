<script setup>
import { ref, onMounted } from "vue";
import { gsap } from "gsap";
import { ScrollTrigger } from "gsap/ScrollTrigger";


gsap.registerPlugin(ScrollTrigger);

const preloader = ref(null);

onMounted(() => {
    // Анимация для заголовка
    gsap.from("h1", {
        opacity: 0,
        y: -50,
        duration: 1,
        ease: "power2.out",
    });

    // Анимация для кнопки "launch app"
    gsap.from(".launch-app-button", {
        opacity: 0,
        y: 50,
        duration: 1,
        ease: "power2.out",
    });

    // Анимация для блоков с преимуществами при скролле
    gsap.utils.toArray(".advantage-block").forEach((block) => {
        gsap.from(block, {
            opacity: 0,
            y: 50,
            duration: 1,
            ease: "power2.out",
            scrollTrigger: {
                trigger: block,
                start: "top 80%",
                toggleActions: "play none none reverse",
            },
        });
    });

    // Прелоадер
    if (preloader.value) {
        gsap.to(preloader.value, {
            width: "100%",
            duration: 2,
            ease: "power2.inOut",
            onComplete: () => {
                preloader.value.style.display = "none";
            },
        });
    }
});
</script>

<template>
    <main class="max-w-[1200px] w-screen mx-auto p-2 gap-8 relative min-h-screen overflow-hidden z-10 h-full flex flex-col text-white">
        <div ref="preloader" class="preloader">
            <div class="preloader-bar"></div>
        </div>
        <header class="flex justify-between w-full items-center py-2">
            <div class="p-1 rounded-3xl border w-12">
                <img src="/img/duck.svg" alt="" class="duration-500 invert hover:invert-0">
            </div>
            <div class="flex items-center gap-4">
                <a href="" class="max-sm:hidden">
                    <img src="/img/tg.svg" alt="" class="w-8 border hover:bg-[#04A08B] duration-500 rounded-3xl">
                </a>
                <a href="" class="max-sm:hidden">
                    <img src="/img/getgems.svg" alt="" class="w-8 border hover:bg-[#04A08B] duration-500 rounded-3xl">
                </a>
                <a href="" class="max-sm:hidden">
                    <img src="/img/twitter.svg" alt="" class="w-8 border hover:bg-[#04A08B] duration-500 rounded-3xl">
                </a>
                <a href="" class="px-4 py-2 border rounded-3xl">Get Started</a>
                <button>
                    <svg xmlns="http://www.w3.org/2000/svg" width="24px" height="24px" viewBox="0 0 48 48">
                        <path fill="currentColor"
                              d="M10 14a4 4 0 1 0 0-8a4 4 0 0 0 0 8m14 0a4 4 0 1 0 0-8a4 4 0 0 0 0 8m14 0a4 4 0 1 0 0-8a4 4 0 0 0 0 8M10 28a4 4 0 1 0 0-8a4 4 0 0 0 0 8m14 0a4 4 0 1 0 0-8a4 4 0 0 0 0 8m14 0a4 4 0 1 0 0-8a4 4 0 0 0 0 8M10 42a4 4 0 1 0 0-8a4 4 0 0 0 0 8m14 0a4 4 0 1 0 0-8a4 4 0 0 0 0 8m14 0a4 4 0 1 0 0-8a4 4 0 0 0 0 8"/>
                    </svg>
                </button>
            </div>
        </header>

        <section class="flex flex-col w-full items-center p-8 gap-6">
            <h1 class="text-5xl text-center font-bold">Give the world freedom<br> and be part of it</h1>
            <p class="text-center">Our goal is freedom of speech and no censorship.</p>
            <button class="px-4 py-2 bg-white text-black border gap-4 rounded-3xl flex items-center justify-center launch-app-button">
                <img src="/img/telegram.svg" alt="" class="h-7">launch app
            </button>
            <div class="flex p-8 rounded-3xl">
                <img src="/img/lendbn.png" alt="">
            </div>
        </section>

        <section class="flex flex-wrap gap-4">
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">The most powerful neural network</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">NFT Artists</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">Music & Video Hosting</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">Marketplace and Exchange</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">P2P Market</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">WEB3 Universe</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">Secure Messenger</span>
            <span class="px-4 py-2 hover:bg-[#ed75ff] font-mono font-bold hover:text-black rounded-3xl bg-black border duration-500">AR & VR support</span>
        </section>

        <section class="flex flex-wrap gap-4 justify-between">
            <div class="bg-op-black w-[49%] max-lg:w-full backdrop-blur-sm border border-[#00FFDD] p-6 rounded-3xl gap-8 flex flex-col advantage-block">
                <div class="flex max-lg:flex-col max-lg:gap-8 justify-between">
                    <p class="font-medium w-11/12 max-lg:w-full font-mono">Мы не стремимся изменить мир — он изменяется сам по себе. Наша цель состоит в том, чтобы люди обрели подлинную свободу и независимость, чтобы каждый мог быть самим собой и свободно выражать свои мысли, не боясь осуждения. Мы верим, что истинная свобода начинается с внутреннего принятия и смелости быть честным перед собой и окружающими.</p>
                    <div class="w-6/12 flex justify-end max-lg:w-full max-lg:justify-start">
                        <img src="/img/freedom.svg" alt="" class="w-24 h-24 float-right">
                    </div>
                </div>
            </div>

            <div class="bg-op-black w-[49%] max-lg:w-full backdrop-blur-sm border p-6 rounded-3xl gap-8 flex flex-col advantage-block">
                <div class="flex max-lg:flex-col max-lg:gap-8 justify-between">
                    <p class="font-medium w-11/12 max-lg:w-full font-mono">Платформа абсолютно бесплатна и открывает перед вами уникальные возможности для заработка, не требуя никаких вложений. Просто наслаждайтесь использованием системы и играйте в игры, создавайте любой контент — пусть ваше увлечение приносит вам доход.</p>
                    <div class="w-6/12 flex justify-end max-lg:w-full max-lg:justify-start">
                        <img src="/img/free-use.svg" alt="" class="w-24 h-24 float-right">
                    </div>
                </div>
            </div>

            <div class="bg-op-black w-[49%] max-lg:w-full backdrop-blur-sm border p-6 rounded-3xl gap-8 flex flex-col advantage-block">
                <div class="flex max-lg:flex-col max-lg:gap-8 justify-between">
                    <p class="font-medium w-11/12 max-lg:w-full font-mono">Платформа открывает безграничные возможности для творчества и свободы. Здесь вы можете безопасно создавать любой контент без цензуры и страха перед последствиями. Общайтесь свободно и используйте первый в мире децентрализованный самообучающийся ИИ с прямым доступом в интернет — всё это без сбора личных данных и без контроля со стороны государства и третьих лиц.</p>
                    <div class="w-6/12 flex justify-end max-lg:w-full max-lg:justify-start">
                        <img src="/img/endless.svg" alt="" class="w-24 h-24 float-right">
                    </div>
                </div>
            </div>

            <div class="bg-op-black w-[49%] max-lg:w-full backdrop-blur-sm border border-[#00FFDD] p-6 rounded-3xl gap-8 flex flex-col advantage-block">
                <div class="flex max-lg:flex-col max-lg:gap-8 justify-between">
                    <p class="font-medium w-11/12 max-lg:w-full font-mono">Платформа полностью децентрализована и не зависит от отдельных серверов. Мы разработали уникальный протокол, который позволяет хранить данные "в движении" между пользователями в сильно сжатом и зашифрованном виде. Это обеспечивает высокую безопасность и конфиденциальность, делая вашу деятельность на платформе свободной от вмешательства третьих лиц.</p>
                    <div class="w-6/12 flex justify-end max-lg:w-full max-lg:justify-start">
                        <img src="/img/security.svg" alt="" class="w-24 h-24 float-right">
                    </div>
                </div>
            </div>
        </section>

        <section class="flex flex-col w-full gap-6 p-4 bg-op-black border-op-br border rounded-3xl">
            <h1 class="text-3xl">RoadMap</h1>
            <div class="backdrop-blur-sm w-full">
                <div class="border-l border-dashed border-[#00ffdd85] gap-4 py-4 flex flex-col">
                    <div class="flex gap-2 items-center relative">
                        <div class="absolute left-[-4px] w-2 h-2 bg-[#00FFDD] rounded-full"></div>
                        <hr class="w-12 border-[#00FFDD] border-t">
                        <span class="flex gap-2 items-center">
                            <p class="text-[#00FFDD]">Q3 2023 - Q4 2024</p>
                            <p class="max-sm:w-6/12">#Development and testing of the platform</p>
                        </span>
                    </div>

                    <div class="flex gap-2 items-center relative opacity-50">
                        <div class="absolute left-[-4px] w-2 h-2 bg-[#00FFDD] rounded-full"></div>
                        <hr class="w-12 border-[#00FFDD] border-t">
                        <span class="flex gap-2 items-center">
                            <p class="text-[#00FFDD]">Q1 2025</p>
                            <p class="max-sm:w-6/12">#Closed testing of the alpha version of the neural network with the best players & Airdrop for World</p>
                        </span>
                    </div>

                    <div class="flex gap-2 items-center relative opacity-50">
                        <div class="absolute left-[-4px] w-2 h-2 bg-[#00FFDD] rounded-full"></div>
                        <hr class="w-12 border-[#00FFDD] border-t">
                        <span class="flex gap-2 items-center">
                            <p class="text-[#00FFDD]">Q2 2025</p>
                            <p class="max-sm:w-6/12">#Public Beta Realise AI & Grandiose Airdrop</p>
                        </span>
                    </div>

                    <div class="flex gap-2 items-center relative opacity-50">
                        <div class="absolute left-[-4px] w-2 h-2 bg-[#00FFDD] rounded-full"></div>
                        <hr class="w-12 border-[#00FFDD] border-t">
                        <span class="flex gap-2 items-center">
                            <p class="text-[#00FFDD]">Q3 2025 - Q4 2025</p>
                            <p class="max-sm:w-6/12">#Public Realise Platform</p>
                        </span>
                    </div>
                </div>
            </div>
            <div class="flex flex-wrap justify-between gap-8">
                <img src="/block/ton.svg" class="w-16" alt="">
                <img src="/block/binance.svg" class="w-16" alt="">
                <img src="/block/ethereum.svg" class="w-16" alt="">
                <img src="/block/tron.svg" class="w-16" alt="">
                <img src="/block/solana.svg" class="w-16" alt="">
                <img src="/block/cosmos.svg" class="w-16" alt="">
                <img src="/block/arbitrum.svg" class="w-16" alt="">
            </div>
        </section>
    </main>
</template>

<style scoped>
.preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 4px;
    background-color: #00FFDD;
    z-index: 9999;
    overflow: hidden;
}

.preloader-bar {
    width: 0;
    height: 100%;
    background-color: #04A08B;
}
</style>