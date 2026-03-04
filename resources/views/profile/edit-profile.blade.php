@push('head')
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.css">
@endpush

<x-app-layout>
    <div class="max-w-3xl mx-auto px-4 py-8 sm:py-10">
        <div class="mb-8 flex flex-col gap-4 sm:flex-row sm:items-start sm:justify-between">
            <div>
                <h1 class="text-3xl font-bold mb-2 text-slate-100">{{ __('ui.edit_profile_page_title') }}</h1>
                <p class="text-slate-300">{{ __('ui.edit_profile_page_subtitle') }}</p>
            </div>
            <a href="{{ route('users.show', auth()->user()) }}" class="inline-flex items-center rounded-lg bg-slate-800 px-4 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-700 transition">
                {{ __('ui.edit_profile_back') }}
            </a>
        </div>

        @if (session('status') === 'profile-info-updated')
            <div class="mb-4 rounded border border-green-200 bg-green-50 px-4 py-3 text-green-800">
                {{ __('ui.edit_profile_updated') }}
            </div>
        @endif

        <div class="rounded-2xl border border-slate-700 bg-slate-900 p-6 sm:p-7 shadow-sm">
            <form method="POST" action="{{ route('settings.profile.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                @method('PATCH')
                <input type="hidden" name="cropped_profile_photo" id="cropped_profile_photo">

                <div class="flex flex-col items-center">
                    <img
                        id="current_profile_preview"
                        src="{{ $user->profile_photo_path ? asset('storage/' . $user->profile_photo_path) : 'https://www.gravatar.com/avatar/' . md5(strtolower(trim($user->email))) . '?s=160&d=mp' }}"
                        alt="{{ $user->name }}"
                        class="w-24 h-24 rounded-full border-4 border-slate-600 shadow-sm object-cover bg-slate-800"
                    >
                </div>

                <div>
                    <label for="profile_photo" class="block text-sm font-medium text-slate-200">{{ __('ui.edit_profile_photo') }}</label>
                    <input id="profile_photo" name="profile_photo" type="file" accept="image/jpeg,image/png,image/webp" class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    <p class="mt-1 text-xs text-slate-400">{{ __('ui.edit_profile_photo_format') }}</p>
                    <p id="crop_status" class="mt-1 text-xs text-emerald-600 hidden">{{ __('ui.edit_profile_crop_saved') }}</p>
                    @error('profile_photo')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="name" class="block text-sm font-medium text-slate-200">{{ __('ui.edit_profile_account_name') }}</label>
                    <input id="name" name="name" type="text" value="{{ old('name', $user->name) }}" required class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500">
                    @error('name')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="bio" class="block text-sm font-medium text-slate-200">{{ __('ui.edit_profile_bio') }}</label>
                    <textarea id="bio" name="bio" rows="4" class="mt-1 block w-full rounded-md border-slate-600 bg-slate-800 text-slate-100 text-sm shadow-sm focus:border-slate-500 focus:ring-slate-500" placeholder="{{ __('ui.edit_profile_bio_placeholder') }}">{{ old('bio', $user->bio) }}</textarea>
                    @error('bio')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex items-center rounded-lg bg-slate-700 px-4 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">
                        {{ __('ui.edit_profile_save_changes') }}
                    </button>
                </div>
            </form>
        </div>
    </div>

    <div id="cropper_modal" class="hidden fixed inset-0 z-50">
        <div id="cropper_overlay" class="absolute inset-0 bg-black/60"></div>
        <div class="relative z-10 h-full w-full flex items-center justify-center p-4">
            <div class="w-full max-w-xl rounded-2xl bg-slate-900 shadow-xl border border-slate-700">
                <div class="flex items-center justify-between border-b border-slate-700 px-4 py-3">
                    <h3 class="text-sm font-semibold text-slate-100">{{ __('ui.edit_profile_adjust_photo') }}</h3>
                    <button type="button" id="close_cropper" class="rounded-md px-2 py-1 text-slate-400 hover:bg-slate-800 hover:text-slate-200 transition">✕</button>
                </div>
                <div class="p-4">
                    <div class="w-full h-80 overflow-hidden rounded-lg bg-slate-800">
                        <img id="cropper_image" src="" alt="{{ __('ui.edit_profile_crop_preview') }}" class="max-w-full">
                    </div>
                    <div class="mt-3 flex flex-wrap items-center gap-2">
                        <button type="button" id="zoom_in" class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-slate-200 hover:bg-slate-700 transition">{{ __('ui.edit_profile_zoom_in') }}</button>
                        <button type="button" id="zoom_out" class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-slate-200 hover:bg-slate-700 transition">{{ __('ui.edit_profile_zoom_out') }}</button>
                        <button type="button" id="reset_crop" class="inline-flex items-center rounded-md bg-slate-800 px-3 py-1.5 text-sm font-medium text-slate-200 hover:bg-slate-700 transition">{{ __('ui.edit_profile_reset') }}</button>
                    </div>
                </div>
                <div class="flex justify-end gap-2 border-t border-slate-700 px-4 py-3">
                    <button type="button" id="cancel_crop" class="inline-flex items-center rounded-md bg-slate-800 px-3 py-2 text-sm font-semibold text-slate-200 hover:bg-slate-700 transition">{{ __('ui.settings_cancel') }}</button>
                    <button type="button" id="save_crop" class="inline-flex items-center rounded-md bg-slate-700 px-3 py-2 text-sm font-semibold text-white hover:bg-slate-600 transition">{{ __('ui.edit_profile_save_crop') }}</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/cropperjs@1.6.2/dist/cropper.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const input = document.getElementById('profile_photo');
            const modal = document.getElementById('cropper_modal');
            const overlay = document.getElementById('cropper_overlay');
            const image = document.getElementById('cropper_image');
            const hiddenInput = document.getElementById('cropped_profile_photo');
            const currentPreview = document.getElementById('current_profile_preview');
            const cropStatus = document.getElementById('crop_status');
            const zoomIn = document.getElementById('zoom_in');
            const zoomOut = document.getElementById('zoom_out');
            const resetCrop = document.getElementById('reset_crop');
            const saveCrop = document.getElementById('save_crop');
            const cancelCrop = document.getElementById('cancel_crop');
            const closeCropper = document.getElementById('close_cropper');
            let cropperInstance = null;
            let activeObjectUrl = null;

            const destroyCropper = () => {
                if (cropperInstance) {
                    cropperInstance.destroy();
                    cropperInstance = null;
                }
                if (activeObjectUrl) {
                    URL.revokeObjectURL(activeObjectUrl);
                    activeObjectUrl = null;
                }
            };

            const closeModal = (resetFile = false) => {
                modal.classList.add('hidden');
                destroyCropper();
                if (resetFile && input) {
                    input.value = '';
                }
            };

            input?.addEventListener('change', function (event) {
                const file = event.target.files?.[0];
                if (!file) {
                    hiddenInput.value = '';
                    destroyCropper();
                    return;
                }

                if (cropStatus) {
                    cropStatus.classList.add('hidden');
                }

                destroyCropper();

                activeObjectUrl = URL.createObjectURL(file);
                image.src = activeObjectUrl;
                modal.classList.remove('hidden');

                image.onload = function () {
                    cropperInstance = new Cropper(image, {
                        aspectRatio: 1,
                        viewMode: 1,
                        dragMode: 'move',
                        autoCropArea: 1,
                        responsive: true,
                        background: false,
                    });
                };
            });

            const applyCrop = () => {
                if (!cropperInstance) {
                    return;
                }

                const canvas = cropperInstance.getCroppedCanvas({
                    width: 512,
                    height: 512,
                    imageSmoothingQuality: 'high',
                });

                if (!canvas) {
                    return;
                }

                const croppedData = canvas.toDataURL('image/jpeg', 0.92);
                hiddenInput.value = croppedData;

                if (currentPreview) {
                    currentPreview.src = croppedData;
                }

                if (cropStatus) {
                    cropStatus.classList.remove('hidden');
                }

                closeModal(false);
            };

            zoomIn?.addEventListener('click', function () {
                cropperInstance?.zoom(0.1);
            });

            zoomOut?.addEventListener('click', function () {
                cropperInstance?.zoom(-0.1);
            });

            resetCrop?.addEventListener('click', function () {
                cropperInstance?.reset();
            });

            saveCrop?.addEventListener('click', applyCrop);

            cancelCrop?.addEventListener('click', function () {
                closeModal(true);
            });

            closeCropper?.addEventListener('click', function () {
                closeModal(true);
            });

            overlay?.addEventListener('click', function () {
                closeModal(true);
            });
        });
    </script>
</x-app-layout>
