<section>
    <header class="mb-6">
        <h2 class="text-lg font-bold text-slate-100 mb-1">{{ __('ui.settings_change_email_title') }}</h2>
        <p class="text-sm text-slate-300">{{ __('ui.settings_change_email_desc') }}</p>
    </header>

    <form method="post" action="{{ route('profile.update') }}" class="mt-2 space-y-6">
        @csrf
        @method('patch')

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autofocus autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

        </div>

        <div>
            <x-input-label for="current_password" :value="__('ui.settings_current_password')" />
            <x-text-input id="current_password" name="current_password" type="password" class="mt-1 block w-full" required autocomplete="current-password" />
            <x-input-error class="mt-2" :messages="$errors->get('current_password')" />
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('ui.settings_save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-slate-300"
                >{{ __('ui.settings_saved') }}</p>
            @endif
        </div>
    </form>
</section>
