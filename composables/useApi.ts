/**
 * 门户对外接口统一封装。
 *
 * 约定（详见 docs/api.md）：
 *   导航栏目树   GET  /category/category/menu
 *   栏目文章列表 GET  /article/article/menu/list?categoryId=&templateType=&pageNum=&pageSize=
 *   单页栏目内容 GET  /article/article/menu/{categoryId}?templateType=
 *   文章详情     GET  /article/article/{id}（待后端提供，门户列表点进详情用）
 *   轮播图       GET  /system/banner/list
 *   留言报名     POST /guestbook/guestbook/add
 *
 * 前端统一请求 /api/**，由 Nitro（server/api/[...path].ts）转发到真实后端，
 * 或在内置 mock 模式下返回示例数据。浏览器端不需要带 token。
 */

export interface Banner {
  id: number
  title: string
  image: string
  linkUrl?: string | null
  resourceId?: number | null
  /** 0 无跳转 / 1 文章 / 2 栏目 / 3 外链 */
  resourceType: number
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

function toArticle(raw: any): Article {
  return {
    id: Number(raw.id ?? raw.cxid),
    categoryId: Number(raw.categoryId ?? raw.cxsortid ?? 0),
    title: raw.title ?? raw.cxtitlecn ?? '',
    summary: raw.summary ?? stripHtml(raw.cxcontentcn),
    cover: raw.cover ?? raw.image ?? raw.cximg0 ?? '',
    content: raw.content ?? raw.cxcontentcn ?? '',
    publishTime: String(raw.publishAt ?? raw.publishTime ?? raw.cxupdatetime ?? '').slice(0, 10),
    views: Number(raw.views ?? raw.cxhits ?? 0),
    link: raw.link ?? raw.cxlink ?? '',
    categoryName: raw.categoryName ?? '',
  }
}

function toBanner(raw: any): Banner {
  return {
    id: Number(raw.id ?? raw.cxid),
    title: raw.title ?? raw.cxtitlecn ?? '',
    image: raw.image ?? raw.imageUrl ?? raw.cximg0 ?? '',
    linkUrl: raw.linkUrl ?? raw.link ?? raw.cxlink ?? null,
    resourceId: raw.resourceId ?? null,
    resourceType: Number(raw.resourceType ?? (raw.linkUrl || raw.cxlink ? 3 : 0)),
    sort: Number(raw.sort ?? 0),
  }
}

/** 轮播图 / 文章跳转目标统一在这里解析 */
export function bannerTarget(banner: Banner): { link: string; external: boolean } {
  switch (banner.resourceType) {
    case 1:
      return { link: `/article/${banner.resourceId}`, external: false }
    case 2:
      return { link: `/list/${banner.resourceId}`, external: false }
    case 3:
      return { link: banner.linkUrl || '#', external: true }
    default:
      return { link: '#', external: false }
  }
}

export const useApi = () => {
  const config = useRuntimeConfig()
  const prefix = config.public.apiPrefix || '/api'
  const url = (path: string) => `${prefix}${path.startsWith('/') ? '' : '/'}${path}`

  return {
    /** 首页轮播图 */
    async getBanners(): Promise<Banner[]> {
      const res = await $fetch<any>(url('/system/banner/list'))
      return pickRows(res).map(toBanner).sort((a, b) => a.sort - b.sort)
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
      const rows = pickRows(res).map(toArticle)
      return { rows, total: pickTotal(res, rows) }
    },

    /** 单页栏目：该栏目绑定的文章（导航 templateType=2 时使用） */
    async getMenuArticle(categoryId: number | string, templateType = 2): Promise<Article> {
      const res = await $fetch<any>(url(`/article/article/menu/${categoryId}`), {
        query: { templateType },
      })
      return toArticle(pickData(res))
    },

    /** 文章 / 单页详情 */
    async getArticle(id: number | string): Promise<Article> {
      const res = await $fetch<any>(url(`/article/article/${id}`))
      return toArticle(pickData(res))
    },

    /** 救护员培训报名 / 留言 */
    async submitGuestbook(payload: {
      name: string
      people: number
      phone: string
      trainTime: string[]
      remark?: string
    }): Promise<{ code: number; msg: string }> {
      const res = await $fetch<any>(url('/guestbook/guestbook/add'), {
        method: 'POST',
        body: payload,
      })
      return { code: Number(res?.code ?? 200), msg: res?.msg ?? '提交成功' }
    },
  }
}
