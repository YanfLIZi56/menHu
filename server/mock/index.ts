/**
 * 内置 mock 服务：接口路径与正式后端保持一致，
 * 把 NUXT_MOCK 设为 false 后自动切到真实网关，前端代码无需改动。
 */
import data from './data.json'

interface MockArticle {
  id: number
  categoryId: number
  title: string
  summary: string
  cover: string
  content: string
  publishTime: string
  views: number
  link: string
}

const db = data as {
  banners: any[]
  categories: any[]
  articles: MockArticle[]
  guestbookSubmits: any[]
}

const ok = (rows: any[], total?: number) => ({
  code: 200,
  msg: '查询成功',
  rows,
  total: total ?? rows.length,
})

const toListItem = (a: MockArticle) => ({
  id: a.id,
  categoryId: a.categoryId,
  title: a.title,
  summary: a.summary,
  cover: a.cover,
  publishTime: a.publishTime,
  views: a.views,
  link: a.link,
})

/** 取某栏目及其所有下级栏目的 id */
function subtreeIds(rootId: number): number[] {
  const ids = [rootId]
  let frontier = [rootId]
  while (frontier.length) {
    const next: number[] = []
    db.categories
      .filter((c) => frontier.includes(Number(c.parentId)))
      .forEach((c) => {
        if (!ids.includes(Number(c.id))) {
          ids.push(Number(c.id))
          next.push(Number(c.id))
        }
      })
    frontier = next
  }
  return ids
}

/** 门户导航根栏目顺序（与老站 8 项导航一致，后台可按需调整） */
const MENU_ROOTS = [413, 412, 411, 410, 414, 415, 416]

/** 单页栏目：导航直接进入一篇文章；其余按列表页展示 */
const SINGLE_PAGES = new Set([446, 444, 445, 416, 432, 433])

/** 库内栏目名与老站导航文案的差异（415 库内叫“信息公开”，导航显示“公告公示”） */
const MENU_LABELS: Record<number, string> = { 415: '公告公示' }

const templateTypeOf = (id: number) => (SINGLE_PAGES.has(id) ? 2 : 1)

/** 栏目树：最多两级，字段与 /category/category/menu 对齐 */
function buildMenuNode(cat: any, depth = 0): any {
  const seen = new Set<number>()
  const children = db.categories
    .filter((c) => Number(c.parentId) === Number(cat.id))
    .filter((c) => {
      const id = Number(c.id)
      if (seen.has(id)) return false
      seen.add(id)
      return true
    })
    .sort((a, b) => Number(a.sort ?? 0) - Number(b.sort ?? 0))
    .map((c) =>
      depth >= 1
        ? {
            value: Number(c.id),
            label: MENU_LABELS[Number(c.id)] ?? c.name,
            sort: Number(c.sort ?? 0),
            templateType: templateTypeOf(Number(c.id)),
            isExternal: 0,
            children: [],
          }
        : buildMenuNode(c, depth + 1),
    )

  return {
    value: Number(cat.id),
    label: MENU_LABELS[Number(cat.id)] ?? cat.name,
    sort: Number(cat.sort ?? 0),
    templateType: templateTypeOf(Number(cat.id)),
    isExternal: 0,
    children,
  }
}

/** /article/article/menu/list 的行结构（不含正文，字段按后端示例） */
const toMenuRow = (a: MockArticle) => ({
  id: a.id,
  categoryId: a.categoryId,
  title: a.title,
  cover: a.cover,
  coverAlt: '',
  link: a.link ?? '',
  publishAt: a.publishTime,
  isTop: 0,
  sort: 0,
  createTime: a.publishTime,
  updateTime: a.publishTime,
  summary: a.summary,
})

function articlesOf(categoryId: number): MockArticle[] {
  // 父栏目聚合下级栏目的文章（老站新闻资讯就是这种结构：412 下挂 418/419/420…）
  let rows = db.articles.filter((a) => subtreeIds(categoryId).includes(a.categoryId))
  if (rows.length) return rows

  // 叶子栏目没有独立内容时，回退到父栏目整棵子树，避免页面空白
  const cat = db.categories.find((c) => Number(c.id) === categoryId)
  if (cat && Number(cat.parentId)) {
    rows = db.articles.filter((a) => subtreeIds(Number(cat.parentId)).includes(a.categoryId))
    if (rows.length) return rows
  }

  return db.articles.filter((a) => a.categoryId === 412)
}

export function handleMock(
  path: string,
  method: string,
  query: Record<string, any>,
  body: any,
) {
  const clean = path.replace(/^\/+|\/+$/g, '')

  // ---------------- 轮播图 ----------------
  if (clean === 'system/banner/list' && method === 'GET') {
    const rows = db.banners
      .filter((b) => b.status !== 0)
      .sort((a, b) => (a.sort ?? 0) - (b.sort ?? 0))
    return ok(rows)
  }

  // ---------------- 栏目 ----------------
  if (clean === 'category/category/list' && method === 'GET') {
    const parentId = query.parentId !== undefined ? Number(query.parentId) : undefined
    const rows =
      parentId === undefined
        ? db.categories
        : db.categories.filter((c) => Number(c.parentId) === parentId)
    return ok(rows)
  }

  // ---------------- 文章列表 ----------------
  if (clean === 'article/article/list' && method === 'GET') {
    const categoryId = Number(query.categoryId ?? 0)
    const pageNum = Math.max(1, Number(query.pageNum ?? 1))
    const pageSize = Math.max(1, Number(query.pageSize ?? 10))
    const keyword = String(query.keyword ?? '').trim()

    let rows = categoryId ? articlesOf(categoryId) : db.articles
    if (keyword) rows = rows.filter((a) => a.title.includes(keyword))

    const total = rows.length
    const start = (pageNum - 1) * pageSize
    return ok(rows.slice(start, start + pageSize).map(toListItem), total)
  }

  // ---------------- 文章详情 ----------------
  const detail = clean.match(/^article\/article\/(\d+)$/)
  if (detail && method === 'GET') {
    const id = Number(detail[1])
    const article = db.articles.find((a) => a.id === id)
    if (!article) {
      return { code: 404, msg: '文章不存在' }
    }
    const category = db.categories.find((c) => c.id === article.categoryId)
    return {
      code: 200,
      msg: '查询成功',
      data: {
        ...article,
        categoryName: category?.name ?? '',
      },
    }
  }

  // ---------------- 门户导航：栏目树 ----------------
  if (clean === 'category/category/menu' && method === 'GET') {
    const rows = MENU_ROOTS.map((id) => db.categories.find((c) => Number(c.id) === id))
      .filter(Boolean)
      .map((c) => buildMenuNode(c))
    return { code: 200, msg: '操作成功', data: rows }
  }

  // ---------------- 门户导航：栏目文章列表 ----------------
  if (clean === 'article/article/menu/list' && method === 'GET') {
    const categoryId = Number(query.categoryId ?? 0)
    const pageNum = Math.max(1, Number(query.pageNum ?? 1))
    const pageSize = Math.max(1, Number(query.pageSize ?? 10))

    let rows = categoryId ? articlesOf(categoryId) : db.articles
    rows = [...rows].sort(
      (a, b) => String(b.publishTime).localeCompare(String(a.publishTime)),
    )

    const total = rows.length
    const start = (pageNum - 1) * pageSize
    return ok(rows.slice(start, start + pageSize).map(toMenuRow), total)
  }

  // ---------------- 门户导航：文章详情（按 id） ----------------
  const articleDetail = clean.match(/^article\/article\/menu\/info\/(\d+)$/)
  if (articleDetail && method === 'GET') {
    const id = Number(articleDetail[1])
    const article = db.articles.find((a) => Number(a.id) === id)
    if (!article) return { code: 404, msg: '文章不存在' }
    const cat = db.categories.find((c) => Number(c.id) === article.categoryId)
    return {
      code: 200,
      msg: '操作成功',
      data: {
        ...article,
        categoryName: cat?.name ?? '',
        mobileContent: `<p>${article.summary ?? ''}</p>`,
        coverAlt: '',
        publishAt: article.publishTime,
        createBy: null,
        createTime: article.publishTime,
        updateBy: null,
        updateTime: '',
        isTop: 0,
        sort: 0,
      },
    }
  }

  // ---------------- 门户导航：单页栏目内容 ----------------
  const menuDetail = clean.match(/^article\/article\/menu\/(\d+)$/)
  if (menuDetail && method === 'GET') {
    const categoryId = Number(menuDetail[1])
    const cat = db.categories.find((c) => Number(c.id) === categoryId)
    const article =
      db.articles.find((a) => a.categoryId === categoryId) ??
      db.articles.find((a) => subtreeIds(categoryId).includes(a.categoryId))

    if (!article) {
      return {
        code: 200,
        msg: '操作成功',
        data: {
          id: categoryId,
          categoryId,
          title: MENU_LABELS[categoryId] ?? cat?.name ?? '栏目内容',
          content: `<p>${MENU_LABELS[categoryId] ?? cat?.name ?? '栏目'}内容待后台维护。</p>`,
          mobileContent: '',
          cover: '',
          coverAlt: '',
          link: '',
          publishAt: '',
          isTop: 0,
          sort: 0,
          createBy: null,
          createTime: '',
          updateBy: null,
          updateTime: '',
        },
      }
    }

    return {
      code: 200,
      msg: '操作成功',
      data: {
        ...article,
        categoryName: cat?.name ?? '',
        mobileContent: `<p>${article.summary ?? ''}</p>`,
        coverAlt: '',
        publishAt: article.publishTime,
        isTop: 0,
        sort: 0,
      },
    }
  }

  // ---------------- 留言 / 报名 ----------------
  if (clean === 'guestbook/guestbook/add' && method === 'POST') {
    const payload = body || {}
    if (!payload.name || !payload.phone) {
      return { code: 500, msg: '姓名和手机号不能为空' }
    }
    if (!/^1[3-9]\d{9}$/.test(String(payload.phone))) {
      return { code: 500, msg: '手机号格式不正确' }
    }
    db.guestbookSubmits.push({ ...payload, createdAt: new Date().toISOString() })
    return { code: 200, msg: '报名成功，工作人员会在 1-2 个工作日内与您联系确认' }
  }

  return { code: 404, msg: `mock 未实现的接口：${method} ${clean}` }
}
