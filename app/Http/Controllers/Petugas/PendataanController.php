<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\AsetKeluarga;
use App\Models\BantuanSosial;
use App\Models\DataEkonomi;
use App\Models\JenisAset;
use App\Models\JenisBantuan;
use App\Models\JenisUsaha;
use App\Models\Keluarga;
use App\Models\KondisiRumah;
use App\Models\Pendataan;
use App\Models\PeriodeSensus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PendataanController extends Controller
{
    public function create(Request $request): View|RedirectResponse
    {
        $periode = PeriodeSensus::berjalan();

        if (! $request->filled('keluarga_id')) {
            $filters = $request->validate([
                'cari' => ['nullable', 'string', 'max:100'],
            ]);
            $cari = trim($filters['cari'] ?? '');
            $keluarga = Keluarga::query()
                ->with(['kepalaKeluarga:id,keluarga_id,nama', 'rt.rw:id,no_rw'])
                ->withCount([
                    'pendataan as sudah_didata' => fn (Builder $query) => $query->where('periode_id', $periode?->id ?? 0),
                    'anggota',
                ]);

            if ($cari !== '') {
                $keluarga->where(function (Builder $query) use ($cari): void {
                    $query->where('no_kk', 'like', "%{$cari}%")
                        ->orWhereHas('anggota', function (Builder $anggota) use ($cari): void {
                            $anggota->where('nama', 'like', "%{$cari}%")
                                ->orWhere('nik', 'like', "%{$cari}%");
                        });
                });
            }

            return view('petugas.pendataan-pilih', [
                'keluarga' => $keluarga->orderBy('no_kk')->paginate(8)->withQueryString(),
                'periode' => $periode,
                'cari' => $cari,
            ]);
        }

        $data = $request->validate([
            'keluarga_id' => ['required', 'integer', Rule::exists('keluarga', 'id')],
        ]);

        if (! $periode) {
            return redirect('/petugas/pendataan')
                ->withErrors(['periode' => 'Belum ada periode sensus yang sedang berjalan.']);
        }

        $keluarga = Keluarga::query()
            ->with(['kepalaKeluarga:id,keluarga_id,nama', 'rt.rw'])
            ->findOrFail($data['keluarga_id']);
        $pendataan = Pendataan::query()
            ->with(['dataEkonomi', 'kondisiRumah', 'asetKeluarga', 'bantuanSosial'])
            ->where('keluarga_id', $keluarga->id)
            ->where('periode_id', $periode->id)
            ->first();

        return view('petugas.pendataan', [
            'keluarga' => $keluarga,
            'periode' => $periode,
            'pendataan' => $pendataan,
            'ekonomi' => $pendataan?->dataEkonomi,
            'kondisiRumahTersimpan' => $pendataan?->kondisiRumah,
            'asetTersimpan' => $pendataan?->asetKeluarga ?? collect(),
            'bantuanTersimpan' => $pendataan?->bantuanSosial ?? collect(),
            'jenisUsaha' => JenisUsaha::query()->orderBy('nama_jenis')->pluck('nama_jenis', 'id'),
            'jenisAset' => JenisAset::query()->orderBy('kategori')->orderBy('nama_aset')->get()->groupBy('kategori'),
            'jenisBantuan' => JenisBantuan::query()->orderBy('nama_bantuan')->pluck('nama_bantuan', 'id'),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $periode = PeriodeSensus::berjalan();

        if (! $periode) {
            return redirect('/petugas/keluarga')
                ->withErrors(['periode' => 'Belum ada periode sensus yang sedang berjalan.']);
        }

        $data = $request->validate([
            'keluarga_id' => ['required', 'integer', Rule::exists('keluarga', 'id')],
            'penghasilan_bulanan' => ['required', 'numeric', 'min:0'],
            'pengeluaran_makan' => ['required', 'numeric', 'min:0'],
            'pengeluaran_pendidikan' => ['required', 'numeric', 'min:0'],
            'pengeluaran_listrik_air' => ['required', 'numeric', 'min:0'],
            'pengeluaran_kesehatan' => ['required', 'numeric', 'min:0'],
            'pengeluaran_lainnya' => ['required', 'numeric', 'min:0'],
            'jumlah_tabungan' => ['required', 'numeric', 'min:0'],
            'jumlah_utang' => ['required', 'numeric', 'min:0'],
            'jenis_usaha_id' => ['nullable', 'integer', Rule::exists('jenis_usaha', 'id')],
            'omzet_bulanan' => ['required', 'numeric', 'min:0'],
            'jumlah_tenaga_kerja' => ['required', 'integer', 'min:0'],
            'status_kepemilikan' => ['required', Rule::in(['milik_sendiri', 'sewa', 'menumpang', 'dinas'])],
            'jenis_lantai' => ['required', Rule::in(['tanah', 'semen', 'keramik', 'lainnya'])],
            'jenis_dinding' => ['required', Rule::in(['bambu', 'kayu', 'tembok', 'lainnya'])],
            'jenis_atap' => ['required', Rule::in(['rumbia', 'seng', 'genteng', 'beton', 'lainnya'])],
            'luas_lantai' => ['nullable', 'numeric', 'min:0'],
            'sumber_air' => ['required', Rule::in(['sumur', 'mata_air', 'pdam', 'sungai', 'lainnya'])],
            'sumber_penerangan' => ['required', Rule::in(['listrik_pln', 'listrik_non_pln', 'non_listrik'])],
            'bahan_bakar_masak' => ['required', Rule::in(['kayu', 'minyak_tanah', 'lpg', 'listrik', 'lainnya'])],
            'fasilitas_mck' => ['required', Rule::in(['sendiri', 'bersama', 'umum', 'tidak_ada'])],
            'aset' => ['nullable', 'array', 'max:100'],
            'aset.*.jenis_aset_id' => ['required', 'integer', 'distinct', Rule::exists('jenis_aset', 'id')],
            'aset.*.jumlah' => ['required', 'numeric', 'min:0'],
            'aset.*.keterangan' => ['nullable', 'string', 'max:255'],
            'bantuan' => ['nullable', 'array', 'max:100'],
            'bantuan.*.jenis_bantuan_id' => ['required', 'integer', Rule::exists('jenis_bantuan', 'id')],
            'bantuan.*.tahun_terima' => ['required', 'integer', 'min:2000', 'max:2100'],
            'bantuan.*.keterangan' => ['nullable', 'string', 'max:255'],
        ]);

        $bantuanKeys = collect($data['bantuan'] ?? [])
            ->map(fn (array $bantuan): string => $bantuan['jenis_bantuan_id'].'-'.$bantuan['tahun_terima']);
        if ($bantuanKeys->duplicates()->isNotEmpty()) {
            throw ValidationException::withMessages([
                'bantuan' => 'Jenis bantuan dan tahun penerimaan tidak boleh dicatat dua kali.',
            ]);
        }

        $keluarga = Keluarga::query()->findOrFail($data['keluarga_id']);

        DB::transaction(function () use ($data, $keluarga, $periode): void {
            $pendataan = Pendataan::query()->firstOrNew([
                'keluarga_id' => $keluarga->id,
                'periode_id' => $periode->id,
            ]);
            $pendataan->fill([
                'petugas_id' => auth()->id(),
                'tgl_pendataan' => now()->toDateString(),
            ])->save();

            DataEkonomi::query()->updateOrCreate(
                ['pendataan_id' => $pendataan->id],
                Arr::only($data, [
                    'penghasilan_bulanan',
                    'pengeluaran_makan',
                    'pengeluaran_pendidikan',
                    'pengeluaran_listrik_air',
                    'pengeluaran_kesehatan',
                    'pengeluaran_lainnya',
                    'jumlah_tabungan',
                    'jumlah_utang',
                    'jenis_usaha_id',
                    'omzet_bulanan',
                    'jumlah_tenaga_kerja',
                ])
            );

            KondisiRumah::query()->updateOrCreate(
                ['pendataan_id' => $pendataan->id],
                Arr::only($data, [
                    'status_kepemilikan',
                    'jenis_lantai',
                    'jenis_dinding',
                    'jenis_atap',
                    'luas_lantai',
                    'sumber_air',
                    'sumber_penerangan',
                    'bahan_bakar_masak',
                    'fasilitas_mck',
                ])
            );

            AsetKeluarga::query()->where('pendataan_id', $pendataan->id)->delete();
            foreach ($data['aset'] ?? [] as $aset) {
                AsetKeluarga::query()->create([
                    'pendataan_id' => $pendataan->id,
                    ...$aset,
                ]);
            }

            BantuanSosial::query()->where('pendataan_id', $pendataan->id)->delete();
            foreach ($data['bantuan'] ?? [] as $bantuan) {
                BantuanSosial::query()->create([
                    'pendataan_id' => $pendataan->id,
                    ...$bantuan,
                ]);
            }
        });

        return redirect('/petugas/keluarga')
            ->with('status', 'Data pendataan keluarga berhasil disimpan.');
    }
}
