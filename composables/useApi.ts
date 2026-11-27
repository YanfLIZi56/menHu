/**
 * 门户对外接口统一封装。
 *
 * 约定（详见 docs/api.md）：
 *   导航栏目树   GET  /category/category/menu
 *   栏目文章列表 GET  /article/article/menu/list?categoryId=&templateType=&pageNum=&pageSize=
 *   单页栏目内容 GET  /article/article/menu/{categoryId}?templateType=
 *   文章详情     GET  /article/article/menu/info/{id}
 *   首页版块     GET  /portal/home（骨架 + 条目一次返回，由 home_block 表驱动）
 *   轮播图       GET  /system/banner/list
 *   留言报名     POST /guestbook/guestbook/add
 *
 * 前端统一请求 /api/**，由 Nitro（server/api/[...path].ts）转发到真实后端，
 * 或在内置 mock 模式下返回示例数据。浏览器端不需要带 token。
 */
import type { HomeBlock } from '~/utils/homeBlocks'

export interface Banner {
  id: number
  title: string
  image: string
  linkUrl?: string | null
  resourceId?: number | null
  /** 0 无跳转 / 1 类别 / 2 文章 / 3 外链（按后端 portalList 定义） */
  resourceType: number
  /** 1 = 新标签页打开 */
  openType?: number
  sort: number
}

export interface Category {
  id: number
  parentId: number
  name: string
  sort?: number
}

export interface Article {
  id: number
  categoryId: number
  title: string
  summary: string
  cover: string
  content: string
  publishTime: string
  views?: number
  /** 外链或视频地址（老站 cxlink） */
  link?: string
  /** 栏目名称（面包屑用，后端可选返回） */
  categoryName?: string
}

export interface MenuNode {
  id: number
  parentId: number
  name: string
  sort: number
  /** 1 列表页 / 2 单页（详情页） */
  templateType: number
  /** 1 表示外链 */
  isExternal: number
  /** 外链地址（isExternal=1 时使用） */
  link?: string
  children: MenuNode[]
}

export interface MenuQuery {
  categoryId: number
  templateType?: number
  pageNum?: number
  pageSize?: number
}

export interface ArticleQuery {
  categoryId?: number | string
  templateType?: number
  pageNum?: number
  pageSize?: number
  keyword?: string
}

const stripHtml = (html?: string) =>
  (html || '')
    .replace(/<[^>]+>/g, ' ')
    .replace(/&nbsp;/g, ' ')
    .replace(/\s+/g, ' ')
    .trim()

/** 兼容若依 TableDataInfo({rows,total}) 与老站({codes,data:{rows}}) 两种外壳 */
function pickRows(res: any): any[] {
  if (!res) return []
  if (Array.isArray(res.rows)) return res.rows
  if (Array.isArray(res.data?.rows)) return res.data.rows
  if (Array.isArray(res.data)) return res.data
  return []
}

function pickTotal(res: any, rows: any[]): number {
  return Number(res?.total ?? res?.data?.total ?? rows.length ?? 0)
}

function pickData(res: any): any {
  return res?.data !== undefined ? res.data : res
}

/** 兼容后端 {value,label} 与前端 {id,name} 两种命名，递归归一化栏目树 */
function toMenuNode(raw: any, parentId = 0): MenuNode {
  const id = Number(raw?.value ?? raw?.id ?? 0)
  const children = Array.isArray(raw?.children)
    ? raw.children
      .filter((c: any) => c && typeof c === 'object')
      .map((c: any) => toMenuNode(c, id))
    : []

  return {
    id,
    parentId,
    name: String(raw?.label ?? raw?.name ?? ''),
    sort: Number(raw?.sort ?? 0),
    templateType: Number(raw?.templateType ?? 1),
    isExternal: Number(raw?.isExternal ?? 0),
    link: raw?.link ?? raw?.linkUrl ?? '',
    children,
  }
}

/** 后端上传的相对路径（/profile/upload/...）补齐为可访问地址；mock 站内静态图保持原样 */
function resolveAsset(path: string | undefined, fileBase: string): string {
  const p = String(path ?? '').trim()
  if (!p) return ''
  if (/^(?:https?:)?\/\//i.test(p) || p.startsWith('data:') || p.startsWith('blob:')) return p
  if (!fileBase) return p
  if (p.startsWith('/images/') || p.startsWith('/fonts/')) return p
  return `${fileBase.replace(/\/+$/, '')}/${p.replace(/^\/+/, '')}`
}

/** 正文里后台上传的图片也要补全域名，否则详情页 404 */
function rewriteContent(html: string, fileBase: string): string {
  if (!fileBase || !html) return html
  return html.replace(/(src|href)="\/profile\//g, (_m, attr) => `${attr}="${fileBase}/profile/`)
}

function toArticle(raw: any, fileBase = ''): Article {
  return {
    id: Number(raw.id ?? raw.cxid),
    categoryId: Number(raw.categoryId ?? raw.cxsortid ?? 0),
    title: raw.title ?? raw.cxtitlecn ?? '',
    summary: raw.summary ?? stripHtml(raw.cxcontentcn),
    cover: resolveAsset(raw.cover ?? raw.image ?? raw.cximg0 ?? '', fileBase),
    content: rewriteContent(raw.content ?? raw.cxcontentcn ?? '', fileBase),
    publishTime: String(raw.publishAt ?? raw.publishTime ?? raw.cxupdatetime ?? '').slice(0, 10),
    views: Number(raw.views ?? raw.cxhits ?? 0),
    link: raw.link ?? raw.cxlink ?? '',
    categoryName: raw.categoryName ?? '',
  }
}

function toBanner(raw: any, fileBase = ''): Banner {
  return {
    id: Number(raw.id ?? raw.cxid),
    title: raw.title ?? raw.cxtitlecn ?? '',
    image: resolveAsset(raw.image ?? raw.imageUrl ?? raw.cximg0 ?? '', fileBase),
    linkUrl: raw.linkUrl ?? raw.link ?? raw.cxlink ?? null,
    resourceId: raw.resourceId ?? null,
    resourceType: Number(raw.resourceType ?? (raw.linkUrl || raw.cxlink ? 3 : 0)),
    openType: Number(raw.openType ?? 0),
    sort: Number(raw.sort ?? 0),
  }
}

/** 轮播图跳转目标：resourceType 0 无跳转 / 1 类别 / 2 文章 / 3 外链；openType=1 新标签页 */
export function bannerTarget(banner: Banner): {
  link: string
  external: boolean
  newTab: boolean
} {
  const newTab = Number(banner.openType) === 1

  switch (Number(banner.resourceType)) {
    case 1:
      return { link: `/list/${banner.resourceId}`, external: false, newTab }
    case 2:
      return { link: `/article/${banner.resourceId}`, external: false, newTab }
    case 3:
      return { link: banner.linkUrl || '', external: true, newTab }
    default:
      return { link: '', external: false, newTab: false }
  }
}

function toHomeBlock(raw: any, fileBase = ''): HomeBlock {
  const items = Array.isArray(raw.items) ? raw.items : []
  return {
    id: raw.id !== undefined ? Number(raw.id) : undefined,
    blockKey: String(raw.blockKey ?? raw.block_key ?? ''),
    blockType: raw.blockType === 'banner' || raw.block_type === 'banner' ? 'banner' : 'content',
    title: raw.title ?? '',
    styleKey: String(raw.styleKey ?? raw.style_key ?? 'text_list'),
    sourceType: String(raw.sourceType ?? raw.source_type ?? 'category') as HomeBlock['sourceType'],
    sourceConfig: raw.sourceConfig ?? raw.source_config ?? null,
    limitCount: Number(raw.limitCount ?? raw.limit_count ?? 6),
    moreLink: raw.moreLink ?? raw.more_link ?? '',
    rowNo: Number(raw.rowNo ?? raw.row_no ?? 1),
    rowSpan: Number(raw.rowSpan ?? raw.row_span ?? 1),
    colStart: Number(raw.colStart ?? raw.col_start ?? 1),
    colSpan: Number(raw.colSpan ?? raw.col_span ?? 12),
    isVisible: Number(raw.isVisible ?? raw.is_visible ?? 1),
    // 条目：封面 / 正文图片统一补全文件域名
    items: items.map((item: any) => ({
      ...item,
      cover: resolveAsset(item.cover ?? '', fileBase),
      image: resolveAsset(item.image ?? item.imageUrl ?? '', fileBase),
    })),
  }
}

export const useApi = () => {
  const config = useRuntimeConfig()
  const prefix = config.public.apiPrefix || '/api'
  const fileBase = String(config.public.fileBase || '')
  const url = (path: string) => `${prefix}${path.startsWith('/') ? '' : '/'}${path}`

  return {
    /** 首页轮播图（保留给旧代码 / 单页使用，首页已并入 /portal/home） */
    async getBanners(): Promise<Banner[]> {
      const res = await $fetch<any>(url('/system/banner/portalList'))
      return pickRows(res)
        .map((r: any) => toBanner(r, fileBase))
        .sort((a, b) => a.sort - b.sort)
    },

    /** 首页版块（后台可配置）：一次返回骨架与已取好的条目 */
    async getHomePage(): Promise<{ blocks: HomeBlock[] }> {
      const res = await $fetch<any>(url('/portal/home'))
      const payload = pickData(res) ?? {}
      const blocks = Array.isArray(payload.blocks) ? payload.blocks : []
      return {
        blocks: blocks.map((b: any) => toHomeBlock(b, fileBase)),
      }
    },

    /** 栏目树（扁平返回，前端按 parentId 组树） */
    async getCategories(): Promise<Category[]> {
      const res = await $fetch<any>(url('/category/category/list'))
      return pickRows(res).map((r: any) => ({
        id: Number(r.id ?? r.cxid),
        parentId: Number(r.parentId ?? r.cxpid ?? 0),
        name: r.name ?? r.cxsortcn ?? '',
        sort: Number(r.sort ?? 0),
      }))
    },

    /** 门户导航栏目树（两级，含 templateType / isExternal） */
    async getMenuTree(): Promise<MenuNode[]> {
      const res = await $fetch<any>(url('/category/category/menu'))
      const data = pickData(res)
      return Array.isArray(data) ? data.map((n: any) => toMenuNode(n)) : []
    },

    /** 栏目文章列表（导航列表栏目 / 首页各版块共用） */
    async getArticles(query: ArticleQuery = {}): Promise<{ rows: Article[]; total: number }> {
      const res = await $fetch<any>(url('/article/article/menu/list'), {
        query: {
          categoryId: query.categoryId,
          // 后端未做 null 判断（templateType 空会 NPE），首页等无类型场景固定传 1（列表）
          templateType: Number(query.templateType ?? 1),
          pageNum: query.pageNum ?? 1,
          pageSize: query.pageSize ?? 10,
        },
      })
      const rows = pickRows(res).map((r: any) => toArticle(r, fileBase))
      return { rows, total: pickTotal(res, rows) }
    },

    /** 单页栏目：该栏目绑定的文章（导航 templateType=2 时使用） */
    async getMenuArticle(categoryId: number | string, templateType = 2): Promise<Article> {
      const res = await $fetch<any>(url(`/article/article/menu/${categoryId}`), {
        query: { templateType },
      })
      return toArticle(pickData(res), fileBase)
    },

    /** 文章 / 单页详情 */
    async getArticle(id: number | string): Promise<Article> {
      const res = await $fetch<any>(url(`/article/article/menu/info/${id}`))
      return toArticle(pickData(res), fileBase)
    },

    /** 救护员培训报名 / 留言（后端 05e4021 起改为 /leaveMessage/leaveMessage） */
    async submitGuestbook(payload: {
      name: string
      people: number
      phone: string
      trainingTimes: string
      remark?: string
    }): Promise<{ code: number; msg: string }> {
      const res = await $fetch<any>(url('/leaveMessage/leaveMessage'), {
        method: 'POST',
        body: payload,
      })
      return { code: Number(res?.code ?? 200), msg: res?.msg ?? '提交成功' }
    },
  }
}
