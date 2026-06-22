import React from 'react';

const { useState, useEffect, useRef } = React;

const VERDICTS = [
    { value: 'up', label: '1' },
    { value: 'mid', label: '2' },
    { value: 'down', label: '3' },
];

function RatingButtons({ dishId, trainerId, apiUrl, currentVerdict, onChanged }) {
    const [verdict, setVerdict] = React.useState(currentVerdict);
    const [saving, setSaving] = React.useState(false);

    React.useEffect(() => {
        setVerdict(currentVerdict);
    }, [currentVerdict]);

    async function setRating(value) {
        if (saving || value === verdict) return;
        setSaving(true);
        try {
            const method = verdict ? 'PUT' : 'POST';
            const url = method === 'PUT'
                ? `${apiUrl}/api/dishes/${dishId}/rating?trainer_id=${trainerId}`
                : `${apiUrl}/api/dishes/${dishId}/rating`;

            const res = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(
                    method === 'POST'
                        ? { trainer_id: trainerId, verdict: value }
                        : { verdict: value }
                ),
            });

            if (res.ok) {
                setVerdict(value);
                onChanged?.(dishId, value);
            } else if (res.status === 409) {
                const retryRes = await fetch(
                    `${apiUrl}/api/dishes/${dishId}/rating?trainer_id=${trainerId}`,
                    {
                        method: 'PUT',
                        headers: { 'Content-Type': 'application/json' },
                        body: JSON.stringify({ verdict: value }),
                    }
                );
                if (retryRes.ok) {
                    setVerdict(value);
                    onChanged?.(dishId, value);
                }
            }
        } finally {
            setSaving(false);
        }
    }

    return (
        <span className="inline-flex gap-1">
            {VERDICTS.map(v => (
                <button
                    key={v.value}
                    onClick={() => setRating(v.value)}
                    disabled={saving}
                    title={v.value}
                    className={`px-1.5 py-0.5 rounded text-sm ${
                        verdict === v.value ? 'bg-indigo-100 ring-1 ring-indigo-400' : 'hover:bg-gray-100'
                    }`}
                >
                    {v.label}
                </button>
            ))}
        </span>
    );
}

export default function TrainerDiary({ clientId, date, apiUrl, trainerId }) {
    const [entries, setEntries] = useState([]);
    const [deletedIds, setDeletedIds] = useState(new Set());
    const [ratings, setRatings] = useState({});
    const wsRef = useRef(null);

    useEffect(() => {
        fetch(`${apiUrl}/api/clients/${clientId}/diary?date=${date}`)
            .then(r => r.json())
            .then(data => {
                setEntries(data.entries);

                const dishIds = [...new Set(
                    data.entries.filter(e => e.dish_id).map(e => e.dish_id)
                )];
                dishIds.forEach(dishId => {
                    fetch(`${apiUrl}/api/dishes/${dishId}?trainer_id=${trainerId}`)
                        .then(r => r.json())
                        .then(d => {
                            if (d.rating) {
                                setRatings(prev => ({ ...prev, [dishId]: d.rating.verdict }));
                            }
                        });
                });
            });
    }, [clientId, date, apiUrl, trainerId]);

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
            <tr><td colSpan="5" className="px-3 py-4 text-center text-gray-500">
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
            <td className="px-3 py-2">
                {entry.dish_id && (
                    <RatingButtons
                        dishId={entry.dish_id}
                        trainerId={trainerId}
                        apiUrl={apiUrl}
                        currentVerdict={ratings[entry.dish_id]}
                        onChanged={(dishId, value) => setRatings(prev => ({ ...prev, [dishId]: value }))}
                    />
                )}
            </td>
        </tr>
    ));
}
