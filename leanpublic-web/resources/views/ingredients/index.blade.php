<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Ингредиенты</h2>
    </x-slot>

    <div class="py-8 max-w-5xl mx-auto sm:px-6 lg:px-8 space-y-6">

        @if (session('success'))
            <div class="p-4 bg-green-50 border border-green-200 text-green-700 rounded-lg">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow-sm rounded-lg p-6">
            <h3 class="text-lg font-medium text-gray-900 mb-4">Новый ингредиент</h3>

            <form action="{{ route('ingredients.store') }}" method="POST" class="flex flex-col md:flex-row items-start gap-4">
                @csrf

                <div class="w-full">
                    <label class="block text-sm text-gray-600 mb-1">Название</label>
                    <input type="text" name="name" value="{{ old('name') }}" required class="w-full border-gray-300 rounded-md">
                    @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 w-full md:w-auto">
                    @php
                        $macros = [
                            'kcal_100' => 'Ккал',
                            'protein_100' => 'Белки',
                            'fat_100' => 'Жиры',
                            'carb_100' => 'Углеводы'
                        ];
                    @endphp

                    @foreach($macros as $field => $label)
                        <div>
                            <label class="block text-sm text-gray-600 mb-1">{{ $label }}</label>
                            <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field) }}" required class="w-full border-gray-300 rounded-md">
                            @error($field) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                        </div>
                    @endforeach
                </div>

                <div class="mt-6">
                    <button class="px-5 py-2 bg-green-600 hover:bg-green-700 text-white rounded-md transition">
                        Добавить
                    </button>
                </div>
            </form>
        </div>

        <div class="bg-white shadow-sm rounded-lg overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 border-b">
                <tr>
                    <th class="px-4 py-3 font-medium text-gray-500">Название</th>
                    <th class="px-4 py-3 font-medium text-gray-500">Ккал</th>
                    <th class="px-4 py-3 font-medium text-gray-500">Б</th>
                    <th class="px-4 py-3 font-medium text-gray-500">Ж</th>
                    <th class="px-4 py-3 font-medium text-gray-500">У</th>
                    <th class="px-4 py-3"></th>
                </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                @forelse ($ingredients as $ingredient)
                    <tr class="hover:bg-gray-50">
                        <td class="px-4 py-3 font-medium text-gray-900">{{ $ingredient->name }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ingredient->kcal_100 }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ingredient->protein_100 }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ingredient->fat_100 }}</td>
                        <td class="px-4 py-3 text-gray-600">{{ $ingredient->carb_100 }}</td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('ingredients.edit', $ingredient) }}" class="text-blue-600 hover:text-blue-800 mr-3">Изменить</a>
                            <form action="{{ route('ingredients.destroy', $ingredient) }}" method="POST" class="inline" onsubmit="return confirm('Точно удалить?')">
                                @csrf @method('DELETE')
                                <button class="text-red-500 hover:text-red-700">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-4 py-8 text-center text-gray-400">Список ингредиентов пуст</td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div>{{ $ingredients->links() }}</div>
    </div>
</x-app-layout>
