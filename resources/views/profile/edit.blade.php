<x-app-layout>
    <div class="max-w-2xl mx-auto py-8 px-4 space-y-6">

        <p class="font-medium text-lg mb-2">Profil Saya</p>

        <div class="bg-white rounded-xl border p-6">
            @include('profile.partials.update-profile-information-form')
        </div>

        <div class="bg-white rounded-xl border p-6">
            @include('profile.partials.update-password-form')
        </div>

        <div class="bg-white rounded-xl border p-6">
            @include('profile.partials.delete-user-form')
        </div>

    </div>
</x-app-layout>