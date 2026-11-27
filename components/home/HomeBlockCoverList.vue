<template>
  <div class="hb-cover-list">
    <div class="hb-cover-left">
      <NuxtLink v-if="first" :to="`/article/${first.id}`" :title="first.title">
        <img
          :src="first.cover || '/images/theme/nopic.gif'"
          :alt="first.title"
        />
      </NuxtLink>
    </div>
    <ul class="hb-cover-right">
      <li v-for="item in items" :key="item.id">
        <NuxtLink :to="`/article/${item.id}`" :title="item.title">
          <i class="layui-icon layui-icon-right" style="font-size: 11px; color: #ccc" />
          {{ item.title }}
        </NuxtLink>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock } from '~/utils/homeBlocks'

/** 应急救护：左侧大图 + 右侧标题列表（首条即左图，其余全部列出） */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])
const first = computed(() => items.value[0])
</script>
