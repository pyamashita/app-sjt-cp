import './bootstrap';
import { createApp } from 'vue';
import CompetitionForm from './components/CompetitionForm.vue';
import CommitteeMemberInput from './components/CommitteeMemberInput.vue';
import ScheduleManager from './components/ScheduleManager.vue';

const app = createApp({});

app.component('competition-form', CompetitionForm);
app.component('committee-member-input', CommitteeMemberInput);
app.component('schedule-manager', ScheduleManager);

app.mount('#app');
