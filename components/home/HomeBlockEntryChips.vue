<template>
  <ul class="hb-chips">
    <li v-for="item in entries" :key="item.name">
      <NuxtLink :to="item.link">{{ item.name }}</NuxtLink>
    </li>
  </ul>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { redCrossEntries } from '~/utils/site'
import type { HomeBlock } from '~/utils/homeBlocks'

/** 红会事业：6 个栏目入口（老站静态数据），两列小卡片 */
defineProps<{ block: HomeBlock }>()

const entries = computed(() => redCrossEntries)
</script>
