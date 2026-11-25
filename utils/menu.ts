import type { MenuNode } from '~/composables/useApi'

/**
 * 门户导航栏目 → 前端路由/外链的统一解析。
 *
 * 后端 /category/category/menu 返回：
 *   { value, label, sort, templateType, isExternal, children }
 * templateType：1 = 列表页（/list/{id}），2 = 单页（/page/{id}）
 * isExternal=1 且带 link 时按外链处理，直接新窗口打开。
 *
 * 如果要调整“哪种类型走哪个页面”，只改这里的一处即可。
 */
export const TEMPLATE_LIST = 1
export const TEMPLATE_SINGLE = 2

export interface MenuTarget {
  /** 站内路由 */
  to?: string
  /** 站外链接 */
  href?: string
  external: boolean
}

export function menuTarget(node: Pick<MenuNode, 'id' | 'templateType' | 'isExternal' | 'link'>): MenuTarget {
  const externalLink = (node.link || '').trim()
  if (Number(node.isExternal) === 1 && externalLink) {
    return { href: externalLink, external: true }
  }
  if (Number(node.templateType) === TEMPLATE_SINGLE) {
    return { to: `/page/${node.id}`, external: false }
  }
  return { to: `/list/${node.id}`, external: false }
}

/** 栏目树拍平（保留 parentId，便于查父级/同级） */
export function flattenMenu(nodes: MenuNode[], parentId = 0, out: MenuNode[] = []): MenuNode[] {
  for (const node of nodes || []) {
    out.push({ ...node, parentId: node.parentId || parentId })
    if (node.children?.length) flattenMenu(node.children, node.id, out)
  }
  return out
}

/** 按 id 在树里找节点 */
export function findMenuNode(nodes: MenuNode[], id: number): MenuNode | undefined {
  return flattenMenu(nodes).find((n) => n.id === id)
}

/** 从根到当前节点的路径（面包屑用） */
export function menuPath(nodes: MenuNode[], id: number): MenuNode[] {
  const walk = (list: MenuNode[], trail: MenuNode[]): MenuNode[] | null => {
    for (const node of list) {
      const next = [...trail, node]
      if (node.id === id) return next
      if (node.children?.length) {
        const found = walk(node.children, next)
        if (found) return found
      }
    }
    return null
  }
  return walk(nodes, []) ?? []
}
