<template>
  <div>
    <template v-if="list.length">
      <ul class="menu">
        <li
          v-for="contact in list"
          :key="contact.id"
          :class="{
            selected:
              +$route.params.id === contact.id && $route.name === 'Contact'
          }"
        >
          <router-link :to="`/contact/${contact.id}`">
            <div class="wide">
              <div class="flexbox">
                <span class="icon"><img
                  :src="contact.photo_url_absolute"
                  alt=""
                  class="userpic"
                ></span>
                <div class="wide">
                  <span>{{ contact.name }}</span>
                </div>
              <!-- <span class="count">
                <span
                  v-for="(currency, i) in contact.stat"
                  :key="currency.currency"
                >
                  {{ currency.data.summaryShorten }} {{ $helper.currencySignByCode(currency.currency) }}<span v-if="i < contact.stat.length - 1">,</span>
                </span>
              </span> -->
              </div>
              <div class="flexbox">
                <span class="icon" />
                <div class="smaller hint custom-mt-4">
                  {{ contact.last_transaction.amountShorten }}
                  {{ $helper.currencySignByCode(contact.last_transaction.currency) }},
                  {{
                    $moment(contact.last_transaction.date).format("D MMMM")
                  }}
                </div>
              </div>
            </div>
          </router-link>
        </li>
        <template v-if="loading">
          <li
            v-for="i in limit"
            :key="i"
            class="skeleton custom-px-16"
            style="padding: 6px 0;"
          >
            <span
              class="skeleton-list"
              style="margin-bottom: 0;"
            />
          </li>
        </template>
      </ul>
      <div
        v-if="list.length < total"
        class="custom-mx-16 align-center"
      >
        <a
          class="button light-gray rounded smaller"
          @click.prevent="offset += limit"
        >{{ $t("showMore") }}</a>
      </div>
    </template>
    <div
      v-else
      class="hint align-center custom-my-12"
    >
      {{ $t('contactsWillSoon') }}
    </div>
  </div>
</template>

<script>
import api from '@/plugins/api'
export default {
  data () {
    return {
      list: [],
      loading: false,
      offset: 0,
      limit: 15,
      total: 0
    }
  },

  watch: {
    offset: 'fetch'
  },

  mounted () {
    this.fetch()
  },

  methods: {
    async fetch () {
      this.loading = true
      const { data } = await api.get(
        `cash.contact.getList?offset=${this.offset}&limit=${this.limit}`
      )
      this.list = [...this.list, ...data.data]
      this.total = data.total
      this.loading = false
    }
  }
}
</script>
