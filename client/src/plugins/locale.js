import Vue from 'vue'
import VueI18n from 'vue-i18n'
import en from '@/locales/en_US.json'
import ru from '@/locales/ru_RU.json'

Vue.use(VueI18n)

const locale = window?.appState?.lang || 'en_US'

const i18n = new VueI18n({
  locale,
  fallbackLocale: 'en_US',
  messages: {
    en_US: en,
    ru_RU: ru
  },
  pluralizationRules: {
    /**
     * @param choice {number} a choice index given by the input to $tc: `$tc('path.to.rule', choiceIndex)`
     * @param choicesLength {number} an overall amount of available choices
     * @returns a final choice index to select plural word by
     */
    ru_RU: function (choice, choicesLength) {
      // this === VueI18n instance, so the locale property also exists here

      if (choice === 0) {
        return 0
      }

      const teen = choice > 10 && choice < 20
      const endsWithOne = choice % 10 === 1

      if (choicesLength < 4) {
        return (!teen && endsWithOne) ? 1 : 2
      }
      if (!teen && endsWithOne) {
        return 1
      }
      if (!teen && choice % 10 >= 2 && choice % 10 <= 4) {
        return 2
      }

      return (choicesLength < 4) ? 2 : 3
    }
  }
})

export { locale, i18n }
