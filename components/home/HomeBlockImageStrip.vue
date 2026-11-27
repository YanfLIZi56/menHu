<template>
  <div class="hb-strip">
    <NuxtLink
      v-for="item in items"
      :key="item.id"
      :to="targetOf(item).to"
      :target="targetOf(item).external ? '_blank' : undefined"
      :rel="targetOf(item).external ? 'noopener' : undefined"
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

/**
 * 跳转目标：优先用条目自带的 link（子类别版块是 /list/{id}，外链是 http…），
 * 没有 link 才回落到文章详情。
 */
const targetOf = (item: HomeBlockItem) => {
  const external = Boolean(item.link && /^https?:/.test(item.link))
  if (item.link) return { to: item.link, external }
  return { to: `/article/${item.id}`, external: false }
}
</script>
