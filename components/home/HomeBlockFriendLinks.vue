<template>
  <div class="hb-friend-links">
    <span class="hb-friend-label">友情链接：</span>
    <ul class="hb-friend-items">
      <li v-for="item in items" :key="item.id">
        <a
          v-if="isExternal(item)"
          :href="item.link"
          target="_blank"
          rel="noopener"
          :title="item.title"
        >
          {{ item.title }}
        </a>
        <NuxtLink v-else :to="`/article/${item.id}`" :title="item.title">
          {{ item.title }}
        </NuxtLink>
      </li>
      <li v-if="!items.length" class="hb-empty">暂无链接</li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock, HomeBlockItem } from '~/utils/homeBlocks'

/**
 * 友情链接版式（老站首页底部那条）：
 * 数据仍来自「友情链接」栏目（category 417 的 menu/list），文章 link 为外链时新窗口打开。
 */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])
const isExternal = (item: HomeBlockItem) => Boolean(item.link && /^https?:/.test(item.link))
</script>
