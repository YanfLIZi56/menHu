<template>
  <div class="page-wrap">
    <SiteHeader />

    <!-- 老站首页/内页统一的浅色底纹 + 11px 顶部留白 -->
    <div class="page-bg">
      <slot />
    </div>

    <SiteFooter />
  </div>
</template>
