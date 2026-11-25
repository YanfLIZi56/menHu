<template>
  <div class="w1200">
    <SiteBreadcrumb :items="breadcrumbItems" />

    <div class="newInfoContent mt" style="background: #fff; min-height: 810px">
      <div v-if="article">
        <div class="title">
          {{ article.title }}
          <div v-if="article.publishTime" class="more">时间：{{ article.publishTime }}</div>
        </div>

        <div class="contents newInfoContents" v-html="article.content" />
      </div>

      <div v-else class="empty-state">
        <p>该栏目内容待维护</p>
        <NuxtLink to="/">返回首页</NuxtLink>
      </div>
    </div>
    <div class="clearfix" />
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import type { MenuNode } from '~/composables/useApi'
import {
  findMenuNode,
  menuPath,
  menuTarget,
  TEMPLATE_SINGLE,
} from '~/utils/menu'

/**
 * 单页栏目（导航 templateType=2）：栏目直接展示一篇文章，
 * 数据来自 GET /article/article/menu/{categoryId}。
 * 路由：/page/{categoryId}
 * 若该栏目其实是列表栏目，301 回 /list/{id}。
 */
const route = useRoute()
const api = useApi()

const categoryId = computed(() => Number(route.params.categoryId))

const { data: menuTreeData } = await useAsyncData('portal-menu', () =>
  api.getMenuTree().catch(() => [] as MenuNode[]),
)

const currentNode = computed(() => findMenuNode(menuTreeData.value ?? [], categoryId.value))

if (currentNode.value && Number(currentNode.value.templateType) !== TEMPLATE_SINGLE) {
  await navigateTo(`/list/${categoryId.value}`, { redirectCode: 301 })
}

const { data: article } = await useAsyncData(
  () => `page-${categoryId.value}`,
  () =>
    api
      .getMenuArticle(categoryId.value, Number(currentNode.value?.templateType ?? TEMPLATE_SINGLE))
      .catch(() => null),
  { watch: [categoryId] },
)

/** 面包屑：父栏目可点击，当前栏目不带链接；相邻同名（413 与 446 都叫“机构简介”）只保留一个 */
const breadcrumbItems = computed(() => {
  const trail = menuPath(menuTreeData.value ?? [], categoryId.value)
  if (!trail.length) return [{ title: currentNode.value?.name || '栏目内容' }]

  const merged: { title: string; link?: string }[] = []
  trail.forEach((node) => {
    const link = menuTarget(node).to || `/list/${node.id}`
    const last = merged[merged.length - 1]
    // 同名相邻节点合并，保留后一个（更靠近当前页）
    if (last && last.title === node.name) last.link = link
    else merged.push({ title: node.name, link })
  })

  // 最后一项是当前页，不带链接
  delete merged[merged.length - 1].link
  return merged
})

const pageTitle = computed(
  () => currentNode.value?.name || article.value?.title || '栏目内容',
)

useSeoMeta({
  title: () => `${pageTitle.value} - 汕头市红十字会`,
  description: () => `${pageTitle.value}，汕头市红十字会官方网站。`,
})
</script>
