<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\RingkasanPimpinan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    public function index(Request $request, RingkasanPimpinan $ringkasan): View
    {
        $periode = $ringkasan->pilihPeriode($request);

        return view('pimpinan.statistik', [
            'periode' => $periode,
            'daftarPeriode' => $ringkasan->daftarPeriode(),
            'statistik' => $ringkasan->statistik($periode),
        ]);
    }
}
