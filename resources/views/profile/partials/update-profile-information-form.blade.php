<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6"
          x-data="{
              errors: {},
              name: '{{ old('name', $user->name) }}',
              email: '{{ old('email', $user->email) }}',
              validate() {
                  this.errors = {};
                  if (!this.name.trim()) this.errors.name = 'Nama wajib diisi.';
                  if (!this.email.trim()) this.errors.email = 'Email wajib diisi.';
                  if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(this.email)) this.errors.email = 'Format email tidak valid.';
                  return Object.keys(this.errors).length === 0;
              },
              submit() {
                  if (this.validate()) {
                      this.$refs.form.submit();
                  }
              }
          }" x-ref="form" novalidate>
        @csrf
        @method('patch')

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :class="errors.name ? 'border-red-500' : ''" x-model="name" autofocus autocomplete="name" />
            <template x-if="errors.name">
                <p class="text-sm text-red-600 mt-2" x-text="errors.name"></p>
            </template>
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :class="errors.email ? 'border-red-500' : ''" x-model="email" autocomplete="username" />
            <template x-if="errors.email">
                <p class="text-sm text-red-600 mt-2" x-text="errors.email"></p>
            </template>
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-lg focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-[#1F3864]/30">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <button type="button" @click="submit()"
                    class="px-5 py-2.5 text-sm font-medium rounded-lg bg-[#1F3864] hover:bg-[#16294a] text-white transition-colors">
                {{ __('Save') }}
            </button>

            @if (session('status') === 'profile-updated')
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
