<template>
  <div class="hb-service-list">
    <ul>
      <li
        v-for="item in entries"
        :key="item.name"
        :title="item.name"
        @click="goEntry(item)"
      >
        <i :class="item.icon" />
        <div class="wenzi">
          {{ item.name }}<br /><span>{{ item.sub }}</span>
        </div>
      </li>
    </ul>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { serviceEntries } from '~/utils/site'
import type { HomeBlock } from '~/utils/homeBlocks'

/** 网上办事：老站为静态入口（不走接口），外链新窗口、站内走路由 */
defineProps<{ block: HomeBlock }>()

const entries = computed(() => serviceEntries)

const goEntry = (item: (typeof serviceEntries)[number]) => {
  if (item.external) {
    window.open(item.link, '_blank', 'noopener')
  } else {
    navigateTo(item.link)
  }
}
</script>
