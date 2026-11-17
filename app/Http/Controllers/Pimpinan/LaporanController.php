<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\RingkasanPimpinan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpFoundation\StreamedResponse;

class LaporanController extends Controller
{
    public function index(Request $request, RingkasanPimpinan $ringkasan): View
    {
        $periode = $ringkasan->pilihPeriode($request);
        $filter = $request->validate([
            'tingkat' => ['nullable', Rule::in(['dusun', 'rw', 'rt'])],
            'dusun_id' => ['nullable', 'integer', Rule::exists('dusun', 'id')],
        ]);
        $tingkat = $filter['tingkat'] ?? 'dusun';
        $dusunId = isset($filter['dusun_id']) ? (int) $filter['dusun_id'] : null;
        $rekap = $periode ? $ringkasan->rekapWilayah($periode->id, $tingkat, $dusunId) : collect();

        return view('pimpinan.laporan', [
            'periode' => $periode,
            'daftarPeriode' => $ringkasan->daftarPeriode(),
            'dusun' => $ringkasan->daftarDusun(),
            'tingkat' => $tingkat,
            'dusunId' => $dusunId,
            'rekap' => $rekap,
        ]);
    }

    public function ekspor(Request $request, RingkasanPimpinan $ringkasan): StreamedResponse
    {
        $periode = $ringkasan->pilihPeriode($request);
        abort_if($periode === null, 404, 'Belum ada periode sensus untuk diekspor.');

        $filter = $request->validate([
            'tingkat' => ['nullable', Rule::in(['dusun', 'rw', 'rt'])],
            'dusun_id' => ['nullable', 'integer', Rule::exists('dusun', 'id')],
        ]);
        $tingkat = $filter['tingkat'] ?? 'dusun';
        $dusunId = isset($filter['dusun_id']) ? (int) $filter['dusun_id'] : null;
        $rekap = $ringkasan->rekapWilayah($periode->id, $tingkat, $dusunId);

        return response()->streamDownload(function () use ($rekap, $tingkat, $periode): void {
            $output = fopen('php://output', 'w');
            fwrite($output, "\xEF\xBB\xBF");
            $amanUntukSpreadsheet = static function (?string $nilai): string {
                if ($nilai !== null && preg_match('/^\s*[=+\-@]/u', $nilai) === 1) {
                    return "'".$nilai;
                }

                return $nilai ?? '';
            };
            fputcsv($output, ['Periode', $amanUntukSpreadsheet($periode->nama), 'Tahun', $periode->tahun], ';');
            fputcsv($output, ['Dusun', 'RW', 'RT', 'Total Keluarga', 'Sudah Didata', 'Layak Bantuan'], ';');

            foreach ($rekap as $baris) {
                fputcsv($output, [
                    $amanUntukSpreadsheet($baris->nama_dusun),
                    $tingkat === 'dusun' ? '' : $amanUntukSpreadsheet($baris->no_rw),
                    $tingkat === 'rt' ? $amanUntukSpreadsheet($baris->no_rt) : '',
                    (int) $baris->total_keluarga,
                    (int) $baris->sudah_didata,
                    (int) $baris->layak_bantuan,
                ], ';');
            }

            fclose($output);
        }, 'rekap-sensus-'.$periode->tahun.'.csv', [
            'Content-Type' => 'text/csv; charset=UTF-8',
        ]);
    }
}
