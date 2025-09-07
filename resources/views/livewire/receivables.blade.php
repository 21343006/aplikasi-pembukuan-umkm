<div>
    <div class="card">
        <div class="card-body">
            <div class="d-flex justify-content-between align-items-center mb-3">
                <h5 class="card-title">Kelola Piutang Usaha</h5>
                <button wire:click="create" class="btn btn-success">
                    <i class="bi bi-plus-circle me-1"></i>
                    Tambah Piutang
                </button>
            </div>

            <!-- Statistics Cards -->
            <div class="row mb-4">
                <div class="col-xl-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Total Piutang</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-currency-dollar"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>Rp {{ number_format($totalReceivables, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card info-card customers-card">
                        <div class="card-body">
                            <h5 class="card-title">Sudah Diterima</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-check-circle"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>Rp {{ number_format($totalPaid, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card info-card revenue-card">
                        <div class="card-body">
                            <h5 class="card-title">Belum Diterima</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-exclamation-triangle"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>Rp {{ number_format($totalRemaining, 0, ',', '.') }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="col-xl-3 col-md-6">
                    <div class="card info-card sales-card">
                        <div class="card-body">
                            <h5 class="card-title">Jatuh Tempo</h5>
                            <div class="d-flex align-items-center">
                                <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                    <i class="bi bi-clock"></i>
                                </div>
                                <div class="ps-3">
                                    <h6>{{ $overdueCount }}</h6>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Flash Message -->
            @if (session()->has('message'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('message') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Table -->
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead>
                        <tr>
                            <th scope="col">Debitur</th>
                            <th scope="col">Deskripsi</th>
                            <th scope="col">Jumlah</th>
                            <th scope="col">Diterima</th>
                            <th scope="col">Tanggal Diterima</th>
                            <th scope="col">Jatuh Tempo</th>
                            <th scope="col">Status</th>
                            <th scope="col">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($receivables as $receivable)
                            <tr class="{{ $receivable->is_overdue ? 'table-danger' : '' }}">
                                <td>
                                    <strong>{{ $receivable->debtor_name }}</strong>
                                </td>
                                <td>{{ Str::limit($receivable->description, 50) }}</td>
                                <td>
                                    <strong>Rp {{ number_format($receivable->amount, 0, ',', '.') }}</strong>
                                </td>
                                <td>
                                    @if($receivable->paid_amount && $receivable->paid_amount > 0)
                                        <strong class="text-success">Rp {{ number_format($receivable->paid_amount, 0, ',', '.') }}</strong>
                                    @else
                                        <span class="text-muted">Belum Diterima</span>
                                    @endif
                                </td>
                                <td>
                                    @if($receivable->paid_date)
                                        <span class="text-success">{{ $receivable->paid_date->format('d/m/Y') }}</span>
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td>
                                    {{ $receivable->due_date->format('d/m/Y') }}
                                    @if($receivable->is_overdue)
                                        <br><small class="text-danger">Terlambat {{ $receivable->days_overdue }} hari</small>
                                    @endif
                                </td>
                                <td>
                                    <span class="badge {{ $receivable->status_badge_class }}">{{ $receivable->status_text }}</span>
                                </td>
                                <td>
                                    <div class="btn-group" role="group">
                                        @if($receivable->status !== 'paid')
                                            <button wire:click="showPayment({{ $receivable->id }})" class="btn btn-sm btn-success" title="Catat Penerimaan">
                                                <i class="bi bi-cash-coin"></i>
                                            </button>
                                        @endif
                                        <button wire:click="edit({{ $receivable->id }})" class="btn btn-sm btn-primary" title="Edit">
                                            <i class="bi bi-pencil"></i>
                                        </button>
                                        <button wire:click="delete({{ $receivable->id }})" onclick="return confirm('Yakin ingin menghapus piutang ini?')" class="btn btn-sm btn-danger" title="Hapus">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center text-muted">
                                    <i class="bi bi-inbox fs-1 d-block mb-2"></i>
                                    Belum ada data piutang
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            <!-- Pagination -->
            <div class="d-sm-flex justify-content-sm-between align-items-sm-center mt-3">
                <div class="text-muted text-center text-sm-start mb-2 mb-sm-0">
                    <small>
                        Menampilkan {{ $receivables->firstItem() ?? 0 }}
                        sampai {{ $receivables->lastItem() ?? 0 }}
                        dari {{ $receivables->total() ?? 0 }} data
                    </small>
                </div>

                {{-- Custom Pagination Links --}}
                @if ($receivables->hasPages())
                    <nav aria-label="Pagination Navigation" class="d-flex justify-content-center">
                        <ul class="pagination pagination-sm mb-0">
                            {{-- Previous Page Link --}}
                            @if ($receivables->onFirstPage())
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bi bi-chevron-left"></i>
                                    </span>
                                </li>
                            @else
                                <li class="page-item">
                                    <button class="page-link" wire:click="previousPage" rel="prev">
                                        <i class="bi bi-chevron-left"></i>
                                    </button>
                                </li>
                            @endif

                            {{-- Smart Pagination Elements --}}
                            @php
                                $currentPage = $receivables->currentPage();
                                $lastPage = $receivables->lastPage();
                                $maxVisible = 7;
                                
                                if ($lastPage <= $maxVisible) {
                                    $links = range(1, $lastPage);
                                } else {
                                    $links = [];
                                    $links[] = 1;
                                    
                                    $start = max(2, $currentPage - floor(($maxVisible - 4) / 2));
                                    $end = min($lastPage - 1, $start + $maxVisible - 5);
                                    
                                    if ($end == $lastPage - 1) {
                                        $start = max(2, $end - $maxVisible + 5);
                                    }
                                    
                                    if ($start > 2) {
                                        $links[] = 'ellipsis';
                                    }
                                    
                                    for ($i = $start; $i <= $end; $i++) {
                                        $links[] = $i;
                                    }
                                    
                                    if ($end < $lastPage - 1) {
                                        $links[] = 'ellipsis';
                                    }
                                    
                                    if ($lastPage > 1) {
                                        $links[] = $lastPage;
                                    }
                                }
                            @endphp
                            
                            @foreach ($links as $page)
                                @if ($page === 'ellipsis')
                                    <li class="page-item ellipsis">
                                        <span class="page-link"></span>
                                    </li>
                                @elseif ($page == $currentPage)
                                    <li class="page-item active">
                                        <span class="page-link">{{ $page }}</span>
                                    </li>
                                @else
                                    <li class="page-item">
                                        <button class="page-link" wire:click="gotoPage({{ $page }})">
                                            {{ $page }}
                                        </button>
                                    </li>
                                @endif
                            @endforeach

                            {{-- Next Page Link --}}
                            @if ($receivables->hasMorePages())
                                <li class="page-item">
                                    <button class="page-link" wire:click="nextPage" rel="next">
                                        <i class="bi bi-chevron-right"></i>
                                    </button>
                                </li>
                            @else
                                <li class="page-item disabled">
                                    <span class="page-link">
                                        <i class="bi bi-chevron-right"></i>
                                    </span>
                                </li>
                            @endif
                        </ul>
                    </nav>
                @endif
            </div>
        </div>
    </div>

    <!-- Form Modal -->
    @if($showForm)
        <div class="modal fade show" style="display: block; z-index: 10000;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">{{ $editingReceivableId ? 'Edit Piutang' : 'Tambah Piutang Baru' }}</h5>
                        <button type="button" class="btn-close" wire:click="cancel"></button>
                    </div>
                    <form wire:submit.prevent="save">
                        <div class="modal-body">
                            <div class="mb-3">
                                <label for="debtor_name" class="form-label">Nama Debitur</label>
                                <input type="text" class="form-control" id="debtor_name" wire:model="debtor_name">
                                @error('debtor_name') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="description" class="form-label">Deskripsi</label>
                                <textarea class="form-control" id="description" rows="3" wire:model="description"></textarea>
                                @error('description') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="amount" class="form-label">Jumlah Piutang</label>
                                <input type="number" class="form-control" id="amount" step="0.01" wire:model="amount">
                                @error('amount') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="due_date" class="form-label">Tanggal Jatuh Tempo</label>
                                <input type="date" class="form-control" id="due_date" wire:model="due_date">
                                @error('due_date') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="notes" class="form-label">Catatan</label>
                                <textarea class="form-control" id="notes" rows="2" wire:model="notes"></textarea>
                                @error('notes') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cancel">Batal</button>
                            <button type="submit" class="btn btn-success">
                                {{ $editingReceivableId ? 'Update' : 'Simpan' }}
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 9998;"></div>
    @endif

    <!-- Payment Modal -->
    @if($showPaymentForm && $selectedReceivable)
        <div class="modal fade show" style="display: block; z-index: 10000;" tabindex="-1">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Catat Penerimaan</h5>
                        <button type="button" class="btn-close" wire:click="cancel"></button>
                    </div>
                    <form wire:submit.prevent="recordPayment">
                        <div class="modal-body">
                            <div class="alert alert-info">
                                <h6 class="alert-heading">Informasi Piutang</h6>
                                <p class="mb-1"><strong>Debitur:</strong> {{ $selectedReceivable->debtor_name }}</p>
                                <p class="mb-1"><strong>Total Piutang:</strong> Rp {{ number_format($selectedReceivable->amount, 0, ',', '.') }}</p>
                                <p class="mb-1"><strong>Sudah Diterima:</strong> Rp {{ number_format($selectedReceivable->paid_amount ?? 0, 0, ',', '.') }}</p>
                                <p class="mb-0"><strong>Sisa:</strong> <span class="text-warning">Rp {{ number_format($selectedReceivable->remaining_amount, 0, ',', '.') }}</span></p>
                            </div>

                            <div class="mb-3">
                                <label for="payment_amount" class="form-label">Jumlah Penerimaan</label>
                                <input type="number" class="form-control" id="payment_amount" step="0.01" max="{{ $selectedReceivable->remaining_amount }}" wire:model="payment_amount">
                                @error('payment_amount') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>

                            <div class="mb-3">
                                <label for="payment_date" class="form-label">Tanggal Penerimaan</label>
                                <input type="date" class="form-control" id="payment_date" wire:model="payment_date">
                                @error('payment_date') <div class="text-danger small">{{ $message }}</div> @enderror
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" wire:click="cancel">Batal</button>
                            <button type="submit" class="btn btn-success">
                                <i class="bi bi-cash-coin me-1"></i>
                                Catat Penerimaan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <div class="modal-backdrop fade show" style="z-index: 9998;"></div>
    @endif
</div>
