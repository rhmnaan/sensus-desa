<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Services\RiwayatSinkronisasi;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class SinkronController extends Controller
{
    public function index(RiwayatSinkronisasi $riwayatSinkronisasi): View
    {
        $hasil = $riwayatSinkronisasi->terbaru();
        $ringkasan = $riwayatSinkronisasi->ringkasan();

        return view('petugas.sinkron', [
            'riwayat' => $hasil['riwayat'],
            'sumberTersedia' => $hasil['tersedia'],
            'ringkasan' => $ringkasan,
        ]);
    }

    public function jalankan(): RedirectResponse
    {
        $job = config('replication.merge_sync_job');

        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return back()->withErrors([
                'sinkron' => 'Sinkronisasi manual hanya tersedia saat aplikasi terhubung ke SQL Server.',
            ]);
        }

        if (! is_string($job) || trim($job) === '') {
            return back()->withErrors([
                'sinkron' => 'Nama job SQL Server Agent belum diatur pada REPLICATION_MERGE_SYNC_JOB.',
            ]);
        }

        try {
            DB::statement('EXEC msdb.dbo.sp_start_job @job_name = ?', [$job]);
        } catch (QueryException $exception) {
            Log::error('Gagal memulai job sinkronisasi replikasi.', [
                'job' => $job,
                'pesan' => $exception->getMessage(),
            ]);

            return back()->withErrors([
                'sinkron' => 'Job sinkronisasi gagal dijalankan. Periksa konfigurasi SQL Server Agent dan hak akses akun database.',
            ]);
        }

        return back()->with('status', 'Job sinkronisasi Merge berhasil dimulai. Perbarui halaman beberapa saat lagi untuk melihat status terbaru.');
    }
}
