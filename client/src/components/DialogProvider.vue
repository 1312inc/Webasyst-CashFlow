<script>
import Modal from '@/components/Modal'
import { nextTick } from 'vue'

export default ({
  components: {
    Modal
  },
  data: () => {
    return {
      open: false
    }
  },
  async mounted () {
    if (this.$route.query.show_ss_import_hint) {
      await nextTick()
      this.open = true
    }
  },
  methods: {
    close () {
      this.open = false
      const query = { ...this.$route.query }
      delete query.show_ss_import_hint
      this.$router.replace({ query })
    }
  }
})
</script>

<template>
  <div>
    <slot />

    <portal>
      <Modal v-if="open">
        <div class="dialog-body">
          <div class="dialog-content">
            <template v-if="$i18n.locale === 'ru_RU'">
              <p>
                Столько наличных <i>было бы</i> в кассе прямо сейчас, если бы у бизнеса были только доходы
                (продажи) и никаких расходов, таких как зарплаты, аренда, маркетинг, дивиденды и так далее.
              </p>
              <p>
                <strong>Теперь добавьте вручную или импортируйте расходные операции</strong>, и тогда
                финансовая модель бизнеса и прогноз будут построены автоматически.
              </p>
            </template>
            <template v-else>
              <p>
                This is how much cash you <i>would</i> have on hand right now if there were only sales
                (income), but no business expenses such as salaries, office rent, marketing, purchase &
                supplies, dividends, and so on.
              </p>
              <p>
                <strong>Add manually or import your historical expenses now</strong> to get your real cash
                on hand forecast.
                Both income &amp; expense transactions together will show you the real picture on how your
                business finances work.
              </p>
            </template>
          </div>
          <div class="dialog-footer">
            <div class="flexbox">
              <div class="flexbox space-12 wide">
                <button
                  class="button"
                  @click="close"
                >
                  {{ $t('close') }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Modal>
    </portal>
  </div>
</template>
