// assets/controllers/dark-mode_controller.js
import { Controller } from "@hotwired/stimulus"

export default class extends Controller {
    static targets = ["toggle"]

    connect() {
        this.data.set("isDarkMode", this.getDarkModeFromCookie())
        this.updateDarkMode()
    }

    toggleDarkMode() {
        const isDarkMode = !this.isDarkMode
        this.setDarkMode(isDarkMode)
        this.updateDarkMode()
    }

    setDarkMode(isDarkMode) {
        const expirationDate = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000)
        document.cookie = `dark_mode=${isDarkMode}; expires=${expirationDate.toUTCString()}; path=/`
        this.data.set("isDarkMode", isDarkMode.toString())
    }

    updateDarkMode() {
        const isDarkMode = this.isDarkMode
        document.documentElement.classList.toggle("dark", isDarkMode)

        // Mettre à jour les SVG en fonction du mode sombre
        const button = this.toggleTarget
        if (isDarkMode) {
            // @todo changer pour la version uxComponent
            const darkIcon = `
            <svg xmlns="http://www.w3.org/2000/svg" class="size-6 shrink-0" viewBox="0 0 24 24"><path fill="currentColor" d="M12 2.25a.75.75 0 0 1 .75.75v2.25a.75.75 0 0 1-1.5 0V3a.75.75 0 0 1 .75-.75M7.5 12a4.5 4.5 0 1 1 9 0a4.5 4.5 0 0 1-9 0m11.394-5.834a.75.75 0 0 0-1.06-1.06l-1.591 1.59a.75.75 0 1 0 1.06 1.061zM21.75 12a.75.75 0 0 1-.75.75h-2.25a.75.75 0 0 1 0-1.5H21a.75.75 0 0 1 .75.75m-3.916 6.894a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 1 0-1.061 1.06zM12 18a.75.75 0 0 1 .75.75V21a.75.75 0 0 1-1.5 0v-2.25A.75.75 0 0 1 12 18m-4.242-.697a.75.75 0 0 0-1.061-1.06l-1.591 1.59a.75.75 0 0 0 1.06 1.061zM6 12a.75.75 0 0 1-.75.75H3a.75.75 0 0 1 0-1.5h2.25A.75.75 0 0 1 6 12m.697-4.243a.75.75 0 0 0 1.06-1.06l-1.59-1.591a.75.75 0 0 0-1.061 1.06z"/></svg>
            `
            button.classList.remove("text-emerald-400", "hover:bg-emerald-50")
            button.classList.add("text-emerald-400", "hover:bg-white/10")
            button.innerHTML = darkIcon
        } else {
            // @todo changer pour la version uxComponent
            const lightIcon = `
            <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke="currentColor" class="size-6 shrink-0">
                <path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.7 9.7 0 0 1 18 15.75A9.75 9.75 0 0 1 8.25 6c0-1.33.266-2.597.748-3.752A9.75 9.75 0 0 0 3 11.25A9.75 9.75 0 0 0 12.75 21a9.75 9.75 0 0 0 9.002-5.998" />
            </svg>
            `
            button.classList.remove("bg-white/10", "text-emerald-400", "hover:bg-white/20")
            button.classList.add("text-emerald-400", "hover:bg-emerald-50")
            button.innerHTML = lightIcon
        }

        // Charger le fichier CSS approprié en fonction du mode sombre
        const link = document.querySelector("link[data-theme]")
        if (link) {
            const theme = isDarkMode ? "dark" : "app"
            const cssPath = link.href.replace(/(app|dark)\.css/, `${theme}.css`)
            link.href = `${cssPath}?v=${Date.now()}` // Ajouter le paramètre aléatoire pour forcer le rechargement du fichier CSS
        }
    }

    getDarkModeFromCookie() {
        const cookieValue = document.cookie
            .split("; ")
            .find((row) => row.startsWith("dark_mode="))

        if (cookieValue) {
            // Si le cookie existe, retourner la valeur stockée dans le cookie
            return cookieValue.split("=")[1] === "true"
        } else {
            // Si le cookie n"existe pas, le créer avec une valeur par défaut (false)
            const expirationDate = new Date(Date.now() + 365 * 24 * 60 * 60 * 1000)
            document.cookie = `dark_mode=false; expires=${expirationDate.toUTCString()}; path=/`
            return false
        }
    }

    get isDarkMode() {
        return this.getDarkModeFromCookie()
    }
}