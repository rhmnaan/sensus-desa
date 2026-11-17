<section class="kartu">
    <h2>{{ $judul }}</h2>
    @php($maksimum = max(1, (int) collect($data)->max('jumlah')))
    @forelse ($data as $item)
    <div class="baris-bar">
        <div class="judul">
            <span>{{ $item->label }}</span>
            <span>{{ number_format($item->jumlah) }}</span>
        </div>
        <div class="bar"><span style="width: {{ min(100, (int) round($item->jumlah / $maksimum * 100)) }}%"></span></div>
    </div>
    @empty
    <p class="ket-kecil">Belum ada data untuk ditampilkan.</p>
    @endforelse
</section>
