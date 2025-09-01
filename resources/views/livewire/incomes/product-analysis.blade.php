<div class="card">
    <div class="card-body">
        <h5 class="card-title">Analisis Produk Terlaris & Paling Menguntungkan</h5>

        @if (!empty($analyzedProducts))
            <ul class="list-group list-group-flush">
                @foreach ($analyzedProducts as $produk => $data)
                    <li class="list-group-item d-sm-flex justify-content-sm-between align-items-sm-center">
                        <div class="mb-2 mb-sm-0">
                            <strong>{{ $produk }}</strong><br>
                            <small class="text-muted">
                                Terjual: {{ number_format($data['total_terjual'], 0, ',', '.') }} unit | Laba: Rp {{ number_format($data['total_laba'], 0, ',', '.') }}
                            </small>
                        </div>
                        <div>
                            @foreach ($data['categories'] as $category)
                                @if ($category == 'terlaris')
                                    <span class="badge bg-primary">Terlaris</span>
                                @elseif ($category == 'menguntungkan')
                                    <span class="badge bg-success">Paling Menguntungkan</span>
                                @endif
                            @endforeach
                        </div>
                    </li>
                @endforeach
            </ul>
        @else
            <div class="alert alert-info mt-3">
                Tidak ada data produk untuk dianalisis pada periode ini.
            </div>
        @endif
    </div>
</div>