@php
    $isKabag = \App\Support\ModeKerja::ketuaAktif(auth()->user());
@endphp
<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @include('laporan.partials.form-laporan', [
            'action' => route($isKabag ? 'kabag.laporan.store' : 'laporan.store'),
            'routeIndex' => route($isKabag ? 'kabag.laporan.index' : 'laporan.index'),
        ])
    </div>
</x-app-layout>