<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PeriodeSensus;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class PeriodeController extends Controller
{
    public function index(): View
    {
        return view('admin.periode', [
            'periode' => PeriodeSensus::query()->orderByDesc('tahun')->paginate(10),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validasi($request);
        $this->simpanStatusBerjalan(fn () => PeriodeSensus::query()->create($data));

        return back()->with('status', 'Periode sensus berhasil ditambahkan.');
    }

    public function update(Request $request, PeriodeSensus $periode): RedirectResponse
    {
        $data = $this->validasi($request);
        $this->simpanStatusBerjalan(fn () => $periode->update($data), $periode);

        return back()->with('status', 'Periode sensus berhasil diperbarui.');
    }

    private function validasi(Request $request): array
    {
        return $request->validate([
            'nama' => ['required', 'string', 'max:100'],
            'tahun' => ['required', 'integer', 'min:2000', 'max:2100'],
            'tgl_mulai' => ['required', 'date'],
            'tgl_selesai' => ['required', 'date', 'after_or_equal:tgl_mulai'],
            'status' => ['required', Rule::in(['draft', 'berjalan', 'selesai'])],
        ]);
    }

    private function simpanStatusBerjalan(callable $simpan, ?PeriodeSensus $periode = null): void
    {
        DB::transaction(function () use ($simpan, $periode): void {
            if ($periode) {
                PeriodeSensus::query()->whereKey($periode->id)->lockForUpdate()->firstOrFail();
            }

            $simpan();

            if (PeriodeSensus::query()->where('status', 'berjalan')->count() > 1) {
                throw ValidationException::withMessages([
                    'status' => 'Masih ada periode lain yang berjalan. Selesaikan periode tersebut sebelum mengaktifkan periode baru.',
                ]);
            }
        });
    }
}
