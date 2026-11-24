<template>
  <button
    v-show="visible"
    class="back-to-top"
    type="button"
    title="回到顶部"
    aria-label="回到顶部"
    @click="toTop"
  >
    <span class="arrow" />
  </button>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

/**
 * 一键回到顶部：固定右下角，红底圆形 + 纯 CSS 箭头（不使用图片）。
 * 下滚 240px 后出现，点击平滑滚回顶部。
 */
const visible = ref(false)

const onScroll = () => {
  visible.value = window.scrollY > 240
}

const toTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

onMounted(() => {
  onScroll()
  window.addEventListener('scroll', onScroll, { passive: true })
})

onBeforeUnmount(() => {
  window.removeEventListener('scroll', onScroll)
})
</script>
