<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">{{ $dish->name }}</h2>
    </x-slot>

    <div class="py-8 max-w-3xl mx-auto sm:px-6 lg:px-8 space-y-6">

        <a href="{{ route('dishes.index') }}" class="text-gray-600 hover:underline">
            ← Назад к списку блюд
        </a>

        @if (session('success'))
            <div class="p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif
            @php($totals = $dish->totals)
        <div class="bg-white shadow rounded-lg p-6">
            <h3 class="text-lg font-semibold mb-4">Параметры блюда</h3>
            <form action="{{ route('dishes.update', $dish) }}" method="POST"
                  class="grid grid-cols-1 sm:grid-cols-3 gap-4">
                @csrf
                @method('PUT')

                <div class="sm:col-span-2">
                    <label class="block text-sm font-medium text-gray-700 mb-1">Название</label>
                    <input type="text" name="name" value="{{ old('name', $dish->name) }}" required
                           class="w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">Порций</label>
                    <input type="number" name="servings" value="{{ old('servings', $dish->servings) }}" required
                           class="w-full border-gray-300 rounded-md shadow-sm">
                </div>

                <div class="sm:col-span-3">
                    <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                        Сохранить
                    </button>
                </div>
            </form>
            <h3 class="text-lg font-semibold mb-2 py-4">КБЖУ всего блюда</h3>
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
            <p class="text-sm text-gray-500 mt-2 text-center">
                На порцию (из {{ $dish->servings }}):
                {{ round($totals['kcal'] / $dish->servings) }} ккал
            </p>
            <h3 class="text-lg font-semibold py-4 pb-0">Состав блюда</h3>
            <table class="w-full text-sm text-left mt-4">
                <thead class="bg-gray-50 text-gray-600">
                <tr>
                    @foreach(['Ингредиент', 'Граммы', 'Ккал', 'Белки', 'Жиры', 'Углеводы', ''] as $title)
                        <th class="px-4 py-3"> {{$title}}</th>
                    @endforeach
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse ($dish->ingredients as $ingredient)
                    <tr>
                        <td class="px-4 py-3">{{ $ingredient->name }}</td>
                        <td class="px-4 py-3">{{ $ingredient->pivot->grams }} г</td>
                        @foreach(['kcal_100' => 0, 'protein_100' => 1, 'fat_100' => 1, 'carb_100' => 1] as $field => $precision)
                            <td class="px-4 py-3 {{ $precision === 0 ? '' : 'text-gray-600' }}">
                                {{ round($ingredient->$field * $ingredient->pivot->grams / 100, $precision) }}
                            </td>
                        @endforeach
                        <td class="px-4 py-3 text-right">
                            <form action="{{ route('dishes.ingredients.destroy', [$dish, $ingredient]) }}"
                                  method="POST" onsubmit="return confirm('Убрать ингредиент из блюда?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Убрать</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            Ингредиенты пока не добавлены
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>

            <div class="py-6 border-t bg-gray-50">
                @if ($availableIngredients->isEmpty())
                    <p class="text-sm text-gray-500">
                        Все ваши ингредиенты уже добавлены в это блюдо.
                        <a href="{{ route('ingredients.index') }}" class="text-indigo-600 hover:underline">
                            Добавить новый ингредиент
                        </a>
                    </p>
                @else
                    <form action="{{ route('dishes.ingredients.store', $dish) }}" method="POST"
                          class="flex flex-wrap gap-3 items-end">
                        @csrf

                        <div class="flex-1 min-w-[200px]">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Ингредиент</label>
                            <select name="ingredient_id" required
                                    class="w-full border-gray-300 rounded-md shadow-sm">
                                @foreach ($availableIngredients as $ingredient)
                                    <option value="{{ $ingredient->id }}">{{ $ingredient->name }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="w-32">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Граммы</label>
                            <input type="number" step="0.01" name="grams" required
                                   class="w-full border-gray-300 rounded-md shadow-sm">
                        </div>

                        <button class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                            Добавить
                        </button>
                    </form>
                @endif
                @error('ingredient_id')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
                @error('grams')
                <p class="text-sm text-red-600 mt-2">{{ $message }}</p>
                @enderror
            </div>
        </div>
    </div>
</x-app-layout>
