import { createI18n } from 'vue-i18n'

import en from "./language/en.json";
import ru from "./language/ru.json";
import kz from "./language/kz.json";
import hy from "./language/hy.json";

export default createI18n({
    locale: 'ru',
    fallbackLocale: 'en', // Язык, который будет использоваться, если перевод для текущего языка не найден
    messages: {
        en: en,
        kz: kz,
        hy: hy,
        ru: ru
    }
})
