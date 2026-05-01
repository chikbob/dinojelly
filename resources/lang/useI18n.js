import { ref } from "vue"
import ru from "./ru"
import uk from "./uk"
import en from "./en"

const messages = { ru, uk, en }
const defaultLang = "ru"
const resolveLang = (lang) => (lang && messages[lang] ? lang : defaultLang)

// Загружаем язык из localStorage или ставим ru по умолчанию
const currentLang = ref(resolveLang(localStorage.getItem("lang")))

export function useI18n() {
    const t = (key, params = {}) => {
        const parts = key.split(".")
        let value = messages[resolveLang(currentLang.value)]

        for (const p of parts) {
            value = value[p]
            if (!value) return key
        }

        if (typeof value !== "string") {
            return value
        }

        return Object.entries(params).reduce((result, [paramKey, paramValue]) => {
            return result.replaceAll(`:${paramKey}`, String(paramValue))
        }, value)
    }

    const setLang = (lang) => {
        const nextLang = resolveLang(lang)
        currentLang.value = nextLang
        localStorage.setItem("lang", nextLang)
    }

    return {
        t,
        setLang,
        currentLang
    }
}
