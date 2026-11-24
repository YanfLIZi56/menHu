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
            v-for="item in mainMenu"
            :key="item.title"
            class="layui-nav-item"
          >
            <a v-if="item.external" :href="item.link" target="_blank" rel="noopener">{{ item.title }}</a>
            <NuxtLink v-else :to="item.link">
              {{ item.title }}
              <span
                v-if="item.children && item.children.length"
                class="nav-more"
                aria-hidden="true"
              />
            </NuxtLink>

            <dl v-if="item.children && item.children.length" class="layui-nav-child">
              <dd v-for="child in item.children" :key="child.title">
                <a v-if="child.external" :href="child.link" target="_blank" rel="noopener">
                  {{ child.title }}
                </a>
                <NuxtLink v-else :to="child.link">{{ child.title }}</NuxtLink>
              </dd>
            </dl>
          </li>
        </ul>
      </div>
    </div>
  </header>
</template>

<script setup lang="ts">
import { onBeforeUnmount, onMounted, ref } from 'vue'

/**
 * 站点头部：欢迎条 + Logo + 红色主导航（与老站 header.js 的模板一致）
 * 老站右侧的「注册 / 登陆」按需求去掉，新站不提供会员体系。
 */
const nowText = ref('')

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
