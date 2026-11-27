# 汕头市红十字会门户 · 接口文档

> 面向后端开发（若依 Spring Boot 侧）。门户前端只依赖本文档中的 5 个接口，字段名可调整，但请同步修改前端 `composables/useApi.ts` 的映射层。

## 一、总体约定

图片字段（`cover`/`image`/正文 `<img>`）可以直接返回相对路径 `/profile/upload/...`，前端会按
`NUXT_PUBLIC_FILE_BASE`（未配置时自动取 `NUXT_API_BASE` 的协议+域名）补全为绝对地址。


| 项目 | 说明 |
| --- | --- |
| 网关地址 | 由环境变量 `NUXT_API_BASE` 指定，例如 `http://127.0.0.1:8080/prod-api` |
| 前端请求 | 浏览器统一请求本站 `/api/{path}`，Nitro 在服务端转发到网关，天然同源，**不需要后端开 CORS** |
| 鉴权 | 门户为公开站点，**所有接口免 token**（建议后端放行 `/article/**`、`/category/**`、`/system/banner/**`、`/leaveMessage/**`、`/portal/**`） |
| 方法 | 查询 GET，提交 POST（`Content-Type: application/json`） |
| 分页 | `pageNum` 从 1 开始，`pageSize` 默认 10 |
| 列表响应 | 若依 `TableDataInfo`：`{ "code": 200, "msg": "查询成功", "rows": [...], "total": 12 }` |
| 详情响应 | `{ "code": 200, "msg": "查询成功", "data": { ... } }` |
| 失败响应 | `{ "code": 500, "msg": "具体错误信息" }`，HTTP 状态码可用 200 或对应 4xx/5xx |

前端 `useApi` 已兼容两种外壳（`{rows,total}` 与老站 `{codes,data:{rows}}`），后端按若依风格实现即可。

## 二、门户菜单接口（新版，前端已按此实现）

来源：后端「默认模块」OpenAPI（`/category/category/menu`、`/article/article/menu/*`）。
前端导航、列表页、单页栏目都走这三条，不再用旧的 `/category/category/list` + `/article/article/list` 组装门户。

### 1. 查询导航栏目树

`GET /category/category/menu`

无参数，返回两级栏目树：

```json
{
  "msg": "操作成功",
  "code": 200,
  "data": [
    {
      "value": 413,
      "label": "机构简介",
      "sort": 4,
      "templateType": 1,
      "isExternal": 0,
      "children": [
        { "value": 446, "label": "机构简介", "sort": 25, "templateType": 2, "isExternal": 0, "children": [] }
      ]
    }
  ]
}
```

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| value | int | 栏目 id（前端当作 id 用） |
| label | string | 导航显示名 |
| sort | int | 排序（同级升序） |
| templateType | int | **1 = 列表页，2 = 单页（直接显示一篇文章）** |
| isExternal | int | 0 站内 / 1 外链（外链还需要 link 字段，见缺口） |
| children | array | 子栏目，结构同上，最多两级 |

### 2. 栏目文章列表（列表页 / 首页各版块）

`GET /article/article/menu/list`

| 参数 | 必填 | 说明 |
| --- | --- | --- |
| categoryId | 是 | 栏目 id；父栏目需要聚合所有子栏目的文章 |
| templateType | 否 | 1 列表 / 2 单页，前端会带当前栏目类型 |
| pageNum | 否 | 页码，默认 1（已与后端确认可用） |
| pageSize | 否 | 每页条数，默认 10（已与后端确认可用） |

响应为若依 `TableDataInfo`：

```json
{
  "code": 200,
  "msg": "查询成功",
  "total": 2,
  "rows": [
    {
      "id": 2252,
      "categoryId": 419,
      "title": "入库更简单！我市新增口腔黏膜拭子采样入库方式",
      "cover": "/images/demo/news-1.jpg",
      "coverAlt": "",
      "link": "",
      "publishAt": "2026-11-16",
      "isTop": 0,
      "sort": 0,
      "createTime": "2026-11-16",
      "updateTime": "2026-11-16",
      "summary": "点击查看详情，了解更多红十字工作动态。"
    }
  ]
}
```

`summary / viewCount` 不在 OpenAPI 里，前端已在代码中做了缺省（不返回就不显示摘要）。

### 3. 单页栏目内容（导航 templateType=2）

`GET /article/article/menu/{categoryId}?templateType=2`

返回该栏目绑定的那一篇文章，`data` 结构与文章详情一致：`id/categoryId/title/content/mobileContent/cover/coverAlt/link/publishAt/isTop/sort`。
前端用 `content` 渲染正文，`publishAt` 显示时间。

### 4. 前端路由映射

| 栏目类型 | 前端路由 | 数据接口 |
| --- | --- | --- |
| templateType=1（列表） | `/list/{categoryId}` | `menu/list` |
| templateType=2（单页） | `/page/{categoryId}` | `menu/{categoryId}` |
| 列表文章点进详情 | `/article/{id}` | `GET /article/article/menu/info/{id}`（响应同 `menu/{categoryId}` 的 `data`） |

**外链规则**：栏目本身不做外链（不用 `isExternal`）。外链挂在“单页栏目 → 文章”上：文章 `link` 为 http/https 且不是 `.mp4/.m3u8` 时优先跳外链。
- 单页栏目：`/page/{id}` 服务端取到文章后，直接 **302** 到 `link`；
- 列表行：该行直接渲染成 `target="_blank"` 的外链，不再进详情页；
- 视频 `link` 仍进详情页由播放器加载。

列表页左侧栏目导航**统一按列表路由** `/list/{id}`，即使该栏目是单页类型也只展示列表，不再跳 `/page`。
`/page/412`（列表栏目被当作单页访问）仍会 301 回 `/list/412`。

## 三、其他接口清单（轮播 / 栏目 / 文章 / 留言）

### 1. 轮播图列表（门户）

`GET /system/banner/portalList`

无参数。响应为 `{code,msg,data:[...]}`（注意这里是 `data` 数组，不是 `rows`）：

```json
{
  "msg": "操作成功",
  "code": 200,
  "data": [
    {
      "id": 4,
      "imageUrl": "/profile/upload/2026/11/25/xxx.jpg",
      "linkUrl": "",
      "openType": 1,
      "resourceId": 7,
      "resourceType": 2,
      "sort": 1,
      "status": null
    }
  ]
}
```

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| id | long | 主键 |
| imageUrl | string | 图片地址，可为相对路径（前端按 `NUXT_PUBLIC_FILE_BASE` 补全） |
| resourceType | int | **0 无跳转 / 1 类别 / 2 文章 / 3 外链** |
| resourceId | long | `1` 时为栏目 id，`2` 时为文章 id，其余为空 |
| linkUrl | string | `3` 时外链地址 |
| openType | int | `1` = 新标签页打开，其他为当前页 |
| sort | int | 升序 |

前端映射：`1 → /list/{resourceId}`、`2 → /article/{resourceId}`、`3 → linkUrl`、`0` 不可点击；`openType=1` 时统一 `target="_blank"`。

### 2. 栏目列表

`GET /category/category/list?parentId=`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| parentId | long | 否 | 不传返回全部栏目（前端自行组树）；传 0 返回一级栏目 |

**响应 rows 字段**

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| id | long | 栏目 id |
| parentId | long | 父栏目 id，一级为 0 |
| name | string | 栏目名称 |
| sort | int | 排序，升序 |

栏目 id 建议沿用老站编号（412 新闻资讯、411 红会事业、415 公告公示…），这样历史外链与 SEO 权重可直接平移。

### 3. 文章列表

`GET /article/article/list?categoryId=&pageNum=&pageSize=&keyword=`

**请求参数**

| 参数 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| categoryId | long | 是 | 栏目 id；父栏目需聚合其所有子栏目文章 |
| pageNum | int | 否 | 默认 1 |
| pageSize | int | 否 | 默认 10（首页各板块分别取 3/4/6/10） |
| keyword | string | 否 | 标题模糊搜索（预留） |

**响应 rows 字段**（列表不返回正文，减小体积）

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| id | long | 文章 id |
| categoryId | long | 所属栏目 |
| title | string | 标题 |
| summary | string | 摘要（纯文本，建议 120 字内） |
| cover | string | 封面图，无图返回空串 |
| publishTime | string | `YYYY-MM-DD` |
| views | int | 浏览量（可选） |
| link | string | 外链或视频地址（普通文章为空） |

### 4. 文章详情

`GET /article/article/menu/info/{id}`

**响应 data 字段**

| 字段 | 类型 | 说明 |
| --- | --- | --- |
| id | long | 文章 id |
| categoryId | long | 栏目 id |
| title | string | 标题 |
| summary | string | 摘要 |
| cover | string | 封面 |
| content | string | 正文 HTML（富文本原样返回，图片请使用可访问的绝对/根相对地址） |
| publishTime | string | `YYYY-MM-DD` |
| views | int | 浏览量 |
| link | string | 视频地址；非空且以 `.mp4` 结尾时，前端会在标题下渲染视频播放器 |
| categoryName | string | 栏目名称（用于面包屑，可选） |

### 5. 救护员培训报名 / 留言

`POST /leaveMessage/leaveMessage`

**请求体**

```json
{
  "name": "张三",
  "people": 3,
  "phone": "13800138000",
  "trainingTimes": "[\"工作日（周一至周五）\"]",
  "remark": ""
}
```

| 字段 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| name | string | 是 | 联系人姓名 |
| people | int | 是 | 参训人数，≥1 |
| phone | string | 是 | 11 位手机号，前端已做正则校验 |
| trainingTimes | string | 是 | 参训时间多选，**JSON 字符串**：`"[\"工作日（周一至周五）\",\"非工作日（周六、周日）\"]"` |
| remark | string | 否 | 备注 |

**响应**

```json
{ "code": 200, "msg": "报名成功，工作人员会在 1-2 个工作日内与您联系确认" }
```

建议后端字段落库为：`name / people / phone / train_time / remark / created_at / status(0待处理 1已联系)`。

## 四、老站字段对照（迁移参考）

| 老站字段（`cxnews`） | 新接口字段 |
| --- | --- |
| cxid | id |
| cxsortid | categoryId |
| cxtitlecn | title |
| cximg0 | cover / image |
| cxcontentcn | content |
| cxupdatetime | publishTime |
| cxhits | views |
| cxlink | link |

老站「网上办事」「信息公开」「红会事业」等入口在重构后由 `utils/site.ts` 静态维护（不占接口），后续如要后台可视化配置，再补 `system/menu` 一类的接口即可。

## 五、前端联调方式

```bash
# 1) 默认：使用内置 mock（数据在 server/mock/data.json）
npm run dev

# 2) 接真实后端：关掉 mock，指向若依网关
NUXT_MOCK=false NUXT_API_BASE=http://127.0.0.1:8080/prod-api npm run dev
```

切换只需要环境变量，页面与组件代码不用动。

## 六、首页区块配置（后台可配首页布局）

首页不再由前端写死版块顺序，改为按 `home_block` 表渲染：一行一个版块，既存内容定义（标题、样式、取数来源），也存 12 栅格摆位（`rowNo / rowSpan / colStart / colSpan`）。门户只请求一个接口拿到“骨架 + 已取好的条目”，后台拖拽只提交一份布局。

### 1. 栅格约定

- 一行 12 栏，`colStart` 从 1 开始，`colStart + colSpan - 1 ≤ 12`
- `colSpan` 建议 3 / 4 / 6 / 12（老站 22%/77% 近似为 3/9）
- `rowSpan` 默认 1；老站“捐赠公示”在右侧跨两排，seed 里为 2
- 同一行内区间不允许重叠；保存后服务端会把 `rowNo` 压实成 1..n

### 2. 门户接口（前台）

#### 2.1 首页数据（唯一请求）

`GET /portal/home`

无参数，按 `isVisible=1` 的行列顺序返回：

```json
{
  "code": 200,
  "data": {
    "blocks": [
      {
        "blockKey": "banner", "blockType": "banner", "styleKey": "banner", "title": "",
        "rowNo": 1, "rowSpan": 1, "colStart": 4, "colSpan": 9, "moreLink": "",
        "items": [{ "id": 2255, "imageUrl": "/images/demo/banner-1.jpg", "linkUrl": "",
                    "resourceType": 1, "resourceId": 412, "openType": 0, "sort": 1 }]
      },
      {
        "blockKey": "news", "blockType": "content", "styleKey": "news_list", "title": "新闻资讯",
        "rowNo": 2, "rowSpan": 1, "colStart": 4, "colSpan": 9, "moreLink": "/list/412",
        "items": [{ "id": 2252, "categoryId": 419, "title": "入库更简单！…",
                   "cover": "/profile/upload/…jpg", "summary": "…", "publishAt": "2026-11-16", "link": "" }]
      }
    ]
  }
}
```

- 轮播块的 `items` 与 `/system/banner/portalList` 字段一致（`imageUrl / resourceType / openType`）
- 内容块的 `items` 为 `{id, categoryId, title, cover, summary, publishAt, link}`
- `sourceType=static` 的入口型版块不返回条目，由前端按 `sourceConfig.entriesKey` 读静态配置

#### 2.2 仅骨架（可选，调试/懒加载用）

`GET /portal/home/layout` → `data.blocks` 同上，但不含 `items`。

### 3. 后台接口（供管理端）

| 方法 | 路径 | 说明 |
| --- | --- | --- |
| GET | `/admin/home/blocks` | 全部区块（含隐藏），按行、列排序 |
| GET | `/admin/home/blocks/{blockKey}` | 单个区块 |
| POST | `/admin/home/blocks` | 新建区块 |
| PUT | `/admin/home/blocks/{blockKey}` | 修改内容配置（标题/样式/来源/条数/更多链接） |
| DELETE | `/admin/home/blocks/{blockKey}` | 删除区块（`banner` 不可删） |
| PUT | `/admin/home/layout` | 保存整份布局（拖拽只调这个） |
| POST | `/admin/home/layout/reset` | 恢复默认布局 |

> **后端尚未实现时的兜底（重要）**：门户的 `/api/portal/home` 由门户服务端（Nitro）提供，不是直接打后端。规则是：
> 1. 后端已实现 `GET /portal/home` → 原样透传（后台配置立即生效）；
> 2. 后端未实现 → 用 `utils/homeBlocks.ts` 的默认布局，逐个版块调用**现有接口**（`system/banner/portalList`、`article/article/menu/list`、`article/article/menu/info/{id}`）自行聚合，响应里带 `"source": "fallback"`；
> 3. `NUXT_MOCK=true` → 走内置 mock 的聚合结果。
>
> 也就是说：`home_block` 表 / 后台接口没上线时，首页布局固定为默认那套、但内容照常取；上线后无需改前端，自动切到后台配置。

#### 3.1 新建区块

```json
POST /admin/home/blocks
{
  "blockKey": "notice", "blockType": "content", "title": "通知公告",
  "styleKey": "text_list", "sourceType": "category",
  "sourceConfig": { "categoryIds": [436], "includeChildren": false },
  "limitCount": 8, "moreLink": "/list/436",
  "rowNo": 2, "rowSpan": 1, "colStart": 7, "colSpan": 6, "isVisible": 1
}
```

`blockKey` 规则：小写字母开头，`[a-z0-9_]`，2–64 位；重复返回 `409`。摆位字段不传时追加到新行。

#### 3.2 保存布局

```json
PUT /admin/home/layout
{
  "items": [
    { "blockKey": "banner", "rowNo": 1, "rowSpan": 1, "colStart": 4, "colSpan": 9, "isVisible": 1 },
    { "blockKey": "news",   "rowNo": 2, "rowSpan": 1, "colStart": 4, "colSpan": 9, "isVisible": 1 }
  ]
}
```

- `items` 必须覆盖该页所有未删除区块，否则 `400 布局缺少区块：xxx`
- 越界 / 重叠返回 `422`，`data` 为明细数组：`[{"blockKey":"notice","reason":"第2行与 news 的栅格区间重叠"}]`
- 成功返回刷新后的完整列表

### 4. styleKey 与组件映射（前端）

| styleKey | 组件 | 适用 |
| --- | --- | --- |
| `banner` | `HomeBlockBanner` | 轮播位 |
| `service_entries` | `HomeBlockServiceEntries` | 网上办事（图标入口） |
| `entry_grid` | `HomeBlockEntryGrid` | 信息公开（6 入口 + 公益图） |
| `entry_chips` | `HomeBlockEntryChips` | 红会事业（两列小卡片） |
| `news_list` | `HomeBlockNewsList` | 新闻资讯（首条图文 + 标题列表） |
| `image_strip` | `HomeBlockImageStrip` | 专题专栏（横排图片） |
| `cover_list` | `HomeBlockCoverList` | 应急救护（左图右列表） |
| `text_list` | `HomeBlockTextList` | 通知公告 / 捐赠公示 |
| `card_list` | `HomeBlockCardList` | 志愿服务（图文卡片） |
| `link_list` | `HomeBlockLinkList` | 通用文字链接行（无「友情链接：」前缀） |
| `friend_links` | `HomeBlockFriendLinks` | 友情链接（绑定栏目 417，`link` 为外链时新窗口） |

未知 `styleKey` 回落 `text_list`，新增样式只需加组件并登记映射。

### 5. sourceConfig 约定

```json
// category：includeChildren=true 时聚合子栏目
{ "categoryIds": [412], "includeChildren": true }

// article：手工指定文章 id
{ "articleIds": [2252, 2243] }

// custom：后台直接维护条目
{ "items": [{ "id": 1, "title": "…", "link": "/article/2250", "cover": "" }] }

// static：入口型版块，entriesKey ∈ service / infoPublic / redCross
{ "entriesKey": "service" }

// banner：null
```

`entry_grid` 还可带 `promoImage / promoLink` 渲染底部公益图。

### 6. 默认布局（老站还原）

| 行 | 左 | 中 | 右 |
| --- | --- | --- | --- |
| 1 | 网上办事 3 格 | 轮播 9 格 | — |
| 2 | 信息公开 3 格 | 新闻资讯 9 格 | — |
| 3 | 专题专栏 12 格 | — | — |
| 4 | 红会事业 3 格 | 应急救护 6 格 | 捐赠公示 3 格（跨 4–5 行） |
| 5 | 通知公告 3 格 | 志愿服务 6 格 | 捐赠公示续 |
| 6 | 友情链接（`friend_links`，栏目 417）12 格 | — | — |

该布局同时是 `utils/homeBlocks.ts` 的 `DEFAULT_HOME_BLOCKS`、mock 种子与接口异常时的前端兜底。版块高度按老站实测值写在 `assets/css/home-blocks.css`（`.hb-{blockKey}`），新增版块不写则随内容撑开。

## 七、缺口与处理约定（2026-11-25 更新）

| # | 用途 | 现状 / 约定 |
| --- | --- | --- |
| 1 | 文章详情（按 id） | **已提供**：`GET /article/article/menu/info/{id}`，`data` 与 `menu/{categoryId}` 一致 |
| 2 | 列表分页 | **已确认可用**：`pageNum/pageSize` 由前端传入 |
| 3 | 外链 | 栏目不做外链；单页栏目文章 `link` 为 http(s) 时优先跳转（视频除外），前端已实现 302 / 新窗口 |
| 4 | 轮播图 | 已提供 `GET /system/banner/portalList`（`data` 数组，字段见第三节），前端已切换 |
| 5 | 父栏目聚合 | 前端按 `categoryId` 递归聚合子栏目文章（412 含 418/419…） |
| 6 | 友情链接 | 复用文章列表的 `link` 字段，不单独建接口 |
| 7 | 留言 / 报名 | 保持现有契约，接口就绪后 `NUXT_MOCK=false` 即切换 |
| 8 | 站点信息（页脚） | 暂写在前端（版权、备案号、二维码），后续需要再做 `system/config` |
| 9 | 搜索 | 暂不做，`menu/list` 的 `keyword` 先不加 |
| 10 | 摘要 / 浏览量 | 接口不返回时前端不显示，不阻塞页面 |
