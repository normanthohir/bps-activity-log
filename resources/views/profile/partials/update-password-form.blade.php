<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <form method="post" action="{{ route('password.update') }}" class="mt-6 space-y-6"
          x-data="{
              errors: {},
              current_password: '',
              password: '',
              password_confirmation: '',
              validate() {
                  this.errors = {};
                  if (!this.current_password) this.errors.current_password = 'Password lama wajib diisi.';
                  if (!this.password) this.errors.password = 'Password baru wajib diisi.';
                  if (this.password.length < 8) this.errors.password = 'Password minimal 8 karakter.';
                  if (this.password !== this.password_confirmation) this.errors.password_confirmation = 'Konfirmasi password tidak cocok.';
                  return Object.keys(this.errors).length === 0;
              },
              submit() {
                  if (this.validate()) {
                      this.$refs.form.submit();
                  }
              }
          }" x-ref="form" novalidate>
        @csrf
        @method('put')

        <div>
            <x-input-label for="update_password_current_password" :value="__('Current Password')" />
            <x-text-input id="update_password_current_password" name="current_password" type="password" class="mt-1 block w-full" :class="errors.current_password ? 'border-red-500' : ''" x-model="current_password" autocomplete="current-password" />
            <template x-if="errors.current_password">
                <p class="text-sm text-red-600 mt-2" x-text="errors.current_password"></p>
            </template>
            <x-input-error :messages="$errors->updatePassword->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password" :value="__('New Password')" />
            <x-text-input id="update_password_password" name="password" type="password" class="mt-1 block w-full" :class="errors.password ? 'border-red-500' : ''" x-model="password" autocomplete="new-password" />
            <template x-if="errors.password">
                <p class="text-sm text-red-600 mt-2" x-text="errors.password"></p>
            </template>
            <x-input-error :messages="$errors->updatePassword->get('password')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
            <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" class="mt-1 block w-full" :class="errors.password_confirmation ? 'border-red-500' : ''" x-model="password_confirmation" autocomplete="new-password" />
            <template x-if="errors.password_confirmation">
                <p class="text-sm text-red-600 mt-2" x-text="errors.password_confirmation"></p>
            </template>
            <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <button type="button" @click="submit()"
                    class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'password-updated')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
