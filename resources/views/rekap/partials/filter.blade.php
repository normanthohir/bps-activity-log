{{-- Parameter: $action (URL), $bulan, $tahun, $bagianId, $daftarBagian.
     Dipakai di dalam elemen ber-x-data yang punya variabel "tab". --}}
<form method="GET" action="{{ $action }}"
    class="bg-white rounded-xl border border-gray-200 p-3 mb-6 grid grid-cols-2 sm:grid-cols-4 gap-3">
    <input type="hidden" name="tab" :value="tab">

    <select name="bulan" onchange="this.form.submit()"
        class="text-sm border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
        @foreach (\App\Support\TampilanRekap::NAMA_BULAN as $angka => $nama)
            <option value="{{ $angka }}" @selected($bulan === $angka)>{{ $nama }}</option>
        @endforeach
    </select>

    <select name="tahun" onchange="this.form.submit()"
        class="text-sm border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
        @for ($t = now()->year + 1; $t >= now()->year - 5; $t--)
            <option value="{{ $t }}" @selected($tahun === $t)>{{ $t }}</option>
        @endfor
    </select>

    <select name="bagian" onchange="this.form.submit()"
        class="col-span-2 text-sm border border-gray-300 rounded-lg py-2 focus:ring-2 focus:ring-[#1F3864]/20 focus:border-[#1F3864]">
        <option value="">Semua bagian</option>
        @foreach ($daftarBagian as $b)
            <option value="{{ $b->id }}" @selected($bagianId === $b->id)>{{ $b->nama_bagian }}</option>
        @endforeach
    </select>
</form>