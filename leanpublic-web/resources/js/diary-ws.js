import React from 'react';
import { createRoot } from 'react-dom/client';
import TrainerDiary from './Components/TrainerDiary';

document.querySelectorAll('[id^="diary-"]').forEach(el => {
    const clientId = parseInt(el.id.replace('diary-', ''));
    const root = createRoot(el);
    root.render(React.createElement(TrainerDiary, { clientId }));
});
