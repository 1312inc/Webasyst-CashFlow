<template>
  <div class="home">

    <Chart></Chart>

    <table>
      <thead>
        <tr>
          <th>Дата</th>
          <th>Сумма</th>
          <th>Категория</th>
          <th>Описание</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="transaction in listItems"
          :key="transaction.id"
        >
          <td>{{$moment(transaction.date).format('ll')}}</td>
          <td>{{$numeral(+transaction.amount).format('0,0 $')}}</td>
          <td>{{transaction.category_id}}</td>
          <td>{{transaction.description}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { mapState, mapActions } from 'vuex'
import Chart from '@/components/Chart'

export default {
  components: {
    Chart
  },
  computed: mapState(['listItems']),
  mounted () {
    this.getList({ from: '2019-01-01', to: '2020-09-08' })
  },
  methods: {
    ...mapActions(['getList'])
  }
}
</script>
