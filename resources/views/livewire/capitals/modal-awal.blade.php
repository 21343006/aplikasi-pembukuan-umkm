<div>
    <main id="main" class="main">

        <div class="pagetitle">
            <h1>Modal</h1>
        </div><!-- End Page Title -->

        <section class="section">
            <div class="row">
                <div class="col-lg-12">

                    <div class="card">
                        <div class="card-body">
                            <div class="card-title">
                                <div class="row">
                                    <div class="col-6">
                                        <h5 class="card-title">Modal Awal</h5>
                                    </div>
                                    <div class="col-6">
                                        <a wire:navigate href="/capitals/create"
                                            class="btn btn-primary float-end">Tambah Modal Awal</a>
                                    </div>
                                </div>
                            </div>
                            <h1>Modal Awal</h1>
                            <table class="table table-hover">
                                <thead>
                                    <tr>
                                        <th scope="col">Nama</th>
                                        <th scope="col">Tanggal</th>
                                        <th scope="col">Keperluan</th>
                                        <th scope="col">Keterangan</th>
                                        <th scope="col">Nominal</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($capitals as $Capital)
                                        <tr>
                                            <td>{{ $Capital->nama }}</td>
                                            <td>{{ $Capital->tanggal }}</td>
                                            <td>{{ $Capital->keperluan }}</td>
                                            <td>{{ $Capital->keterangan }}</td>
                                            <td>{{ $Capital->nominal }}</td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <nav aria-label="...">
                        <ul class="pagination">
                            <li class="page-item disabled">
                                <a class="page-link" href="#" tabindex="-1" aria-disabled="true">Previous</a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">1</a></li>
                            <li class="page-item active" aria-current="page">
                                <a class="page-link" href="#">2</a>
                            </li>
                            <li class="page-item"><a class="page-link" href="#">3</a></li>
                            <li class="page-item">
                                <a class="page-link" href="#">Next</a>
                            </li>
                        </ul>
                    </nav>
                    <p>Total Modal: Rp {{ number_format($jumlah, 0, ',', '.') }}</p>

                    <a href="/dashboard" class="btn btn-secondary mt-2">
                        Kembali
                    </a>
                </div>
            </div>
        </section>

    </main><!-- End #main -->

</div>
