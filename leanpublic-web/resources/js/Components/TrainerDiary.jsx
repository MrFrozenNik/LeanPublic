import React from 'react';

const { useState, useEffect, useRef } = React;

export default function TrainerDiary({ clientId, date, apiUrl }) {
    const [entries, setEntries] = useState([]);
    const [deletedIds, setDeletedIds] = useState(new Set());
    const wsRef = useRef(null);

    useEffect(() => {
        fetch(`${apiUrl}/api/clients/${clientId}/diary?date=${date}`)
            .then(r => r.json())
            .then(data => setEntries(data.entries));
    }, [clientId, date, apiUrl]);

    useEffect(() => {
        let reconnectTimer;
        function connect() {
            const ws = new WebSocket(`${apiUrl.replace('http', 'ws')}/ws/clients/${clientId}/diary`);
            wsRef.current = ws;
            ws.onmessage = (e) => {
                const data = JSON.parse(e.data);
                if (data.event === 'entry.created') {
                    setEntries(prev => [...prev, data.entry]);
                }
                if (data.event === 'entry.deleted') {
                    setDeletedIds(prev => new Set([...prev, data.entry_id]));
                }
            };
            ws.onclose = () => { reconnectTimer = setTimeout(connect, 3000); };
        }
        connect();
        return () => { clearTimeout(reconnectTimer); wsRef.current?.close(); };
    }, [clientId, apiUrl]);

    const visible = entries.filter(e => !deletedIds.has(e.id));

    if (visible.length === 0) {
        return (
            <tr><td colSpan="4" className="px-3 py-4 text-center text-gray-500">
                Записей на этот день нет
            </td></tr>
        );
    }

    return visible.map(entry => (
        <tr key={entry.id}>
            <td className="px-3 py-2">{entry.eaten_at.slice(11, 16)}</td>
            <td className="px-3 py-2">{entry.dish_name || entry.ingredient_name || '—'}</td>
            <td className="px-3 py-2">{entry.grams} г</td>
            <td className="px-3 py-2">{Math.round(entry.totals.kcal)}</td>
        </tr>
    ));
}
