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
      .filter((b) => b.status !== 0)
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

  // 1) 后端已实现聚合接口 → 透传（后台配置的布局/条目原样使用）
  const remote = await get('/portal/home')
  const remoteBlocks = remote?.data?.blocks
  if (Array.isArray(remoteBlocks) && remoteBlocks.length) {
    return remote
  }

  // 2) 未实现 → 默认布局 + 现有接口逐个版块聚合
  const blocks: any[] = []
  for (const block of DEFAULT_HOME_BLOCKS) {
    if (Number(block.isVisible) === 0) continue
    blocks.push({ ...block, items: await itemsOf(block, get) })
  }

  return {
    code: 200,
    msg: '操作成功',
    data: { blocks },
    source: 'fallback', // 便于排查：说明后端还没有 /portal/home，走的是前端聚合
  }
})
