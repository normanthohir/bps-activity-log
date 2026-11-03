<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @include('admin.bagian.partials.form-tim', [
            'action' => route('admin.bagian.store'),
            'judul' => 'Tambah Tim',
            'deskripsi' => 'Buat unit kerja baru dalam struktur organisasi.',
            'tombol' => 'Simpan',
        ])
    </div>
</x-app-layout>