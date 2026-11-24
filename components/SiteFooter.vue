<template>
  <footer>
    <div class="foots mt">
      <div class="banQuan w1200">
        <table>
          <tbody>
            <tr>
              <td width="50%" valign="top">
                <p>汕头市红十字会</p>
                <p>地　　址：汕头市金平区中山路130号协华大厦11A</p>
                <p>电话号码：0754-88295834　　传真号码：0754-88295834</p>
                <p>邮政编码：515000</p>
                <p>
                  技术支持：广东弘业科技有限公司　
                  <a href="http://beian.miit.gov.cn/" target="_blank" rel="noopener">
                    粤ICP备20035147号
                  </a>
                </p>
              </td>
              <td width="9%" align="center" valign="middle">
                <img src="/images/demo/qr-wechat.jpg" alt="微信公众号" /><br />公众号
              </td>
              <td width="9%" align="center" valign="middle">
                <NuxtLink to="/article/1319">
                  <img src="/images/demo/qr-donate.jpg" alt="扫码捐款" />
                </NuxtLink>
                <br />扫码捐款
              </td>
              <td align="right">
                募捐账户<br />
                户名：汕头市红十字会<br />
                开户行：中行汕头金园支行<br />
                账号：665257760303<br />
                备注：捐款请注明用途
              </td>
            </tr>
          </tbody>
        </table>
      </div>
    </div>

    <button class="totop" type="button" title="回到顶部" @click="backToTop" />
  </footer>
</template>

<script setup lang="ts">
/**
 * 站点页脚：红底版权信息 + 公众号/捐款二维码 + 回到顶部。
 * 文案取自老站 cxid=1328「底部版权信息」，后续若需后台维护，
 * 可新增 system/config 接口替换这里的静态内容。
 */
const backToTop = () => {
  window.scrollTo({ top: 0, behavior: 'smooth' })
}
</script>
