<template>
  <header
    class="site-header"
    style="background: url('/images/theme/strip.gif') repeat-x top"
  >
    <div
      class="header-band"
      style="
        background: url('/images/theme/header-band.gif') no-repeat center;
        height: 180px;
      "
    >
      <div class="w1200">
        <div class="header-welcome">
          欢迎您访问汕头市红十字会官方网站!
          <span class="welcome-time">{{ nowText }}</span>
        </div>
        <div class="logins">
          <NuxtLink to="/" title="返回首页">
            <img src="/images/theme/logo.gif" alt="汕头市红十字会" />
          </NuxtLink>
        </div>
      </div>
    </div>

    <div style="background: #ee0000">
      <div class="nav w1200">
        <ul class="layui-nav">
          <li
            v-for="item in navItems"
            :key="item.name"
            class="layui-nav-item"
          >
            <!-- 有子栏目的条目只作为下拉容器，不提供点击跳转（老站行为） -->
            <a
              v-if="item.children.length"
              class="nav-root"
              aria-haspopup="true"
            >
              {{ item.name }}
              <span class="nav-more" aria-hidden="true" />
            </a>
            <a
              v-else-if="item.href"
              :href="item.href"
              target="_blank"
              rel="noopener"
            >{{ item.name }}</a>
            <NuxtLink v-else :to="item.to || '/'">{{ item.name }}</NuxtLink>

            <dl v-if="item.children.length" class="layui-nav-child">
              <dd v-for="child in item.children" :key="child.name">
                <a v-if="child.href" :href="child.href" target="_blank" rel="noopener">
                  {{ child.name }}
                </a>
                <NuxtLink v-else :to="child.to || '/'">{{ child.name }}</NuxtLink>
              </dd>
            </dl>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { computed, onBeforeUnmount, onMounted, ref } from 'vue'
import type { MenuNode } from '~/composables/useApi'
import { mainMenu } from '~/utils/site'

/**
 * 站点头部：欢迎条 + Logo + 红色主导航（与老站 header.js 的模板一致）
 * 导航数据来自 /category/category/menu：templateType=2 走单页 /page/{id}，
 * 其余走列表 /list/{id}；接口异常时回退到本地 mainMenu 配置。
 * 老站右侧的「注册 / 登陆」按需求去掉，新站不提供会员体系。
 */
const nowText = ref('')
const api = useApi()

const { data: menuTree } = await useAsyncData('portal-menu', () =>
  api.getMenuTree().catch(() => [] as MenuNode[]),
)

interface NavChild {
  name: string
  to?: string
  href?: string
  external: boolean
}

interface NavItem extends NavChild {
  children: NavChild[]
}

const toNavChild = (node: MenuNode): NavChild => ({
  name: node.name,
  ...menuTarget(node),
})

/** 接口不可用时的兜底：沿用 utils/site.ts 里的静态菜单 */
const fallbackNav: NavItem[] = mainMenu.map((item) => ({
  name: item.title,
  to: item.external ? undefined : item.link,
  href: item.external ? item.link : undefined,
  external: Boolean(item.external),
  children: (item.children ?? []).map((child) => ({
    name: child.title,
    to: child.external ? undefined : child.link,
    href: child.external ? child.link : undefined,
    external: Boolean(child.external),
  })),
}))

const navItems = computed<NavItem[]>(() => {
  const tree = (menuTree.value ?? []) as MenuNode[]
  const items: NavItem[] = tree.length
    ? tree.map((node) => ({
        name: node.name,
        ...menuTarget(node),
        children: (node.children ?? []).map(toNavChild),
      }))
    : fallbackNav

  return [{ name: '首页', to: '/', external: false, children: [] }, ...items]
})

const pad = (n: number) => String(n).padStart(2, '0')
const formatNow = () => {
  const d = new Date()
  return `${d.getFullYear()}-${pad(d.getMonth() + 1)}-${pad(d.getDate())} ${pad(
    d.getHours(),
  )}:${pad(d.getMinutes())}:${pad(d.getSeconds())}`
}

let timer: ReturnType<typeof setInterval> | undefined
onMounted(() => {
  nowText.value = formatNow()
  timer = setInterval(() => {
    nowText.value = formatNow()
  }, 1000)
})
onBeforeUnmount(() => timer && clearInterval(timer))
</script>
