<template>
  <div class="hb-text-wrap">
    <ul v-if="items.length" class="hb-text-list">
      <li v-for="item in items" :key="item.id">
        <NuxtLink
          :to="item.link && /^https?:/.test(item.link) ? item.link : `/article/${item.id}`"
          :title="item.title"
        >
          <span class="hb-text-title">
            <i class="layui-icon layui-icon-right" style="font-size: 11px; color: #ccc" />
            {{ item.title }}
          </span>
          <span v-if="showDate" class="hb-text-date">{{ item.publishAt }}</span>
          <div class="clearfix" />
        </NuxtLink>
      </li>
    </ul>
    <div v-else class="hb-empty">暂无内容</div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { HomeBlock } from '~/utils/homeBlocks'

/** 纯标题列表：通知公告、捐赠公示共用；条数多时（捐赠公示）额外显示日期 */
const props = defineProps<{ block: HomeBlock }>()

const items = computed(() => props.block.items ?? [])
const showDate = computed(() => items.value.length > 5)
</script>
