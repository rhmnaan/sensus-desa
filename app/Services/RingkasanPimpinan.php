<?php

namespace App\Services;

use App\Models\PeriodeSensus;
use Illuminate\Database\Query\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class RingkasanPimpinan
{
    public function pilihPeriode(Request $request): ?PeriodeSensus
    {
        $data = $request->validate([
            'periode_id' => ['nullable', 'integer', Rule::exists('periode_sensus', 'id')],
        ]);

        if (! empty($data['periode_id'])) {
            return PeriodeSensus::findOrFail($data['periode_id']);
        }

        return PeriodeSensus::berjalan() ?? PeriodeSensus::query()->orderByDesc('tahun')->first();
    }

    public function daftarPeriode(): Collection
    {
        return PeriodeSensus::query()->orderByDesc('tahun')->get();
    }

    public function dashboard(?PeriodeSensus $periode): array
    {
        $totalKeluarga = DB::table('keluarga')->count();
        $totalPendataan = $periode
            ? DB::table('pendataan')->where('periode_id', $periode->id)->distinct()->count('keluarga_id')
            : 0;
        $totalPenilaian = $periode
            ? DB::table('penilaian_kesejahteraan as pk')
                ->join('pendataan as p', 'p.id', '=', 'pk.pendataan_id')
                ->where('p.periode_id', $periode->id)
                ->distinct()->count('p.keluarga_id')
            : 0;
        $totalLayak = $periode ? $this->keluargaLayak($periode->id)->count() : 0;

        return [
            'kartu' => [
                ['label' => 'Total Keluarga', 'nilai' => $totalKeluarga, 'keterangan' => 'semua keluarga terdaftar'],
                ['label' => 'Sudah Didata', 'nilai' => $totalPendataan, 'keterangan' => 'pada periode terpilih'],
                ['label' => 'Belum Didata', 'nilai' => max(0, $totalKeluarga - $totalPendataan), 'keterangan' => 'pada periode terpilih'],
                ['label' => 'Layak Bantuan', 'nilai' => $totalLayak, 'keterangan' => $totalPenilaian.' keluarga sudah dinilai'],
            ],
            'kategori' => $periode ? $this->jumlahPerKategori($periode->id) : collect(),
            'dusun' => $periode ? $this->progresPerDusun($periode->id) : collect(),
        ];
    }

    public function statistik(?PeriodeSensus $periode): array
    {
        if (! $periode) {
            return [
                'ekonomi' => null,
                'pekerjaan' => collect(),
                'pendidikan' => collect(),
                'rumah' => [],
                'aset' => collect(),
                'bantuan' => collect(),
            ];
        }

        $ekonomi = DB::table('data_ekonomi as e')
            ->join('pendataan as p', 'p.id', '=', 'e.pendataan_id')
            ->where('p.periode_id', $periode->id)
            ->selectRaw('COUNT(*) AS jumlah, AVG(e.penghasilan_bulanan) AS rata_penghasilan, AVG(e.jumlah_utang) AS rata_utang, AVG(e.jumlah_tabungan) AS rata_tabungan')
            ->first();

        $pekerjaan = DB::table('anggota_keluarga as a')
            ->join('keluarga as k', 'k.id', '=', 'a.keluarga_id')
            ->join('pendataan as p', function ($join): void {
                $join->on('p.keluarga_id', '=', 'k.id');
            })
            ->join('pekerjaan as j', 'j.id', '=', 'a.pekerjaan_utama_id')
            ->where('p.periode_id', $periode->id)
            ->select('j.nama_pekerjaan as label')
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('j.nama_pekerjaan')
            ->orderByDesc('jumlah')
            ->get();

        $pendidikan = DB::table('anggota_keluarga as a')
            ->join('keluarga as k', 'k.id', '=', 'a.keluarga_id')
            ->join('pendataan as p', 'p.keluarga_id', '=', 'k.id')
            ->where('p.periode_id', $periode->id)
            ->selectRaw("COALESCE(a.pendidikan_terakhir, 'Belum diisi') AS label")
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('a.pendidikan_terakhir')
            ->orderByDesc('jumlah')
            ->get();

        $kolomRumah = [
            'status_kepemilikan' => 'Kepemilikan',
            'jenis_lantai' => 'Jenis lantai',
            'jenis_dinding' => 'Jenis dinding',
            'jenis_atap' => 'Jenis atap',
            'sumber_air' => 'Sumber air',
            'sumber_penerangan' => 'Sumber penerangan',
            'bahan_bakar_masak' => 'Bahan bakar memasak',
            'fasilitas_mck' => 'Fasilitas MCK',
        ];
        $rumah = [];

        foreach ($kolomRumah as $kolom => $label) {
            $rumah[] = [
                'label' => $label,
                'distribusi' => $this->distribusiKolom('kondisi_rumah', $kolom, $periode->id),
            ];
        }

        $aset = DB::table('aset_keluarga as a')
            ->join('pendataan as p', 'p.id', '=', 'a.pendataan_id')
            ->join('jenis_aset as j', 'j.id', '=', 'a.jenis_aset_id')
            ->where('p.periode_id', $periode->id)
            ->select('j.nama_aset as label')
            ->selectRaw('SUM(a.jumlah) AS jumlah')
            ->groupBy('j.nama_aset')
            ->orderByDesc('jumlah')
            ->get();

        $bantuan = DB::table('bantuan_sosial as b')
            ->join('pendataan as p', 'p.id', '=', 'b.pendataan_id')
            ->join('jenis_bantuan as j', 'j.id', '=', 'b.jenis_bantuan_id')
            ->where('p.periode_id', $periode->id)
            ->select('j.nama_bantuan as label')
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('j.nama_bantuan')
            ->orderByDesc('jumlah')
            ->get();

        return compact('ekonomi', 'pekerjaan', 'pendidikan', 'rumah', 'aset', 'bantuan');
    }

    public function daftarLayak(int $periodeId): Builder
    {
        return DB::table('penilaian_kesejahteraan as pk')
            ->join('pendataan as p', 'p.id', '=', 'pk.pendataan_id')
            ->join('keluarga as k', 'k.id', '=', 'p.keluarga_id')
            ->join('kategori_kesejahteraan as c', function ($join): void {
                $join->on('pk.skor', '>=', 'c.skor_min')
                    ->on('pk.skor', '<=', 'c.skor_max');
            })
            ->join('rt', 'rt.id', '=', 'k.rt_id')
            ->join('rw', 'rw.id', '=', 'rt.rw_id')
            ->join('dusun as d', 'd.id', '=', 'rw.dusun_id')
            ->leftJoin('anggota_keluarga as a', function ($join): void {
                $join->on('a.keluarga_id', '=', 'k.id')
                    ->where('a.hubungan_keluarga', '=', 'kepala');
            })
            ->where('p.periode_id', $periodeId)
            ->where('c.layak_bantuan', true)
            ->select([
                'p.id as pendataan_id',
                'k.id as keluarga_id',
                'k.no_kk',
                'k.alamat',
                'a.nama as kepala_keluarga',
                'd.nama_dusun',
                'rw.no_rw',
                'rt.no_rt',
                'pk.skor',
                'c.nama_kategori',
                'p.tgl_pendataan',
            ]);
    }

    public function rekapWilayah(int $periodeId, string $tingkat, ?int $dusunId = null): Collection
    {
        $survey = DB::table('pendataan')
            ->where('periode_id', $periodeId)
            ->select('keluarga_id')
            ->distinct();
        $layak = $this->keluargaLayak($periodeId);

        $query = DB::table('dusun as d')
            ->leftJoin('rw', 'rw.dusun_id', '=', 'd.id')
            ->leftJoin('rt', 'rt.rw_id', '=', 'rw.id')
            ->leftJoin('keluarga as k', 'k.rt_id', '=', 'rt.id')
            ->leftJoinSub($survey, 's', 's.keluarga_id', '=', 'k.id')
            ->leftJoinSub($layak, 'l', 'l.keluarga_id', '=', 'k.id')
            ->when($dusunId, fn (Builder $builder) => $builder->where('d.id', $dusunId))
            ->select('d.nama_dusun')
            ->selectRaw('COUNT(DISTINCT k.id) AS total_keluarga')
            ->selectRaw('COUNT(DISTINCT s.keluarga_id) AS sudah_didata')
            ->selectRaw('COUNT(DISTINCT l.keluarga_id) AS layak_bantuan');

        if ($tingkat === 'rw') {
            $query->addSelect('rw.no_rw')->groupBy('d.id', 'd.nama_dusun', 'rw.id', 'rw.no_rw');
        } elseif ($tingkat === 'rt') {
            $query->addSelect('rw.no_rw', 'rt.no_rt')->groupBy('d.id', 'd.nama_dusun', 'rw.id', 'rw.no_rw', 'rt.id', 'rt.no_rt');
        } else {
            $query->groupBy('d.id', 'd.nama_dusun');
        }

        $query->orderBy('d.nama_dusun');

        if ($tingkat !== 'dusun') {
            $query->orderBy('rw.no_rw');
        }

        if ($tingkat === 'rt') {
            $query->orderBy('rt.no_rt');
        }

        return $query->get();
    }

    public function daftarDusun(): Collection
    {
        return DB::table('dusun')->orderBy('nama_dusun')->get(['id', 'nama_dusun']);
    }

    public function kepalaKeluarga(int $pendataanId): ?object
    {
        return $this->daftarLayakForAllPeriods()
            ->where('p.id', $pendataanId)
            ->first();
    }

    private function daftarLayakForAllPeriods(): Builder
    {
        return DB::table('penilaian_kesejahteraan as pk')
            ->join('pendataan as p', 'p.id', '=', 'pk.pendataan_id')
            ->join('keluarga as k', 'k.id', '=', 'p.keluarga_id')
            ->join('kategori_kesejahteraan as c', function ($join): void {
                $join->on('pk.skor', '>=', 'c.skor_min')
                    ->on('pk.skor', '<=', 'c.skor_max');
            })
            ->join('rt', 'rt.id', '=', 'k.rt_id')
            ->join('rw', 'rw.id', '=', 'rt.rw_id')
            ->join('dusun as d', 'd.id', '=', 'rw.dusun_id')
            ->leftJoin('anggota_keluarga as a', function ($join): void {
                $join->on('a.keluarga_id', '=', 'k.id')
                    ->where('a.hubungan_keluarga', '=', 'kepala');
            })
            ->where('c.layak_bantuan', true)
            ->select([
                'p.id as pendataan_id',
                'p.periode_id',
                'k.id as keluarga_id',
                'k.no_kk',
                'k.alamat',
                'a.nama as kepala_keluarga',
                'd.nama_dusun',
                'rw.no_rw',
                'rt.no_rt',
                'pk.skor',
                'pk.catatan',
                'c.nama_kategori',
                'p.tgl_pendataan',
            ]);
    }

    private function jumlahPerKategori(int $periodeId): Collection
    {
        $jumlah = DB::table('penilaian_kesejahteraan as pk')
            ->join('pendataan as p', 'p.id', '=', 'pk.pendataan_id')
            ->join('kategori_kesejahteraan as c', function ($join): void {
                $join->on('pk.skor', '>=', 'c.skor_min')
                    ->on('pk.skor', '<=', 'c.skor_max');
            })
            ->where('p.periode_id', $periodeId)
            ->select('c.id')
            ->selectRaw('COUNT(DISTINCT p.keluarga_id) AS jumlah')
            ->groupBy('c.id');

        return DB::table('kategori_kesejahteraan as c')
            ->leftJoinSub($jumlah, 'j', 'j.id', '=', 'c.id')
            ->orderBy('c.skor_min')
            ->get(['c.nama_kategori as label', 'c.layak_bantuan', DB::raw('COALESCE(j.jumlah, 0) as jumlah')]);
    }

    private function progresPerDusun(int $periodeId): Collection
    {
        return DB::table('dusun as d')
            ->leftJoin('rw', 'rw.dusun_id', '=', 'd.id')
            ->leftJoin('rt', 'rt.rw_id', '=', 'rw.id')
            ->leftJoin('keluarga as k', 'k.rt_id', '=', 'rt.id')
            ->leftJoin('pendataan as p', function ($join) use ($periodeId): void {
                $join->on('p.keluarga_id', '=', 'k.id')
                    ->where('p.periode_id', '=', $periodeId);
            })
            ->groupBy('d.id', 'd.nama_dusun')
            ->orderBy('d.nama_dusun')
            ->get([
                'd.nama_dusun as label',
                DB::raw('COUNT(DISTINCT k.id) as total'),
                DB::raw('COUNT(DISTINCT p.keluarga_id) as terdata'),
            ])
            ->map(function (object $dusun): object {
                $dusun->persen = $dusun->total > 0
                    ? (int) round($dusun->terdata / $dusun->total * 100)
                    : 0;

                return $dusun;
            });
    }

    private function keluargaLayak(int $periodeId): Builder
    {
        return DB::table('penilaian_kesejahteraan as pk')
            ->join('pendataan as p', 'p.id', '=', 'pk.pendataan_id')
            ->join('kategori_kesejahteraan as c', function ($join): void {
                $join->on('pk.skor', '>=', 'c.skor_min')
                    ->on('pk.skor', '<=', 'c.skor_max');
            })
            ->where('p.periode_id', $periodeId)
            ->where('c.layak_bantuan', true)
            ->select('p.keluarga_id')
            ->distinct();
    }

    private function distribusiKolom(string $tabel, string $kolom, int $periodeId): Collection
    {
        return DB::table($tabel.' as x')
            ->join('pendataan as p', 'p.id', '=', 'x.pendataan_id')
            ->where('p.periode_id', $periodeId)
            ->selectRaw("COALESCE(x.{$kolom}, 'Belum diisi') AS label")
            ->selectRaw('COUNT(*) AS jumlah')
            ->groupBy('x.'.$kolom)
            ->orderByDesc('jumlah')
            ->get();
    }
}
