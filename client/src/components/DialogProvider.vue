<script setup>
import { useStorage } from '@vueuse/core'
import Modal from '@/components/Modal'
import { appState } from '@/utils/appState'

const showSsInstalledInfo = useStorage('cash_show_ss_installed_info', appState.shopscriptInstalled ?? 0)

function navigateShopSettings () {
  showSsInstalledInfo.value = 0
  window.location.href = `${appState.baseUrl}shop/settings/`
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
                :src="`${appState.baseStaticUrl}img/shop.svg`"
                alt=""
                style="height: 72px; width: 72px; object-fit: contain;"
              >
              <div class="icon">
                <i class="fas fa-arrow-right" />
              </div>
              <img
                :src="`${appState.baseStaticUrl}img/cash.png`"
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
                  class="button green rounded"
                  @click.prevent="navigateShopSettings"
                >
                  {{ $i18n.locale === 'ru_RU' ? 'Включить импорт данных из Shop-Script' : 'Import orders data from Shop-Script' }}
                </a>
              </div>
              <div>
                <button
                  class="button light-gray rounded"
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
  </div>
</template>
