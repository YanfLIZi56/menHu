<template>
  <div class="links hb-links">
    <span class="lefts hb-links-label">友情链接：</span>
    <ul class="lefts">
      <li v-for="item in items" :key="item.id" class="lefts">
        <NuxtLink v-if="!isExternal(item)" :to="`/article/${item.id}`">{{ item.title }}</NuxtLink>
        <a v-else :href="item.link" target="_blank" rel="noopener">{{ item.title }}</a>
      </li>
      <li v-if="!items.length" class="hb-empty">暂无链接</li>
    </ul>
    <div class="clearfix" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock, HomeBlockItem } from '~/utils/homeBlocks'

/** 友情链接：一横排文字链接，外链直接新窗口 */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])
const isExternal = (item: HomeBlockItem) => Boolean(item.link && /^https?:/.test(item.link))
</script>
