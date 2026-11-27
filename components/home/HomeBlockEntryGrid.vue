<template>
  <div class="hb-entry-grid">
    <ul class="hb-entry-list">
      <li v-for="item in entries" :key="item.name">
        <NuxtLink :to="item.link" :title="item.name">
          <i :class="item.icon" />
          <div>{{ item.name }}</div>
        </NuxtLink>
      </li>
    </ul>

    <div v-if="promoImage" class="hb-entry-promo">
      <NuxtLink v-if="promoLink" :to="promoLink">
        <img :src="promoImage" alt="爱心捐赠" />
      </NuxtLink>
      <img v-else :src="promoImage" alt="爱心捐赠" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { infoPublicEntries } from '~/utils/site'
import type { HomeBlock } from '~/utils/homeBlocks'

/** 信息公开：6 个入口 + 底部公益图，入口集合与图片都来自区块配置 */
const props = defineProps<{ block: HomeBlock }>()

const entries = computed(() => infoPublicEntries)
const promoImage = computed(() => props.block.sourceConfig?.promoImage ?? '')
const promoLink = computed(() => props.block.sourceConfig?.promoLink ?? '')
</script>
