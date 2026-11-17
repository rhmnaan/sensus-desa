<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Pendataan;
use App\Models\PeriodeSensus;
use App\Services\RiwayatSinkronisasi;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Throwable;

class DashboardController extends Controller
{
    public function index(RiwayatSinkronisasi $riwayatSinkronisasi): View
    {
        $periode = PeriodeSensus::berjalan();
        $periodeId = $periode->id ?? 0;

        $total = Keluarga::count();
        $sudah = $periode ? Pendataan::where('periode_id', $periodeId)->count() : 0;
        $belum = max($total - $sudah, 0);
        $konflik = $this->hitungKonflik();

        $statistik = [
            ['label' => 'Total Keluarga',   'nilai' => $total,  'ket' => 'terdaftar di sistem'],
            ['label' => 'Sudah Didata',     'nilai' => $sudah,  'ket' => $periode ? 'periode '.$periode->tahun : 'belum ada periode berjalan'],
            ['label' => 'Belum Didata',     'nilai' => $belum,  'ket' => 'masih perlu didatangi'],
            ['label' => 'Konflik Tercatat', 'nilai' => $konflik ?? '-', 'ket' => $konflik === null ? 'tersedia setelah Merge dibuat' : 'perlu ditinjau admin'],
        ];

        // Progres per dusun: dusun > RW > RT > keluarga > pendataan periode berjalan
        $dusun = collect(DB::select(
            'SELECT d.nama_dusun,
                    COUNT(DISTINCT k.id) AS total,
                    COUNT(DISTINCT p.id) AS terdata
               FROM dusun d
               LEFT JOIN rw ON rw.dusun_id = d.id
               LEFT JOIN rt ON rt.rw_id = rw.id
               LEFT JOIN keluarga k ON k.rt_id = rt.id
               LEFT JOIN pendataan p ON p.keluarga_id = k.id AND p.periode_id = ?
              GROUP BY d.id, d.nama_dusun
              ORDER BY d.nama_dusun',
            [$periodeId]
        ))->map(function ($d) {
            $d->persen = $d->total > 0 ? (int) round($d->terdata / $d->total * 100) : 0;

            return $d;
        });

        $hasilSinkronisasi = $riwayatSinkronisasi->terbaru();
        $sinkron = $hasilSinkronisasi['riwayat'];
        $sumberSinkronTersedia = $hasilSinkronisasi['tersedia'];

        return view('admin.dashboard', compact('periode', 'statistik', 'dusun', 'sinkron', 'sumberSinkronTersedia'));
    }

    // Jumlah konflik Merge. Tabel ini baru ada setelah publikasi Merge dibuat.
    private function hitungKonflik(): ?int
    {
        try {
            return (int) DB::selectOne('SELECT COUNT(*) AS n FROM dbo.MSmerge_conflicts_info')->n;
        } catch (Throwable $e) {
            return null;
        }
    }
}
