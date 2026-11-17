<?php

namespace App\Http\Controllers\Pimpinan;

use App\Http\Controllers\Controller;
use App\Services\RingkasanPimpinan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index(Request $request, RingkasanPimpinan $ringkasan): View
    {
        $periode = $ringkasan->pilihPeriode($request);
        $data = $ringkasan->dashboard($periode);

        return view('pimpinan.dashboard', [
            'periode' => $periode,
            'daftarPeriode' => $ringkasan->daftarPeriode(),
            ...$data,
        ]);
    }
}
