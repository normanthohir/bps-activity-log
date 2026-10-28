<?php

namespace App\Http\Controllers;

use App\Models\KamusKerja;
use App\Support\ExcelMasterData;
use App\Support\ModeKerja;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;
use Symfony\Component\HttpFoundation\StreamedResponse;

class KamusExcelController extends Controller
{
    private function boleh(Request $request): void
    {
        abort_unless($request->user()->isAdmin() || ModeKerja::ketuaAktif($request->user()), 403);
    }

    public function template(Request $request): StreamedResponse
    {
        $this->boleh($request);
        return ExcelMasterData::unduh('kamus');
    }

    public function import(Request $request)
    {
        $this->boleh($request);
        $data = $request->validate([
            'file_excel' => 'required|file|mimes:xlsx|max:5120',
            'bagian_id' => 'nullable|integer|exists:bagian,id',
        ]);
        $user = $request->user();
        $bagianId = $data['bagian_id'] ?? null;
        if ($user->isAdmin()) {
            if ($bagianId !== null) throw ValidationException::withMessages(['bagian_id' => 'Kamus kantor dari Admin tidak memerlukan tim.']);
        } elseif (!$bagianId || !$user->memimpinTim((int) $bagianId)) {
            throw ValidationException::withMessages(['bagian_id' => 'Ketua Tim wajib memilih tim yang dipimpinnya.']);
        }
        $rows = ExcelMasterData::baca($data['file_excel'], 'Kamus Kerja', ExcelMasterData::KAMUS);
        if (!$rows) throw ValidationException::withMessages(['file_excel' => 'Tidak ada baris Kamus Kerja untuk diimpor.']);
        $errors = []; $unik = []; $hasil = [];
        foreach ($rows as $row) {
            $nama = trim($row['nama']);
            if ($nama === '' || mb_strlen($nama) > 255) {
                $errors[] = "Baris {$row['_baris']}: nama pekerjaan wajib dan maksimal 255 karakter.";
                continue;
            }
            $kunci = mb_strtolower($nama);
            if (isset($unik[$kunci])) {
                $errors[] = "Baris {$row['_baris']}: nama '{$nama}' duplikat dengan baris {$unik[$kunci]}.";
            }
            $unik[$kunci] = $row['_baris'];
            $sudahAda = KamusKerja::where('nama', $nama);
            $bagianId === null ? $sudahAda->whereNull('bagian_id') : $sudahAda->where('bagian_id', $bagianId);
            if ($sudahAda->exists()) $errors[] = "Baris {$row['_baris']}: '{$nama}' sudah ada dalam kamus lingkup ini.";
            $hasil[] = [
                'nama' => $nama,
                'input' => $row['input'] !== '' ? $row['input'] : null,
                'proses' => $row['proses'] !== '' ? $row['proses'] : null,
                'output' => $row['output'] !== '' ? $row['output'] : null,
                'bagian_id' => $bagianId,
                'dibuat_oleh' => $user->id,
            ];
        }
        if ($errors) throw ValidationException::withMessages(['file_excel' => implode("\n", array_slice($errors, 0, 30))]);
        DB::transaction(function () use ($hasil) {
            foreach ($hasil as $item) KamusKerja::create($item);
        });
        return redirect()->route('kamus.index')->with('success', count($hasil).' entri Kamus Kerja berhasil diimpor.');
    }
}
