<template>
  <div class="w1200">
    <!-- ================= 网上办事 + 轮播图 ================= -->
    <div class="banShi lefts" style="width: 22%; background: #fff; height: 330px">
      <div class="titles">网上办事</div>
      <div class="cen w92">
        <ul>
          <li
            v-for="item in serviceEntries"
            :key="item.name"
            :title="item.name"
            @click="goService(item)"
          >
            <i :class="item.icon" />
            <div class="wenzi">
              {{ item.name }}<br /><span>{{ item.sub }}</span>
            </div>
          </li>
        </ul>
      </div>
    </div>

    <div
      class="banner rights"
      style="width: 77%; margin-left: 1%; background: #fff; height: 330px"
    >
      <HomeBanner :items="banners" />
    </div>
    <div class="clearfix" />

    <!-- ================= 信息公开 + 新闻资讯 ================= -->
    <div class="mt" style="background: #fff">
      <div class="xinXiGongKai lefts" style="width: 22%; height: 473px">
        <div style="border: 1px solid #eee; margin: 13px 0 13px 13px">
          <div class="titles">信息公开</div>
          <div>
            <ul>
              <NuxtLink
                v-for="item in infoPublicEntries"
                :key="item.name"
                :to="item.link"
                class="lefts anim"
              >
                <li>
                  <i :class="item.icon" />
                  <div>{{ item.name }}</div>
                </li>
              </NuxtLink>
            </ul>
            <div class="clearfix" />
          </div>
        </div>
        <div class="smallBanner">
          <NuxtLink to="/article/1319">
            <img src="/images/theme/small-banner.gif" alt="爱心捐赠" />
          </NuxtLink>
        </div>
      </div>

      <div class="XinWen rights" style="width: 77%; margin-left: 1%; height: 473px">
        <div class="titles" style="margin-left: 0">
          <NuxtLink to="/list/412">新闻资讯</NuxtLink>
          <span class="more">
            <NuxtLink to="/list/412">
              更多 <i class="layui-icon layui-icon-next" />
            </NuxtLink>
          </span>
        </div>

        <div class="newList">
          <ul>
            <li v-for="(item, key) in news" :key="item.id">
              <!-- 第一条：图 + 摘要 -->
              <div v-if="key === 0">
                <NuxtLink
                  :to="`/article/${item.id}`"
                  class="yinying"
                  :title="item.title"
                >
                  <div class="lefts">
                    <img
                      :src="item.cover || '/images/theme/nopic.gif'"
                      class="yinying"
                      style="width: 281px; height: 170px"
                      :alt="item.title"
                    />
                  </div>
                  <div class="rights" style="width: 67%">
                    <div class="title">{{ item.title }}</div>
                    <div class="contents">{{ item.summary }}</div>
                  </div>
                  <div class="clearfix" />
                </NuxtLink>
              </div>

              <!-- 其余：纯标题 + 日期 -->
              <div v-else>
                <NuxtLink :to="`/article/${item.id}`" :title="item.title">
                  <span class="lefts title">
                    <i
                      class="layui-icon layui-icon-right"
                      style="font-size: 11px; color: #ccc"
                    />
                    {{ item.title }}
                  </span>
                  <span class="rights">[ {{ item.publishTime }} ]</span>
                  <div class="clearfix" />
                </NuxtLink>
              </div>
            </li>
          </ul>
        </div>
      </div>
      <div class="clearfix" />
    </div>

    <!-- ================= 专题专栏 ================= -->
    <div class="ztzl bgWhite mt">
      <div class="titles">专题专栏</div>
      <div class="ad2" style="margin: 15px 15px 0; overflow: hidden; padding-bottom: 15px">
        <div>
          <NuxtLink
            v-for="item in specials"
            :key="item.id"
            :to="item.link || `/article/${item.id}`"
            :target="item.link ? '_blank' : undefined"
          >
            <img
              :src="item.cover || '/images/theme/nopic.gif'"
              class="yinying"
              style="margin-right: 5px"
              :alt="item.title"
            />
          </NuxtLink>
        </div>
      </div>
    </div>

    <!-- ================= 红会事业 / 应急救护 / 通知公告 / 志愿服务 ================= -->
    <div class="lefts mt" style="width: 77%">
      <div class="hhsy lefts" style="width: 30%; background: #fff; height: 252px">
        <div class="titles">红会事业</div>
        <ul class="hongHuiShiYe">
          <NuxtLink
            v-for="item in redCrossEntries"
            :key="item.name"
            :to="item.link"
          >
            <li class="anim">{{ item.name }}</li>
          </NuxtLink>
        </ul>
      </div>

      <div
        class="gongYiXiangMu hhsy lefts"
        style="width: 69%; margin-left: 1%; background: #fff; height: 252px"
      >
        <div class="titles">
          应急救护
          <span class="more">
            <NuxtLink to="/list/426">
              更多 <i class="layui-icon layui-icon-next" />
            </NuxtLink>
          </span>
        </div>
        <div>
          <div class="lefts">
            <img
              :src="jijiu[0]?.cover || '/images/theme/nopic.gif'"
              :alt="jijiu[0]?.title || '应急救护'"
            />
          </div>
          <div class="rights" style="width: 63%">
            <NuxtLink
              v-for="item in jijiu"
              :key="item.id"
              :to="`/article/${item.id}`"
              :title="item.title"
            >
              <span class="title">
                <i
                  class="layui-icon layui-icon-right"
                  style="font-size: 11px; color: #ccc"
                />
                {{ item.title }}
              </span>
              <div class="clearfix" />
            </NuxtLink>
          </div>
          <div class="clearfix" />
        </div>
      </div>

      <div class="indexVideo lefts mt bgWhite" style="width: 30%; height: 252px">
        <div class="titles">
          通知公告
          <span class="more">
            <NuxtLink to="/list/436">
              更多 <i class="layui-icon layui-icon-next" />
            </NuxtLink>
          </span>
        </div>
        <div class="tongZhiGongGao">
          <NuxtLink
            v-for="item in notices"
            :key="item.id"
            :to="`/article/${item.id}`"
            :title="item.title"
          >
            <span class="title">
              <i
                class="layui-icon layui-icon-right"
                style="font-size: 11px; color: #ccc"
              />
              {{ item.title }}
            </span>
            <div class="clearfix" />
          </NuxtLink>
        </div>
      </div>

      <div
        class="zhiYuanZhe lefts mt"
        style="width: 69%; margin-left: 1%; background: #fff; height: 252px"
      >
        <div class="titles">
          志愿服务
          <span class="more">
            <NuxtLink to="/list/453">
              更多 <i class="layui-icon layui-icon-next" />
            </NuxtLink>
          </span>
        </div>
        <ul>
          <li v-for="item in volunteers" :key="item.id" class="lefts">
            <NuxtLink :to="`/article/${item.id}`" :title="item.title">
              <img
                :src="item.cover || '/images/theme/nopic.gif'"
                class="yinying"
                :alt="item.title"
              />
              <div class="title">{{ item.title }}</div>
            </NuxtLink>
          </li>
        </ul>
      </div>
      <div class="clearfix" />
    </div>

    <!-- ================= 捐赠公示 ================= -->
    <div
      class="rights mt"
      style="width: 22%; margin-left: 1%; background: #fff; height: 510px"
    >
      <div class="jkgs">
        <div class="titles">
          捐赠公示
          <span class="more">
            <NuxtLink to="/list/438">
              更多 <i class="layui-icon layui-icon-next" />
            </NuxtLink>
          </span>
        </div>
        <ul style="padding: 5px">
          <li v-for="item in donate" :key="item.id" class="anim">
            <NuxtLink :to="`/article/${item.id}`" :title="item.title">
              <i
                class="layui-icon layui-icon-right"
                style="font-size: 11px; color: #ccc"
              />
              {{ item.title }}
            </NuxtLink>
          </li>
        </ul>
      </div>
    </div>
    <div class="clearfix" />
  </div>

  <!-- ================= 友情链接 ================= -->
  <div class="links mt">
    <div class="w1200" style="line-height: 67px">
      <span class="lefts" style="color: #f00">友情链接：</span>
      <ul class="lefts">
        <li v-for="item in links" :key="item.id" class="lefts">
          <a :href="item.link || '#'" target="_blank" rel="noopener">{{ item.title }}</a>
        </li>
      </ul>
      <div class="clearfix" />
    </div>
  </div>
</template>

<script setup lang="ts">
import { computed } from 'vue'
import { serviceEntries, infoPublicEntries, redCrossEntries } from '~/utils/site'

/**
 * 首页：版块顺序、宽度比例、留白全部按老站 index.html 复刻。
 * 数据来源：system/banner 轮播 + article/article/menu/list 按栏目取文章；导航由 /category/category/menu 驱动。
 */
const api = useApi()

const emptyPage = { rows: [] as any[], total: 0 }

const { data } = await useAsyncData('home-data', async () => {
  const [banners, news, jijiu, volunteers, specials, notices, donate, links] =
    await Promise.all([
      api.getBanners().catch(() => []),
      api.getArticles({ categoryId: 412, pageSize: 6 }).catch(() => emptyPage),
      api.getArticles({ categoryId: 426, pageSize: 4 }).catch(() => emptyPage),
      api.getArticles({ categoryId: 453, pageSize: 3 }).catch(() => emptyPage),
      api.getArticles({ categoryId: 441, pageSize: 6 }).catch(() => emptyPage),
      api.getArticles({ categoryId: 436, pageSize: 4 }).catch(() => emptyPage),
      api.getArticles({ categoryId: 438, pageSize: 10 }).catch(() => emptyPage),
      api.getArticles({ categoryId: 417, pageSize: 8 }).catch(() => emptyPage),
    ])

  return {
    banners,
    news: news.rows,
    jijiu: jijiu.rows,
    volunteers: volunteers.rows,
    specials: specials.rows,
    notices: notices.rows,
    donate: donate.rows,
    links: links.rows,
  }
})

const banners = computed(() => data.value?.banners ?? [])
const news = computed(() => data.value?.news ?? [])
const jijiu = computed(() => data.value?.jijiu ?? [])
const volunteers = computed(() => data.value?.volunteers ?? [])
const specials = computed(() => data.value?.specials ?? [])
const notices = computed(() => data.value?.notices ?? [])
const donate = computed(() => data.value?.donate ?? [])
const links = computed(() => data.value?.links ?? [])

/** 网上办事五项：外链新窗口打开，站内跳转走路由 */
const goService = (item: (typeof serviceEntries)[number]) => {
  if (item.external) {
    window.open(item.link, '_blank')
  } else {
    navigateTo(item.link)
  }
}

useSeoMeta({
  title: '汕头市红十字会 官方网站 www.stredcross.org.cn',
  description:
    '汕头市红十字会官方门户，提供机构简介、新闻资讯、红会事业、政策法规、网上办事与公告公示等信息。',
  ogTitle: '汕头市红十字会 官方网站',
  ogDescription: '人道 博爱 奉献 —— 汕头市红十字会官方网站',
})
</script>
