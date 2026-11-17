<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Pendataan;
use App\Models\PeriodeSensus;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Contracts\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $periode   = PeriodeSensus::berjalan();
        $periodeId = $periode->id ?? 0;
        $userId    = auth()->id();

        $total     = Keluarga::count();
        $sudah     = $periode ? Pendataan::where('periode_id', $periodeId)->count() : 0;
        $belum     = max($total - $sudah, 0);
        $olehSaya  = $periode ? Pendataan::where('periode_id', $periodeId)->where('petugas_id', $userId)->count() : 0;
        $persen    = $total > 0 ? (int) round($sudah / $total * 100) : 0;

        $statistik = [
            ['label' => 'Total keluarga',    'nilai' => $total,    'ket' => 'terdaftar di sistem'],
            ['label' => 'Sudah didata',      'nilai' => $sudah,    'ket' => $periode ? 'periode '.$periode->tahun : 'belum ada periode berjalan'],
            ['label' => 'Belum didata',      'nilai' => $belum,    'ket' => 'perlu didatangi'],
            ['label' => 'Didata oleh saya',  'nilai' => $olehSaya, 'ket' => 'pada periode ini'],
        ];

        $belumList = Keluarga::query()
            ->with(['kepalaKeluarga:id,keluarga_id,nama', 'rt:id,rw_id,no_rt', 'rt.rw:id,no_rw'])
            ->whereDoesntHave('pendataan', fn (Builder $query) => $query->where('periode_id', $periodeId))
            ->orderBy('id')
            ->limit(5)
            ->get();

        return view('petugas.dashboard', compact('periode', 'statistik', 'persen', 'sudah', 'total', 'belumList'));
    }
}
