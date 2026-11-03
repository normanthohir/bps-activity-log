{{-- Riwayat laporan. Parameter: $laporan (relasi user dan logApproval.approver sudah dimuat) --}}
<div class="animate-in delay-3 bg-white rounded-xl border border-gray-200 p-5">
    <p class="text-xs font-semibold uppercase tracking-wide text-gray-500 mb-4">Riwayat laporan</p>

    <ol class="relative border-s border-gray-200 ms-2 space-y-5">
        <li class="ms-5">
            <span class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-gray-400"></span>
            <p class="text-sm font-medium text-gray-900">Dibuat oleh {{ $laporan->user->name }}</p>
            <p class="text-xs text-gray-400">{{ $laporan->created_at->format('d M Y, H:i') }}</p>
        </li>

        @foreach ($laporan->logApproval as $log)
            @php $disetujui = $log->aksi === 'disetujui'; @endphp
            <li class="ms-5">
                <span
                    class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full {{ $disetujui ? 'bg-green-500' : 'bg-red-500' }}"></span>
                <div class="flex flex-wrap items-center gap-2">
                    <p class="text-sm font-medium text-gray-900">
                        {{ $disetujui ? 'Disetujui' : 'Dikembalikan' }} oleh {{ $log->approver?->name ?? '-' }}
                    </p>
                    <span
                        class="text-[11px] px-1.5 py-0.5 rounded-full {{ $disetujui ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ $log->approver?->isKepalaBps() ? 'Kepala BPS' : 'Kepala Tim' }}
                    </span>
                </div>
                <p class="text-xs text-gray-400">{{ $log->created_at->format('d M Y, H:i') }}</p>
                @if ($log->catatan)
                    <p class="text-sm text-gray-600 bg-gray-50 rounded-lg px-3 py-2 mt-2">{{ $log->catatan }}</p>
                @endif
            </li>
        @endforeach

        @if ($laporan->status === 'menunggu')
            <li class="ms-5">
                <span
                    class="absolute -start-[5px] mt-1.5 w-2.5 h-2.5 rounded-full bg-amber-400 animate-pulse motion-reduce:animate-none"></span>
                <p class="text-sm font-medium text-amber-700">Menunggu keputusan</p>
            </li>
        @endif
    </ol>
</div>
