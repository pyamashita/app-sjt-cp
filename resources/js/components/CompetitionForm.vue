<template>
  <form :action="formAction" method="POST" class="space-y-6">
    <input type="hidden" name="_token" :value="csrfToken">
    <input v-if="isEdit" type="hidden" name="_method" value="PUT">

    <!-- 大会名称 -->
    <div>
      <label for="name" class="block text-sm font-semibold text-gray-700 mb-2">
        大会名称 <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        id="name" 
        name="name" 
        v-model="form.name"
        required
        placeholder="例：第60回技能五輪全国大会"
        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
      >
      <div v-if="errors.name" class="text-red-500 text-sm mt-1">{{ errors.name[0] }}</div>
    </div>

    <!-- 開催日・終了日 -->
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
      <div>
        <label for="start_date" class="block text-sm font-semibold text-gray-700 mb-2">
          開催日 <span class="text-red-500">*</span>
        </label>
        <input 
          type="date" 
          id="start_date" 
          name="start_date" 
          v-model="form.start_date"
          required
          class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
        >
        <div v-if="errors.start_date" class="text-red-500 text-sm mt-1">{{ errors.start_date[0] }}</div>
      </div>

      <div>
        <label for="end_date" class="block text-sm font-semibold text-gray-700 mb-2">
          終了日 <span class="text-red-500">*</span>
        </label>
        <input 
          type="date" 
          id="end_date" 
          name="end_date" 
          v-model="form.end_date"
          :min="form.start_date"
          required
          class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
        >
        <div v-if="errors.end_date" class="text-red-500 text-sm mt-1">{{ errors.end_date[0] }}</div>
      </div>
    </div>

    <!-- 開催場所 -->
    <div>
      <label for="venue" class="block text-sm font-semibold text-gray-700 mb-2">
        開催場所 <span class="text-red-500">*</span>
      </label>
      <input 
        type="text" 
        id="venue" 
        name="venue" 
        v-model="form.venue"
        required
        placeholder="例：東京ビッグサイト"
        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
      >
      <div v-if="errors.venue" class="text-red-500 text-sm mt-1">{{ errors.venue[0] }}</div>
    </div>

    <!-- 競技主査 -->
    <div>
      <label for="chief_judge_id" class="block text-sm font-semibold text-gray-700 mb-2">
        競技主査
      </label>
      <select 
        id="chief_judge_id" 
        name="chief_judge_id" 
        v-model="form.chief_judge_id"
        class="block w-full px-3 py-3 border border-gray-300 rounded-lg text-gray-900 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
      >
        <option value="">選択してください</option>
        <option 
          v-for="member in committeeMembers" 
          :key="member.id" 
          :value="member.id"
        >
          {{ member.display_name }}
        </option>
      </select>
      <div v-if="errors.chief_judge_id" class="text-red-500 text-sm mt-1">{{ errors.chief_judge_id[0] }}</div>
    </div>

    <!-- 競技委員 -->
    <div>
      <label class="block text-sm font-semibold text-gray-700 mb-2">
        競技委員
      </label>
      <div class="space-y-2 max-h-48 overflow-y-auto border border-gray-300 rounded-lg p-3">
        <label 
          v-for="member in committeeMembers" 
          :key="member.id"
          class="flex items-center space-x-2 cursor-pointer hover:bg-gray-50 p-2 rounded"
        >
          <input 
            type="checkbox" 
            :value="member.id"
            v-model="form.committee_member_ids"
            :disabled="form.chief_judge_id == member.id"
            class="h-4 w-4 text-blue-600 focus:ring-blue-500 border-gray-300 rounded"
          >
          <span :class="{ 'text-gray-400': form.chief_judge_id == member.id }">
            {{ member.display_name }}
            <span v-if="form.chief_judge_id == member.id" class="text-xs text-gray-500">（競技主査として選択済み）</span>
          </span>
        </label>
      </div>
      <input 
        v-for="(memberId, index) in form.committee_member_ids" 
        :key="index"
        type="hidden" 
        name="committee_member_ids[]" 
        :value="memberId"
      >
      <div v-if="errors.committee_member_ids" class="text-red-500 text-sm mt-1">{{ errors.committee_member_ids[0] }}</div>
    </div>

    <!-- フォームボタン -->
    <div class="flex items-center justify-between pt-6 border-t border-gray-200">
      <a :href="cancelUrl" 
         class="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-lg text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200">
        キャンセル
      </a>
      <button 
        type="submit"
        class="inline-flex items-center px-6 py-2 border border-transparent text-sm font-medium rounded-lg shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 transition duration-200"
      >
        <svg v-if="!isEdit" class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path>
        </svg>
        <svg v-else class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path>
        </svg>
        {{ isEdit ? '更新' : '作成' }}
      </button>
    </div>
  </form>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'CompetitionForm',
  props: {
    formAction: {
      type: String,
      required: true
    },
    cancelUrl: {
      type: String,
      required: true
    },
    csrfToken: {
      type: String,
      required: true
    },
    isEdit: {
      type: Boolean,
      default: false
    },
    competition: {
      type: Object,
      default: () => ({})
    },
    committeeMembers: {
      type: Array,
      default: () => []
    },
    errors: {
      type: Object,
      default: () => ({})
    }
  },
  setup(props) {
    const form = ref({
      name: '',
      start_date: '',
      end_date: '',
      venue: '',
      chief_judge_id: null,
      committee_member_ids: []
    });

    // 初期データの設定
    onMounted(() => {
      if (props.competition) {
        form.value = {
          name: props.competition.name || '',
          start_date: props.competition.start_date || '',
          end_date: props.competition.end_date || '',
          venue: props.competition.venue || '',
          chief_judge_id: props.competition.chief_judge_id || null,
          committee_member_ids: props.competition.committee_member_ids || []
        };
      }
    });

    return {
      form
    };
  }
};
</script>

<style scoped>
/* 追加のスタイリングが必要な場合はここに記述 */
</style>