<section class="mb-6 rounded-xl border border-blue-100 bg-white p-5 shadow-sm">
    <div class="flex flex-col sm:flex-row sm:items-start sm:justify-between gap-3 mb-4">
        <div>
            <h2 class="text-base font-semibold text-gray-900">Import Excel — {{ $judul }}</h2>
            <p class="text-xs text-gray-500 mt-1">Unduh template, isi data sesuai kolom, lalu unggah file .xlsx (maksimum 5 MB). Impor dibatalkan bila ada baris tidak valid.</p>
        </div>
        <a href="{{ route($templateRoute) }}"
           class="shrink-0 inline-flex items-center justify-center gap-2 rounded-lg border border-[#1F3864] px-4 py-2 text-sm font-medium text-[#1F3864] hover:bg-blue-50">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="1.8"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v12m0 0 4-4m-4 4-4-4M5 17v3h14v-3"/></svg>
            Unduh Template Excel
        </a>
    </div>
    @if ($errors->has('file_excel'))
        <div role="alert" class="bg-red-50 border border-red-200 rounded-lg px-4 py-3 mb-4 text-sm text-red-800 whitespace-pre-line">{{ $errors->first('file_excel') }}</div>
    @endif
    <form method="POST" action="{{ route($importRoute) }}" enctype="multipart/form-data"
          class="flex flex-col sm:flex-row sm:items-center gap-3">
        @csrf
        @if($jenisImport === 'sop' && isset($timPilihanImport) && $timPilihanImport->isNotEmpty())
            <label class="text-sm font-medium">Tim SOP *
                <select required name="bagian_id" class="rounded-lg border-slate-300 block w-full mt-1">
                    <option value="">Pilih tim</option>
                    @foreach($timPilihanImport as $tim)<option value="{{ $tim->id }}" @selected(old('bagian_id') == $tim->id)>{{ $tim->kode_bagian }} — {{ $tim->nama_bagian }}</option>@endforeach
                </select>
            </label>
        @endif
        <label for="file-{{ $jenisImport }}" class="sr-only">File Excel {{ $judul }}</label>
        <input id="file-{{ $jenisImport }}" name="file_excel" type="file" accept=".xlsx" required
               class="block min-w-0 flex-1 text-sm text-gray-700 file:mr-3 file:rounded-md file:border-0 file:bg-gray-100 file:px-3 file:py-2 file:text-sm file:font-medium file:text-gray-700 hover:file:bg-gray-200">
        <button type="submit" class="shrink-0 inline-flex justify-center items-center gap-2 rounded-lg bg-[#1F3864] px-5 py-2.5 text-sm font-medium text-white hover:bg-[#16294a]">
            Import Excel
        </button>
    </form>
    @if (!empty($catatanImport))
        <p class="text-xs text-gray-500 mt-3">{{ $catatanImport }}</p>
    @endif
</section>
