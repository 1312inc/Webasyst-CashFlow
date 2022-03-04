<template>
  <div>
    <ul v-if="list.length" class="menu">
      <li
        v-for="contact in list"
        :key="contact.id"
        :class="{
          selected: $route.params.id === contact.id && $route.name === 'Contact'
        }"
      >
        <router-link :to="{ name: 'Contact', params: { id: contact.id } }">
          <span class="icon"
            ><img :src="contact.photo_url_absolute" alt="" class="userpic"
          /></span>
          <span>{{ contact.name }}</span>
          <span class="count">
            <span v-for="currency in contact.stat" :key="currency.currency">
              {{ currency.data.summaryShorten }} {{ currency.currency }}
            </span>
          </span>
        </router-link>
      </li>
    </ul>
    <ul v-if="loading" class="skeleton menu">
      <li v-for="i in 5" :key="i" class="custom-px-16">
        <span class="skeleton-list"></span>
      </li>
    </ul>
  </div>
</template>

<script>
import api from '@/plugins/api'
export default {
  data () {
    return {
      list: [],
      loading: false
    }
  },

  async created () {
    this.loading = true
    const { data } = await api.get('cash.contact.getList?offset=0&limit=15')
    this.list = data
    this.loading = false
  }
}
</script>
