<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Дневник питания</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">{{ session('success') }}</div>
        @endif

        <form method="GET" action="{{ route('diary.index') }}" class="flex items-center gap-3">
            <input type="date" name="date" value="{{ $date->toDateString() }}"
                   onchange="this.form.submit()"
                   class="border-gray-300 rounded-md shadow-sm">
            <span class="text-gray-500">{{ $date->isoFormat('D MMMM YYYY') }}</span>
        </form>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-2">Итого за день</h3>
            <div class="grid grid-cols-4 gap-4 text-center">
                <div>
                    <div class="text-2xl font-bold">{{ round($totals['kcal']) }}</div>
                    <div class="text-sm text-gray-500">ккал</div>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ round($totals['protein'], 1) }}</div>
                    <div class="text-sm text-gray-500">белки</div>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ round($totals['fat'], 1) }}</div>
                    <div class="text-sm text-gray-500">жиры</div>
                </div>
                <div>
                    <div class="text-2xl font-bold">{{ round($totals['carb'], 1) }}</div>
                    <div class="text-sm text-gray-500">углеводы</div>
                </div>
            </div>
        </div>

            <div class="bg-white shadow rounded-lg overflow-hidden">
                <table class="w-full text-sm text-left">
                    <thead class="bg-gray-50 text-gray-600">
                    <tr>
                        <th class="px-4 py-3">Время</th>
                        <th class="px-4 py-3">Что</th>
                        <th class="px-4 py-3">Граммы</th>
                        <th class="px-4 py-3">Ккал</th>
                        <th class="px-4 py-3">Б</th>
                        <th class="px-4 py-3">Ж</th>
                        <th class="px-4 py-3">У</th>
                        <th class="px-4 py-3"></th>
                    </tr>
                    </thead>
                    <tbody class="divide-y">
                    @forelse ($entries as $entry)
                        @php($entryTotals = $entry->totals)
                        <tr>
                            <td class="px-4 py-3">{{ $entry->eaten_at->format('H:i') }}</td>
                            <td class="px-4 py-3">
                                {{ $entry->dish->name ?? $entry->ingredient->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">{{ $entry->grams }} г</td>
                            <td class="px-4 py-3">{{ round($entryTotals['kcal']) }}</td>
                            <td class="px-4 py-3">{{ round($entryTotals['protein'], 1) }}</td>
                            <td class="px-4 py-3">{{ round($entryTotals['fat'], 1) }}</td>
                            <td class="px-4 py-3">{{ round($entryTotals['carb'], 1) }}</td>
                            <td class="px-4 py-3 text-right">
                                <form action="{{ route('diary.destroy', $entry) }}" method="POST"
                                      onsubmit="return confirm('Удалить запись?')">
                                    @csrf
                                    @method('DELETE')
                                    <button class="text-red-600 hover:underline">Удалить</button>
                                </form>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-6 text-center text-gray-500">
                                Записей за этот день нет
                            </td>
                        </tr>
                    @endforelse
                    </tbody>
                </table>
            </div>

        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Добавить запись</h3>
            <form action="{{ route('diary.store') }}" method="POST" class="grid grid-cols-1 sm:grid-cols-4 gap-4">
                @csrf

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Блюдо / ингредиент</label>
                    <select name="item" required class="w-full border-gray-300 rounded-md shadow-sm">
                        @if ($dishes->isNotEmpty())
                            <optgroup label="Блюда">
                                @foreach ($dishes as $dish)
                                    <option value="dish:{{ $dish->id }}">{{ $dish->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif

                        @if ($ingredients->isNotEmpty())
                            <optgroup label="Ингредиенты">
                                @foreach ($ingredients as $ingredient)
                                    <option value="ingredient:{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                                @endforeach
                            </optgroup>
                        @endif
                    </select>
                    @error('item')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Граммы</label>
                    <input type="number" step="0.01" name="grams" required
                           class="w-full border-gray-300 rounded-md shadow-sm">
                    @error('grams')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Время</label>
                    <input type="datetime-local" name="eaten_at"
                           value="{{ $date->format('Y-m-d') }}T{{ now()->format('H:i') }}" required
                           class="w-full border-gray-300 rounded-md shadow-sm">
                    @error('eaten_at')
                    <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div class="sm:col-span-4">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Добавить
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
