/**
 * 首页区块配置（12 栅格）
 * 表结构：home_block（内容定义 + 摆位），接口：GET /portal/home、/admin/home/*
 * 这份常量既是 mock/后端的默认布局，也是接口异常时的前端兜底。
 */

export type BlockType = 'banner' | 'content'
export type SourceType = 'banner' | 'category' | 'article' | 'custom' | 'static'

export interface HomeBlockItem {
  id: number
  title: string
  cover?: string
  summary?: string
  publishAt?: string
  link?: string
  categoryId?: number
  /** banner 专用 */
  image?: string
  linkUrl?: string | null
  resourceType?: number
  resourceId?: number | null
  openType?: number
  sort?: number
}

export interface HomeBlockConfig {
  /** static 用：utils/site.ts 里的入口集合 key */
  entriesKey?: string
  /** entry_grid 底部的公益图 */
  promoImage?: string
  promoLink?: string
  categoryIds?: number[]
  includeChildren?: boolean
  articleIds?: number[]
  items?: HomeBlockItem[]
}

export interface HomeBlock {
  id?: number
  blockKey: string
  blockType: BlockType
  title: string
  styleKey: string
  sourceType: SourceType
  sourceConfig: HomeBlockConfig | null
  limitCount: number
  moreLink: string
  rowNo: number
  rowSpan: number
  colStart: number
  colSpan: number
  isVisible: number
  items?: HomeBlockItem[]
}

/** 老站首页默认布局：22%/77% 的非等宽排版近似成 3/9 栅格，捐赠公示跨 2 行填满右侧 */
export const DEFAULT_HOME_BLOCKS: HomeBlock[] = [
  {
    blockKey: 'service',
    blockType: 'content',
    title: '网上办事',
    styleKey: 'service_entries',
    sourceType: 'static',
    sourceConfig: { entriesKey: 'service' },
    limitCount: 5,
    moreLink: '',
    rowNo: 1,
    rowSpan: 1,
    colStart: 1,
    colSpan: 3,
    isVisible: 1,
  },
  {
    blockKey: 'banner',
    blockType: 'banner',
    title: '',
    styleKey: 'banner',
    sourceType: 'banner',
    sourceConfig: null,
    limitCount: 5,
    moreLink: '',
    rowNo: 1,
    rowSpan: 1,
    colStart: 4,
    colSpan: 9,
    isVisible: 1,
  },
  {
    blockKey: 'info',
    blockType: 'content',
    title: '信息公开',
    styleKey: 'entry_grid',
    sourceType: 'static',
    sourceConfig: {
      entriesKey: 'infoPublic',
      promoImage: '/images/theme/small-banner.gif',
      promoLink: '/article/1319',
    },
    limitCount: 6,
    moreLink: '/list/415',
    rowNo: 2,
    rowSpan: 1,
    colStart: 1,
    colSpan: 3,
    isVisible: 1,
  },
  {
    blockKey: 'news',
    blockType: 'content',
    title: '新闻资讯',
    styleKey: 'news_list',
    sourceType: 'category',
    sourceConfig: { categoryIds: [412], includeChildren: true },
    limitCount: 6,
    moreLink: '/list/412',
    rowNo: 2,
    rowSpan: 1,
    colStart: 4,
    colSpan: 9,
    isVisible: 1,
  },
  {
    blockKey: 'specials',
    blockType: 'content',
    title: '专题专栏',
    styleKey: 'image_strip',
    sourceType: 'category',
    sourceConfig: { categoryIds: [441], includeChildren: false },
    limitCount: 6,
    moreLink: '/list/441',
    rowNo: 3,
    rowSpan: 1,
    colStart: 1,
    colSpan: 12,
    isVisible: 1,
  },
  {
    blockKey: 'cause',
    blockType: 'content',
    title: '红会事业',
    styleKey: 'entry_chips',
    sourceType: 'static',
    sourceConfig: { entriesKey: 'redCross' },
    limitCount: 6,
    moreLink: '/list/411',
    rowNo: 4,
    rowSpan: 1,
    colStart: 1,
    colSpan: 3,
    isVisible: 1,
  },
  {
    blockKey: 'rescue',
    blockType: 'content',
    title: '应急救护',
    styleKey: 'cover_list',
    sourceType: 'category',
    sourceConfig: { categoryIds: [426], includeChildren: true },
    limitCount: 4,
    moreLink: '/list/426',
    rowNo: 4,
    rowSpan: 1,
    colStart: 4,
    colSpan: 6,
    isVisible: 1,
  },
  {
    blockKey: 'donate',
    blockType: 'content',
    title: '捐赠公示',
    styleKey: 'text_list',
    sourceType: 'category',
    sourceConfig: { categoryIds: [438], includeChildren: false },
    limitCount: 10,
    moreLink: '/list/438',
    rowNo: 4,
    rowSpan: 2,
    colStart: 10,
    colSpan: 3,
    isVisible: 1,
  },
  {
    blockKey: 'notice',
    blockType: 'content',
    title: '通知公告',
    styleKey: 'text_list',
    sourceType: 'category',
    sourceConfig: { categoryIds: [436], includeChildren: false },
    limitCount: 4,
    moreLink: '/list/436',
    rowNo: 5,
    rowSpan: 1,
    colStart: 1,
    colSpan: 3,
    isVisible: 1,
  },
  {
    blockKey: 'volunteer',
    blockType: 'content',
    title: '志愿服务',
    styleKey: 'card_list',
    sourceType: 'category',
    sourceConfig: { categoryIds: [453], includeChildren: true },
    limitCount: 3,
    moreLink: '/list/453',
    rowNo: 5,
    rowSpan: 1,
    colStart: 4,
    colSpan: 6,
    isVisible: 1,
  },
  {
    blockKey: 'links',
    blockType: 'content',
    title: '友情链接',
    styleKey: 'link_list',
    sourceType: 'category',
    sourceConfig: { categoryIds: [417], includeChildren: false },
    limitCount: 8,
    moreLink: '',
    rowNo: 6,
    rowSpan: 1,
    colStart: 1,
    colSpan: 12,
    isVisible: 1,
  },
]

/** styleKey → 组件名（components/home/*.vue），未知样式回落 text_list */
export const HOME_STYLE_COMPONENTS: Record<string, string> = {
  banner: 'HomeBlockBanner',
  service_entries: 'HomeBlockServiceEntries',
  entry_grid: 'HomeBlockEntryGrid',
  entry_chips: 'HomeBlockEntryChips',
  news_list: 'HomeBlockNewsList',
  image_strip: 'HomeBlockImageStrip',
  cover_list: 'HomeBlockCoverList',
  text_list: 'HomeBlockTextList',
  card_list: 'HomeBlockCardList',
  link_list: 'HomeBlockLinkList',
}

export function styleComponentOf(styleKey: string): string {
  return HOME_STYLE_COMPONENTS[styleKey] ?? HOME_STYLE_COMPONENTS.text_list
}

/** 12 栅格定位：把 row/col/span 转成 CSS grid 的行列占位 */
export function homeBlockGridStyle(block: HomeBlock) {
  const rowSpan = Math.max(1, Number(block.rowSpan ?? 1))
  return {
    gridRow: `${block.rowNo} / span ${rowSpan}`,
    gridColumn: `${block.colStart} / span ${block.colSpan}`,
  }
}

/** 按行、列排序（后端已排好，前端兜底再排一次） */
export function sortHomeBlocks(blocks: HomeBlock[]): HomeBlock[] {
  return [...blocks].sort(
    (a, b) => Number(a.rowNo) - Number(b.rowNo) || Number(a.colStart) - Number(b.colStart),
  )
}
