/**
 * /api/** 统一出口：
 *   - NUXT_MOCK=true（默认）→ 走内置 mock，方便前端独立开发
 *   - NUXT_MOCK=false      → 服务端转发到 NUXT_API_BASE（若依网关，如 http://127.0.0.1:8080/prod-api）
 *
 * 服务端转发的好处：浏览器同源请求，不需要后端开 CORS，也不用带 token。
 */
export default defineEventHandler(async (event) => {
  const path = (getRouterParam(event, 'path') || '').replace(/^\/+/, '')
  const config = useRuntimeConfig(event)
  const method = event.method || 'GET'

  const query = getQuery(event)
  const body = ['POST', 'PUT', 'PATCH', 'DELETE'].includes(method)
    ? await readBody(event).catch(() => null)
    : undefined

  if (config.mock) {
    const { handleMock } = await import('../mock')
    return handleMock(path, method, query, body)
  }

  const apiBase = String(config.apiBase || '').replace(/\/+$/, '')
  if (!apiBase) {
    throw createError({
      statusCode: 500,
      statusMessage: '未配置 NUXT_API_BASE，且 NUXT_MOCK 已关闭',
    })
  }

  try {
    return await $fetch(`${apiBase}/${path}`, {
      method: method as any,
      query,
      body,
      // 后端如启用白名单，这里可统一加固定请求头，例如 X-Client: portal
    })
  } catch (error: any) {
    throw createError({
      statusCode: error?.response?.status || 502,
      statusMessage: error?.data?.msg || error?.message || '后端接口请求失败',
    })
  }
})
