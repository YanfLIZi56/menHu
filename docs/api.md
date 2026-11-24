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

## 二、接口清单

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

## 三、老站字段对照（迁移参考）

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

## 四、前端联调方式

```bash
# 1) 默认：使用内置 mock（数据在 server/mock/data.json）
npm run dev

# 2) 接真实后端：关掉 mock，指向若依网关
NUXT_MOCK=false NUXT_API_BASE=http://127.0.0.1:8080/prod-api npm run dev
```

切换只需要环境变量，页面与组件代码不用动。
