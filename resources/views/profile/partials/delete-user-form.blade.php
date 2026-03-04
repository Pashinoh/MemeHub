<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium text-slate-100">
            {{ __('ui.settings_delete_account_title') }}
        </h2>

        <p class="mt-1 text-sm text-slate-300">
            {{ __('ui.settings_delete_account_desc') }}
        </p>
    </header>

    <x-danger-button
        x-data=""
        x-on:click.prevent="$dispatch('open-modal', 'confirm-user-deletion')"
    >{{ __('ui.settings_delete_account_title') }}</x-danger-button>

    <x-modal name="confirm-user-deletion" :show="$errors->userDeletion->isNotEmpty()" focusable>
        <form method="post" action="{{ route('profile.destroy') }}" class="p-6">
            @csrf
            @method('delete')

            <h2 class="text-lg font-medium text-slate-100">
                {{ __('ui.settings_delete_account_confirm_title') }}
            </h2>

            @if (! blank($user->google_id))
                <p class="mt-1 text-sm text-slate-300">
                    Akun Google Anda akan dihapus permanen. Tindakan ini tidak dapat dibatalkan.
                </p>
            @else
                <p class="mt-1 text-sm text-slate-300">
                    {{ __('ui.settings_delete_account_confirm_desc') }}
                </p>
            @endif

            @if (blank($user->google_id))
                <div class="mt-6">
                    <x-input-label for="password" value="{{ __('ui.settings_password') }}" class="sr-only" />

                    <x-text-input
                        id="password"
                        name="password"
                        type="password"
                        class="mt-1 block w-3/4"
                        placeholder="{{ __('ui.settings_password') }}"
                    />

                    <x-input-error :messages="$errors->userDeletion->get('password')" class="mt-2" />
                </div>
            @endif

            <div class="mt-6 flex justify-end">
                <x-secondary-button x-on:click="$dispatch('close')">
                    {{ __('ui.settings_cancel') }}
                </x-secondary-button>

                <x-danger-button class="ms-3">
                    {{ __('ui.settings_delete_account_title') }}
                </x-danger-button>
            </div>
        </form>
    </x-modal>
</section>
