import React from 'react';

const { useState, useEffect, useRef } = React;

export default function TrainerDiary({ clientId }) {
    const [newEntries, setNewEntries] = useState([]);
    const [deletedIds, setDeletedIds] = useState(new Set());
    const wsRef = useRef(null);

    useEffect(() => {
        let reconnectTimer;

        function connect() {
            const wsUrl = document.getElementById(`diary-${clientId}`).dataset.wsUrl;
            const ws = new WebSocket(`${wsUrl}/ws/clients/${clientId}/diary`);
            wsRef.current = ws;

            ws.onmessage = (e) => {
                const data = JSON.parse(e.data);
                if (data.event === 'entry.created') {
                    setNewEntries(prev => [...prev, data.entry]);
                }
                if (data.event === 'entry.deleted') {
                    setDeletedIds(prev => new Set([...prev, data.entry_id]));
                    setNewEntries(prev => prev.filter(e => e.id !== data.entry_id));
                }
            };

            ws.onclose = () => {
                reconnectTimer = setTimeout(connect, 3000);
            };
        }

        connect();

        return () => {
            clearTimeout(reconnectTimer);
            wsRef.current?.close();
        };
    }, [clientId]);

    if (newEntries.length === 0) return null;

    return newEntries
        .filter(e => !deletedIds.has(e.id))
        .map(entry => (
            <tr key={entry.id}>
                <td className="px-3 py-2">{entry.eaten_at.slice(11, 16)}</td>
                <td className="px-3 py-2">{entry.dish_name || entry.ingredient_name || '—'}</td>
                <td className="px-3 py-2">{entry.grams} г</td>
                <td className="px-3 py-2">{Math.round(entry.totals.kcal)}</td>
            </tr>
        ));
}
