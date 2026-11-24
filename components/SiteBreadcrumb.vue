<template>
  <div class="breadcrumb-wrap">
    <span class="layui-breadcrumb" style="visibility: visible">
      <NuxtLink to="/"><i class="iconfont icon-dingwei" />首页</NuxtLink>
      <template v-for="(item, index) in items" :key="index">
        <span lay-separator>›</span>
        <NuxtLink v-if="item.link" :to="item.link">{{ item.title }}</NuxtLink>
        <a v-else><cite>{{ item.title }}</cite></a>
      </template>
    </span>
  </div>
</template>

<script setup lang="ts">
/**
 * 面包屑：layui 的 breadcrumb 默认 visibility:hidden，需要 layui.js 才显示，
 * 这里用 CSS 覆盖为可见，并手动补上分隔符。
 */
defineProps<{ items: { title: string; link?: string }[] }>()
</script>
