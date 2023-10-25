<script setup>
import { onMounted, ref } from 'vue'
import { useRouter } from 'vue-router/composables'

const router = useRouter()
const show = ref(false)

onMounted(() => {
  if (router.currentRoute.query.show_ss_import_hint) {
    show.value = true

    const query = { ...router.currentRoute.query }
    delete query.show_ss_import_hint
    router.replace({ query })
  }
})

</script>

<template>
  <div
    class="c-header custom-py-0"
    style="max-width: 1000px;"
  >
    <div
      v-if="show"
      class="alert success"
    >
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
  </div>
</template>
