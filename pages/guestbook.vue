<template>
  <div class="w1200">
    <SiteBreadcrumb :items="[{ title: '救护员培训报名' }]" />

    <div class="newInfoContent mt" style="background: #fff; min-height: 510px">
      <div class="title">救护员培训报名</div>

      <div class="contents">
        <div class="cen forms">
          <table class="layui-table" lay-skin="line">
            <tbody>
              <tr>
                <td width="15%">姓名：</td>
                <td>
                  <input v-model="form.name" type="text" placeholder="请输入联系人姓名" />
                </td>
              </tr>
              <tr>
                <td>人数：</td>
                <td>
                  <input v-model.number="form.people" type="number" min="1" placeholder="请输入参训人数" />
                </td>
              </tr>
              <tr>
                <td>手机：</td>
                <td>
                  <input v-model="form.phone" type="tel" maxlength="11" placeholder="请输入联系手机号" />
                </td>
              </tr>
              <tr>
                <td>参训时间：</td>
                <td>
                  <label style="margin-right: 30px">
                    <input v-model="form.trainTime" type="checkbox" value="工作日（周一至周五）" />
                    工作日（周一至周五）
                  </label>
                  <label>
                    <input v-model="form.trainTime" type="checkbox" value="非工作日（周六、周日）" />
                    非工作日（周六、周日）
                  </label>
                </td>
              </tr>
              <tr>
                <td colspan="2" style="padding: 55px 0">
                  <a class="submits" style="margin-right: 50px" @click="submit">提交</a>
                  <a class="submits" @click="goBack">返回</a>
                </td>
              </tr>
            </tbody>
          </table>
          <div v-if="tip" class="form-tip" :class="{ ok: tipOk }">{{ tip }}</div>
        </div>
      </div>
    </div>
    <div class="clearfix" />
  </div>
</template>

<script setup lang="ts">
import { reactive, ref } from 'vue'

/**
 * 救护员培训报名 / 留言（替代老站 guestBook.html）
 * 提交接口：POST /guestbook/guestbook/add
 */
const api = useApi()

const form = reactive({
  name: '',
  people: 1,
  phone: '',
  trainTime: [] as string[],
})

const tip = ref('')
const tipOk = ref(false)
const submitting = ref(false)

const submit = async () => {
  if (submitting.value) return
  tip.value = ''
  tipOk.value = false

  if (!form.name.trim()) {
    tip.value = '请填写联系人姓名'
    return
  }
  if (!form.people || form.people < 1) {
    tip.value = '参训人数至少为 1 人'
    return
  }
  if (!/^1[3-9]\d{9}$/.test(form.phone)) {
    tip.value = '请输入正确的 11 位手机号'
    return
  }
  if (!form.trainTime.length) {
    tip.value = '请选择参训时间'
    return
  }

  submitting.value = true
  try {
    const res = await api.submitGuestbook({
      name: form.name.trim(),
      people: Number(form.people),
      phone: form.phone,
      trainTime: form.trainTime,
    })
    tip.value = res.msg
    tipOk.value = res.code === 200
    if (res.code === 200) {
      form.name = ''
      form.people = 1
      form.phone = ''
      form.trainTime = []
    }
  } catch (e: any) {
    tip.value = e?.data?.msg || e?.message || '提交失败，请稍后重试'
  } finally {
    submitting.value = false
  }
}

const goBack = () => {
  if (import.meta.client) window.history.back()
}

useSeoMeta({
  title: '救护员培训报名 - 汕头市红十字会',
  description: '汕头市红十字会救护员培训在线报名，填写姓名、人数、手机号与参训时间即可提交。',
})
</script>
