<template>
  <div class="w1200">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <div
      class="newInfoContent mt"
      style="background: #fff; min-height: 810px"
    >
      <div v-if="article">
        <div class="title">
          {{ article.title }}
          <div class="more">时间：{{ article.publishTime }}</div>
        </div>

        <div v-if="isVideo" class="cen">
          <video :src="article.link" controls style="width: 80%; margin: 50px auto" autoplay />
        </div>

        <div class="contents newInfoContents" v-html="article.content" />
      </div>

      <div v-else class="empty-state">
        <p>内容不存在或已下线</p>
        <NuxtLink to="/">返回首页</NuxtLink>
      </div>
    </div>
    <div class="clearfix" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'

/**
 * 文章 / 单页详情（替代老站 newInfo.html）
 * 路由：/article/{id}
 */
const route = useRoute()
const api = useApi()

const articleId = computed(() => Number(route.params.id))

const { data: article, error } = await useAsyncData(
  () => `article-${articleId.value}`,
  async () => {
    try {
      return await api.getArticle(articleId.value)
    } catch {
      return null
    }
  },
  { watch: [articleId] },
)

const categoryName = computed(() => {
  // 接口若返回 categoryName 可直接用；mock 已带，真实后端可选择性实现
  return (article.value as any)?.categoryName || ''
})

const breadcrumbItems = computed(() => {
  const items: { title: string; link?: string }[] = []
  if (categoryName.value) {
    items.push({ title: categoryName.value, link: `/list/${article.value?.categoryId ?? 412}` })
  }
  items.push({ title: '信息详情' })
  return items
})

const isVideo = computed(() => {
  const link = (article.value?.link || '').toLowerCase()
  return link.endsWith('.mp4') || link.endsWith('.m3u8')
})

if (error.value) {
  // 接口异常时不要让整页 500，仍然渲染站点头尾
  console.warn('[article] 加载失败：', error.value.message)
}

useSeoMeta({
  title: () => (article.value ? `${article.value.title} - 汕头市红十字会` : '信息详情 - 汕头市红十字会'),
  description: () => article.value?.summary || '汕头市红十字会官方网站信息详情',
  ogTitle: () => article.value?.title || '信息详情',
})
</script>
