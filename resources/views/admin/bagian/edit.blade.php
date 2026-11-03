<x-app-layout>
    <div class="max-w-3xl mx-auto py-8 px-4 sm:px-6 lg:px-8">
        @include('admin.bagian.partials.form-tim', [
            'bagian' => $bagian,
            'action' => route('admin.bagian.update', $bagian),
            'judul' => 'Edit Tim: ' . $bagian->nama_bagian,
            'deskripsi' => 'Perbarui informasi, ketua, dan anggota tim.',
            'tombol' => 'Simpan Perubahan',
        ])
    </div>
</x-app-layout>