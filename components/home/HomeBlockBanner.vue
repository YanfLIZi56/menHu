<template>
  <HomeBanner :items="bannerItems" />
</template>

<script setup lang="ts">
import { computed } from 'vue'
import HomeBanner from '~/components/HomeBanner.vue'
import type { Banner } from '~/composables/useApi'
import type { HomeBlock, HomeBlockItem } from '~/utils/homeBlocks'

/** 轮播位版块：把区块条目转成 Banner 结构，复用站内轮播组件 */
const props = defineProps<{ block: HomeBlock }>()

const bannerItems = computed<Banner[]>(() =>
  (props.block.items ?? []).map((item: HomeBlockItem) => ({
    id: Number(item.id),
    title: item.title ?? '',
    image: item.image ?? '',
    linkUrl: item.linkUrl ?? null,
    resourceId: item.resourceId ?? null,
    resourceType: Number(item.resourceType ?? 0),
    openType: Number(item.openType ?? 0),
    sort: Number(item.sort ?? 0),
  })),
)
</script>
