<template>
  <div>

    <AmChart></AmChart>

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
import { mapState } from 'vuex'
import AmChart from '@/components/AmChart'

export default {
  components: {
    AmChart
  },
  computed: mapState(['listItems'])
}
</script>
