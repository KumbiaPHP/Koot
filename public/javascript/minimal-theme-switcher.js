/*!
 * Minimal theme switcher
 *
 * Pico.css - https://picocss.com
 * Copyright 2019-2023 - Licensed under MIT
 * Copyright 2024 Updated for Koot
 */

const themeSwitcher = {
  // Config
  buttonsTarget: ".theme-switcher",
  rootAttribute: "data-theme",
  localStorageKey: "kootPreferredColorScheme",

  // Init
  init() {
    this.scheme = this.schemeFromLocalStorage
    this.initSwitchers()
  },

  // Get color scheme from local storage
  get schemeFromLocalStorage() {
        return window.localStorage?.getItem(this.localStorageKey) ?? this.preferredColorScheme
  },

  // Preferred color scheme
  get preferredColorScheme() {
    return window.matchMedia?.("(prefers-color-scheme: dark)").matches ? "dark" : "light";
  },

  // Init switchers
  initSwitchers() {
    const buttons = document.querySelectorAll(this.buttonsTarget)
    buttons.forEach((button) => {
      button.addEventListener(
        "click",
        (event) => {
          event.preventDefault()
          // Set scheme
          this.toogleScheme()
        },
        false
      )
    })
  },

  // Set scheme
  set scheme(scheme) {
    if (scheme == "dark" || scheme == "light") {
      this._scheme = scheme
    }
    this.applyScheme()
    this.schemeToLocalStorage()
  },

  // Get scheme
  get scheme() {
    return this._scheme
  },

  toogleScheme() {
    this.scheme = "dark" == this.scheme ? "light" :  "dark"
  },
  // Apply scheme
  applyScheme() {
    document.querySelector("html").setAttribute(this.rootAttribute, this.scheme)
  },

  // Store scheme to local storage
  schemeToLocalStorage() {
      window.localStorage?.setItem(this.localStorageKey, this.scheme)
  },
}

// Init
themeSwitcher.init()
