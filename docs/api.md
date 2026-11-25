# 汕头市红十字会门户 · 接口文档

> 面向后端开发（若依 Spring Boot 侧）。门户前端只依赖本文档中的 5 个接口，字段名可调整，但请同步修改前端 `composables/useApi.ts` 的映射层。

## 一、总体约定

| 项目 | 说明 |
| --- | --- |
| 网关地址 | 由环境变量 `NUXT_API_BASE` 指定，例如 `http://127.0.0.1:8080/prod-api` |
| 前端请求 | 浏览器统一请求本站 `/api/{path}`，Nitro 在服务端转发到网关，天然同源，**不需要后端开 CORS** |
| 鉴权 | 门户为公开站点，**所有接口免 token**（建议后端放行 `/article/**`、`/category/**`、`/system/banner/**`、`/guestbook/**`） |
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
| 列表文章点进详情 | `/article/{id}` | `GET /article/article/{id}`（已提供，响应同 `menu/{categoryId}` 的 `data`） |

**外链规则**：栏目本身不做外链（不用 `isExternal`）。外链挂在“单页栏目 → 文章”上：文章 `link` 为 http/https 且不是 `.mp4/.m3u8` 时优先跳外链。
- 单页栏目：导航渲染时会预取该栏目文章的 `link`，若是外链，菜单项直接渲染成 `target="_blank"` 的锚点，**点击新窗口打开**；直接访问 `/page/{id}` 时仍 302 到外链（同标签页兜底）。
- 列表行：该行直接渲染成 `target="_blank"` 的外链，不再进详情页；
- 视频 `link` 仍进详情页由播放器加载。

列表页左侧栏目导航**统一按列表路由** `/list/{id}`，即使该栏目是单页类型也只展示列表，不再跳 `/page`。
`/page/412`（列表栏目被当作单页访问）仍会 301 回 `/list/412`。

## 三、旧版 / 兼容接口清单

### 1. 轮播图列表

`GET /system/banner/list`

**请求参数**：无（可选 `position`，用于区分首页/栏目页，前端暂未传）

**响应 rows 字段**

| 字段 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| id | long | 是 | 主键 |
| title | string | 是 | 标题，用于图片 alt 与 SEO |
| image | string | 是 | 图片地址（建议 1600×600 以内，返回相对路径或完整 URL） |
| resourceType | int | 是 | 跳转类型：0 无跳转、1 文章、2 栏目、3 外链 |
| resourceId | long | 否 | `resourceType=1/2` 时必填，对应文章 id / 栏目 id |
| linkUrl | string | 否 | `resourceType=3` 时必填，外链地址 |
| sort | int | 是 | 升序展示 |
| status | int | 是 | 1 启用、0 停用（前端会过滤 0） |

> 与后台「横幅管理」的约束一致：`resourceType=0` 时 `linkUrl`、`resourceId` 均为空；`resourceType=3` 时 `resourceId` 为空；`resourceType=1/2` 时 `linkUrl` 为空。

**示例**

```json
{
  "code": 200,
  "msg": "查询成功",
  "rows": [
    {
      "id": 2255,
      "title": "学习宣传贯彻党的二十届五中全会精神",
      "image": "/upload/2026/11/banner-1.jpg",
      "resourceType": 1,
      "resourceId": 2084,
      "linkUrl": null,
      "sort": 1,
      "status": 1
    }
  ],
  "total": 9
}
```

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

`GET /article/article/{id}`

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

`POST /guestbook/guestbook/add`

**请求体**

```json
{
  "name": "张三",
  "people": 3,
  "phone": "13800138000",
  "trainTime": ["工作日（周一至周五）"],
  "remark": ""
}
```

| 字段 | 类型 | 必填 | 说明 |
| --- | --- | --- | --- |
| name | string | 是 | 联系人姓名 |
| people | int | 是 | 参训人数，≥1 |
| phone | string | 是 | 11 位手机号，前端已做正则校验 |
| trainTime | string[] | 是 | 参训时间多选：`工作日（周一至周五）` / `非工作日（周六、周日）` |
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

## 六、缺口与处理约定（2026-11-25 更新）

| # | 用途 | 现状 / 约定 |
| --- | --- | --- |
| 1 | 文章详情（按 id） | **已提供**：`GET /article/article/{id}`，`data` 与 `menu/{categoryId}` 一致 |
| 2 | 列表分页 | **已确认可用**：`pageNum/pageSize` 由前端传入 |
| 3 | 外链 | 栏目不做外链；单页栏目文章 `link` 为 http(s) 时优先跳转（视频除外），前端已实现 302 / 新窗口 |
| 4 | 轮播图 | **后端暂未开发**，前端继续用 mock（`system/banner/list` 契约先冻结） |
| 5 | 父栏目聚合 | 前端按 `categoryId` 递归聚合子栏目文章（412 含 418/419…） |
| 6 | 友情链接 | 复用文章列表的 `link` 字段，不单独建接口 |
| 7 | 留言 / 报名 | 保持现有契约，接口就绪后 `NUXT_MOCK=false` 即切换 |
| 8 | 站点信息（页脚） | 暂写在前端（版权、备案号、二维码），后续需要再做 `system/config` |
| 9 | 搜索 | 暂不做，`menu/list` 的 `keyword` 先不加 |
| 10 | 摘要 / 浏览量 | 接口不返回时前端不显示，不阻塞页面 |
