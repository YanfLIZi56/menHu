# 汕头市红十字会门户（Nuxt 3 重构版）

原站点是 ThinkPHP + Vue2/jQuery/layui 的静态 H5 页面（仓库 `main` 分支）。本分支用 **Nuxt 3** 重建门户，保留原站视觉，接口按模块化规范重新设计，**不含注册 / 登录**。

- 版式：原 `/h5/` 1200px 宽屏版，样式与老站像素级对齐（layui + 原 `style.css` + 原 iconfont）
- 渲染：SSR（`ssr: true`），标题 / 描述 / 面包屑均服务端输出，满足政务站 SEO 收录
- 数据：默认走内置 mock；配置环境变量即可切到若依网关，前端零改动

## 快速开始

```bash
npm install
npm run dev          # http://localhost:3000
npm run build        # 产物在 .output/
node .output/server/index.mjs
```

环境变量（见 `.env.example`）：

```bash
NUXT_API_BASE=http://127.0.0.1:8080/prod-api   # 真实后端网关，仅服务端使用
NUXT_MOCK=false                                # 关掉内置 mock
NUXT_PUBLIC_FILE_BASE=http://127.0.0.1:8080    # 可选：上传图片域名，不填自动取网关的协议+域名
```

## 路由

| 路由 | 说明 | 老站对应 |
| --- | --- | --- |
| `/` | 首页（轮播、网上办事、信息公开、新闻资讯、专题、红会事业、志愿服务、捐赠公示、友情链接） | `/h5/index.html` |
| `/list/[categoryId]` | 栏目列表（面包屑、左侧栏目导航、分页）；导航 `templateType=1` | `/h5/newList.html?cxsortid=` |
| `/page/[categoryId]` | 单页栏目（栏目直接显示一篇文章）；导航 `templateType=2` | `/h5/newInfo.html?cxid=` |
| `/article/[id]` | 文章详情（列表点进，含视频播放） | `/h5/newInfo.html?cxid=` |
| `/guestbook` | 救护员培训在线报名 | `/h5/guestBook.html` |

老站 `/h5/**`、`/mobile/**` 已在 `nuxt.config.ts` 的 `routeRules` 中 301 到新站，避免外链与搜索结果失效。

## 目录结构

```
.
├── assets/css/           # layui.css / iconfont / legacy.css（原 style.css 原样搬运）/ site.css
├── components/           # SiteHeader（导航接口驱动）、SiteFooter、SiteBreadcrumb、HomeBanner、BackToTop
├── composables/useApi.ts # 门户接口统一封装 + 字段归一化
├── layouts/default.vue
├── pages/                # index / list/[id] / page/[categoryId] / article/[id] / guestbook
├── public/
│   ├── images/theme/     # 头部底纹、Logo、标题底纹、回到顶部等原站素材
│   ├── images/demo/      # mock 演示图（轮播、新闻、二维码、专题）
│   └── fonts/            # 原站 iconfont 与 layui 字体
├── server/
│   ├── api/[...path].ts  # /api/** 统一出口：mock 或转发到 NUXT_API_BASE
│   └── mock/             # 与正式接口同路径的示例数据
├── utils/menu.ts         # 栏目 → 路由/外链解析（templateType=1 列表、2 单页）
├── utils/site.ts         # 首页各版块入口、联系方式等静态配置
└── docs/api.md           # 接口文档（给后端）
```

## 接口

导航与列表按新版「菜单接口」实现（来自后端默认模块 OpenAPI）。
外链规则：栏目不做外链，单页栏目绑定的文章若 `link` 为 http(s)（非视频），优先跳外链（页面 302 / 列表新窗口）。

| 方法 | 路径 | 用途 |
| --- | --- | --- |
| GET | `/category/category/menu` | 门户导航栏目树（含 templateType / isExternal） |
| GET | `/article/article/menu/list?categoryId=&templateType=&pageNum=&pageSize=` | 栏目文章列表（列表页 + 首页各版块） |
| GET | `/article/article/menu/{categoryId}?templateType=` | 单页栏目内容 |
| GET | `/article/article/menu/info/{id}` | 文章详情（响应同 `menu/{categoryId}`） |
| GET | `/system/banner/list` | 首页轮播图（后端未开发，暂用 mock） |
| POST | `/guestbook/guestbook/add` | 培训报名 / 留言 |

浏览器端不要求 token；详细字段、示例与老站字段映射见 [`docs/api.md`](./docs/api.md)。

## 与老站的差异（有意为之）

1. 去掉右上角「注册 / 登陆」与会员中心（需求明确新站不做账号体系）。
2. 导航父级菜单可点击（老站只作为 hover 容器），其余视觉不变。
3. 新增 301 规则、`useSeoMeta`、语义化链接，提升收录。
4. 老站的 layui / jQuery / Vue2 运行时全部移除，仅保留其 CSS 与图标字体。

## 部署建议

- 门户独立部署（Node SSR：`node .output/server/index.mjs`，由 Nginx 反代到 3000），后台管理仍是单独的 Vue3 项目（`/admin`），两者不合并。
- 若要纯静态托管，可执行 `npm run generate` 得到 `.output/public`，上传 CDN / OSS 即可；动态留言需要保留一个后端接口。
- 后续可加 `@nuxtjs/sitemap` 生成 `sitemap.xml`、`@nuxt/image` 做图片压缩。
