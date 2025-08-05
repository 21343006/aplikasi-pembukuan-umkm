<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Input Modal Awal</h1>
        </div>

        <section class="section mt-5">
            <div class="row justify-content-center"> {{-- Tengah --}}
                <div class="col-md-6"> {{-- Lebar Form --}}
                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-body pt-4">
                            <form wire:submit.prevent="save">
                                <div class="mb-3">
                                    <label for="modal_awal" class="form-label">Modal Awal</label>
                                    <input type="number" step="0.01" wire:model="modal_awal" class="form-control"
                                        id="modal_awal">
                                    @error('modal_awal')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="text-end">
                                    <button type="submit" class="btn btn-primary">Simpan</button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <table class="table table-hover mt-4">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Modal Awal</th>
                                <th>Tanggal Input</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($capitals as $index => $capital)
                                <tr>
                                    <td>{{ $index + 1 }}</td>
                                    <td>Rp {{ number_format($capital->modal_awal, 0, ',', '.') }}</td>
                                    <td>{{ $capital->created_at->format('d M Y H:i') }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>



                </div>
            </div>
        </section>
    </main>
</div>
