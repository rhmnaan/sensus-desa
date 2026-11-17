<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\RingkasanPimpinan;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class LayakBantuanController extends Controller
{
    public function index(Request $request, RingkasanPimpinan $ringkasan): View
    {
        $periode = $ringkasan->pilihPeriode($request);
        $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'dusun_id' => ['nullable', 'integer', Rule::exists('dusun', 'id')],
        ]);

        $keluarga = $periode
            ? $ringkasan->daftarLayak($periode->id)
                ->when($request->filled('q'), function (Builder $query) use ($request): void {
                    $cari = '%'.$request->string('q')->toString().'%';
                    $query->where(function (Builder $subquery) use ($cari): void {
                        $subquery->where('k.no_kk', 'like', $cari)
                            ->orWhere('a.nama', 'like', $cari)
                            ->orWhere('k.alamat', 'like', $cari);
                    });
                })
                ->when($request->filled('dusun_id'), fn (Builder $query) => $query->where('d.id', $request->integer('dusun_id')))
                ->orderBy('d.nama_dusun')
                ->orderBy('rw.no_rw')
                ->orderBy('rt.no_rt')
                ->paginate(10)
                ->withQueryString()
            : collect();

        return view('pimpinan.layak-bantuan', [
            'periode' => $periode,
            'daftarPeriode' => $ringkasan->daftarPeriode(),
            'dusun' => $ringkasan->daftarDusun(),
            'keluarga' => $keluarga,
        ]);
    }

    public function show(int $pendataan, RingkasanPimpinan $ringkasan): View
    {
        $keluarga = $ringkasan->kepalaKeluarga($pendataan);

        abort_if($keluarga === null, 404);

        return view('pimpinan.layak-bantuan-detail', compact('keluarga'));
    }
}
