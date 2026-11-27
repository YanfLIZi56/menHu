/**
 * 内置 mock 服务：接口路径与正式后端保持一致，
 * 把 NUXT_MOCK 设为 false 后自动切到真实网关，前端代码无需改动。
 */
import data from './data.json'
import { DEFAULT_HOME_BLOCKS, type HomeBlock } from '../../utils/homeBlocks'

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

/**
 * 首页区块：mock 内存里维护一份，
 * 后台接口（/admin/home/*）改动后门户（/portal/home）立即生效，方便前端联调。
 */
let homeBlocks: HomeBlock[] = JSON.parse(JSON.stringify(DEFAULT_HOME_BLOCKS))

/** 轮播：portalList 的响应结构（imageUrl / resourceType / openType） */
const toPortalBanner = (b: any) => ({
  id: Number(b.id),
  title: b.title ?? '',
  imageUrl: b.imageUrl ?? b.image ?? '',
  linkUrl: b.linkUrl ?? '',
  openType: Number(b.openType ?? 0),
  resourceId: b.resourceId ?? null,
  resourceType: Number(b.resourceType ?? 0),
  sort: Number(b.sort ?? 0),
  status: b.status ?? null,
})

/** 门户版块条目：统一成前端 HomeBlockItem（banner 块另走 toPortalBanner） */
const toBlockItem = (a: MockArticle) => ({
  id: a.id,
  categoryId: a.categoryId,
  title: a.title,
  summary: a.summary,
  cover: a.cover,
  publishAt: a.publishTime,
  link: a.link ?? '',
})

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

/** 按区块配置取条目（banner / category / article / custom / static） */
function blockItems(block: HomeBlock) {
  const cfg: any = block.sourceConfig ?? {}

  if (block.blockType === 'banner' || block.sourceType === 'banner') {
    return db.banners
      .filter((b) => b.status !== 0)
      .sort((a, b) => Number(a.sort ?? 0) - Number(b.sort ?? 0))
      .slice(0, Number(block.limitCount || 9))
      .map(toPortalBanner)
  }

  if (block.sourceType === 'article') {
    const ids: number[] = Array.isArray(cfg.articleIds) ? cfg.articleIds : []
    return ids
      .map((id) => db.articles.find((a) => Number(a.id) === Number(id)))
      .filter(Boolean)
      .map((a) => toBlockItem(a as MockArticle))
  }

  if (block.sourceType === 'custom') {
    return Array.isArray(cfg.items) ? cfg.items : []
  }

  if (block.sourceType === 'static') return []

  // category：父栏目默认聚合子栏目（includeChildren !== false）
  const ids: number[] = Array.isArray(cfg.categoryIds) ? cfg.categoryIds : []
  const pool = ids.flatMap((id) =>
    cfg.includeChildren === false
      ? db.articles.filter((a) => a.categoryId === Number(id))
      : articlesOf(Number(id)),
  )

  const seen = new Set<number>()
  return pool
    .filter((a) => {
      if (seen.has(a.id)) return false
      seen.add(a.id)
      return true
    })
    .sort((a, b) => String(b.publishTime).localeCompare(String(a.publishTime)))
    .slice(0, Number(block.limitCount || 6))
    .map(toBlockItem)
}

/** 首页布局：按行、列排好并带上条目 */
function composePortalHome() {
  const blocks = [...homeBlocks]
    .filter((b) => Number(b.isVisible) !== 0)
    .sort((a, b) => a.rowNo - b.rowNo || a.colStart - b.colStart)
    .map((b) => ({ ...b, items: blockItems(b) }))
  return { code: 200, msg: '操作成功', data: { blocks } }
}

/** 12 栅格校验：越界 / 同行重叠 */
function validateLayout(items: any[]) {
  const errors: any[] = []
  const rows = new Map<number, Array<{ key: string; start: number; end: number }>>()

  for (const item of items) {
    const key = String(item.blockKey ?? item.block_key ?? '')
    const rowNo = Number(item.rowNo ?? item.row_no ?? 0)
    const rowSpan = Math.max(1, Number(item.rowSpan ?? item.row_span ?? 1))
    const colStart = Number(item.colStart ?? item.col_start ?? 0)
    const colSpan = Number(item.colSpan ?? item.col_span ?? 0)

    if (!key) errors.push({ blockKey: key, reason: 'blockKey 不能为空' })
    if (rowNo < 1) errors.push({ blockKey: key, reason: 'rowNo 必须从 1 开始' })
    if (rowSpan < 1 || rowSpan > 3)
      errors.push({ blockKey: key, reason: 'rowSpan 只能是 1-3' })
    if (colSpan < 1 || colSpan > 12)
      errors.push({ blockKey: key, reason: 'colSpan 只能是 1-12' })
    if (colStart < 1 || colStart + colSpan - 1 > 12)
      errors.push({ blockKey: key, reason: `colStart=${colStart} + colSpan=${colSpan} 超过 12 栏` })

    for (let r = rowNo; r < rowNo + rowSpan; r++) {
      const list = rows.get(r) ?? []
      for (const other of list) {
        if (colStart <= other.end && other.start <= colStart + colSpan - 1) {
          errors.push({
            blockKey: key,
            reason: `第${r}行与 ${other.key} 的栅格区间重叠`,
          })
        }
      }
      list.push({ key, start: colStart, end: colStart + colSpan - 1 })
      rows.set(r, list)
    }
  }

  return errors
}

/** 空行压实：把用到的 rowNo 重新编号成 1..n */
function compactRows() {
  const used = [...new Set(homeBlocks.map((b) => b.rowNo))].sort((a, b) => a - b)
  const map = new Map<number, number>(used.map((row, index) => [row, index + 1]))
  homeBlocks.forEach((b) => {
    b.rowNo = map.get(b.rowNo) ?? 1
  })
}

export function handleMock(
  path: string,
  method: string,
  query: Record<string, any>,
  body: any,
) {
  const clean = path.replace(/^\/+|\/+$/g, '')

  // ---------------- 轮播图（门户） ----------------
  if (clean === 'system/banner/portalList' && method === 'GET') {
    const list = db.banners
      .filter((b) => b.status !== 0)
      .sort((a, b) => Number(a.sort ?? 0) - Number(b.sort ?? 0))
      .map(toPortalBanner)
    return { code: 200, msg: '操作成功', data: list }
  }

  // ---------------- 首页版块（门户） ----------------
  if (clean === 'portal/home' && method === 'GET') {
    return composePortalHome()
  }
  if (clean === 'portal/home/layout' && method === 'GET') {
    const blocks = [...homeBlocks]
      .filter((b) => Number(b.isVisible) !== 0)
      .sort((a, b) => a.rowNo - b.rowNo || a.colStart - b.colStart)
      .map(({ items, ...rest }) => rest)
    return { code: 200, msg: '操作成功', data: { blocks } }
  }

  // ---------------- 首页版块（后台） ----------------
  if (clean === 'admin/home/blocks' && method === 'GET') {
    const rows = [...homeBlocks].sort(
      (a, b) => a.rowNo - b.rowNo || a.colStart - b.colStart,
    )
    return ok(rows)
  }

  const adminBlock = clean.match(/^admin\/home\/blocks\/([a-z0-9_]+)$/)

  if (adminBlock && method === 'GET') {
    const key = adminBlock[1]
    const row = homeBlocks.find((b) => b.blockKey === key)
    if (!row) return { code: 404, msg: '区块不存在' }
    return { code: 200, msg: '操作成功', data: row }
  }

  if (adminBlock && method === 'PUT') {
    const key = adminBlock[1]
    const row = homeBlocks.find((b) => b.blockKey === key)
    if (!row) return { code: 404, msg: '区块不存在' }
    const payload = body || {}
    if (row.blockType === 'banner' && payload.sourceType && payload.sourceType !== 'banner') {
      return { code: 400, msg: '轮播位不支持改数据来源' }
    }
    if ('title' in payload) row.title = String(payload.title ?? '')
    if ('styleKey' in payload) row.styleKey = String(payload.styleKey)
    if ('sourceType' in payload) row.sourceType = payload.sourceType
    if ('sourceConfig' in payload) row.sourceConfig = payload.sourceConfig
    if ('limitCount' in payload) row.limitCount = Math.min(20, Math.max(1, Number(payload.limitCount)))
    if ('moreLink' in payload) row.moreLink = String(payload.moreLink ?? '')
    return { code: 200, msg: '操作成功', data: row }
  }

  if (clean === 'admin/home/blocks' && method === 'POST') {
    const payload = body || {}
    const key = String(payload.blockKey ?? '').trim()
    if (!/^[a-z][a-z0-9_]{1,63}$/.test(key)) {
      return { code: 400, msg: '区块标识只能用小写字母、数字和下划线，且以字母开头' }
    }
    if (homeBlocks.some((b) => b.blockKey === key)) {
      return { code: 409, msg: '区块标识已存在' }
    }
    const lastRow = homeBlocks.reduce((max, b) => Math.max(max, b.rowNo), 0)
    const row: HomeBlock = {
      blockKey: key,
      blockType: payload.blockType === 'banner' ? 'banner' : 'content',
      title: String(payload.title ?? ''),
      styleKey: String(payload.styleKey ?? 'text_list'),
      sourceType: payload.sourceType ?? 'category',
      sourceConfig: payload.sourceConfig ?? null,
      limitCount: Math.min(20, Math.max(1, Number(payload.limitCount ?? 6))),
      moreLink: String(payload.moreLink ?? ''),
      rowNo: Number(payload.rowNo ?? lastRow + 1),
      rowSpan: Math.max(1, Number(payload.rowSpan ?? 1)),
      colStart: Number(payload.colStart ?? 1),
      colSpan: Number(payload.colSpan ?? 12),
      isVisible: Number(payload.isVisible ?? 1),
    }
    homeBlocks.push(row)
    return { code: 200, msg: '操作成功', data: row }
  }

  if (adminBlock && method === 'DELETE') {
    const key = adminBlock[1]
    const index = homeBlocks.findIndex((b) => b.blockKey === key)
    if (index < 0) return { code: 404, msg: '区块不存在' }
    if (homeBlocks[index].blockType === 'banner') {
      return { code: 400, msg: '轮播位不可删除' }
    }
    const [removed] = homeBlocks.splice(index, 1)
    return { code: 200, msg: '操作成功', data: removed }
  }

  if (clean === 'admin/home/layout' && method === 'PUT') {
    const payload = body || {}
    const items: any[] = Array.isArray(payload.items) ? payload.items : []
    if (!items.length) return { code: 400, msg: '布局不能为空' }

    const missing = homeBlocks
      .map((b) => b.blockKey)
      .filter((key) => !items.some((it) => String(it.blockKey ?? it.block_key) === key))
    if (missing.length) {
      return { code: 400, msg: `布局缺少区块：${missing.join('、')}` }
    }

    const errors = validateLayout(items)
    if (errors.length) {
      return { code: 422, msg: '布局校验失败', data: errors }
    }

    items.forEach((it) => {
      const key = String(it.blockKey ?? it.block_key)
      const row = homeBlocks.find((b) => b.blockKey === key)
      if (!row) return
      row.rowNo = Number(it.rowNo ?? it.row_no ?? row.rowNo)
      row.rowSpan = Math.max(1, Number(it.rowSpan ?? it.row_span ?? row.rowSpan))
      row.colStart = Number(it.colStart ?? it.col_start ?? row.colStart)
      row.colSpan = Number(it.colSpan ?? it.col_span ?? row.colSpan)
      row.isVisible = Number(it.isVisible ?? it.is_visible ?? row.isVisible)
    })
    compactRows()

    return { code: 200, msg: '操作成功', data: homeBlocks }
  }

  if (clean === 'admin/home/layout/reset' && method === 'POST') {
    homeBlocks = JSON.parse(JSON.stringify(DEFAULT_HOME_BLOCKS))
    return { code: 200, msg: '操作成功', data: homeBlocks }
  }

  // ---------------- 轮播图（旧接口，保留兼容） ----------------
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

  // ---------------- 留言 / 报名（后端 05e4021 起改名为 /leaveMessage/leaveMessage） ----------------
  if (clean === 'leaveMessage/leaveMessage' && method === 'POST') {
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

  // 旧路径保留兼容
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
