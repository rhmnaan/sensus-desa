<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiwayatSinkronisasi
{
    /**
     * @return array{tersedia: bool, menunggu: ?int, berhasil: ?int, konflik: ?int}
     */
    public function ringkasan(): array
    {
        $ringkasan = [
            'tersedia' => false,
            'menunggu' => null,
            'berhasil' => null,
            'konflik' => null,
        ];

        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return $ringkasan;
        }

        try {
            $hasil = DB::select(
                'SELECT runstatus, COUNT_BIG(*) AS jumlah
                   FROM distribution.dbo.MSmerge_history
                  GROUP BY runstatus'
            );
            $ringkasan['tersedia'] = true;
            $ringkasan['berhasil'] = 0;

            foreach ($hasil as $baris) {
                if ((int) $baris->runstatus === 2) {
                    $ringkasan['berhasil'] = (int) $baris->jumlah;
                }
            }
        } catch (QueryException $exception) {
            Log::info('Ringkasan riwayat Merge tidak tersedia.', [
                'pesan' => $exception->getMessage(),
            ]);
        }

        try {
            $tabelKonflik = DB::selectOne(
                "SELECT OBJECT_ID('dbo.MSmerge_conflicts_info', 'U') AS object_id"
            );

            if ($tabelKonflik?->object_id !== null) {
                $jumlahKonflik = DB::selectOne('SELECT COUNT_BIG(*) AS jumlah FROM dbo.MSmerge_conflicts_info');
                $ringkasan['konflik'] = (int) $jumlahKonflik->jumlah;
            }
        } catch (QueryException $exception) {
            Log::info('Jumlah konflik Merge tidak tersedia.', [
                'pesan' => $exception->getMessage(),
            ]);
        }

        return $ringkasan;
    }

    /**
     * @return array{
     *     tersedia: bool,
     *     riwayat: list<array{waktu: string, publikasi: string, jenis: string, status: string}>
     * }
     */
    public function terbaru(int $limit = 6): array
    {
        $status = [1 => 'Mulai', 2 => 'Berhasil', 3 => 'Berjalan', 4 => 'Menunggu', 5 => 'Mencoba ulang', 6 => 'Gagal'];
        $baris = [];
        $tersedia = false;
        $limit = max(1, min($limit, 100));

        $sumber = [
            'Distribusi' => [
                'distribution.dbo.MSdistribution_history',
                'distribution.dbo.MSdistribution_agents',
            ],
            'Merge' => [
                'distribution.dbo.MSmerge_history',
                'distribution.dbo.MSmerge_agents',
            ],
        ];

        foreach ($sumber as $jenis => [$riwayat, $agen]) {
            try {
                $hasil = DB::select(
                    "SELECT TOP {$limit} h.[time] AS waktu, a.publication, h.runstatus
                       FROM $riwayat h
                       JOIN $agen a ON a.id = h.agent_id
                      ORDER BY h.[time] DESC"
                );
                $tersedia = true;

                foreach ($hasil as $r) {
                    $baris[] = [
                        'waktu' => Carbon::parse($r->waktu),
                        'publikasi' => $r->publication,
                        'jenis' => $jenis,
                        'status' => $status[$r->runstatus] ?? 'Tidak diketahui',
                    ];
                }
            } catch (QueryException $exception) {
                Log::info('Riwayat replikasi tidak tersedia.', [
                    'sumber' => $jenis,
                    'pesan' => $exception->getMessage(),
                ]);
            }
        }

        usort($baris, fn (array $a, array $b) => $b['waktu']->timestamp <=> $a['waktu']->timestamp);

        $riwayatTerbaru = array_map(function (array $baris): array {
            $baris['waktu'] = $baris['waktu']->format('d M Y H:i');

            return $baris;
        }, array_slice($baris, 0, $limit));

        return [
            'tersedia' => $tersedia,
            'riwayat' => $riwayatTerbaru,
        ];
    }
}
