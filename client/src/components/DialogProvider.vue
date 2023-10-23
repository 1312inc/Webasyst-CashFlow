<script setup>
import { nextTick, onMounted, ref, watch } from 'vue'
import { useRouter } from 'vue-router/composables'
import { useStorage } from '@vueuse/core'
import Modal from '@/components/Modal'

const router = useRouter()
const showSsImportHint = ref(false)
const showSsInstalledInfo = useStorage('cash_show_ss_installed_info', window.appState.shopscriptInstalled ?? 0)

watch(showSsInstalledInfo, (c) => {
  console.log(c)
})

onMounted(async () => {
  await nextTick()
  if (router.currentRoute.query.show_ss_import_hint) {
    showSsImportHint.value = true
  }
})

function closeSsImportHint () {
  showSsImportHint.value = false
  const query = { ...router.currentRoute.query }
  delete query.show_ss_import_hint
  router.replace({ query })
}

</script>

<template>
  <div>
    <slot />

    <portal v-if="showSsInstalledInfo">
      <Modal>
        <div class="dialog-body">
          <div class="dialog-content">
            <div
              class="flexbox middle space-12 custom-mb-20"
              style="justify-content: center;"
            >
              <img
                :src="`/wa-apps/shop/img/shop.png`"
                alt=""
                style="height: 72px; width: 72px; object-fit: contain;"
              >
              <div class="icon">
                <i class="fas fa-arrow-right" />
              </div>
              <img
                :src="`/wa-apps/cash/img/cash.png`"
                alt=""
                style="height: 72px; width: 72px; object-fit: contain;"
              >
            </div>
            <div class="align-center">
              <div class="custom-mb-20">
                <template v-if="$i18n.locale === 'ru_RU'">
                  Интеграция с Shop-Script позволяет автоматически создавать операции на основе данных об оплаченных
                  заказов и тем самым прогнозировать прибыли и убытки интернет-магазина.
                </template>
                <template v-else>
                  Enabling Shop-Script integration allows you to automatically import sales (paid orders) data into the
                  Cash app and to forecast your future cash balance.
                </template>
              </div>
              <div class="custom-mb-20">
                <a
                  class="button"
                  :href="`${$helper.baseUrl}plugins/`"
                >
                  {{ $i18n.locale === 'ru_RU' ? 'Включить импорт данных из Shop-Script' : 'Import orders data from Shop-Script' }}
                </a>
              </div>
              <div>
                <button
                  class="button white outlined"
                  @click="showSsInstalledInfo = 0"
                >
                  {{ $i18n.locale === 'ru_RU' ? 'Начать без импорта' : 'Skip import for now' }}
                </button>
              </div>
            </div>
          </div>
        </div>
      </Modal>
    </portal>

    <portal v-if="showSsImportHint">
      <Modal>
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
            <div class="align-center">
              <button
                class="button"
                @click="closeSsImportHint"
              >
                {{ $t('close') }}
              </button>
            </div>
          </div>
        </div>
      </Modal>
    </portal>
  </div>
</template>
