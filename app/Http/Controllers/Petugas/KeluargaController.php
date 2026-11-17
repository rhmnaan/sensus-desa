<?php

namespace App\Http\Controllers\Petugas;

use App\Http\Controllers\Controller;
use App\Models\Keluarga;
use App\Models\Pekerjaan;
use App\Models\PeriodeSensus;
use App\Models\Rt;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Arr;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class KeluargaController extends Controller
{
    public function create(): View
    {
        return $this->form(null);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasiKeluarga($request);

        $keluarga = DB::transaction(function () use ($data): Keluarga {
            $keluarga = Keluarga::query()->create([
                'no_kk' => $data['no_kk'],
                'alamat' => $data['alamat'] ?? null,
                'rt_id' => $data['rt_id'],
            ]);

            foreach ($data['anggota'] as $anggota) {
                $keluarga->anggota()->create([
                    'nik' => $anggota['nik'],
                    'nama' => $anggota['nama'],
                    'jenis_kelamin' => $anggota['jenis_kelamin'],
                    'tanggal_lahir' => $anggota['tanggal_lahir'],
                    'hubungan_keluarga' => $anggota['hubungan_keluarga'],
                    'status_perkawinan' => $anggota['status_perkawinan'],
                    'pendidikan_terakhir' => $anggota['pendidikan_terakhir'],
                    'status_sekolah' => $anggota['status_sekolah'],
                    'pekerjaan_utama_id' => $anggota['pekerjaan_utama_id'] ?? null,
                    'pekerjaan_sampingan_id' => $anggota['pekerjaan_sampingan_id'] ?? null,
                    'disabilitas' => $anggota['disabilitas'] ?? false,
                    'penyakit_kronis' => $anggota['penyakit_kronis'] ?? false,
                ]);
            }

            return $keluarga;
        });

        return redirect('/petugas/keluarga')
            ->with('status', "Keluarga dengan No. KK {$keluarga->no_kk} berhasil disimpan.");
    }

    public function edit(Keluarga $keluarga): View
    {
        $keluarga->load('anggota');

        return $this->form($keluarga);
    }

    public function update(Request $request, Keluarga $keluarga): RedirectResponse
    {
        $data = $this->validasiKeluarga($request, $keluarga);

        DB::transaction(function () use ($data, $keluarga): void {
            $keluarga->update(Arr::only($data, ['no_kk', 'alamat', 'rt_id']));
            $anggotaDipertahankan = [];

            foreach ($data['anggota'] as $isianAnggota) {
                $id = $isianAnggota['id'] ?? null;
                $kolomAnggota = Arr::except($isianAnggota, ['id']);

                if ($id) {
                    $anggota = $keluarga->anggota()->findOrFail($id);
                    $anggota->update($kolomAnggota);
                } else {
                    $anggota = $keluarga->anggota()->create($kolomAnggota);
                }

                $anggotaDipertahankan[] = $anggota->id;
            }

            $keluarga->anggota()->whereNotIn('id', $anggotaDipertahankan)->delete();
        });

        return redirect("/petugas/keluarga/{$keluarga->id}")
            ->with('status', "Data keluarga dengan No. KK {$keluarga->no_kk} berhasil diperbarui.");
    }

    public function show(Keluarga $keluarga): View
    {
        $keluarga->load([
            'rt.rw.dusun',
            'anggota.pekerjaanUtama',
            'anggota.pekerjaanSampingan',
            'pendataan.periode',
        ]);

        return view('petugas.keluarga-detail', compact('keluarga'));
    }

    public function index(Request $request): View
    {
        $filters = $request->validate([
            'cari' => ['nullable', 'string', 'max:100'],
            'rt_id' => ['nullable', 'integer', Rule::exists('rt', 'id')],
            'status' => ['nullable', Rule::in(['semua', 'belum', 'terdata'])],
        ]);

        $periode = PeriodeSensus::berjalan();
        $periodeId = $periode?->id ?? 0;
        $cari = trim($filters['cari'] ?? '');
        $status = $filters['status'] ?? 'semua';

        $query = Keluarga::query()
            ->with([
                'kepalaKeluarga:id,keluarga_id,nama,nik',
                'rt.rw:id,no_rw',
            ])
            ->withCount('anggota')
            ->withCount([
                'pendataan as sudah_didata' => fn (Builder $query) => $query->where('periode_id', $periodeId),
            ]);

        if ($cari !== '') {
            $query->where(function (Builder $query) use ($cari): void {
                $query->where('no_kk', 'like', "%{$cari}%")
                    ->orWhereHas('anggota', function (Builder $anggota) use ($cari): void {
                        $anggota->where('nama', 'like', "%{$cari}%")
                            ->orWhere('nik', 'like', "%{$cari}%");
                    });
            });
        }

        if (! empty($filters['rt_id'])) {
            $query->where('rt_id', $filters['rt_id']);
        }

        if ($status === 'terdata') {
            $query->whereHas('pendataan', fn (Builder $query) => $query->where('periode_id', $periodeId));
        } elseif ($status === 'belum') {
            $query->whereDoesntHave('pendataan', fn (Builder $query) => $query->where('periode_id', $periodeId));
        }

        $keluarga = $query->orderBy('no_kk')->paginate(8)->withQueryString();
        $wilayahRt = Rt::query()
            ->with('rw:id,no_rw')
            ->orderBy('no_rt')
            ->get(['id', 'rw_id', 'no_rt']);
        $statusMerge = DB::connection()->getDriverName() === 'sqlsrv'
            && filled(config('replication.merge_sync_job'))
            ? 'konfigurasi_ada'
            : 'belum_dikonfigurasi';

        return view('petugas.keluarga', [
            'keluarga' => $keluarga,
            'jumlahKeluarga' => Keluarga::count(),
            'wilayahRt' => $wilayahRt,
            'filters' => [
                'cari' => $cari,
                'rt_id' => $filters['rt_id'] ?? '',
                'status' => $status,
            ],
            'periode' => $periode,
            'statusMerge' => $statusMerge,
        ]);
    }

    private function form(?Keluarga $keluarga): View
    {
        $wilayahRt = Rt::query()
            ->with(['rw:id,no_rw,dusun_id', 'rw.dusun:id,nama_dusun'])
            ->orderBy('no_rt')
            ->get(['id', 'rw_id', 'no_rt']);

        $opsiRt = $wilayahRt->mapWithKeys(fn (Rt $rt): array => [
            $rt->id => trim("{$rt->rw?->dusun?->nama_dusun}, RW {$rt->rw?->no_rw}, RT {$rt->no_rt}", ', '),
        ]);

        $anggotaAwal = $keluarga?->anggota->map(fn ($anggota): array => [
            'id' => $anggota->id,
            'nik' => $anggota->nik,
            'nama' => $anggota->nama,
            'jenis_kelamin' => $anggota->jenis_kelamin,
            'tanggal_lahir' => $anggota->tanggal_lahir->format('Y-m-d'),
            'hubungan_keluarga' => $anggota->hubungan_keluarga,
            'status_perkawinan' => $anggota->status_perkawinan,
            'pendidikan_terakhir' => $anggota->pendidikan_terakhir,
            'status_sekolah' => $anggota->status_sekolah,
            'pekerjaan_utama_id' => $anggota->pekerjaan_utama_id,
            'pekerjaan_sampingan_id' => $anggota->pekerjaan_sampingan_id,
            'disabilitas' => $anggota->disabilitas,
            'penyakit_kronis' => $anggota->penyakit_kronis,
        ])->all() ?? [];

        return view('petugas.keluarga-tambah', [
            'keluarga' => $keluarga,
            'opsiRt' => $opsiRt,
            'anggotaForm' => old('anggota', $anggotaAwal),
            'pekerjaan' => Pekerjaan::query()->orderBy('nama_pekerjaan')->pluck('nama_pekerjaan', 'id'),
        ]);
    }

    /**
     * @return array<string, mixed>
     */
    private function validasiKeluarga(Request $request, ?Keluarga $keluarga = null): array
    {
        $rules = [
            'no_kk' => [
                'required',
                'digits:16',
                Rule::unique('keluarga', 'no_kk')->ignore($keluarga),
            ],
            'rt_id' => ['required', 'integer', Rule::exists('rt', 'id')],
            'alamat' => ['nullable', 'string'],
            'anggota' => ['required', 'array', 'min:1', 'max:50'],
            'anggota.*.nik' => ['required', 'digits:16', 'distinct'],
            'anggota.*.nama' => ['required', 'string', 'max:100'],
            'anggota.*.jenis_kelamin' => ['required', Rule::in(['L', 'P'])],
            'anggota.*.tanggal_lahir' => ['required', 'date', 'before_or_equal:today'],
            'anggota.*.hubungan_keluarga' => ['required', Rule::in(['kepala', 'istri', 'anak', 'orang_tua', 'famili', 'lainnya'])],
            'anggota.*.status_perkawinan' => ['required', Rule::in(['belum_kawin', 'kawin', 'cerai_hidup', 'cerai_mati'])],
            'anggota.*.pendidikan_terakhir' => ['required', Rule::in(['tidak_sekolah', 'sd', 'smp', 'sma', 'diploma', 'sarjana', 'pascasarjana'])],
            'anggota.*.status_sekolah' => ['required', Rule::in(['tidak_sekolah', 'masih_sekolah', 'tidak_sekolah_lagi'])],
            'anggota.*.pekerjaan_utama_id' => ['nullable', 'integer', Rule::exists('pekerjaan', 'id')],
            'anggota.*.pekerjaan_sampingan_id' => ['nullable', 'integer', Rule::exists('pekerjaan', 'id')],
            'anggota.*.disabilitas' => ['sometimes', 'boolean'],
            'anggota.*.penyakit_kronis' => ['sometimes', 'boolean'],
        ];

        if ($keluarga) {
            $rules['anggota.*.id'] = [
                'nullable',
                'integer',
                Rule::exists('anggota_keluarga', 'id')->where('keluarga_id', $keluarga->id),
            ];
        }

        $data = $request->validate($rules);

        foreach ($data['anggota'] as $indeks => $anggota) {
            $nikSudahDipakai = DB::table('anggota_keluarga')
                ->where('nik', $anggota['nik'])
                ->when(isset($anggota['id']), fn ($query) => $query->where('id', '!=', $anggota['id']))
                ->exists();

            if ($nikSudahDipakai) {
                throw ValidationException::withMessages([
                    "anggota.{$indeks}.nik" => 'NIK sudah digunakan oleh anggota keluarga lain.',
                ]);
            }
        }

        if (collect($data['anggota'])->where('hubungan_keluarga', 'kepala')->count() !== 1) {
            throw ValidationException::withMessages([
                'anggota' => 'Setiap keluarga harus memiliki tepat satu kepala keluarga.',
            ]);
        }

        return $data;
    }
}
