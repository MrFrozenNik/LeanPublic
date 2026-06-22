<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center">
            <h2 class="font-semibold text-xl text-gray-800 leading-tight">Блюда</h2>
            <a href="{{ route('dishes.create') }}"
               class="px-4 py-2 bg-indigo-600 text-white rounded-md hover:bg-indigo-700">
                Новое блюдо
            </a>
        </div>
    </x-slot>

    <div class="py-8 max-w-4xl mx-auto sm:px-6 lg:px-8">

        @if (session('success'))
            <div class="mb-4 p-3 bg-green-100 text-green-800 rounded">
                {{ session('success') }}
            </div>
        @endif

        <div class="bg-white shadow rounded-lg overflow-hidden">
            <table class="w-full text-sm text-left">
                <thead class="bg-gray-50 text-gray-600">
                <tr>
                    <th class="px-4 py-3">Название</th>
                    <th class="px-4 py-3">Порции</th>
                    <th class="px-4 py-3">Ингредиентов</th>
                    <th class="px-4 py-3">ККАЛ</th>
                    <th class="px-4 py-3">Протеина</th>
                    <th class="px-4 py-3">Жиров</th>
                    <th class="px-4 py-3">Углеводов</th>
                    <th class="px-4 py-3">Действия</th>
                </tr>
                </thead>
                <tbody class="divide-y">
                @forelse ($dishes as $dish)
                    <tr>
                        <td class="px-4 py-3">{{ $dish->name }}</td>
                        <td class="px-4 py-3">{{ $dish->servings }}</td>
                        <td class="px-4 py-3">{{ $dish->ingredients_count }}</td>
                        @foreach ($dish->getTotalsAttribute() as $attr)
                            <td class="px-4 py-3">{{ $attr }}</td>
                        @endforeach
                        <td class="px-4 py-3 text-right whitespace-nowrap">
                            <a href="{{ route('dishes.edit', $dish) }}"
                               class="text-indigo-600 hover:underline mr-3">
                                Открыть
                            </a>
                            <form action="{{ route('dishes.destroy', $dish) }}" method="POST"
                                  class="inline" onsubmit="return confirm('Удалить блюдо?')">
                                @csrf
                                @method('DELETE')
                                <button class="text-red-600 hover:underline">Удалить</button>
                            </form>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="4" class="px-4 py-6 text-center text-gray-500">
                            Пока нет блюд
                        </td>
                    </tr>
                @endforelse
                </tbody>
            </table>
        </div>

        <div class="mt-4">
            {{ $dishes->links() }}
        </div>
    </div>
</x-app-layout>
