import './bootstrap';
import { createApp } from 'vue';
import CompetitionForm from './components/CompetitionForm.vue';
import CommitteeMemberInput from './components/CommitteeMemberInput.vue';

const app = createApp({});

app.component('competition-form', CompetitionForm);
app.component('committee-member-input', CommitteeMemberInput);

app.mount('#app');
