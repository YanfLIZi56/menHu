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
 */
const props = defineProps<{ items: Banner[] }>()

const rootEl = ref<HTMLElement | null>(null)
let swiper: any = null

onMounted(async () => {
  if (!rootEl.value || props.items.length === 0) return

  // Swiper 只在浏览器端加载，避免 SSR 阶段访问 window
  const [{ default: Swiper }, { EffectFlip, Navigation, Pagination, Autoplay }] =
    await Promise.all([import('swiper'), import('swiper/modules')])

  swiper = new Swiper(rootEl.value, {
    modules: [EffectFlip, Navigation, Pagination, Autoplay],
    direction: 'horizontal',
    effect: 'flip',
    slidesPerView: 1,
    centeredSlides: true,
    autoHeight: true,
    loop: true,
    autoplay: {
      delay: 3000,
      stopOnLastSlide: false,
      disableOnInteraction: true,
    },
    pagination: {
      el: rootEl.value.querySelector('.swiper-pagination') as HTMLElement,
    },
    navigation: {
      nextEl: rootEl.value.querySelector('.swiper-button-next') as HTMLElement,
      prevEl: rootEl.value.querySelector('.swiper-button-prev') as HTMLElement,
    },
  })

  swiper.slideToLoop(0, 0, false)
})

onBeforeUnmount(() => {
  swiper?.destroy?.(true, true)
})
</script>
