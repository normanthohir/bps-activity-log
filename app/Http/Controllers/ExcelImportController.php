<?php

namespace App\Http\Controllers;

use App\Models\Bagian;
use App\Models\Sop;
use App\Models\User;
use App\Support\AksesPekerjaan;
use App\Support\ExcelMasterData;
use App\Support\KeanggotaanTim;
use App\Support\ModeKerja;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class ExcelImportController extends Controller
{
    public function templateAkun(): StreamedResponse { return ExcelMasterData::unduh('akun'); }
    public function templateTim(): StreamedResponse { return ExcelMasterData::unduh('tim'); }
    public function templateSop(Request $request): StreamedResponse
    {
        abort_unless($request->user()->isAdmin() || ModeKerja::ketuaAktif($request->user()), 403);
        return ExcelMasterData::unduh('sop');
    }

    private function file(Request $request): \Illuminate\Http\UploadedFile
    {
        return $request->validate(['file_excel' => ['required', 'file', 'mimes:xlsx', 'max:5120']])['file_excel'];
    }

    private function gagal(array $pesan): never
    {
        // Satu pesan multi-baris agar sumber kesalahan terlihat tanpa gagal diam-diam.
        throw ValidationException::withMessages(['file_excel' => implode("\n", array_slice($pesan, 0, 35))
            . (count($pesan) > 35 ? "\n... dan " . (count($pesan) - 35) . ' kesalahan lainnya.' : '')]);
    }

    private function wajibAda(array $baris, string $kolom, int $maks, array &$errors, string $ref): void
    {
        $nilai = $baris[$kolom];
        if ($nilai === '') $errors[] = "{$ref}: {$kolom} wajib diisi.";
        elseif (mb_strlen($nilai) > $maks) $errors[] = "{$ref}: {$kolom} maksimal {$maks} karakter.";
    }

    private function cekPanjang(array $baris, string $kolom, int $maks, array &$errors, string $ref): void
    {
        if (mb_strlen($baris[$kolom]) > $maks) $errors[] = "{$ref}: {$kolom} maksimal {$maks} karakter.";
    }

    public function importAkun(Request $request): RedirectResponse
    {
        $baris = ExcelMasterData::baca($this->file($request), 'Akun', ExcelMasterData::AKUN);
        if (!$baris) $this->gagal(['Sheet Akun kosong. Isi setidaknya satu baris akun.']);

        $tim = Bagian::all()->keyBy(fn ($b) => mb_strtoupper($b->kode_bagian));
        $pengguna = User::whereNotNull('nip')->get()->keyBy(fn ($u) => $u->nip);
        $emailTerpakai = User::pluck('email')->map(fn ($v) => mb_strtolower($v))->flip();
        $semuaNip = [];
        $semuaEmail = [];
        $calon = [];
        $errors = [];
        foreach ($baris as $item) {
            $ref = 'Akun baris ' . $item['_baris'];
            foreach (['nama' => 255, 'nip' => 20, 'email' => 255] as $kolom => $max) {
                $this->wajibAda($item, $kolom, $max, $errors, $ref);
            }
            foreach (['kode_tim_utama'=>50, 'nip_atasan'=>20, 'kode_tim_tambahan'=>1000, 'kode_tim_dipimpin'=>1000] as $kolom => $max) {
                $this->cekPanjang($item, $kolom, $max, $errors, $ref);
            }
            $item['role'] = mb_strtolower($item['role'] ?: 'staf');
            if ($item['role'] === 'kepala_bagian') $item['role'] = 'staf'; // kompatibilitas template lama
            $item['email'] = mb_strtolower($item['email']);
            $item['kode_tim_utama'] = mb_strtoupper($item['kode_tim_utama']);
            $item['_tambahan'] = array_values(array_unique(array_filter(array_map(
                fn ($s) => mb_strtoupper(trim($s)), explode(';', $item['kode_tim_tambahan'])
            ), fn ($s) => $s !== '')));
            $item['_dipimpin'] = array_values(array_unique(array_filter(array_map(
                fn ($s) => mb_strtoupper(trim($s)), explode(';', $item['kode_tim_dipimpin'])
            ), fn ($s) => $s !== '')));

            if (!in_array($item['role'], ['staf', 'kepala_bps', 'admin'], true)) {
                $errors[] = "{$ref}: role boleh kosong, atau diisi staf, kepala_bps, admin.";
            }
            if (!filter_var($item['email'], FILTER_VALIDATE_EMAIL)) $errors[] = "{$ref}: email tidak valid.";
            if ($item['nip'] !== '' && !preg_match('/^[0-9]{1,20}$/D', $item['nip'])) {
                $errors[] = "{$ref}: NIP harus berupa digit (bukan notasi ilmiah). Format sel NIP sebagai Teks.";
            }
            if ($item['nip'] !== '' && (isset($semuaNip[$item['nip']]) || $pengguna->has($item['nip']))) {
                $errors[] = "{$ref}: NIP {$item['nip']} duplikat / sudah terdaftar.";
            }
            if ($item['email'] !== '' && (isset($semuaEmail[$item['email']]) || $emailTerpakai->has($item['email']))) {
                $errors[] = "{$ref}: email {$item['email']} duplikat / sudah terdaftar.";
            }
            $semuaNip[$item['nip']] = true;
            $semuaEmail[$item['email']] = true;
            foreach (array_unique(array_filter(array_merge([$item['kode_tim_utama']], $item['_tambahan'], $item['_dipimpin']))) as $kode) {
                if (!$tim->has($kode)) $errors[] = "{$ref}: kode tim '{$kode}' belum ada. Tambah tim terlebih dahulu.";
            }
            if ($item['_dipimpin'] && $item['role'] !== 'staf') {
                $errors[] = "{$ref}: hanya pegawai operasional boleh menjadi ketua tim.";
            }
            $calon[$item['nip']] = $item;
        }
        $dipesan = [];
        foreach ($calon as $nip => $item) {
            foreach ($item['_dipimpin'] as $kode) {
                if (!$tim->has($kode)) continue;
                $ketuaLama = $tim[$kode]->kepala_bagian_id;
                if ($ketuaLama) $errors[] = "Akun baris {$item['_baris']}: tim {$kode} sudah punya ketua. Ubah dari Edit Tim.";
                if (isset($dipesan[$kode])) $errors[] = "Tim {$kode} dinyatakan dipimpin oleh dua akun dalam satu file.";
                $dipesan[$kode] = $nip;
            }
        }
        foreach ($baris as $item) {
            $ref = 'Akun baris ' . $item['_baris'];
            $nip = $item['nip_atasan'];
            if ($nip === '') continue;
            if (!preg_match('/^[0-9]{1,20}$/D', $nip)) $errors[] = "{$ref}: NIP atasan harus berupa digit.";
            $atasan = $calon[$nip] ?? $pengguna->get($nip);
            if (!$atasan) { $errors[] = "{$ref}: NIP atasan {$nip} tidak terdaftar."; continue; }
            $bolehAtasan = is_array($atasan)
                ? $atasan['role'] === 'kepala_bps' || !empty($atasan['_dipimpin'])
                : $atasan->isKepalaBps() || $atasan->isKepalaBagian();
            if (!$bolehAtasan) {
                $errors[] = "{$ref}: atasan harus Kepala BPS atau ketua pada suatu tim.";
            }
            if ($nip === $item['nip']) $errors[] = "{$ref}: tidak boleh menjadi atasan sendiri.";
        }
        // Cegah lingkaran referensi atasan antar-akun dalam file.
        foreach ($calon as $nip => $item) {
            $terlihat = [$nip => true];
            $tujuan = $item['nip_atasan'];
            while ($tujuan !== '' && isset($calon[$tujuan])) {
                if (isset($terlihat[$tujuan])) {
                    $errors[] = 'Akun baris ' . $item['_baris'] . ': ditemukan siklus atasan.';
                    break;
                }
                $terlihat[$tujuan] = true;
                $tujuan = $calon[$tujuan]['nip_atasan'];
            }
        }
        if ($errors) $this->gagal($errors);

        DB::transaction(function () use ($baris, $tim, $pengguna) {
            $dibuat = [];
            foreach ($baris as $item) {
                $utama = mb_strtoupper($item['kode_tim_utama']);
                $user = User::create([
                    'name' => $item['nama'], 'nip' => $item['nip'], 'email' => mb_strtolower($item['email']),
                    'role' => in_array(mb_strtolower($item['role']), ['kepala_bps', 'admin'], true) ? mb_strtolower($item['role']) : 'staf', 'bagian_id' => $utama ? $tim[$utama]->id : null,
                    'password' => Hash::make('password'),
                ]);
                $user->forceFill(['email_verified_at' => now()])->save();
                $tambahan = array_map(fn ($s) => mb_strtoupper(trim($s)), explode(';', $item['kode_tim_tambahan']));
                $dipimpin = array_map(fn ($s) => mb_strtoupper(trim($s)), explode(';', $item['kode_tim_dipimpin']));
                $anggotaIds = [];
                foreach (array_unique(array_filter(array_merge([$utama], $tambahan))) as $kode) {
                    $anggotaIds[] = $tim[$kode]->id;
                }
                $ketuaIds = [];
                foreach (array_unique(array_filter($dipimpin)) as $kode) {
                    $ketuaIds[] = $tim[$kode]->id;
                }
                KeanggotaanTim::sinkronPegawai($user, $anggotaIds, $ketuaIds);
                $dibuat[$item['nip']] = $user;
            }
            foreach ($baris as $item) {
                if (!$item['nip_atasan']) continue;
                $atasan = $dibuat[$item['nip_atasan']] ?? $pengguna->get($item['nip_atasan']);
                $dibuat[$item['nip']]->update(['atasan_id' => $atasan->id]);
            }
        });
        return back()->with('success', count($baris) . ' akun berhasil diimpor. Password awal setiap akun: password (wajib segera diganti).');
    }

    public function importTim(Request $request): RedirectResponse
    {
        $file = $this->file($request);
        $barisTim = ExcelMasterData::baca($file, 'Tim', ExcelMasterData::TIM);
        $barisAnggota = ExcelMasterData::baca($file, 'Anggota Tim', ExcelMasterData::ANGGOTA, 3000);
        if (!$barisTim) $this->gagal(['Sheet Tim kosong. Isi setidaknya satu tim.']);
        $kodeLama = Bagian::pluck('kode_bagian')->map(fn ($v) => mb_strtoupper($v))->flip();
        $pegawai = User::whereNotNull('nip')->get()->keyBy('nip');
        $timBaru = [];
        $anggota = [];
        $errors = [];
        foreach ($barisTim as $item) {
            $ref = 'Tim baris ' . $item['_baris'];
            $this->wajibAda($item, 'kode_tim', 50, $errors, $ref);
            $this->wajibAda($item, 'nama_tim', 255, $errors, $ref);
            $this->cekPanjang($item, 'nip_ketua', 20, $errors, $ref);
            $kode = mb_strtoupper($item['kode_tim']);
            if ($kodeLama->has($kode) || isset($timBaru[$kode])) $errors[] = "{$ref}: kode tim {$kode} duplikat / sudah ada.";
            if ($item['nip_ketua'] !== '' && (!preg_match('/^[0-9]{1,20}$/D', $item['nip_ketua']) || !$pegawai->has($item['nip_ketua']) || !$pegawai[$item['nip_ketua']]->isStaf())) {
                $errors[] = "{$ref}: NIP ketua tidak ditemukan atau bukan pegawai operasional.";
            }
            $timBaru[$kode] = $item;
        }
        foreach ($barisAnggota as $item) {
            $ref = 'Anggota Tim baris ' . $item['_baris'];
            $this->wajibAda($item, 'kode_tim', 50, $errors, $ref);
            $this->wajibAda($item, 'nip_pegawai', 20, $errors, $ref);
            $kode = mb_strtoupper($item['kode_tim']);
            if (!isset($timBaru[$kode])) $errors[] = "{$ref}: kode tim '{$kode}' tidak tercantum pada sheet Tim.";
            if (!preg_match('/^[0-9]{1,20}$/D', $item['nip_pegawai']) || !$pegawai->has($item['nip_pegawai']) || !$pegawai->get($item['nip_pegawai'])?->isStaf()) {
                $errors[] = "{$ref}: NIP pegawai tidak ditemukan atau tidak berperan staf/kepala_bagian.";
            }
            if (isset($anggota[$kode][$item['nip_pegawai']])) $errors[] = "{$ref}: anggota {$item['nip_pegawai']} duplikat pada tim {$kode}.";
            $anggota[$kode][$item['nip_pegawai']] = true;
        }
        if ($errors) $this->gagal($errors);

        DB::transaction(function () use ($timBaru, $anggota, $pegawai) {
            foreach ($timBaru as $kode => $item) {
                $ketua = $item['nip_ketua'] ? $pegawai[$item['nip_ketua']] : null;
                $tim = Bagian::create(['kode_bagian' => $item['kode_tim'], 'nama_bagian' => $item['nama_tim'],
                    'kepala_bagian_id' => $ketua?->id]);
                $nips = array_keys($anggota[$kode] ?? []);
                if ($ketua) $nips[] = $ketua->nip;
                $ids = array_unique(array_map(fn ($nip) => $pegawai[$nip]->id, $nips));
                $tim->anggotaTim()->sync(collect($ids)->mapWithKeys(fn ($id) => [
                    $id => ['peran_tim' => $ketua?->id === $id ? 'ketua' : 'anggota'],
                ])->all());
                foreach ($ids as $id) {
                    // Jangan pindahkan pegawai dari tim utama yang sudah dipilih sebelumnya.
                    User::whereKey($id)->whereNull('bagian_id')->update(['bagian_id' => $tim->id]);
                }
            }
        });
        return back()->with('success', count($timBaru) . ' tim berhasil diimpor dengan keanggotaan dan ketua tim.');
    }

    public function importSop(Request $request): RedirectResponse
    {
        abort_unless($request->user()->isAdmin() || ModeKerja::ketuaAktif($request->user()), 403);
        $bagianId = null;
        if (!$request->user()->isAdmin()) {
            $dataTim = $request->validate(['bagian_id' => 'required|integer|exists:bagian,id']);
            abort_unless($request->user()->memimpinTim((int) $dataTim['bagian_id']), 403,
                'Anda hanya dapat mengimpor SOP untuk tim yang dipimpin.');
            $bagianId = (int) $dataTim['bagian_id'];
        }
        $baris = ExcelMasterData::baca($this->file($request), 'SOP', ExcelMasterData::SOP, 2000);
        if (!$baris) $this->gagal(['Sheet SOP kosong. Isi setidaknya satu langkah.']);
        $kodeLama = Sop::pluck('kode')->map(fn ($v) => mb_strtoupper($v))->flip();
        $sops = [];
        $errors = [];
        foreach ($baris as $item) {
            $ref = 'SOP baris ' . $item['_baris'];
            foreach (['kode_sop'=>100, 'nama_sop'=>255, 'versi'=>50, 'urutan'=>5, 'proses'=>10000, 'sumber_input'=>20, 'durasi_hari'=>5] as $kolom => $max) {
                $this->wajibAda($item, $kolom, $max, $errors, $ref);
            }
            foreach (['keterangan'=>10000, 'input'=>10000, 'output'=>10000] as $kolom => $max) {
                $this->cekPanjang($item, $kolom, $max, $errors, $ref);
            }
            $kode = mb_strtoupper($item['kode_sop']);
            if ($kodeLama->has($kode)) $errors[] = "{$ref}: kode SOP {$kode} sudah digunakan.";
            if (!in_array($item['sumber_input'], ['manual', 'surat', 'sebelumnya'], true)) {
                $errors[] = "{$ref}: sumber_input harus manual, surat, atau sebelumnya.";
            }
            if (!ctype_digit($item['urutan']) || (int) $item['urutan'] < 1 || (int) $item['urutan'] > 50) {
                $errors[] = "{$ref}: urutan harus angka 1 sampai 50.";
            }
            if (!ctype_digit($item['durasi_hari']) || (int) $item['durasi_hari'] > 3650) {
                $errors[] = "{$ref}: durasi_hari harus bilangan bulat 0 sampai 3650.";
            }
            if (!isset($sops[$kode])) $sops[$kode] = ['meta' => $item, 'langkah' => []];
            foreach (['nama_sop', 'versi', 'keterangan'] as $kolom) {
                if ($sops[$kode]['meta'][$kolom] !== $item[$kolom]) {
                    $errors[] = "{$ref}: {$kolom} harus sama di setiap baris SOP {$kode}.";
                }
            }
            if (isset($sops[$kode]['langkah'][$item['urutan']])) $errors[] = "{$ref}: urutan {$item['urutan']} duplikat pada SOP {$kode}.";
            $sops[$kode]['langkah'][(int) $item['urutan']] = $item;
        }
        foreach ($sops as $kode => $sop) {
            $langkah = $sop['langkah'];
            $urutan = array_keys($langkah);
            sort($urutan, SORT_NUMERIC);
            if (count($langkah) > 50 || $urutan !== range(1, count($langkah))) {
                $errors[] = "SOP {$kode}: urutan harus lengkap 1 sampai " . count($langkah) . '.';
            }
            if (($langkah[1]['sumber_input'] ?? '') === 'sebelumnya') {
                $errors[] = "SOP {$kode}: langkah pertama tidak boleh memakai sumber 'sebelumnya'.";
            }
        }
        if ($errors) $this->gagal($errors);
        DB::transaction(function () use ($sops, $request, $bagianId) {
            foreach ($sops as $group) {
                $meta = $group['meta'];
                $sop = Sop::create(['kode' => $meta['kode_sop'], 'nama' => $meta['nama_sop'],
                    'versi' => $meta['versi'], 'keterangan' => $meta['keterangan'] ?: null,
                    'dibuat_oleh' => $request->user()->id, 'bagian_id' => $bagianId]);
                ksort($group['langkah'], SORT_NUMERIC);
                foreach ($group['langkah'] as $no => $item) {
                    $sop->langkah()->create(['urutan' => (int) $no, 'input' => $item['input'] ?: null,
                        'proses' => $item['proses'], 'output' => $item['output'] ?: null,
                        'sumber_input' => $item['sumber_input'], 'durasi_hari' => (int) $item['durasi_hari']]);
                }
            }
        });
        return back()->with('success', count($sops) . ' SOP baru (' . count($baris) . ' langkah IPO) berhasil diimpor.');
    }
}
