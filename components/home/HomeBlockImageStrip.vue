<template>
  <div class="hb-strip">
    <NuxtLink
      v-for="item in items"
      :key="item.id"
      :to="isExternal(item) ? (item.link as string) : `/article/${item.id}`"
      :target="isExternal(item) ? '_blank' : undefined"
      :rel="isExternal(item) ? 'noopener' : undefined"
      :title="item.title"
    >
      <img
        :src="item.cover || '/images/theme/nopic.gif'"
        class="yinying"
        :alt="item.title"
      />
    </NuxtLink>
    <div v-if="!items.length" class="hb-empty">暂无内容</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock, HomeBlockItem } from '~/utils/homeBlocks'

/** 专题专栏：一排图片（老站是 6 张横向广告位） */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])

const isExternal = (item: HomeBlockItem) => Boolean(item.link && /^https?:/.test(item.link))
</script>
