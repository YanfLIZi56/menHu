import type { MenuNode } from '~/composables/useApi'
import { articleExternalLink } from '~/utils/menu'

/**
 * 门户导航数据源（全站共用，key = portal-menu）。
 *
 * 除了拉栏目树，还会为“单页栏目”（templateType=2）补一次文章查询：
 * 如果该栏目的文章带外链（http/https，且不是视频），就把外链写回节点，
 * 让菜单直接渲染成 target="_blank" 的锚点，点击即新窗口打开。
 *
 * 结果在服务端进程内缓存 60s，避免每个页面渲染都打 6 次文章接口。
 */
const CACHE_TTL = 60 * 1000
let cachedAt = 0
let cachedTree: MenuNode[] | null = null

async function buildMenu(): Promise<MenuNode[]> {
  const api = useApi()
  const tree = await api.getMenuTree().catch(() => [] as MenuNode[])

  const singles: MenuNode[] = []
  for (const node of tree) {
    if (Number(node.templateType) === 2) singles.push(node)
    for (const child of node.children ?? []) {
      if (Number(child.templateType) === 2) singles.push(child)
    }
  }

  await Promise.all(
    singles.map(async (node) => {
      const page = await api
        .getArticles({ categoryId: node.id, templateType: 2, pageSize: 1 })
        .catch(() => null)
      const external = articleExternalLink({ link: page?.rows?.[0]?.link || '' })
      if (external) {
        node.link = external
        node.isExternal = 1
      }
    }),
  )

  return tree
}

export async function loadPortalMenu(): Promise<MenuNode[]> {
  if (cachedTree && Date.now() - cachedAt < CACHE_TTL) return cachedTree

  const tree = await buildMenu()
  cachedTree = tree
  cachedAt = Date.now()
  return tree
}

export function usePortalMenu() {
  return useAsyncData('portal-menu', loadPortalMenu, {
    default: () => [] as MenuNode[],
  })
}
