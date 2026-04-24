<script setup>
import { appState } from '@/utils/appState'
</script>

<template>
  <div>
    <!-- Текст для русских -->
    <template v-if="$i18n.locale === 'ru_RU'">
      <h2 class="custom-mt-8">
        Рассказываем, с чего начать
      </h2>
      <p>
        Чтобы быстро увидеть полезность приложения, попробуйте добавить несколько типовых операций, которые
        происходят регулярно — каждый день, неделю, месяц. Приложение автоматически посчитает движение средств и
        покажет нужные отчеты и графики. Пробные операции затем всегда легко удалить.
      </p>
      <ol class="separated">
        <li>Нажмите <strong>«Сразу много»</strong>.</li>
        <li>
          Добавьте <strong>3-5 повторяющихся операций</strong>, например, план продаж (каждый день), кофе в офис (каждую неделю), аренда, интернет, зарплаты
          (каждый месяц).
        </li>
        <li><strong>Профит!</strong> Увидите, сколько денег будет на счете и когда.</li>
      </ol>
      <p>
        Подробнее о всех фишках приложения рассказываем и показываем на сайте <a
          href="https://support.webasyst.ru/cash/"
          target="_blank"
        ><b>https://support.webasyst.ru/cash/</b></a>
      </p>
      <p>
        А еще не забудьте установить наше мобильное приложение:
      </p>
    </template>

    <!-- Текст для нерусских -->
    <template v-else>
      <h2> Explaining where to start </h2>
      <p>
        To quickly see the app ultimate purpose, try adding a few typical transactions that occur or a
        regular basis. The app will automatically calculate your cash flow and display all the necessary
        reports and charts. Test transactions can always be easily deleted.
      </p>
      <ol class="separated">
        <li>Click <strong>‘Add Many’</strong>.</li>
        <li>
          Add <strong>3—5 recurring transactions</strong>, e.g. sales plan (daily), rent, internet, salaries (monthly), and
          office supplies (weekly).
        </li>
        <li><strong>Profit!</strong> See how much money will be in your account and when.</li>
      </ol>
      <p>
        Sharing more about the app top features and use cases here: <a
          href="https://support.webasyst.ru/cash/"
          target="_blank"
        ><b>https://support.webasyst.com/cash/</b></a>
      </p>
      <p>
        And don’t forget to install our cozy mobile app and to keep your money in sync:
      </p>
    </template>

    <div
      class="flexbox wrap-mobile middle space-12"
      style="justify-content: center;"
    >
      <a
        :href="`https://apps.apple.com/${$i18n.locale === 'ru_RU' ? 'ru' : 'us'}/app/webasyst-cash-flow/id6450029747`"
        target="_blank"
      >
        <img
          :src="`${appState.baseStaticUrl}img/badge-appstore${$i18n.locale === 'ru_RU' ? '-ru' : ''}.png`"
          class="c-app-inline-badge"
          style="height: 50px;"
          alt="App Store"
        >
      </a>
      <a
        href="https://play.google.com/store/apps/details?id=com.webasyst.cash"
        target="_blank"
      >
        <img
          :src="`${appState.baseStaticUrl}img/badge-googleplay${$i18n.locale === 'ru_RU' ? '-ru' : ''}.png`"
          class="c-app-inline-badge"
          style="height: 50px;"
          alt="Google Play"
        >
      </a>
      <template v-if="$i18n.locale === 'ru_RU'">
        <a
          href="https://apps.rustore.ru/app/com.webasyst.cash"
          target="_blank"
        >
          <img
            :src="`${appState.baseStaticUrl}img/badge-rustore.svg`"
            class="c-app-inline-badge"
            style="height: 50px;"
            alt="RuStore"
          >
        </a>
      </template>
    </div>
  </div>
</template>
