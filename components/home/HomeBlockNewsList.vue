<template>
  <div class="hb-news-list">
    <NuxtLink
      v-if="lead"
      :to="linkOf(lead)"
      class="hb-news-lead"
      :title="lead.title"
    >
      <div class="hb-news-lead-img">
        <img
          :src="lead.cover || '/images/theme/nopic.gif'"
          :alt="lead.title"
        />
      </div>
      <div class="hb-news-lead-text">
        <div class="title">{{ lead.title }}</div>
        <div class="contents">{{ lead.summary }}</div>
      </div>
    </NuxtLink>

    <ul class="hb-news-rows">
      <li v-for="item in rest" :key="item.id">
        <NuxtLink :to="linkOf(item)" :title="item.title">
          <span class="title">
            <i class="layui-icon layui-icon-right" style="font-size: 11px; color: #ccc" />
            {{ item.title }}
          </span>
          <span class="date">[ {{ item.publishAt }} ]</span>
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock, HomeBlockItem } from '~/utils/homeBlocks'

/** 新闻资讯：首条图文 + 其余标题列表 */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])
const lead = computed(() => items.value[0])
const rest = computed(() => items.value.slice(1))

const linkOf = (item: HomeBlockItem) =>
  item.link && /^https?:/.test(item.link) ? item.link : `/article/${item.id}`
</script>
