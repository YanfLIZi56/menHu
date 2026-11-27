/**
 * 版块取数：服务端只负责「布局」（home_block 配置），
 * 条目不参与聚合 —— 由每个版块组件拿着自己的 sourceType / sourceConfig 自行请求。
 *
 * - sourceType=banner   → /system/banner/portalList
 * - sourceType=category → /article/article/menu/list（父栏目由后端聚合子栏目）
 * - sourceType=article  → /article/article/menu/info/{id}
 * - sourceType=custom   → 直接用 sourceConfig.items
 * - sourceType=static   → 入口型版块，由组件读前端静态配置，不发请求
 *
 * 若接口已经返回了 items（后端将来把条目一起带回来），则优先用，避免重复请求。
 */
import type { HomeBlock, HomeBlockItem } from '~/utils/homeBlocks'

const pickImage = (item: any) => item?.image ?? item?.imageUrl ?? ''

const normalize = (item: any): HomeBlockItem => ({
  ...item,
  id: Number(item?.id ?? 0),
  title: item?.title ?? '',
  image: pickImage(item),
  cover: item?.cover ?? item?.image ?? item?.imageUrl ?? '',
  summary: item?.summary ?? '',
  publishAt: String(item?.publishAt ?? item?.publishTime ?? '').slice(0, 10),
  link: item?.link ?? '',
  categoryId: Number(item?.categoryId ?? 0),
  linkUrl: item?.linkUrl ?? '',
  resourceType: Number(item?.resourceType ?? 0),
  resourceId: item?.resourceId ?? null,
  openType: Number(item?.openType ?? 0),
  sort: Number(item?.sort ?? 0),
})

export const useHomeBlockItems = (block: HomeBlock) =>
  useAsyncData<HomeBlockItem[]>(
    `home-block-${block.blockKey}`,
    async () => {
      // 1) 后端/接口已带条目 → 直接用
      const given = Array.isArray(block.items) ? block.items : []
      if (given.length) return given.map(normalize)

      const api = useApi()
      const cfg: any = block.sourceConfig ?? {}
      const limit = Number(block.limitCount || 6)

      // 2) 轮播
      if (block.blockType === 'banner' || block.sourceType === 'banner') {
        const rows = await api.getBanners()
        return rows
          .slice(0, Number(block.limitCount || 9))
          .map((b) => normalize({ ...b, image: b.image }))
      }

      // 3) 指定文章
      if (block.sourceType === 'article') {
        const ids: number[] = Array.isArray(cfg.articleIds) ? cfg.articleIds : []
        const out: HomeBlockItem[] = []
        for (const id of ids) {
          const row = await api.getArticle(id)
          if (row?.id) out.push(normalize(row))
        }
        return out
      }

      // 4) 自定义写入的条目
      if (block.sourceType === 'custom') {
        return Array.isArray(cfg.items) ? cfg.items.map(normalize) : []
      }

      // 5) 入口型静态版块（网上办事 / 信息公开 / 红会事业），不发请求
      if (block.sourceType === 'static') return []

      // 6) 直接子类别（target=children）：条目是类别，点进去是列表页
      if (cfg.target === 'children') {
        const ids: number[] = Array.isArray(cfg.categoryIds) ? cfg.categoryIds : []
        const cats: any[] = []
        for (const id of ids) {
          cats.push(...(await api.getCategoryItems(id)))
        }
        return cats
          .sort((a, b) => Number(a.sort ?? 0) - Number(b.sort ?? 0))
          .slice(0, limit)
          .map((c) =>
            normalize({
              id: c.id,
              categoryId: c.parentId ?? ids[0] ?? 0,
              title: c.name,
              cover: c.cover,
              link: `/list/${c.id}`,
            }),
          )
      }

      // 7) 栏目文章
      const ids: number[] = Array.isArray(cfg.categoryIds) ? cfg.categoryIds : []
      const rows: any[] = []
      for (const id of ids) {
        const res = await api.getArticles({ categoryId: id, pageSize: limit })
        rows.push(...(res.rows ?? []))
      }

      const seen = new Set<number>()
      return rows
        .filter((a) => {
          if (seen.has(Number(a.id))) return false
          seen.add(Number(a.id))
          return true
        })
        .slice(0, limit)
        .map(normalize)
    },
    { default: () => (Array.isArray(block.items) ? block.items.map(normalize) : []) },
  )
