/**
 * 门户首页「布局」接口（服务端）
 *
 * 职责只有一件事：把 home_block 的版块配置（内容类型/样式/栅格摆位）给到门户。
 * 条目不在这里聚合 —— 每个版块组件拿到 sourceType / sourceConfig 后自己请求
 * （见 composables/useHomeBlockData.ts），这样：
 *   - 后端加/删/改版块，门户即时生效，不需要等前端改代码；
 *   - 单个版块接口慢/失败，只影响这一块，不会拖垮整页；
 *   - F12 里能直接看到每个版块各自的请求。
 *
 * 三种来源：
 *   1. 后端已实现 GET /portal/home → 用它的配置（data 可能是数组，也可能是 { blocks }）
 *   2. 未实现 → 用 utils/homeBlocks.ts 的默认布局
 *   3. NUXT_MOCK=true → 内置 mock
 */
import { DEFAULT_HOME_BLOCKS, type HomeBlock } from '../../../utils/homeBlocks'

/** sourceConfig 后端若按 json/字符串存，统一解析成对象 */
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

/** 兼容 camelCase / snake_case，缺省值按 12 栅格补 */
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
  // 后端若已带了条目就原样传下去，组件会优先使用
  items: Array.isArray(raw.items) ? raw.items : undefined,
})

export default defineEventHandler(async (event) => {
  const config = useRuntimeConfig(event)

  // mock 模式：复用内置 mock（mock 内部有版块配置）
  if (config.mock) {
    const { handleMock } = await import('../../mock')
    return handleMock('portal/home', 'GET', {}, null)
  }

  const apiBase = String(config.apiBase || '').replace(/\/+$/, '')
  if (!apiBase) {
    throw createError({ statusCode: 500, statusMessage: '未配置 NUXT_API_BASE，且 NUXT_MOCK 已关闭' })
  }

  const get = async (path: string): Promise<any> => {
    try {
      return await $fetch<any>(`${apiBase}${path}`)
    } catch {
      return null
    }
  }

  const remote = await get('/portal/home')
  const raw = remote?.data
  const list = Array.isArray(raw) ? raw : Array.isArray(raw?.blocks) ? raw.blocks : []

  if (list.length) {
    return {
      code: 200,
      msg: '操作成功',
      data: { blocks: list.map(toBlock) },
      source: 'backend', // 布局来自后台配置
    }
  }

  return {
    code: 200,
    msg: '操作成功',
    data: { blocks: DEFAULT_HOME_BLOCKS },
    source: 'fallback', // 后端还没有 /portal/home，用默认布局
  }
})
