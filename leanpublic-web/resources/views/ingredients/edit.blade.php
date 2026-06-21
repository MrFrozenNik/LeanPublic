<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Редактировать ингредиент</h2>
    </x-slot>

    <div class="py-8 max-w-2xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('ingredients.update', $ingredient) }}" method="POST" class="bg-white shadow-sm rounded-lg p-6 space-y-4">
            @csrf
            @method('PUT')

            <div>
                <label class="block text-sm text-gray-600 mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name', $ingredient->name) }}" required class="w-full border-gray-300 rounded-md">
                @error('name') <span class="text-xs text-red-500">{{ $message }}</span> @enderror
            </div>

            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                @php
                    $macros = [
                        'kcal_100' => 'Ккал/100г',
                        'protein_100' => 'Белки',
                        'fat_100' => 'Жиры',
                        'carb_100' => 'Углеводы'
                    ];
                @endphp

                @foreach($macros as $field => $label)
                    <div>
                        <label class="block text-sm text-gray-600 mb-1">{{ $label }}</label>
                        <input type="number" step="0.01" name="{{ $field }}" value="{{ old($field, $ingredient->$field) }}" required class="w-full border-gray-300 rounded-md">
                        @error($field) <span class="text-xs text-red-500">{{ $message }}</span> @enderror
                    </div>
                @endforeach
            </div>

            <div class="pt-4 flex items-center gap-3">
                <button class="px-5 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-md transition">
                    Сохранить
                </button>
                <a href="{{ route('ingredients.index') }}" class="text-gray-500 hover:text-gray-800">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
