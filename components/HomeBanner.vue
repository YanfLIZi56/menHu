<template>
  <div ref="rootEl" class="swiper ad1" style="overflow: hidden">
    <div class="swiper-wrapper">
      <div
        v-for="item in items"
        :key="item.id"
        class="swiper-slide"
      >
        <NuxtLink
          :to="bannerTarget(item).link"
          :target="bannerTarget(item).external ? '_blank' : undefined"
          :title="item.title"
        >
          <img :src="item.image" :alt="item.title" />
        </NuxtLink>
      </div>
    </div>

    <div class="swiper-pagination" />
    <div class="swiper-button-prev" />
    <div class="swiper-button-next" />
  </div>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'
import { bannerTarget, type Banner } from '~/composables/useApi'

import 'swiper/css'
import 'swiper/css/effect-flip'
import 'swiper/css/navigation'
import 'swiper/css/pagination'

/**
 * 首页轮播图：还原老站 Swiper 配置
 * effect: flip / slidesPerView:1 / centeredSlides / autoHeight / autoplay 3000 / loop
 * 注意：loop 需要至少 3 张图，后台只配 1~2 张时关闭 loop，避免 Swiper 警告与空白。
 */
const props = defineProps<{ items: Banner[] }>()

const rootEl = ref<HTMLElement | null>(null)
let swiper: any = null

onMounted(async () => {
  if (!rootEl.value || props.items.length === 0) return

  // Swiper 只在浏览器端加载，避免 SSR 阶段访问 window
  const [{ default: Swiper }, { EffectFlip, Navigation, Pagination, Autoplay }] =
    await Promise.all([import('swiper'), import('swiper/modules')])

  const canLoop = props.items.length >= 3

  swiper = new Swiper(rootEl.value, {
    modules: [EffectFlip, Navigation, Pagination, Autoplay],
    direction: 'horizontal',
    effect: 'flip',
    slidesPerView: 1,
    centeredSlides: true,
    autoHeight: true,
    loop: canLoop,
    autoplay: props.items.length > 1
      ? {
          delay: 3000,
          stopOnLastSlide: false,
          disableOnInteraction: true,
        }
      : false,
    pagination: {
      el: rootEl.value.querySelector('.swiper-pagination') as HTMLElement,
    },
    navigation: {
      nextEl: rootEl.value.querySelector('.swiper-button-next') as HTMLElement,
      prevEl: rootEl.value.querySelector('.swiper-button-prev') as HTMLElement,
    },
  })

  if (canLoop) swiper.slideToLoop(0, 0, false)
})

onBeforeUnmount(() => {
  swiper?.destroy?.(true, true)
})
</script>
