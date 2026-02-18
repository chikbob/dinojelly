import { ref } from "vue"
import ru from "./ru"
import uk from "./uk"
import en from "./en"

const messages = { ru, uk, en }

// Загружаем язык из localStorage или ставим ru по умолчанию
const currentLang = ref(localStorage.getItem("lang") || "ru")

export function useI18n() {
    const t = (key) => {
        const parts = key.split(".")
        let value = messages[currentLang.value]

        for (const p of parts) {
            value = value[p]
            if (!value) return key
        }
        return value
    }

    const setLang = (lang) => {
        currentLang.value = lang
        localStorage.setItem("lang", lang)
    }

    return {
        t,
        setLang,
        currentLang
    }
}
