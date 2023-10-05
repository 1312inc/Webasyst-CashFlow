<template>
  <div>
    <!-- Categories list block -->
    <SidebarHeading
      class="custom-mt-24"
      updatingEntityName="Category"
      type="income"
    >
      {{ $t("income") }}
    </SidebarHeading>
    <SortableList
      :items="categoriesIncome"
      sortingTarget="category"
      :group="{ name: 'categoriesIncome', pull: false }"
    >
      <SortableItemCategory
        v-for="category in categoriesIncome"
        :key="category.id"
        :category="category"
      />
    </SortableList>

    <SidebarHeading updatingEntityName="Category" type="expense">
      {{ $t("expense") }}
    </SidebarHeading>
    <SortableList
      :items="categoriesExpense"
      sortingTarget="category"
      :group="{ name: 'categoriesExpense', pull: false }"
    >
      <SortableItemCategory
        v-for="category in categoriesExpense"
        :key="category.id"
        :category="category"
      />
    </SortableList>
  </div>
</template>

<script>
import { mapGetters } from 'vuex'
import SidebarHeading from './SidebarHeading'
import SortableList from './Sortable/SortableList'
import SortableItemCategory from './Sortable/SortableItemCategory'
export default {
  components: {
    SidebarHeading,
    SortableList,
    SortableItemCategory
  },

  computed: {
    ...mapGetters({
      categoriesByType: ['category/getByType']
    }),

    categoriesIncome () {
      return this.categoriesByType('income').filter(
        c => c.parent_category_id === null
      )
    },

    categoriesExpense () {
      return this.categoriesByType('expense').filter(
        c => c.parent_category_id === null
      )
    }
  }
}
</script>
