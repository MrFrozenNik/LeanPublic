<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">Новое блюдо</h2>
    </x-slot>

    <div class="py-8 max-w-xl mx-auto sm:px-6 lg:px-8">
        <form action="{{ route('dishes.store') }}" method="POST"
              class="bg-white shadow rounded-lg p-6">
            @csrf

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                <input type="text" name="name" value="{{ old('name') }}" required
                       class="w-full border-gray-300 rounded-md shadow-sm">
                @error('name')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-700 mb-1">Порций</label>
                <input type="number" name="servings" value="{{ old('servings', 1) }}" required
                       class="w-full border-gray-300 rounded-md shadow-sm">
                @error('servings')
                <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div class="flex gap-2">
                <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                    Создать и добавить ингредиенты
                </button>
                <a href="{{ route('dishes.index') }}" class="px-4 py-2 text-gray-600 hover:underline">
                    Отмена
                </a>
            </div>
        </form>
    </div>
</x-app-layout>
