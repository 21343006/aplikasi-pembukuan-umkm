<div>
    <main id="main" class="main">
        <div class="pagetitle">
            <h1>Input Laporan Harian</h1>
        </div>

        <section class="section mt-3">
            <div class="row justify-content-center">
                <div class="col-md-6">

                    @if (session()->has('success'))
                        <div class="alert alert-success">
                            {{ session('success') }}
                        </div>
                    @endif

                    <div class="card">
                        <div class="card-body pt-4">
                            <form wire:submit.prevent="save">
                                <div class="mb-3">
                                    <label for="tanggal" class="form-label">Tanggal</label>
                                    <input type="date" wire:model="tanggal" class="form-control" id="tanggal">
                                    @error('tanggal')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="keterangan" class="form-label">Keterangan</label>
                                    <input type="text" wire:model="keterangan" class="form-control" id="keterangan">
                                    @error('keterangan')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="uang_masuk" class="form-label">Uang Masuk</label>
                                    <input type="number" wire:model="uang_masuk" class="form-control" id="uang_masuk"
                                        step="0.01">
                                    @error('uang_masuk')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="mb-3">
                                    <label for="uang_keluar" class="form-label">Uang Keluar</label>
                                    <input type="number" wire:model="uang_keluar" class="form-control" id="uang_keluar"
                                        step="0.01">
                                    @error('uang_keluar')
                                        <small class="text-danger">{{ $message }}</small>
                                    @enderror
                                </div>

                                <div class="row mb-3">
                                    <div class="col-sm-10">
                                        <button type="submit" class="btn btn-primary">Tambah</button>
                                        <a href="/reports" class="btn btn-secondary">Batal</a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
        </section>
    </main>
</div>
