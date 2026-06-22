<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Дашборд тренера</h2>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <form method="GET" action="{{ route('trainer.dashboard') }}" class="flex items-center gap-3">
            <input type="date" name="date" value="{{ $date->toDateString() }}"
                   onchange="this.form.submit()"
                   class="border-gray-300 rounded-md shadow-sm">
            <span class="text-gray-500">{{ $date->isoFormat('D MMMM YYYY') }}</span>
        </form>

        @forelse ($clients as $client)
            <div class="bg-white shadow rounded-lg p-6">
                <div class="flex justify-between items-center mb-4">
                    <h3 class="text-lg font-semibold">{{ $client->name }}</h3>
                    <span class="text-sm text-gray-500">{{ $client->diary_entries_count }} записей всего</span>
                </div>

                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-3 py-2">Время</th>
                        <th class="px-3 py-2">Что</th>
                        <th class="px-3 py-2">Граммы</th>
                        <th class="px-3 py-2">Ккал</th>
                    </tr>
                    </thead>
                    <tbody class="divide-y" id="diary-{{ $client->id }}" data-ws-url="{{ $fastapiWsUrl }}">
                    @forelse ($diaries[$client->id] as $entry)
                        <tr>
                            <td class="px-3 py-2">{{ $entry->eaten_at->format('H:i') }}</td>
                            <td class="px-3 py-2">
                                {{ $entry->dish->name ?? $entry->ingredient->name ?? '—' }}
                            </td>
                            <td class="px-3 py-2">{{ $entry->grams }} г</td>
                            <td class="px-3 py-2">{{ round($entry->totals['kcal']) }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="px-3 py-4 text-center text-gray-500">
                                Сегодня записей пока нет
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>
        @empty
            <div class="bg-white shadow rounded-lg p-6 text-center text-gray-500">
                У вас пока нет клиентов
            </div>
        @endforelse

    </div>
</x-app-layout>
