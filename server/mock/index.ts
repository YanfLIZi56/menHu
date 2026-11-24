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
