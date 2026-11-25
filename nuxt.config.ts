// https://nuxt.com/docs/api/configuration/nuxt-config

// 后端网关（服务端代理用）；文件（/profile/upload/...）需要浏览器可直接访问的地址
const apiBaseEnv = process.env.NUXT_API_BASE || ''
let fileBaseEnv = process.env.NUXT_PUBLIC_FILE_BASE || ''
if (!fileBaseEnv && apiBaseEnv) {
  try {
    // 若网关是 http://host:8080/prod-api，文件默认走同源根路径 http://host:8080
    fileBaseEnv = new URL(apiBaseEnv).origin
  } catch {
    fileBaseEnv = ''
  }
}

export default defineNuxtConfig({
  compatibilityDate: '2024-11-01',
  telemetry: false,

  // 全站 SSR，保证政务门户的 SEO / 收录
  ssr: true,

  devtools: { enabled: process.env.NODE_ENV !== 'production' },

  css: [
    '~/assets/css/layui.css', // 老站依赖的 layui 基础样式（导航、面包屑、表格、按钮）
    '~/assets/css/iconfont.css', // 老站自建图标字体
    '~/assets/css/legacy.css', // 从老站 style.css 原样搬过来，保持像素级一致
    '~/assets/css/site.css', // Nuxt 侧补充与覆盖
  ],

  runtimeConfig: {
    // 后端真实网关地址，只在服务端使用（Nitro 代理，天然规避浏览器跨域）
    // 例如 http://127.0.0.1:8080/prod-api
    apiBase: apiBaseEnv,
    // 是否使用内置 mock 数据；正式联调时设置 NUXT_MOCK=false
    mock: process.env.NUXT_MOCK !== 'false',
    public: {
      // 前端统一走 /api/**，由 Nitro 转发或返回 mock
      apiPrefix: '/api',
      // 后端上传图片域名前缀；为空时保持接口返回的相对路径
      fileBase: fileBaseEnv,
      siteName: '汕头市红十字会 官方网站 www.stredcross.org.cn',
    },
  },

  app: {
    head: {
      htmlAttrs: { lang: 'zh-CN' },
      title: '汕头市红十字会 官方网站 www.stredcross.org.cn',
      meta: [
        { charset: 'utf-8' },
        { name: 'renderer', content: 'webkit' },
        { name: 'robots', content: 'all' },
        {
          name: 'keywords',
          content:
            '汕头市红十字会地址,汕头市红十字会联系方式,汕头市红十字会在哪儿,shantoushihongshizihui',
        },
        {
          name: 'description',
          content:
            '汕头市红十字会是从人道主义工作的社会救助团体。单位单独设置为副处级单位，列入群团机关管理，内设办公室、赈济救护科。',
        },
        { name: 'viewport', content: 'width=1200' },
      ],
      link: [{ rel: 'icon', href: '/favicon.ico' }],
    },
  },

  nitro: {
    compressPublicAssets: true,
  },

  // 老站地址兼容由 server/middleware/legacy-redirect.ts 处理（301 到新路由）
})
