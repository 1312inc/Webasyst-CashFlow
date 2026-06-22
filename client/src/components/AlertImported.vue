<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router/composables'

const router = useRouter()
const route = useRoute()
const message = ref(router.currentRoute.query)
const show = ref(false)

onMounted(() => {
  if (message.value.show_ss_import_hint || message.value.show_success_import_hint) {
    show.value = true

    const query = { ...router.currentRoute.query }
    delete query.show_ss_import_hint
    delete query.show_success_import_hint
    router.replace({ query })

    watch(() => route.fullPath, () => {
      message.value = null
      show.value = false
    })
  }
})

</script>

<template>
  <div
    v-if="show"
    style="max-width: 1000px;"
    class="custom-m-12 custom-ml-32 custom-ml-12-mobile"
  >
    <div class="alert success small">
      <div class="flexbox space-16 full-width">
        <div v-if="message['show_success_import_hint']">
          <template v-if="$i18n.locale === 'ru_RU'">
            <p>
              Поздравляем, импорт завершился успешно!
            </p>
          </template>
          <template v-else>
            <p>
              Congratulations, import has completed successfully!
            </p>
          </template>
        </div>
        <div v-if="message['show_ss_import_hint']">
          <template v-if="$i18n.locale === 'ru_RU'">
            <p>
              Таким был бы баланс в кассе интернет-магазина прямо сейчас, если бы были только доходы (продажи)
              и никаких расходов, таких как зарплаты, аренда, маркетинг, дивиденды и так далее.
            </p>
            <p>
              <strong>Счет интернет-магазина — виртуальный (пассивный).</strong> В рамках этого счета доступен
              полноценный учет, но история операций по счету на общий баланс реальных денег на сегодня не влияет.
              При этом в прогнозе будущего запланированные операции — считаются и показывают картину в целом.
            </p>
          </template>
          <template v-else>
            <p>
              This is how much cash you would have on hand right now if there were only sales
              (income), but no business expenses such as salaries, office rent, marketing, purchase &
              supplies, dividends, and so on.
            </p>
            <p>
              <strong>Online store is a virtual (passive) account.</strong> All accounting features
              are available within the account, but its transactions won’t affect the overall balance
              for today, while planned future transactions will still form the forecast.
            </p>
          </template>
        </div>
        <div>
          <a
            class="alert-close"
            @click.prevent="show = false"
          ><i class="fas fa-times" /></a>
        </div>
      </div>
    </div>
  </div>
</template>
