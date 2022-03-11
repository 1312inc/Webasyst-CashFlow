<template>
  <div>
    <ul v-if="list.length" class="menu">
      <li
        v-for="contact in list"
        :key="contact.id"
        :class="{
          selected: +$route.params.id === contact.id && $route.name === 'Contact'
        }"
      >
        <router-link :to="`/contact/${contact.id}`">
          <span class="icon"
            ><img :src="contact.photo_url_absolute" alt="" class="userpic"
          /></span>
          <span>{{ contact.name }}</span>
          <span class="count">
            <span
              v-for="(currency, i) in contact.stat"
              :key="currency.currency"
            >
              {{ currency.data.summaryShorten }}
              {{ $helper.currencySignByCode(currency.currency) }}
              <span v-if="i < contact.stat.length - 1">,</span>
            </span>
          </span>
        </router-link>
      </li>
      <template v-if="loading">
        <li
          v-for="i in limit"
          :key="i"
          class="skeleton custom-px-16"
          style="padding: 6px 0;"
        >
          <span class="skeleton-list" style="margin-bottom: 0;"></span>
        </li>
      </template>
    </ul>
    <div v-if="list.length < total" class="custom-mx-16">
      <a @click.prevent="offset += limit" href="#" class="button light-gray rounded smaller">{{
        $t("showMore")
      }}</a>
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
