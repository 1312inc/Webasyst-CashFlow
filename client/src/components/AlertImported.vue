<script setup>
import { onMounted, ref, watch } from 'vue'
import { useRouter, useRoute } from 'vue-router/composables'
import store from '@/store'

const router = useRouter()
const route = useRoute()
const show = ref(false)

onMounted(() => {
  if (router.currentRoute.query.show_ss_import_hint) {
    if (route.name === 'Account' && store.getters['account/getById'](+route.params.id)?.stat.summary) {
      show.value = true
    }

    const query = { ...router.currentRoute.query }
    delete query.show_ss_import_hint
    router.replace({ query })

    watch(() => route.fullPath, () => {
      show.value = false
    })
  }
})

</script>

<template>
  <div
    style="max-width: 1000px;"
    class="custom-m-12 custom-ml-32 custom-ml-12-mobile"
  >
    <div
      v-if="show"
      class="alert success small"
    >
      <div class="flexbox space-16 full-width">
        <div>
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
