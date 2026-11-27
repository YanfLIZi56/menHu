/**
 * 门户首页数据（服务端聚合）
 *
 * 背景：首页版块配置（home_block 表 + /portal/home）后端还没实现，
 * 但如果前端只认 /portal/home，真实后端下整页会退化成"只有默认骨架、没有任何内容"。
 *
 * 所以这里做一层兜底：
 *   1. 后端已实现 GET /portal/home → 直接透传（后台配什么就渲染什么）
 *   2. 还没实现 → 用 DEFAULT_HOME_BLOCKS 的默认布局，逐个版块调用现有接口（轮播 / menu/list / menu/info）自行聚合
 *   3. NUXT_MOCK=true 时仍走内置 mock
 */
import { DEFAULT_HOME_BLOCKS, type HomeBlock, type HomeBlockItem } from '../../../utils/homeBlocks'

type Fetcher = (path: string, query?: Record<string, any>) => Promise<any>

/** sourceConfig 后端若用 json/字符串存，这里统一解析成对象 */
const parseConfig = (v: any): any => {
  if (!v) return null
  if (typeof v === 'string') {
    try {
      return JSON.parse(v)
    } catch {
      return null
    }
  }
  return typeof v === 'object' ? v : null
}

/** 后端只返回版块配置（可能 snake_case、可能缺 rowSpan）时统一成 HomeBlock */
const toBlock = (raw: any): HomeBlock => ({
  id: raw.id !== undefined && raw.id !== null ? Number(raw.id) : undefined,
  blockKey: String(raw.blockKey ?? raw.block_key ?? ''),
  blockType: (raw.blockType ?? raw.block_type) === 'banner' ? 'banner' : 'content',
  title: raw.title ?? '',
  styleKey: String(raw.styleKey ?? raw.style_key ?? 'text_list'),
  sourceType: String(raw.sourceType ?? raw.source_type ?? 'category') as HomeBlock['sourceType'],
  sourceConfig: parseConfig(raw.sourceConfig ?? raw.source_config),
  limitCount: Number(raw.limitCount ?? raw.limit_count ?? 6),
  moreLink: raw.moreLink ?? raw.more_link ?? '',
  rowNo: Number(raw.rowNo ?? raw.row_no ?? 1),
  rowSpan: Number(raw.rowSpan ?? raw.row_span ?? 1),
  colStart: Number(raw.colStart ?? raw.col_start ?? 1),
  colSpan: Number(raw.colSpan ?? raw.col_span ?? 12),
  isVisible: Number(raw.isVisible ?? raw.is_visible ?? 1),
  items: Array.isArray(raw.items) ? raw.items : undefined,
})

const pickList = (res: any): any[] => {
  if (!res) return []
  if (Array.isArray(res)) return res
  if (Array.isArray(res.rows)) return res.rows
  if (Array.isArray(res.data)) return res.data
  if (Array.isArray(res.data?.rows)) return res.data.rows
  if (Array.isArray(res.data?.list)) return res.data.list
  return []
}

const toItem = (a: any): HomeBlockItem => ({
  id: Number(a.id),
  categoryId: Number(a.categoryId ?? 0),
  title: a.title ?? '',
  summary: a.summary ?? '',
  cover: a.cover ?? '',
  publishAt: String(a.publishAt ?? a.publishTime ?? '').slice(0, 10),
  link: a.link ?? '',
})

const toBannerItem = (b: any): HomeBlockItem => ({
  id: Number(b.id),
  title: b.title ?? '',
  image: b.imageUrl ?? b.image ?? '',
  linkUrl: b.linkUrl ?? '',
  resourceType: Number(b.resourceType ?? 0),
  resourceId: b.resourceId ?? null,
  openType: Number(b.openType ?? 0),
  sort: Number(b.sort ?? 0),
})

async function itemsOf(block: HomeBlock, get: Fetcher): Promise<HomeBlockItem[]> {
  const cfg: any = block.sourceConfig ?? {}
  const limit = Number(block.limitCount || 6)

  if (block.blockType === 'banner' || block.sourceType === 'banner') {
    const res = await get('/system/banner/portalList')
    return pickList(res)
      // 若依的 status：'0'/0 = 正常显示，'1'/1 = 停用；不能用 !== 0 过滤
      .filter((b) => !(b.status === 1 || b.status === '1' || b.status === false))
      .sort((a, b) => Number(a.sort ?? 0) - Number(b.sort ?? 0))
      .slice(0, Number(block.limitCount || 9))
      .map(toBannerItem)
  }

  if (block.sourceType === 'article') {
    const ids: number[] = Array.isArray(cfg.articleIds) ? cfg.articleIds : []
    const rows: any[] = []
    for (const id of ids) {
      const res = await get(`/article/article/menu/info/${id}`)
      const data = res?.data ?? res
      if (data && data.id) rows.push(toItem(data))
    }
    return rows
  }

  if (block.sourceType === 'custom') {
    return Array.isArray(cfg.items) ? cfg.items : []
  }

  if (block.sourceType === 'static') return []

  // category：父栏目由后端聚合子栏目（与列表页/导航一致）
  const ids: number[] = Array.isArray(cfg.categoryIds) ? cfg.categoryIds : []
  const rows: any[] = []
  for (const id of ids) {
    const res = await get('/article/article/menu/list', {
      categoryId: id,
      // 后端 templateType 为 null 会 NPE，固定传 1
      templateType: 1,
      pageNum: 1,
      pageSize: limit,
    })
    rows.push(...pickList(res))
  }

  const seen = new Set<number>()
  return rows
    .filter((a) => {
      if (seen.has(a.id)) return false
      seen.add(a.id)
      return true
    })
    .slice(0, limit)
    .map(toItem)
}

/** 版块条目：后端没给（只回了配置）就按 source_type 现取 */
async function withItems(list: HomeBlock[], get: Fetcher): Promise<HomeBlock[]> {
  const out: HomeBlock[] = []
  for (const block of list) {
    if (Number(block.isVisible) === 0) continue
    const given = Array.isArray(block.items) ? block.items : []
    out.push({
      ...block,
      items: given.length ? given : await itemsOf(block, get),
    })
  }
  return out
}

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig(event)

  // mock 模式：复用内置 mock（内部已有 /portal/home 聚合）
  if (config.mock) {
    const { handleMock } = await import('../../mock')
    return handleMock('portal/home', 'GET', {}, null)
  }

  const apiBase = String(config.apiBase || '').replace(/\/+$/, '')
  if (!apiBase) {
    throw createError({ statusCode: 500, statusMessage: '未配置 NUXT_API_BASE，且 NUXT_MOCK 已关闭' })
  }

  const get: Fetcher = async (path, query) => {
    try {
      return await $fetch<any>(`${apiBase}${path}`, { query })
    } catch {
      return null
    }
  }

  // 1) 后端已实现 /portal/home → 用它的版块配置（data 可能是数组，也可能是 { blocks }）
  //    注意：它只回配置、不回条目，条目这里补齐后再返回给页面
  const remote = await get('/portal/home')
  const remoteRaw = remote?.data
  const remoteList = Array.isArray(remoteRaw)
    ? remoteRaw
    : Array.isArray(remoteRaw?.blocks)
      ? remoteRaw.blocks
      : []

  if (remoteList.length) {
    const blocks = await withItems(remoteList.map(toBlock), get)
    return {
      code: 200,
      msg: '操作成功',
      data: { blocks },
      source: 'backend', // 布局来自后台配置
    }
  }

  // 2) 未实现 → 默认布局 + 现有接口逐个版块聚合
  const blocks = await withItems(DEFAULT_HOME_BLOCKS.map((b) => ({ ...b })), get)

  return {
    code: 200,
    msg: '操作成功',
    data: { blocks },
    source: 'fallback', // 便于排查：说明后端还没有 /portal/home，走的是前端聚合
  }
})
