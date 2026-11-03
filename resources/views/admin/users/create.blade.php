<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @include('admin.users.partials.form-akun', [
            'action' => route('admin.users.store'),
            'judul' => 'Tambah Akun Pegawai',
            'deskripsi' => 'Buat akun baru untuk pegawai sistem.',
            'tombol' => 'Simpan',
        ])
    </div>
</x-app-layout>