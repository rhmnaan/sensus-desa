<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Services\RiwayatSinkronisasi;
use Illuminate\Contracts\View\View;

class ReplikasiController extends Controller
{
    public function index(RiwayatSinkronisasi $riwayatSinkronisasi): View
    {
        $monitor = $riwayatSinkronisasi->monitor();
        $monitor['jumlahBerjalan'] = count(array_filter(
            $monitor['agen'],
            fn (array $agen): bool => $agen['status'] === 'Berjalan',
        ));
        $monitor['sinkronTerakhir'] = collect($monitor['riwayat'])
            ->firstWhere('status', 'Berhasil');

        return view('admin.replikasi', [
            'monitor' => $monitor,
        ]);
    }
}
