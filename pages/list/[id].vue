<template>
  <div class="w1200">
    <!-- 面包屑 -->
    <SiteBreadcrumb :items="[{ title: currentName }]" />

    <!-- 栏目分类 -->
    <div class="lefts mt" style="width: 23%">
      <div class="navLeft">
        <div class="title">{{ groupName }}</div>
        <ul>
          <NuxtLink v-for="item in sideCategories" :key="item.id" :to="menuTarget(item).to || `/list/${item.id}`">
            <li :class="{ this: item.id === categoryId }" :title="item.name">
              {{ item.name }}
            </li>
          </NuxtLink>
        </ul>
      </div>

      <div class="contactSide mt bgWhite">
        <div class="titles">联系我们</div>
        <div class="wenzi" style="padding: 0 15px 15px; font-size: 12px; line-height: 28px">
          电话号码：{{ contactInfo.tel }} <br />
          传真号码：{{ contactInfo.fax }}<br />
          邮政编码：{{ contactInfo.zip }}<br />
          地址：{{ contactInfo.address }}
        </div>
      </div>

      <div class="adSide mt">
        <img src="/images/theme/side-ad.gif" alt="红十字公益广告" />
      </div>
    </div>

    <!-- 主内容 -->
    <div
      class="mainContent rights mt"
      style="width: 76%; margin-left: 1%; background: #fff; min-height: 810px"
    >
      <div class="title2s">{{ currentName }}</div>

      <ul v-if="rows.length">
        <li v-for="item in rows" :key="item.id">
          <NuxtLink
            :to="rowLink(item)"
            :target="isRowExternal(item) ? '_blank' : undefined"
            :rel="isRowExternal(item) ? 'noopener' : undefined"
            :title="item.title"
          >
            <span class="lefts title">
              <i class="layui-icon layui-icon-right" style="font-size: 13px; color: #ccc" />
              {{ item.title }}
            </span>
            <span class="rights">[ {{ item.publishTime }} ]</span>
            <div class="clearfix" />
          </NuxtLink>
        </li>
      </ul>

      <div v-else class="cen layui-anim layui-anim-fadein" style="padding: 50px">
        <img src="/images/theme/empty.gif" alt="暂无数据" style="width: 100%; max-width: 620px" />
      </div>

      <div v-if="total > pageSize" class="pager">
        <button :disabled="pageNum <= 1" @click="goPage(pageNum - 1)">上一页</button>
        <button
          v-for="p in pageList"
          :key="p"
          :class="{ active: p === pageNum }"
          @click="goPage(p)"
        >
          {{ p }}
        </button>
        <button :disabled="pageNum >= pageCount" @click="goPage(pageNum + 1)">下一页</button>
        <span class="pager-total">共 {{ total }} 条</span>
      </div>
    </div>
    <div class="clearfix" />
  </div>
</template>

<script setup lang="ts">
import { computed, ref, watch } from 'vue'
import type { MenuNode } from '~/composables/useApi'
import { contactInfo } from '~/utils/site'
import {
  articleTarget,
  flattenMenu,
  findMenuNode,
  menuTarget,
  TEMPLATE_SINGLE,
} from '~/utils/menu'

/**
 * 栏目列表页（替代老站 newList.html）
 * 路由：/list/{categoryId}
 */
const route = useRoute()
const api = useApi()

const categoryId = computed(() => Number(route.params.id) || 412)
const pageSize = 15

/** 门户栏目树（接口驱动，含 templateType 决定单页/列表） */
const { data: menuTreeData } = await useAsyncData('portal-menu', () =>
  api.getMenuTree().catch(() => [] as MenuNode[]),
)
const categories = computed(() => flattenMenu(menuTreeData.value ?? []))

/** 当前栏目节点：单页栏目误访问 /list 时 301 到 /page */
const currentNode = computed(() => findMenuNode(menuTreeData.value ?? [], categoryId.value))
if (currentNode.value && Number(currentNode.value.templateType) === TEMPLATE_SINGLE) {
  await navigateTo(`/page/${categoryId.value}`, { redirectCode: 301 })
}

const templateType = computed(() => Number(currentNode.value?.templateType ?? 1))

const { data: listData } = await useAsyncData(
  () => `list-${categoryId.value}`,
  () =>
    api.getArticles({
      categoryId: categoryId.value,
      templateType: templateType.value,
      pageNum: 1,
      pageSize,
    }),
  { watch: [categoryId] },
)

const rows = ref<any[]>([])
const total = ref(0)
const pageNum = ref(1)

watch(
  listData,
  (value) => {
    rows.value = value?.rows ?? []
    total.value = value?.total ?? 0
    pageNum.value = 1
  },
  { immediate: true },
)

/** 列表行跳转：文章带外链（非视频）时优先外链，新窗口打开 */
const rowLink = (item: any) => articleTarget(item).href || `/article/${item.id}`
const isRowExternal = (item: any) => articleTarget(item).external

const parentOf = (id: number) =>
  categories.value.find((c) => c.id === id)?.parentId ?? 0
const childrenOf = (id: number) =>
  categories.value.filter((c) => c.parentId === id)

/** 当前栏目的名字 */
const currentName = computed(() => {
  if (currentNode.value) return currentNode.value.name
  const cat = categories.value.find((c) => c.id === categoryId.value)
  return cat?.name || '栏目'
})

/** 左侧导航分组：有子栏目就以自身为分组，否则用父级 */
const groupCategory = computed(() => {
  const current = categories.value.find((c) => c.id === categoryId.value)
  if (!current) return undefined
  if (childrenOf(current.id).length) return current
  const parent = categories.value.find((c) => c.id === current.parentId)
  if (parent && childrenOf(parent.id).length) return parent
  return current
})

const groupName = computed(() => groupCategory.value?.name ?? currentName.value)

const sideCategories = computed(() => {
  const group = groupCategory.value
  if (!group) return []
  const children = childrenOf(group.id)
  return children.length ? children : [group]
})

const pageCount = computed(() => Math.max(1, Math.ceil(total.value / pageSize)))

const pageList = computed(() => {
  const count = pageCount.value
  const current = pageNum.value
  const span = 2
  const start = Math.max(1, Math.min(current - span, count - 2 * span - 1))
  const end = Math.min(count, start + 2 * span + 1)
  const list: number[] = []
  for (let i = start; i <= end; i += 1) list.push(i)
  return list
})

const goPage = async (page: number) => {
  if (page < 1 || page > pageCount.value || page === pageNum.value) return
  const res = await api.getArticles({
    categoryId: categoryId.value,
    templateType: templateType.value,
    pageNum: page,
    pageSize,
  })
  rows.value = res.rows
  total.value = res.total
  pageNum.value = page
  window.scrollTo({ top: 0, behavior: 'smooth' })
}

useSeoMeta({
  title: () => `${currentName.value} - 汕头市红十字会`,
  description: () =>
    `汕头市红十字会${currentName.value}栏目，发布相关新闻资讯、公告与业务动态。`,
})
</script>
