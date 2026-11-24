<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class RiwayatSinkronisasi
{
    /**
     * @return array{
     *     koneksi: string,
     *     pesanKoneksi: ?string,
     *     database: ?string,
     *     agenTersedia: bool,
     *     agen: list<array{jenis: string, publikasi: ?string, job: ?string, status: string, mulai: ?string}>,
     *     riwayatTersedia: bool,
     *     riwayat: list<array{waktu: string, publikasi: string, jenis: string, status: string, pesan: ?string}>,
     *     kesalahan: list<array{waktu: string, publikasi: string, jenis: string, status: string, pesan: ?string}>,
     *     konflik: ?int
     * }
     */
    public function monitor(): array
    {
        $database = config('database.connections.'.config('database.default').'.database');
        $hasil = [
            'koneksi' => 'tidak_didukung',
            'pesanKoneksi' => 'Monitor memerlukan koneksi SQL Server.',
            'database' => is_string($database) ? $database : null,
            'agenTersedia' => false,
            'agen' => [],
            'riwayatTersedia' => false,
            'riwayat' => [],
            'kesalahan' => [],
            'konflik' => null,
        ];

        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return $hasil;
        }

        try {
            DB::selectOne('SELECT 1 AS terhubung');
        } catch (QueryException $exception) {
            Log::warning('Koneksi SQL Server untuk monitor replikasi gagal.', [
                'pesan' => $exception->getMessage(),
            ]);

            $hasil['koneksi'] = 'gagal';
            $hasil['pesanKoneksi'] = 'Koneksi ke SQL Server gagal. Periksa jaringan dan konfigurasi koneksi.';

            return $hasil;
        }

        $hasil['koneksi'] = 'terhubung';
        $hasil['pesanKoneksi'] = null;

        try {
            $agen = DB::select(<<<'SQL'
                SELECT a.jenis,
                       a.publication AS publikasi,
                       j.name AS job,
                       CASE
                           WHEN j.job_id IS NULL THEN N'Tidak tersedia'
                           WHEN j.enabled = 0 THEN N'Nonaktif'
                           WHEN aktivitas.start_execution_date IS NOT NULL
                                AND aktivitas.stop_execution_date IS NULL THEN N'Berjalan'
                           WHEN aktivitas.run_requested_date IS NOT NULL
                                AND aktivitas.start_execution_date IS NULL THEN N'Menunggu'
                           ELSE N'Tidak berjalan'
                       END AS status,
                       aktivitas.start_execution_date AS mulai
                  FROM (
                        SELECT N'Merge' AS jenis, publication, job_id
                          FROM distribution.dbo.MSmerge_agents
                        UNION ALL
                        SELECT N'Distribusi' AS jenis, publication, job_id
                          FROM distribution.dbo.MSdistribution_agents
                  ) AS a
                  LEFT JOIN msdb.dbo.sysjobs AS j ON CONVERT(binary(16), j.job_id) = a.job_id
                  OUTER APPLY (
                        SELECT TOP (1) sa.start_execution_date,
                                       sa.stop_execution_date,
                                       sa.run_requested_date
                          FROM msdb.dbo.sysjobactivity AS sa
                         WHERE sa.job_id = j.job_id
                           AND sa.session_id = (SELECT MAX(session_id) FROM msdb.dbo.syssessions)
                         ORDER BY sa.start_execution_date DESC, sa.run_requested_date DESC
                  ) AS aktivitas
                 ORDER BY a.jenis, a.publication
                SQL);
            $hasil['agenTersedia'] = true;
            $hasil['agen'] = array_map(fn (object $baris): array => [
                'jenis' => (string) $baris->jenis,
                'publikasi' => $baris->publikasi === null ? null : (string) $baris->publikasi,
                'job' => $baris->job === null ? null : (string) $baris->job,
                'status' => (string) $baris->status,
                'mulai' => $baris->mulai === null
                    ? null
                    : Carbon::parse($baris->mulai)->format('d M Y H:i'),
            ], $agen);
        } catch (QueryException $exception) {
            Log::info('Status agen replikasi tidak tersedia.', [
                'pesan' => $exception->getMessage(),
            ]);
        }

        $hasilRiwayat = $this->terbaru(12);
        $hasil['riwayatTersedia'] = $hasilRiwayat['tersedia'];
        $hasil['riwayat'] = $hasilRiwayat['riwayat'];
        $hasil['kesalahan'] = array_values(array_filter(
            $hasilRiwayat['riwayat'],
            fn (array $baris): bool => in_array($baris['status'], ['Gagal', 'Mencoba ulang'], true),
        ));
        $hasil['konflik'] = $this->ringkasan()['konflik'];

        return $hasil;
    }

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
                   FROM distribution.dbo.MSmerge_sessions
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
     *     riwayat: list<array{waktu: string, publikasi: string, jenis: string, status: string, pesan: ?string}>
     * }
     */
    public function terbaru(int $limit = 6): array
    {
        $status = [1 => 'Mulai', 2 => 'Berhasil', 3 => 'Berjalan', 4 => 'Siaga', 5 => 'Mencoba ulang', 6 => 'Gagal'];
        $baris = [];
        $tersedia = false;
        $limit = max(1, min($limit, 100));

        $sumber = [
            'Distribusi' => <<<'SQL'
                SELECT TOP %d h.[time] AS waktu,
                       a.publication,
                       h.runstatus,
                       COALESCE(CAST(e.error_text AS nvarchar(max)), h.comments) AS pesan
                  FROM distribution.dbo.MSdistribution_history AS h
                  JOIN distribution.dbo.MSdistribution_agents AS a ON a.id = h.agent_id
                  LEFT JOIN distribution.dbo.MSrepl_errors AS e ON e.id = h.error_id
                 ORDER BY h.[time] DESC
                SQL,
            'Merge' => <<<'SQL'
                SELECT TOP %d s.start_time AS waktu,
                       a.publication,
                       s.runstatus,
                       COALESCE(CAST(e.error_text AS nvarchar(max)), pesan.comments) AS pesan
                  FROM distribution.dbo.MSmerge_sessions AS s
                  JOIN distribution.dbo.MSmerge_agents AS a ON a.id = s.agent_id
                  OUTER APPLY (
                        SELECT TOP (1) h.comments, h.error_id
                          FROM distribution.dbo.MSmerge_history AS h
                         WHERE h.session_id = s.session_id
                           AND h.agent_id = s.agent_id
                         ORDER BY h.timestamp DESC
                  ) AS pesan
                  LEFT JOIN distribution.dbo.MSrepl_errors AS e ON e.id = pesan.error_id
                 ORDER BY s.start_time DESC
                SQL,
        ];

        if (DB::connection()->getDriverName() !== 'sqlsrv') {
            return ['tersedia' => false, 'riwayat' => []];
        }

        foreach ($sumber as $jenis => $sql) {
            try {
                $hasil = DB::select(sprintf($sql, $limit));
                $tersedia = true;

                foreach ($hasil as $r) {
                    $baris[] = [
                        'waktu' => Carbon::parse($r->waktu),
                        'publikasi' => $r->publication,
                        'jenis' => $jenis,
                        'status' => $status[$r->runstatus] ?? 'Tidak diketahui',
                        'pesan' => filled($r->pesan) ? (string) $r->pesan : null,
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
