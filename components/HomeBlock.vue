<template>
  <section class="hb" :class="`hb-${block.blockKey}`" :style="homeBlockGridStyle(block)">
    <!-- 轮播位：没有标题栏，直接铺满 -->
    <HomeBlockBanner v-if="block.blockType === 'banner'" :block="block" />

    <template v-else>
      <div v-if="showTitle" class="titles">
        <NuxtLink v-if="block.moreLink" :to="block.moreLink">{{ block.title }}</NuxtLink>
        <span v-else>{{ block.title }}</span>
        <span v-if="block.moreLink" class="more">
          <NuxtLink :to="block.moreLink">
            更多 <i class="layui-icon layui-icon-next" />
          </NuxtLink>
        </span>
      </div>

      <component :is="styleComponent" :block="block" />
    </template>
  </section>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { homeBlockGridStyle, type HomeBlock } from '~/utils/homeBlocks'

/**
 * 首页版块容器：负责栅格定位、标题栏，并按 styleKey 分发到具体版式组件。
 * 注意：必须用「组件对象」而不是字符串做映射，否则 Nuxt 的按需引入扫不到，运行时会渲染空白。
 */
import HomeBlockBanner from './home/HomeBlockBanner.vue'
import HomeBlockServiceEntries from './home/HomeBlockServiceEntries.vue'
import HomeBlockEntryGrid from './home/HomeBlockEntryGrid.vue'
import HomeBlockEntryChips from './home/HomeBlockEntryChips.vue'
import HomeBlockNewsList from './home/HomeBlockNewsList.vue'
import HomeBlockImageStrip from './home/HomeBlockImageStrip.vue'
import HomeBlockCoverList from './home/HomeBlockCoverList.vue'
import HomeBlockTextList from './home/HomeBlockTextList.vue'
import HomeBlockCardList from './home/HomeBlockCardList.vue'
import HomeBlockLinkList from './home/HomeBlockLinkList.vue'

const STYLE_COMPONENTS: Record<string, any> = {
  banner: HomeBlockBanner,
  service_entries: HomeBlockServiceEntries,
  entry_grid: HomeBlockEntryGrid,
  entry_chips: HomeBlockEntryChips,
  news_list: HomeBlockNewsList,
  image_strip: HomeBlockImageStrip,
  cover_list: HomeBlockCoverList,
  text_list: HomeBlockTextList,
  card_list: HomeBlockCardList,
  link_list: HomeBlockLinkList,
}

const props = defineProps<{ block: HomeBlock }>()

const styleComponent = computed(() => STYLE_COMPONENTS[props.block.styleKey] ?? HomeBlockTextList)

/** 轮播、友情链接自带版式，不再渲染统一标题栏 */
const showTitle = computed(
  () =>
    props.block.blockType !== 'banner' &&
    props.block.styleKey !== 'link_list' &&
    Boolean(props.block.title),
)
</script>
