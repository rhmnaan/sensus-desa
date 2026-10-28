@extends('layouts.app')

@section('judul', $judul ?? 'Halaman')

@section('isi')
<div class="kartu kosong">
    <h2 style="margin-bottom:6px">{{ $judul ?? 'Halaman' }}</h2>
    <p style="margin:0">Halaman ini belum dibuat. Akan dibuat pada tahap berikutnya.</p>
</div>
@endsection