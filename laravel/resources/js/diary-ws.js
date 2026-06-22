import React from 'react';
import { createRoot } from 'react-dom/client';
import TrainerDiary from './Components/TrainerDiary';

document.querySelectorAll('[id^="diary-"]').forEach(el => {
    const clientId = parseInt(el.id.replace('diary-', ''));
    const apiUrl = el.dataset.apiUrl;
    const date = el.dataset.date;
    const trainerId = parseInt(el.dataset.trainerId);
    const root = createRoot(el);
    root.render(React.createElement(TrainerDiary, { clientId, date, apiUrl, trainerId }));
});
