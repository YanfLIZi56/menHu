<template>
  <div class="w1200">
    <div class="home-grid">
      <HomeBlock v-for="block in blocks" :key="block.blockKey" :block="block" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { DEFAULT_HOME_BLOCKS, sortHomeBlocks, type HomeBlock } from '~/utils/homeBlocks'

/**
 * 首页完全由后台「首页区块配置」驱动：
 * GET /portal/home 一次返回 版块骨架 + 已取好的条目，
 * 版块顺序与栅格占位（rowNo / rowSpan / colStart / colSpan）都来自 home_block 表。
 * 接口异常或表为空时回落到 utils/homeBlocks.ts 的默认布局，保证首屏不空白。
 */
const api = useApi()

const { data } = await useAsyncData('portal-home', () => api.getHomePage(), {
  default: () => ({ blocks: [] as HomeBlock[] }),
})

const blocks = computed(() => {
  const remote = data.value?.blocks ?? []
  const list = remote.length ? remote : DEFAULT_HOME_BLOCKS
  return sortHomeBlocks(list).filter((b) => Number(b.isVisible) === 1)
})

useSeoMeta({
  title: '汕头市红十字会 官方网站 www.stredcross.org.cn',
  description:
    '汕头市红十字会官方门户，提供机构简介、新闻资讯、红会事业、政策法规、网上办事与公告公示等信息。',
  ogTitle: '汕头市红十字会 官方网站',
  ogDescription: '人道 博爱 奉献 —— 汕头市红十字会官方网站',
})
</script>
