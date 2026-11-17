<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\JenisAset;
use App\Models\JenisBantuan;
use App\Models\JenisUsaha;
use App\Models\KategoriKesejahteraan;
use App\Models\Pekerjaan;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class DataMasterController extends Controller
{
    private const MASTER = [
        'pekerjaan' => [Pekerjaan::class, 'nama_pekerjaan', 'Pekerjaan'],
        'usaha' => [JenisUsaha::class, 'nama_jenis', 'Jenis Usaha'],
        'aset' => [JenisAset::class, 'nama_aset', 'Jenis Aset'],
        'bantuan' => [JenisBantuan::class, 'nama_bantuan', 'Jenis Bantuan'],
        'kategori' => [KategoriKesejahteraan::class, 'nama_kategori', 'Kategori Kesejahteraan'],
    ];

    public function index(): View
    {
        return view('admin.master', [
            'pekerjaan' => Pekerjaan::query()->orderBy('nama_pekerjaan')->get(),
            'usaha' => JenisUsaha::query()->orderBy('nama_jenis')->get(),
            'aset' => JenisAset::query()->orderBy('kategori')->orderBy('nama_aset')->get(),
            'bantuan' => JenisBantuan::query()->orderBy('nama_bantuan')->get(),
            'kategori' => KategoriKesejahteraan::query()->orderBy('skor_min')->get(),
        ]);
    }

    public function store(Request $request, string $jenis): RedirectResponse
    {
        [$model, $kolom] = $this->konfigurasi($jenis);
        $data = $this->validasi($request, $jenis, $kolom);
        $model::query()->create($data);

        return back()->with('status', 'Data master berhasil ditambahkan.');
    }

    public function update(Request $request, string $jenis, int $id): RedirectResponse
    {
        [$model, $kolom] = $this->konfigurasi($jenis);
        $item = $model::query()->findOrFail($id);
        $item->update($this->validasi($request, $jenis, $kolom, $item));

        return back()->with('status', 'Data master berhasil diperbarui.');
    }

    public function destroy(string $jenis, int $id): RedirectResponse
    {
        [$model] = $this->konfigurasi($jenis);
        $item = $model::query()->findOrFail($id);
        $sedangDipakai = match ($jenis) {
            'pekerjaan' => $item->anggotaDenganPekerjaanUtama()->exists()
                || $item->anggotaDenganPekerjaanSampingan()->exists(),
            'usaha' => $item->dataEkonomi()->exists(),
            'aset' => $item->asetKeluarga()->exists(),
            'bantuan' => $item->bantuanSosial()->exists(),
            default => false,
        };

        if ($sedangDipakai) {
            return back()->withErrors(['master' => 'Data master sedang digunakan dan tidak dapat dihapus.']);
        }

        $item->delete();

        return back()->with('status', 'Data master berhasil dihapus.');
    }

    private function konfigurasi(string $jenis): array
    {
        abort_unless(isset(self::MASTER[$jenis]), 404);

        return self::MASTER[$jenis];
    }

    private function validasi(Request $request, string $jenis, string $kolom, ?object $item = null): array
    {
        $rules = [
            $kolom => ['required', 'string', 'max:100'],
        ];

        if ($jenis === 'aset') {
            $rules += [
                'kategori' => ['required', 'string', 'max:50'],
                'satuan' => ['required', 'string', 'max:30'],
            ];
        } elseif ($jenis === 'bantuan') {
            $rules += [
                'kode' => ['required', 'string', 'max:20', Rule::unique('jenis_bantuan', 'kode')->ignore($item?->id)],
            ];
        } elseif ($jenis === 'kategori') {
            $rules += [
                'skor_min' => ['required', 'numeric', 'min:0'],
                'skor_max' => ['required', 'numeric', 'gte:skor_min'],
                'layak_bantuan' => ['sometimes', 'boolean'],
            ];
        }

        $data = $request->validate($rules);
        if ($jenis === 'kategori') {
            $data['layak_bantuan'] = $request->boolean('layak_bantuan');
        }

        return $data;
    }
}
