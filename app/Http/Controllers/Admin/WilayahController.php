<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Dusun;
use App\Models\Rt;
use App\Models\Rw;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class WilayahController extends Controller
{
    public function index(): View
    {
        return view('admin.wilayah', [
            'dusun' => Dusun::query()->withCount('rw')->orderBy('nama_dusun')->get(),
            'rw' => Rw::query()->with('dusun:id,nama_dusun')->withCount('rt')->orderBy('no_rw')->get(),
            'rt' => Rt::query()->with('rw.dusun')->withCount('keluarga')->orderBy('no_rt')->get(),
            'pilihanDusun' => Dusun::query()->orderBy('nama_dusun')->get(),
            'pilihanRw' => Rw::query()->with('dusun')->orderBy('no_rw')->get(),
        ]);
    }

    public function storeDusun(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'nama_dusun' => ['required', 'string', 'max:100', Rule::unique('dusun', 'nama_dusun')],
        ]);
        Dusun::query()->create($data);

        return back()->with('status', 'Dusun berhasil ditambahkan.');
    }

    public function updateDusun(Request $request, Dusun $dusun): RedirectResponse
    {
        $data = $request->validate([
            'nama_dusun' => ['required', 'string', 'max:100', Rule::unique('dusun', 'nama_dusun')->ignore($dusun)],
        ]);
        $dusun->update($data);

        return back()->with('status', 'Dusun berhasil diperbarui.');
    }

    public function deleteDusun(Dusun $dusun): RedirectResponse
    {
        if ($dusun->rw()->exists()) {
            return back()->withErrors(['wilayah' => 'Dusun masih memiliki RW dan tidak dapat dihapus.']);
        }
        $dusun->delete();

        return back()->with('status', 'Dusun berhasil dihapus.');
    }

    public function storeRw(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'dusun_id' => ['required', 'integer', Rule::exists('dusun', 'id')],
            'no_rw' => ['required', 'string', 'max:10'],
        ]);
        if (Rw::query()->where('dusun_id', $data['dusun_id'])->where('no_rw', $data['no_rw'])->exists()) {
            return back()->withErrors(['no_rw' => 'Nomor RW sudah terdaftar pada dusun tersebut.'])->withInput();
        }
        Rw::query()->create($data);

        return back()->with('status', 'RW berhasil ditambahkan.');
    }

    public function updateRw(Request $request, Rw $rw): RedirectResponse
    {
        $data = $request->validate([
            'dusun_id' => ['required', 'integer', Rule::exists('dusun', 'id')],
            'no_rw' => ['required', 'string', 'max:10'],
        ]);
        if (Rw::query()->where('dusun_id', $data['dusun_id'])->where('no_rw', $data['no_rw'])->where('id', '!=', $rw->id)->exists()) {
            return back()->withErrors(['no_rw' => 'Nomor RW sudah terdaftar pada dusun tersebut.'])->withInput();
        }
        $rw->update($data);

        return back()->with('status', 'RW berhasil diperbarui.');
    }

    public function deleteRw(Rw $rw): RedirectResponse
    {
        if ($rw->rt()->exists()) {
            return back()->withErrors(['wilayah' => 'RW masih memiliki RT dan tidak dapat dihapus.']);
        }
        $rw->delete();

        return back()->with('status', 'RW berhasil dihapus.');
    }

    public function storeRt(Request $request): RedirectResponse
    {
        $data = $request->validate([
            'rw_id' => ['required', 'integer', Rule::exists('rw', 'id')],
            'no_rt' => ['required', 'string', 'max:10'],
        ]);
        if (Rt::query()->where('rw_id', $data['rw_id'])->where('no_rt', $data['no_rt'])->exists()) {
            return back()->withErrors(['no_rt' => 'Nomor RT sudah terdaftar pada RW tersebut.'])->withInput();
        }
        Rt::query()->create($data);

        return back()->with('status', 'RT berhasil ditambahkan.');
    }

    public function updateRt(Request $request, Rt $rt): RedirectResponse
    {
        $data = $request->validate([
            'rw_id' => ['required', 'integer', Rule::exists('rw', 'id')],
            'no_rt' => ['required', 'string', 'max:10'],
        ]);
        if (Rt::query()->where('rw_id', $data['rw_id'])->where('no_rt', $data['no_rt'])->where('id', '!=', $rt->id)->exists()) {
            return back()->withErrors(['no_rt' => 'Nomor RT sudah terdaftar pada RW tersebut.'])->withInput();
        }
        $rt->update($data);

        return back()->with('status', 'RT berhasil diperbarui.');
    }

    public function deleteRt(Rt $rt): RedirectResponse
    {
        if ($rt->keluarga()->exists()) {
            return back()->withErrors(['wilayah' => 'RT masih digunakan oleh keluarga dan tidak dapat dihapus.']);
        }
        $rt->delete();

        return back()->with('status', 'RT berhasil dihapus.');
    }
}
