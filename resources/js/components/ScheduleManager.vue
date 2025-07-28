<template>
  <div class="bg-white shadow-lg rounded-xl overflow-hidden">
    <div class="px-6 py-4 border-b border-gray-200">
      <div class="flex justify-between items-start">
        <h3 class="text-lg font-semibold text-gray-900">スケジュール管理</h3>
        
        <div class="flex flex-col sm:flex-row gap-2">
          <!-- CSV機能 -->
          <div class="flex gap-2">
            <button 
              @click="exportCSV"
              class="inline-flex items-center px-3 py-1 bg-blue-600 text-white text-sm font-medium rounded-lg hover:bg-blue-700 transition duration-200"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
              </svg>
              CSV出力
            </button>
            
            <button 
              @click="showImportModal = true"
              class="inline-flex items-center px-3 py-1 bg-purple-600 text-white text-sm font-medium rounded-lg hover:bg-purple-700 transition duration-200"
            >
              <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M9 19l3 3m0 0l3-3m-3 3V10"></path>
              </svg>
              CSV取込
            </button>
          </div>
          
          <!-- スケジュール追加 -->
          <button 
            @click="addNewSchedule"
            class="inline-flex items-center px-3 py-1 bg-green-600 text-white text-sm font-medium rounded-lg hover:bg-green-700 transition duration-200"
          >
            <svg class="h-4 w-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m-6 0h6m0 0h6"></path>
            </svg>
            スケジュール追加
          </button>
        </div>
      </div>
    </div>

    <!-- 成功メッセージ -->
    <div v-if="successMessage" class="mx-6 mt-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded-lg">
      {{ successMessage }}
    </div>

    <!-- エラーメッセージ -->
    <div v-if="errorMessage" class="mx-6 mt-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded-lg">
      {{ errorMessage }}
    </div>

    <div v-if="schedules.length > 0 || newSchedules.length > 0" class="overflow-x-auto">
      <table class="min-w-full divide-y divide-gray-200">
        <thead class="bg-gray-50">
          <tr>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">時刻</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">内容</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">備考</th>
            <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">エフェクト</th>
            <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">操作</th>
          </tr>
        </thead>
        <tbody class="bg-white divide-y divide-gray-200">
          <!-- 既存のスケジュール -->
          <tr v-for="(schedule, index) in schedules" :key="`existing-${schedule.id}`" class="hover:bg-gray-50">
            <template v-if="!schedule.editing">
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm font-mono font-medium text-gray-900">{{ formatTime(schedule.start_time) }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm font-medium text-gray-900">{{ schedule.content }}</div>
              </td>
              <td class="px-6 py-4">
                <div class="text-sm text-gray-600">{{ schedule.notes || '-' }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap">
                <div class="text-sm text-gray-900">{{ getEffectsString(schedule) }}</div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <button @click="editSchedule(index)" class="text-indigo-600 hover:text-indigo-900">編集</button>
                <button @click="deleteSchedule(schedule.id)" class="text-red-600 hover:text-red-900">削除</button>
              </td>
            </template>
            <template v-else>
              <!-- 編集モード -->
              <td class="px-6 py-4">
                <input 
                  v-model="schedule.start_time" 
                  type="time" 
                  class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  required
                >
              </td>
              <td class="px-6 py-4">
                <input 
                  v-model="schedule.content" 
                  type="text" 
                  class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  placeholder="内容"
                  required
                >
              </td>
              <td class="px-6 py-4">
                <textarea 
                  v-model="schedule.notes" 
                  rows="1"
                  class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                  placeholder="備考"
                ></textarea>
              </td>
              <td class="px-6 py-4">
                <div class="space-y-1">
                  <label class="flex items-center text-xs">
                    <input v-model="schedule.count_up" type="checkbox" class="mr-1">
                    カウントアップ
                  </label>
                  <label class="flex items-center text-xs">
                    <input v-model="schedule.auto_advance" type="checkbox" class="mr-1">
                    自動送り
                  </label>
                </div>
              </td>
              <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
                <button @click="saveSchedule(index)" class="text-green-600 hover:text-green-900">保存</button>
                <button @click="cancelEdit(index)" class="text-gray-600 hover:text-gray-900">キャンセル</button>
              </td>
            </template>
          </tr>

          <!-- 新規追加のスケジュール -->
          <tr v-for="(newSchedule, index) in newSchedules" :key="`new-${index}`" class="hover:bg-gray-50 bg-blue-50">
            <td class="px-6 py-4">
              <input 
                v-model="newSchedule.start_time" 
                type="time" 
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                required
              >
            </td>
            <td class="px-6 py-4">
              <input 
                v-model="newSchedule.content" 
                type="text" 
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="内容"
                required
              >
            </td>
            <td class="px-6 py-4">
              <textarea 
                v-model="newSchedule.notes" 
                rows="1"
                class="w-full px-2 py-1 border border-gray-300 rounded text-sm focus:ring-2 focus:ring-blue-500"
                placeholder="備考"
              ></textarea>
            </td>
            <td class="px-6 py-4">
              <div class="space-y-1">
                <label class="flex items-center text-xs">
                  <input v-model="newSchedule.count_up" type="checkbox" class="mr-1">
                  カウントアップ
                </label>
                <label class="flex items-center text-xs">
                  <input v-model="newSchedule.auto_advance" type="checkbox" class="mr-1">
                  自動送り
                </label>
              </div>
            </td>
            <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-2">
              <button @click="saveNewSchedule(index)" class="text-green-600 hover:text-green-900">保存</button>
              <button @click="removeNewSchedule(index)" class="text-red-600 hover:text-red-900">削除</button>
            </td>
          </tr>
        </tbody>
      </table>
    </div>

    <!-- 空の状態 -->
    <div v-if="schedules.length === 0 && newSchedules.length === 0" class="text-center py-12">
      <div class="mx-auto h-16 w-16 bg-gray-100 rounded-full flex items-center justify-center mb-4">
        <svg class="h-8 w-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
        </svg>
      </div>
      <h3 class="text-lg font-medium text-gray-900 mb-2">スケジュールが登録されていません</h3>
      <p class="text-gray-600 mb-6">この日程のスケジュールを追加してください。</p>
      <button 
        @click="addNewSchedule"
        class="inline-flex items-center px-4 py-2 bg-green-600 text-white font-semibold rounded-lg hover:bg-green-700 transition duration-200"
      >
        <svg class="h-5 w-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
          <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m-6 0h6m0 0h6"></path>
        </svg>
        スケジュール追加
      </button>
    </div>

    <!-- CSVインポートモーダル -->
    <div v-if="showImportModal" class="fixed inset-0 bg-gray-600 bg-opacity-50 overflow-y-auto h-full w-full z-50" @click="closeImportModal">
      <div class="relative top-20 mx-auto p-5 border w-96 shadow-lg rounded-md bg-white" @click.stop>
        <div class="mt-3">
          <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-medium text-gray-900">CSVファイル取込</h3>
            <button @click="closeImportModal" class="text-gray-400 hover:text-gray-600">
              <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
              </svg>
            </button>
          </div>
          
          <form @submit.prevent="importCSV">
            <!-- ファイル選択 -->
            <div class="mb-4">
              <label for="csv_file" class="block text-sm font-medium text-gray-700 mb-2">
                CSVファイル <span class="text-red-500">*</span>
              </label>
              <input 
                type="file" 
                id="csv_file"
                ref="csvFileInput"
                accept=".csv,.txt"
                required
                class="block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-full file:border-0 file:text-sm file:font-semibold file:bg-blue-50 file:text-blue-700 hover:file:bg-blue-100"
              >
            </div>

            <!-- インポートモード -->
            <div class="mb-4">
              <label class="block text-sm font-medium text-gray-700 mb-2">インポートモード</label>
              <div class="space-y-2">
                <label class="flex items-center">
                  <input v-model="importMode" type="radio" value="replace" class="mr-2">
                  <span class="text-sm">置換（既存のスケジュールを削除して追加）</span>
                </label>
                <label class="flex items-center">
                  <input v-model="importMode" type="radio" value="append" class="mr-2">
                  <span class="text-sm">追加（既存のスケジュールに追加）</span>
                </label>
              </div>
            </div>

            <!-- CSVフォーマット説明 -->
            <div class="mb-4 p-3 bg-gray-50 rounded-lg">
              <h4 class="text-sm font-medium text-gray-700 mb-2">CSVフォーマット</h4>
              <div class="text-xs text-gray-600 space-y-1">
                <p>1列目: 開始時刻 (HH:MM形式)</p>
                <p>2列目: 内容</p>
                <p>3列目: 備考（省略可）</p>
                <p>4列目: カウントアップ (0または1)</p>
                <p>5列目: 自動送り (0または1)</p>
              </div>
            </div>

            <!-- ボタン -->
            <div class="flex justify-end space-x-3">
              <button 
                type="button"
                @click="closeImportModal"
                class="px-4 py-2 text-sm font-medium text-gray-700 bg-gray-200 rounded-lg hover:bg-gray-300"
              >
                キャンセル
              </button>
              <button 
                type="submit"
                :disabled="importing"
                class="px-4 py-2 text-sm font-medium text-white bg-purple-600 rounded-lg hover:bg-purple-700 disabled:opacity-50"
              >
                {{ importing ? '取込中...' : 'インポート' }}
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import { ref, onMounted } from 'vue';

export default {
  name: 'ScheduleManager',
  props: {
    competitionDayId: {
      type: Number,
      required: true
    },
    initialSchedules: {
      type: Array,
      default: () => []
    },
    csrfToken: {
      type: String,
      required: true
    }
  },
  setup(props) {
    const schedules = ref([]);
    const newSchedules = ref([]);
    const successMessage = ref('');
    const errorMessage = ref('');
    const originalSchedules = ref({});
    
    // CSV機能用の状態
    const showImportModal = ref(false);
    const importing = ref(false);
    const importMode = ref('replace');
    const csvFileInput = ref(null);

    // 初期データ設定
    onMounted(() => {
      schedules.value = props.initialSchedules.map(schedule => ({
        ...schedule,
        editing: false,
        count_up: Boolean(schedule.count_up),
        auto_advance: Boolean(schedule.auto_advance)
      }));
    });

    // 時刻フォーマット
    const formatTime = (time) => {
      if (typeof time === 'string' && time.includes(':')) {
        return time.slice(0, 5); // HH:MM形式
      }
      return time;
    };

    // エフェクト文字列生成
    const getEffectsString = (schedule) => {
      const effects = [];
      if (schedule.count_up) effects.push('カウントアップ');
      if (schedule.auto_advance) effects.push('自動送り');
      return effects.length > 0 ? effects.join(', ') : '-';
    };

    // 新規スケジュール追加
    const addNewSchedule = () => {
      newSchedules.value.push({
        start_time: '',
        content: '',
        notes: '',
        count_up: false,
        auto_advance: false
      });
    };

    // 新規スケジュール削除
    const removeNewSchedule = (index) => {
      newSchedules.value.splice(index, 1);
    };

    // 編集開始
    const editSchedule = (index) => {
      originalSchedules.value[index] = { ...schedules.value[index] };
      schedules.value[index].editing = true;
    };

    // 編集キャンセル
    const cancelEdit = (index) => {
      schedules.value[index] = { ...originalSchedules.value[index] };
      delete originalSchedules.value[index];
    };

    // メッセージクリア
    const clearMessages = () => {
      successMessage.value = '';
      errorMessage.value = '';
    };

    // 新規スケジュール保存
    const saveNewSchedule = async (index) => {
      const newSchedule = newSchedules.value[index];
      
      if (!newSchedule.start_time || !newSchedule.content) {
        errorMessage.value = '開始時刻と内容は必須です。';
        setTimeout(clearMessages, 3000);
        return;
      }

      try {
        const response = await fetch(`/sjt-cp-admin/competition-days/${props.competitionDayId}/competition-schedules`, {
          method: 'POST',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': props.csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            start_time: newSchedule.start_time,
            content: newSchedule.content,
            notes: newSchedule.notes,
            count_up: newSchedule.count_up ? 1 : 0,
            auto_advance: newSchedule.auto_advance ? 1 : 0
          })
        });

        const data = await response.json();

        if (response.ok) {
          schedules.value.push({
            ...data.schedule,
            editing: false,
            count_up: Boolean(data.schedule.count_up),
            auto_advance: Boolean(data.schedule.auto_advance)
          });
          newSchedules.value.splice(index, 1);
          successMessage.value = 'スケジュールを追加しました。';
          setTimeout(clearMessages, 3000);
        } else {
          errorMessage.value = data.message || 'エラーが発生しました。';
          setTimeout(clearMessages, 3000);
        }
      } catch (error) {
        errorMessage.value = 'ネットワークエラーが発生しました。';
        setTimeout(clearMessages, 3000);
      }
    };

    // 既存スケジュール保存
    const saveSchedule = async (index) => {
      const schedule = schedules.value[index];
      
      if (!schedule.start_time || !schedule.content) {
        errorMessage.value = '開始時刻と内容は必須です。';
        setTimeout(clearMessages, 3000);
        return;
      }

      try {
        const response = await fetch(`/sjt-cp-admin/competition-days/${props.competitionDayId}/competition-schedules/${schedule.id}`, {
          method: 'PUT',
          headers: {
            'Content-Type': 'application/json',
            'X-CSRF-TOKEN': props.csrfToken,
            'Accept': 'application/json'
          },
          body: JSON.stringify({
            start_time: schedule.start_time,
            content: schedule.content,
            notes: schedule.notes,
            count_up: schedule.count_up ? 1 : 0,
            auto_advance: schedule.auto_advance ? 1 : 0
          })
        });

        const data = await response.json();

        if (response.ok) {
          schedule.editing = false;
          delete originalSchedules.value[index];
          successMessage.value = 'スケジュールを更新しました。';
          setTimeout(clearMessages, 3000);
        } else {
          errorMessage.value = data.message || 'エラーが発生しました。';
          setTimeout(clearMessages, 3000);
        }
      } catch (error) {
        errorMessage.value = 'ネットワークエラーが発生しました。';
        setTimeout(clearMessages, 3000);
      }
    };

    // スケジュール削除
    const deleteSchedule = async (scheduleId) => {
      if (!confirm('このスケジュールを削除しますか？')) {
        return;
      }

      try {
        const response = await fetch(`/sjt-cp-admin/competition-days/${props.competitionDayId}/competition-schedules/${scheduleId}`, {
          method: 'DELETE',
          headers: {
            'X-CSRF-TOKEN': props.csrfToken,
            'Accept': 'application/json'
          }
        });

        if (response.ok) {
          const index = schedules.value.findIndex(s => s.id === scheduleId);
          if (index !== -1) {
            schedules.value.splice(index, 1);
          }
          successMessage.value = 'スケジュールを削除しました。';
          setTimeout(clearMessages, 3000);
        } else {
          errorMessage.value = '削除に失敗しました。';
          setTimeout(clearMessages, 3000);
        }
      } catch (error) {
        errorMessage.value = 'ネットワークエラーが発生しました。';
        setTimeout(clearMessages, 3000);
      }
    };

    // CSVエクスポート
    const exportCSV = () => {
      window.location.href = `/sjt-cp-admin/competition-days/${props.competitionDayId}/schedules/export`;
    };

    // CSVインポートモーダルを閉じる
    const closeImportModal = () => {
      showImportModal.value = false;
      importMode.value = 'replace';
      if (csvFileInput.value) {
        csvFileInput.value.value = '';
      }
    };

    // CSVインポート
    const importCSV = async () => {
      const fileInput = csvFileInput.value;
      if (!fileInput.files || !fileInput.files[0]) {
        errorMessage.value = 'ファイルを選択してください。';
        setTimeout(clearMessages, 3000);
        return;
      }

      importing.value = true;
      clearMessages();

      try {
        const formData = new FormData();
        formData.append('csv_file', fileInput.files[0]);
        formData.append('import_mode', importMode.value);

        const response = await fetch(`/sjt-cp-admin/competition-days/${props.competitionDayId}/schedules/import`, {
          method: 'POST',
          headers: {
            'X-CSRF-TOKEN': props.csrfToken,
            'Accept': 'application/json'
          },
          body: formData
        });

        const data = await response.json();

        if (response.ok && data.success) {
          successMessage.value = data.message;
          setTimeout(clearMessages, 5000);
          
          // 成功時はページをリロードしてデータを更新
          window.location.reload();
        } else {
          errorMessage.value = data.message || 'インポートに失敗しました。';
          setTimeout(clearMessages, 5000);
        }
      } catch (error) {
        errorMessage.value = 'ネットワークエラーが発生しました。';
        setTimeout(clearMessages, 3000);
      } finally {
        importing.value = false;
        closeImportModal();
      }
    };

    return {
      schedules,
      newSchedules,
      successMessage,
      errorMessage,
      showImportModal,
      importing,
      importMode,
      csvFileInput,
      formatTime,
      getEffectsString,
      addNewSchedule,
      removeNewSchedule,
      editSchedule,
      cancelEdit,
      saveNewSchedule,
      saveSchedule,
      deleteSchedule,
      exportCSV,
      closeImportModal,
      importCSV
    };
  }
};
</script>

<style scoped>
/* 必要に応じて追加のスタイリング */
</style>