<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\KategoriKesejahteraan;
use App\Models\PenilaianKesejahteraan;
use Illuminate\Contracts\View\View;

class PenilaianController extends Controller
{
    public function index(): View
    {
        return view('admin.penilaian', [
            'kategori' => KategoriKesejahteraan::query()->orderBy('skor_min')->get(),
            'jumlahHasil' => PenilaianKesejahteraan::query()->count(),
        ]);
    }
}
