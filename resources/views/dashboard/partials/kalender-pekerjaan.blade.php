{{-- Style lokal agar rentang kalender langsung bekerja tanpa npm run build. --}}
<style>
    .rapi-calendar-week {
        display: grid;
        grid-template-columns: repeat(7, minmax(0, 1fr));
        grid-template-rows: 32px repeat(var(--baris-tugas), 27px) 25px 8px;
        min-height: 124px;
    }
    .rapi-calendar-day {
        grid-row: 1 / -1;
        border-right: 1px solid #e2e8f0;
        border-bottom: 1px solid #e2e8f0;
        background: #fff;
        pointer-events: none;
    }
    .rapi-calendar-day.outside { background: #f8fafc; }
    .rapi-calendar-date {
        grid-row: 1;
        position: relative;
        z-index: 1;
        justify-self: start;
        align-self: start;
        padding: 4px 7px;
        font-size: 12px;
        color: #64748b;
        pointer-events: none;
    }
    .rapi-calendar-date.today {
        margin: 3px 0 0 5px;
        padding: 2px 7px;
        border-radius: 999px;
        background: #1f3864;
        color: #fff;
        font-weight: 700;
    }
    .rapi-calendar-event {
        position: relative;
        z-index: 2;
        display: flex;
        align-items: center;
        gap: 4px;
        min-width: 0;
        height: 24px;
        margin: 1px 2px;
        padding: 2px 6px;
        border-radius: 5px;
        font-size: 11px;
        font-weight: 600;
        text-decoration: none;
        overflow: hidden;
    }
    .rapi-calendar-event .event-title {
        min-width: 0;
        overflow: hidden;
        white-space: nowrap;
        text-overflow: ellipsis;
    }
    .rapi-calendar-event.belum_dikerjakan { background: #fef3c7; color: #92400e; }
    .rapi-calendar-event.dikerjakan { background: #dbeafe; color: #1e40af; }
    .rapi-calendar-event.selesai { background: #d1fae5; color: #065f46; }
    .rapi-calendar-event:not(.belum_dikerjakan):not(.dikerjakan):not(.selesai) { background: #e2e8f0; color: #334155; }
    .rapi-calendar-event.is-continuation-left { border-top-left-radius: 1px; border-bottom-left-radius: 1px; }
    .rapi-calendar-event.is-continuation-right { border-top-right-radius: 1px; border-bottom-right-radius: 1px; }
    .rapi-calendar-event.is-continuation-left::before { content: '‹'; font-size: 15px; line-height: 1; flex-shrink: 0; }
    .rapi-calendar-event.is-continuation-right::after { content: '›'; font-size: 15px; line-height: 1; flex-shrink: 0; margin-left: auto; }
    .rapi-calendar-event:hover { filter: brightness(.96); text-decoration: underline; }
    .rapi-calendar-event:focus-visible { outline: 2px solid #1f3864; outline-offset: 1px; }
    .rapi-calendar-event.is-sop { box-shadow: inset 3px 0 0 #355b91; }
    .rapi-calendar-more {
        position: relative; z-index: 2; min-width: 0; height: 22px;
        margin: 1px 2px; padding: 1px 4px; text-align: left;
        color: #1f3864; background: #eef2ff; border-radius: 5px;
        font-size: 10px; font-weight: 700; cursor: pointer;
        white-space: nowrap; overflow: hidden; text-overflow: ellipsis;
    }
    .rapi-calendar-more:hover { background: #dbeafe; }
    .rapi-calendar-more:focus-visible { outline: 2px solid #1f3864; }
    #rapi-calendar-more-dialog { max-width: min(580px, calc(100vw - 32px)); width: 100%;
        max-height: min(75vh, 720px); margin: auto; padding: 0;
        border: 1px solid #cbd5e1; border-radius: 16px; box-shadow: 0 24px 70px #0f172a44;
        background: white; color: #0f172a;
    }
    #rapi-calendar-more-dialog::backdrop { background: #0f172a88; }
    .rapi-calendar-hidden-link { display: block; padding: 12px 14px; border: 1px solid #e2e8f0;
        border-radius: 9px; text-decoration: none; color: #0f172a; }
    .rapi-calendar-hidden-link:hover { background: #f8fafc; border-color: #93c5fd; }
</style>
<section class="bg-white rounded-xl border border-slate-200 p-4 sm:p-5 mb-6">
    <div class="flex flex-wrap items-center justify-between gap-3 mb-4">
        <div>
            <h2 class="font-bold text-lg text-slate-900">Kalender Pekerjaan Bulanan</h2>
            <p class="text-xs text-slate-500">{{ $kalender['jumlah'] }} pekerjaan terjadwal pada {{ $kalender['label'] }}. Menampilkan maksimal 5 batang per minggu, memprioritaskan SOP. Pilih +N lainnya pada tanggal terkait untuk melihat pekerjaan yang tidak tampil.</p>
        </div>
        <div class="flex gap-2 items-center">
            <a class="border rounded-lg px-3 py-1 text-sm hover:bg-slate-50" href="{{ request()->fullUrlWithQuery(['bulan' => $kalender['sebelumnya']]) }}" aria-label="Bulan sebelumnya">‹</a>
            <span class="text-sm font-semibold min-w-32 text-center">{{ $kalender['label'] }}</span>
            <a class="border rounded-lg px-3 py-1 text-sm hover:bg-slate-50" href="{{ request()->fullUrlWithQuery(['bulan' => $kalender['berikutnya']]) }}" aria-label="Bulan berikutnya">›</a>
        </div>
    </div>
    <div class="overflow-x-auto">
        <div class="min-w-[700px]" role="group" aria-label="Kalender pekerjaan {{ $kalender['label'] }}">
            <div class="grid grid-cols-7 bg-slate-50 rounded-t-lg text-center text-xs uppercase font-semibold text-slate-500">
                @foreach(['Sen', 'Sel', 'Rab', 'Kam', 'Jum', 'Sab', 'Min'] as $namaHari)
                    <div class="p-2">{{ $namaHari }}</div>
                @endforeach
            </div>
            @foreach($kalender['minggu'] as $minggu)
                <div class="rapi-calendar-week border-t border-l border-slate-200" style="--baris-tugas: {{ $minggu['jumlah_baris'] }};">
                    @foreach($minggu['hari'] as $nomor => $hari)
                        <div aria-hidden="true" class="rapi-calendar-day {{ $hari['tanggal']->format('Y-m') === $kalender['bulan'] ? '' : 'outside' }}" style="grid-column: {{ $nomor + 1 }};"></div>
                        <span class="rapi-calendar-date {{ $hari['tanggal']->isToday() ? 'today' : '' }}" style="grid-column: {{ $nomor + 1 }};" aria-label="{{ $hari['tanggal']->translatedFormat('l, j F Y') }}">{{ $hari['tanggal']->day }}</span>
                    @endforeach
                    @foreach($minggu['segmen'] as $segmen)
                        @php($tugasKalender = $segmen['tugas'])
                        <a
                            href="{{ route('pekerjaan.show', $tugasKalender) }}"
                            class="rapi-calendar-event {{ $tugasKalender->sop_pelaksanaan_id || $tugasKalender->sop_langkah_id ? 'is-sop' : '' }} {{ $tugasKalender->status }} {{ $segmen['lanjut_dari_minggu_lalu'] ? 'is-continuation-left' : '' }} {{ $segmen['lanjut_minggu_depan'] ? 'is-continuation-right' : '' }}"
                            style="grid-column: {{ $segmen['kolom_mulai'] }} / span {{ $segmen['panjang'] }}; grid-row: {{ $segmen['baris'] }};"
                            title="{{ $tugasKalender->judul }} · {{ $segmen['awal_asli']->format('d/m/Y') }} – {{ $segmen['akhir_asli']->format('d/m/Y') }} · {{ $tugasKalender->penerimaTugas?->name ?? 'Belum ada pelaksana' }}"
                            aria-label="{{ $tugasKalender->judul }}, {{ $segmen['awal_asli']->translatedFormat('j F') }} sampai {{ $segmen['akhir_asli']->translatedFormat('j F Y') }}, pelaksana {{ $tugasKalender->penerimaTugas?->name ?? 'belum ada' }}"
                        >
                            <span class="event-title">{{ $tugasKalender->judul }}</span>
                        </a>
                    @endforeach
                    @foreach($minggu['hari'] as $nomor => $hari)
                        @if($hari['lainnya'] > 0)
                            <button type="button"
                                    class="rapi-calendar-more"
                                    style="grid-column: {{ $nomor + 1 }}; grid-row: {{ $minggu['jumlah_baris'] + 2 }};"
                                    data-rapi-kalender-more="{{ $hari['key'] }}"
                                    aria-haspopup="dialog"
                                    aria-label="Lihat {{ $hari['lainnya'] }} pekerjaan lain tanggal {{ $hari['tanggal']->translatedFormat('j F Y') }}">
                                +{{ $hari['lainnya'] }} lainnya
                            </button>
                        @endif
                    @endforeach
                </div>
            @endforeach
        </div>
    </div>

    {{-- Isi popup disiapkan dari hasil query yang sudah difilter sesuai hak akses akun. --}}
    @foreach($kalender['daftar_tersembunyi'] as $tanggal => $daftar)
        <template id="rapi-calendar-hidden-{{ $tanggal }}">
            <div class="space-y-2">
                @foreach($daftar as $item)
                    <a class="rapi-calendar-hidden-link" href="{{ $item['url'] }}">
                        <div class="flex flex-wrap items-center gap-2 mb-1">
                            @if($item['sop'])<span class="inline-block text-xs font-bold text-blue-900 bg-blue-100 rounded px-2 py-0.5">SOP</span>@endif
                            <span class="text-xs text-slate-500">{{ ucwords(str_replace('_', ' ', $item['status'])) }}</span>
                        </div>
                        <p class="text-sm font-semibold">{{ $item['judul'] }}</p>
                        <p class="text-xs text-slate-500 mt-1">{{ $item['pelaksana'] }} · {{ $item['awal'] }} – {{ $item['akhir'] }}</p>
                    </a>
                @endforeach
            </div>
        </template>
    @endforeach
    <dialog id="rapi-calendar-more-dialog" aria-labelledby="rapi-calendar-more-title">
        <div class="flex justify-between items-center gap-3 px-5 py-4 border-b border-slate-200">
            <div>
                <h3 id="rapi-calendar-more-title" class="text-base font-bold">Pekerjaan lainnya</h3>
                <p class="text-xs text-slate-500">Klik salah satu pekerjaan untuk melihat detailnya.</p>
            </div>
            <button type="button" data-rapi-calendar-close class="border rounded-lg px-3 py-1.5 text-sm" aria-label="Tutup daftar pekerjaan">Tutup</button>
        </div>
        <div id="rapi-calendar-more-content" class="p-4 overflow-y-auto" style="max-height: min(58vh, 540px);"></div>
    </dialog>
    <script>
        (() => {
            const dialog = document.getElementById('rapi-calendar-more-dialog');
            if (!dialog) return;
            const content = document.getElementById('rapi-calendar-more-content');
            const title = document.getElementById('rapi-calendar-more-title');
            document.querySelectorAll('[data-rapi-kalender-more]').forEach(button => {
                button.addEventListener('click', () => {
                    const tanggal = button.dataset.rapiKalenderMore;
                    const template = document.getElementById('rapi-calendar-hidden-' + tanggal);
                    if (!template) return;
                    content.replaceChildren(template.content.cloneNode(true));
                    const parts = tanggal.split('-').map(Number);
                    const label = new Intl.DateTimeFormat('id-ID', { day: 'numeric', month: 'long', year: 'numeric' })
                        .format(new Date(parts[0], parts[1] - 1, parts[2]));
                    title.textContent = 'Pekerjaan lainnya · ' + label;
                    dialog.showModal();
                });
            });
            dialog.querySelector('[data-rapi-calendar-close]').addEventListener('click', () => dialog.close());
            dialog.addEventListener('click', event => { if (event.target === dialog) dialog.close(); });
        })();
    </script>
    <div class="flex flex-wrap gap-4 text-xs text-slate-500 mt-3">
        <span class="font-semibold text-blue-900">▌ SOP diprioritaskan</span>
        <span>● Belum dikerjakan (kuning)</span>
        <span>● Dikerjakan (biru)</span>
        <span>● Selesai (hijau)</span>
        <a href="{{ route('pekerjaan.index') }}" class="text-blue-700 ml-auto">Lihat seluruh pekerjaan →</a>
    </div>
</section>
