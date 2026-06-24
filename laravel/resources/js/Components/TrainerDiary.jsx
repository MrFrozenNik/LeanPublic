import React from 'react';

const { useState, useEffect, useRef } = React;

const VERDICTS = [
    {
        value: 'up',
        title: 'Хорошо',
        icon: (
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M7 10v12" />
                <path d="M15 5.88 14 10h5.83a2 2 0 0 1 1.92 2.56l-2.33 8A2 2 0 0 1 17.5 22H4a2 2 0 0 1-2-2v-8a2 2 0 0 1 2-2h2.76a2 2 0 0 0 1.79-1.11L12 2h0a3.13 3.13 0 0 1 3 3.88Z" />
            </svg>
        ),
    },
    {
        value: 'mid',
        title: 'Нормально',
        icon: (
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <circle cx="12" cy="12" r="10" />
                <line x1="8" y1="15" x2="16" y2="15" />
                <line x1="9" y1="9" x2="9.01" y2="9" />
                <line x1="15" y1="9" x2="15.01" y2="9" />
            </svg>
        ),
    },
    {
        value: 'down',
        title: 'Плохо',
        icon: (
            <svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" strokeWidth="2" strokeLinecap="round" strokeLinejoin="round">
                <path d="M17 14V2" />
                <path d="M9 18.12 10 14H4.17a2 2 0 0 1-1.92-2.56l2.33-8A2 2 0 0 1 6.5 2H20a2 2 0 0 1 2 2v8a2 2 0 0 1-2 2h-2.76a2 2 0 0 0-1.79 1.11L12 22h0a3.13 3.13 0 0 1-3-3.88Z" />
            </svg>
        ),
    },
];

const VERDICT_STYLES = {
    up: 'bg-green-50 text-green-700 ring-1 ring-green-300',
    mid: 'bg-gray-100 text-gray-700 ring-1 ring-gray-300',
    down: 'bg-red-50 text-red-700 ring-1 ring-red-300',
};

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
        <span className="inline-flex gap-2">
            {VERDICTS.map(v => (
                <button
                    key={v.value}
                    onClick={() => setRating(v.value)}
                    disabled={saving}
                    title={v.title}
                    className={`w-7 h-7 flex items-center justify-center rounded transition ${
                        verdict === v.value
                            ? VERDICT_STYLES[v.value]
                            : 'text-gray-400 hover:bg-gray-100 hover:text-gray-600'
                    }`}
                >
                    {v.icon}
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
