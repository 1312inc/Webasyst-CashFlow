<template>
  <div>

    <AmChart></AmChart>

    <table class="table-auto w-full">
      <thead>
        <tr>
          <th class="px-4 py-2">Дата</th>
          <th class="px-4 py-2">Сумма</th>
          <th class="px-4 py-2">Категория</th>
          <th class="px-4 py-2">Описание</th>
        </tr>
      </thead>
      <tbody>
        <tr
          v-for="(transaction, i) in listItems"
          :key="transaction.id"
          :class="{'bg-gray-100': i % 2 === 0}"
        >
          <td class="border px-4 py-2">{{$moment(transaction.date).format('ll')}}</td>
          <td class="border px-4 py-2">{{$numeral(+transaction.amount).format('0,0 $')}}</td>
          <td class="border px-4 py-2">{{getCategoryNameById(transaction.category_id)}}</td>
          <td class="border px-4 py-2">{{transaction.description}}</td>
        </tr>
      </tbody>
    </table>
  </div>
</template>

<script>
import { mapState, mapGetters } from 'vuex'
import AmChart from '@/components/AmChart'

export default {
  components: {
    AmChart
  },
  computed: {
    ...mapState('transaction', ['listItems']),
    ...mapGetters('category', ['getCategoryNameById'])
  }
}
</script>
