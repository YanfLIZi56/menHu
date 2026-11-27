<template>
  <ul class="hb-card-list">
    <li v-for="item in items" :key="item.id">
      <NuxtLink :to="`/article/${item.id}`" :title="item.title">
        <img
          :src="item.cover || '/images/theme/nopic.gif'"
          class="yinying"
          :alt="item.title"
        />
        <div class="title">{{ item.title }}</div>
      </NuxtLink>
    </li>
    <li v-if="!items.length" class="hb-empty">暂无内容</li>
  </ul>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock } from '~/utils/homeBlocks'

/** 志愿服务：3 张图文卡片并排 */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])
</script>
