/**
 * 老站地址兼容（301）：
 *   /h5/index.html                          -> /
 *   /h5/newList.html?cxsortid=412           -> /list/412
 *   /h5/newInfo.html?cxsortid=&cxid=2252    -> /article/2252
 *   /h5/guestBook.html                      -> /guestbook
 *   /mobile/**                              -> /（移动版后续如有需要再单独实现）
 *
 * 目的：保留搜索引擎已收录的旧链接与外部引用，避免 404。
 */
export default defineEventHandler((event) => {
  const path = getRequestURL(event).pathname

  if (!/^\/(h5|mobile)(\/|$)/.test(path)) return

  const query = getQuery(event)

  if (/newInfo\.html$/.test(path) && query.cxid) {
    return sendRedirect(event, `/article/${query.cxid}`, 301)
  }
  if (/newList\.html$/.test(path) && query.cxsortid) {
    return sendRedirect(event, `/list/${query.cxsortid}`, 301)
  }
  if (/guestBook\.html$/i.test(path)) {
    return sendRedirect(event, '/guestbook', 301)
  }

  return sendRedirect(event, '/', 301)
})
