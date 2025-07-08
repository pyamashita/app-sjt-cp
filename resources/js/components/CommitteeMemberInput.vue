<template>
  <div class="space-y-4">
    <label class="block text-sm font-semibold text-gray-700 mb-2">
      競技委員
    </label>
    
    <!-- 既存の競技委員リスト -->
    <div v-if="members.length > 0" class="space-y-2">
      <div 
        v-for="(member, index) in members" 
        :key="index"
        class="flex items-center justify-between bg-gray-50 p-3 rounded-lg border border-gray-200"
        draggable="true"
        @dragstart="dragStart(index)"
        @dragover.prevent
        @drop="drop(index)"
        @dragend="dragEnd"
      >
        <div class="flex items-center space-x-3">
          <div class="cursor-move text-gray-400 hover:text-gray-600">
            <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
              <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path>
            </svg>
          </div>
          <span class="text-sm font-medium text-gray-900">{{ member }}</span>
          <span class="text-xs text-gray-500">({{ index + 1 }}番目)</span>
        </div>
        <button 
          type="button"
          @click="removeMember(index)"
          class="text-red-500 hover:text-red-700 transition duration-200"
        >
          <svg class="h-5 w-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path>
          </svg>
        </button>
      </div>
    </div>

    <!-- 新しい競技委員の入力 -->
    <div class="flex space-x-2">
      <input 
        v-model="newMember"
        @keyup.enter="addMember"
        type="text" 
        placeholder="競技委員名を入力してEnterキーで追加"
        class="flex-1 px-3 py-2 border border-gray-300 rounded-lg text-gray-900 placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition duration-200 sm:text-sm"
      >
      <button 
        type="button"
        @click="addMember"
        :disabled="!newMember.trim()"
        class="px-4 py-2 bg-blue-600 text-white font-medium rounded-lg hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2 disabled:opacity-50 disabled:cursor-not-allowed transition duration-200"
      >
        追加
      </button>
    </div>

    <!-- 説明テキスト -->
    <p class="text-xs text-gray-500">
      競技委員名を入力してEnterキーまたは「追加」ボタンで追加できます。ドラッグ&ドロップで順序を変更できます。
    </p>

    <!-- 隠しフィールドで配列データをサーバーに送信 -->
    <input 
      v-for="(member, index) in members" 
      :key="`member-${index}`"
      type="hidden" 
      :name="`committee_members[${index}]`" 
      :value="member"
    >
  </div>
</template>

<script>
import { ref, onMounted, watch } from 'vue';

export default {
  name: 'CommitteeMemberInput',
  props: {
    initialMembers: {
      type: Array,
      default: () => []
    }
  },
  setup(props) {
    const members = ref([]);
    const newMember = ref('');
    const draggedIndex = ref(null);

    // 初期データの設定
    onMounted(() => {
      members.value = [...props.initialMembers];
      console.log('CommitteeMemberInput initial members:', props.initialMembers);
      console.log('CommitteeMemberInput members after init:', members.value);
    });

    // initialMembersプロパティの変更を監視
    watch(() => props.initialMembers, (newMembers) => {
      members.value = [...newMembers];
      console.log('CommitteeMemberInput members updated via watch:', newMembers);
    }, { immediate: true });

    // 新しいメンバーを追加
    const addMember = () => {
      const memberName = newMember.value.trim();
      if (memberName && !members.value.includes(memberName)) {
        members.value.push(memberName);
        newMember.value = '';
      }
    };

    // メンバーを削除
    const removeMember = (index) => {
      members.value.splice(index, 1);
    };

    // ドラッグ開始
    const dragStart = (index) => {
      draggedIndex.value = index;
    };

    // ドロップ処理
    const drop = (dropIndex) => {
      if (draggedIndex.value !== null && draggedIndex.value !== dropIndex) {
        const draggedItem = members.value[draggedIndex.value];
        members.value.splice(draggedIndex.value, 1);
        members.value.splice(dropIndex, 0, draggedItem);
      }
    };

    // ドラッグ終了
    const dragEnd = () => {
      draggedIndex.value = null;
    };

    return {
      members,
      newMember,
      addMember,
      removeMember,
      dragStart,
      drop,
      dragEnd
    };
  }
};
</script>

<style scoped>
.dragging {
  opacity: 0.5;
}
</style>