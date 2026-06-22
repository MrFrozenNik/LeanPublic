<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-gray-900">Доступ тренера</h2>
        <p class="mt-1 text-sm text-gray-600">
            Пригласите тренера — он увидит ваш дневник в реальном времени.
        </p>
    </header>

    @if (session('success'))
        <div class="p-3 bg-green-100 text-green-800 rounded text-sm">{{ session('success') }}</div>
    @endif

    <div>
        @forelse ($user->trainers as $trainer)
            <div class="flex items-center justify-between py-2 border-b">
                <span>{{ $trainer->name }} ({{ $trainer->email }})</span>
                <form action="{{ route('trainers.destroy', $trainer->pivot->id) }}"
                      method="POST" onsubmit="return confirm('Отозвать доступ?')">
                    @csrf
                    @method('DELETE')
                    <button class="text-red-600 hover:underline text-sm">Отозвать</button>
                </form>
            </div>
        @empty
            <p class="text-sm text-gray-500">У вас пока нет тренера</p>
        @endforelse
    </div>

    <form method="POST" action="{{ route('trainers.store') }}" class="flex gap-3 items-end">
        @csrf
        <div class="flex-1">
            <x-input-label for="trainer_email" value="Email тренера" />
            <x-text-input id="trainer_email" name="trainer_email" type="email"
                          class="mt-1 block w-full" required />
            @error('trainer_email')
            <p class="text-sm text-red-600 mt-1">{{ $message }}</p>
            @enderror
        </div>
        <x-primary-button>Пригласить</x-primary-button>
    </form>
</section>
