<main id="main" class="main">
    <div class="pagetitle">
        <h1 class="mb-0">
            <i class="bi bi-cash-coin text-primary me-2"></i>
            Input Modal Awal
        </h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Home</a></li>
                <li class="breadcrumb-item">Modal</li>
                <li class="breadcrumb-item active">Input Modal Awal</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section mt-4">
        <div class="row justify-content-center">
            <div class="col-lg-8 col-md-10">
                <!-- Enhanced Alert Messages -->
                @if (session()->has('success'))
                    <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon me-3">
                                <i class="bi bi-check-circle-fill" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <strong>Berhasil!</strong> {{ session('success') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session()->has('error'))
                    <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert">
                        <div class="d-flex align-items-center">
                            <div class="alert-icon me-3">
                                <i class="bi bi-exclamation-triangle-fill" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <strong>Error!</strong> {{ session('error') }}
                            </div>
                        </div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                <!-- Enhanced Form Card -->
                <div class="card shadow-sm border-0 mb-4" style="border-radius: 15px; overflow: hidden;">
                    <div class="card-header bg-gradient text-white" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                        <div class="d-flex align-items-center">
                            <div class="card-header-icon me-3">
                                <i class="bi bi-plus-circle-fill" style="font-size: 1.5rem;"></i>
                            </div>
                            <div>
                                <h5 class="mb-0">{{ $isEditing ? 'Edit Modal Awal' : 'Form Input Modal Awal' }}</h5>
                                <small class="opacity-75">{{ $isEditing ? 'Perbarui data modal awal Anda' : 'Masukkan modal awal untuk memulai pembukuan Anda' }}</small>
                            </div>
                        </div>
                    </div>
                    <div class="card-body p-4">
                        @if($isEditing)
                            <div class="alert alert-info alert-dismissible fade show shadow-sm border-0 mb-4" role="alert">
                                <div class="d-flex align-items-center">
                                    <div class="alert-icon me-3">
                                        <i class="bi bi-info-circle-fill" style="font-size: 1.5rem;"></i>
                                    </div>
                                    <div>
                                        <strong>Mode Edit!</strong> Anda sedang mengedit data modal awal
                                    </div>
                                </div>
                                <button type="button" wire:click="cancelEdit" class="btn-close" aria-label="Close"></button>
                            </div>
                        @endif
                        
                        <form wire:submit.prevent="save">
                            <div class="row">
                                <!-- Modal Awal Input -->
                                <div class="col-md-6 mb-4">
                                    <label for="modal_awal" class="form-label fw-semibold">
                                        <i class="bi bi-cash-coin me-2 text-primary"></i>Modal Awal
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                                            <strong>Rp</strong>
                                        </span>
                                        <input 
                                            type="number" 
                                            step="0.01" 
                                            wire:model.live="modal_awal" 
                                            class="form-control border-start-0 @error('modal_awal') is-invalid @enderror"
                                            id="modal_awal" 
                                            placeholder="Masukkan nominal modal awal..."
                                            style="border-radius: 0 10px 10px 0; border-left: none !important;"
                                        >
                                    </div>
                                    @error('modal_awal')
                                        <div class="invalid-feedback d-flex align-items-center mt-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Modal awal akan digunakan sebagai dasar perhitungan keuangan Anda
                                    </div>
                                </div>

                                <!-- Tanggal Input -->
                                <div class="col-md-6 mb-4">
                                    <label for="tanggal_input" class="form-label fw-semibold">
                                        <i class="bi bi-calendar-event me-2 text-primary"></i>Tanggal Input
                                    </label>
                                    <div class="input-group input-group-lg">
                                        <span class="input-group-text bg-light border-end-0" style="border-radius: 10px 0 0 10px;">
                                            <i class="bi bi-calendar3"></i>
                                        </span>
                                        <input 
                                            type="date" 
                                            wire:model.live="tanggal_input" 
                                            class="form-control border-start-0 @error('tanggal_input') is-invalid @enderror"
                                            id="tanggal_input"
                                            style="border-radius: 0 10px 10px 0; border-left: none !important;"
                                        >
                                    </div>
                                    @error('tanggal_input')
                                        <div class="invalid-feedback d-flex align-items-center mt-2">
                                            <i class="bi bi-exclamation-triangle me-1"></i>{{ $message }}
                                        </div>
                                    @enderror
                                    <div class="form-text mt-2">
                                        <i class="bi bi-info-circle me-1"></i>
                                        Tanggal saat modal awal dicatat
                                    </div>
                                </div>
                            </div>

                            <div class="d-grid">
                                @if($isEditing)
                                    <div class="row g-2">
                                        <div class="col-md-6">
                                            <button type="submit" class="btn btn-lg text-white shadow w-100" 
                                                    style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; border-radius: 10px;">
                                                <i class="bi bi-check-lg me-2"></i>Update Modal Awal
                                            </button>
                                        </div>
                                        <div class="col-md-6">
                                            <button type="button" wire:click="cancelEdit" class="btn btn-lg btn-outline-secondary shadow w-100" 
                                                    style="border-radius: 10px;">
                                                <i class="bi bi-x-lg me-2"></i>Batal
                                            </button>
                                        </div>
                                    </div>
                                @else
                                    <button type="submit" class="btn btn-lg text-white shadow" 
                                            style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none; border-radius: 10px;">
                                        <i class="bi bi-save me-2"></i>Simpan Modal Awal
                                    </button>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>

                <!-- Enhanced Stats Cards -->
                <div class="row g-3 mb-4">
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title mb-1 opacity-75">Total Modal</h6>
                                        <h3 class="mb-0 fw-bold">
                                            Rp {{ number_format($total_modal, 0, ',', '.') }}
                                        </h3>
                                        <small class="opacity-75">
                                            <i class="bi bi-trending-up me-1"></i>Akumulasi modal
                                        </small>
                                    </div>
                                    <div class="card-icon">
                                        <i class="bi bi-currency-dollar" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="card h-100 shadow-sm border-0" style="border-radius: 15px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);">
                            <div class="card-body text-white">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div>
                                        <h6 class="card-title mb-1 opacity-75">Total Entry</h6>
                                        <h3 class="mb-0 fw-bold">
                                            {{ count($capitals) }} Entry
                                        </h3>
                                        <small class="opacity-75">
                                            <i class="bi bi-list-check me-1"></i>Data tercatat
                                        </small>
                                    </div>
                                    <div class="card-icon">
                                        <i class="bi bi-list-ol" style="font-size: 2.5rem; opacity: 0.3;"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Enhanced History Table -->
                <div class="card shadow-sm border-0" style="border-radius: 15px;">
                    <div class="card-header bg-light border-0" style="border-radius: 15px 15px 0 0;">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center">
                                <i class="bi bi-clock-history me-2 text-primary" style="font-size: 1.2rem;"></i>
                                <h5 class="mb-0 fw-semibold">Riwayat Input Modal</h5>
                            </div>
                            @if(count($capitals) > 0)
                                <span class="badge bg-primary rounded-pill">{{ count($capitals) }} Record</span>
                            @endif
                        </div>
                    </div>
                    <div class="card-body p-0">
                        @if(count($capitals) > 0)
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead class="table-light">
                                        <tr>
                                            <th scope="col" class="border-0 ps-4 py-3">
                                                <i class="bi bi-hash me-1"></i>#
                                            </th>
                                            <th scope="col" class="border-0 py-3">
                                                <i class="bi bi-currency-dollar me-1"></i>Modal Awal
                                            </th>
                                            <th scope="col" class="border-0 py-3">
                                                <i class="bi bi-calendar-event me-1"></i>Tanggal Input
                                            </th>
                                            <th scope="col" class="border-0 py-3">
                                                <i class="bi bi-clock me-1"></i>Waktu Dibuat
                                            </th>
                                            <th scope="col" class="border-0 py-3 text-center">
                                                <i class="bi bi-activity me-1"></i>Status
                                            </th>
                                            <th scope="col" class="border-0 py-3 text-center">
                                                <i class="bi bi-gear me-1"></i>Aksi
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($capitals as $index => $capital)
                                            <tr class="align-middle {{ $isEditing && $editingId == $capital->id ? 'table-warning' : '' }}">
                                                <th scope="row" class="ps-4">
                                                    <div class="d-flex align-items-center">
                                                        <div class="badge bg-primary rounded-circle me-2" style="width: 25px; height: 25px; display: flex; align-items: center; justify-content: center;">
                                                            {{ $index + 1 }}
                                                        </div>
                                                    </div>
                                                </th>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); width: 6px; height: 40px; border-radius: 3px;"></div>
                                                        <div>
                                                            <div class="fw-bold text-success" style="font-size: 1.1rem;">
                                                                Rp {{ number_format($capital->modal_awal, 0, ',', '.') }}
                                                            </div>
                                                            <small class="text-muted">Modal ke-{{ $index + 1 }}</small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <i class="bi bi-calendar3 text-primary"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium">
                                                                {{ $capital->tanggal_input ? $capital->tanggal_input->format('d M Y') : '-' }}
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $capital->tanggal_input ? $capital->tanggal_input->format('l') : 'Tidak diatur' }}
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td>
                                                    <div class="d-flex align-items-center">
                                                        <div class="me-2">
                                                            <i class="bi bi-clock text-info"></i>
                                                        </div>
                                                        <div>
                                                            <div class="fw-medium">
                                                                {{ $capital->created_at->format('d M Y') }}
                                                            </div>
                                                            <small class="text-muted">
                                                                {{ $capital->created_at->format('H:i') }} WIB
                                                            </small>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="text-center">
                                                    <span class="badge bg-success rounded-pill">
                                                        <i class="bi bi-check-circle me-1"></i>Tercatat
                                                    </span>
                                                </td>
                                                <td class="text-center">
                                                    <div class="btn-group" role="group">
                                                        <button 
                                                            type="button" 
                                                            wire:click="edit({{ $capital->id }})"
                                                            class="btn btn-sm btn-outline-primary"
                                                            style="border-radius: 8px 0 0 8px;"
                                                            title="Edit Data"
                                                        >
                                                            <i class="bi bi-pencil-square"></i>
                                                        </button>
                                                        <button 
                                                            type="button" 
                                                            wire:click="delete({{ $capital->id }})"
                                                            onclick="return confirm('Apakah Anda yakin ingin menghapus data modal awal sebesar Rp {{ number_format($capital->modal_awal, 0, ',', '.') }}?')"
                                                            class="btn btn-sm btn-outline-danger"
                                                            style="border-radius: 0 8px 8px 0;"
                                                            title="Hapus Data"
                                                        >
                                                            <i class="bi bi-trash3"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                            
                            <!-- Enhanced Footer Summary -->
                            <div class="card-footer bg-light border-0 rounded-bottom">
                                <div class="row align-items-center">
                                    <div class="col-md-6">
                                        <small class="text-muted">
                                            <i class="bi bi-info-circle me-1"></i>
                                            Menampilkan {{ count($capitals) }} dari {{ count($capitals) }} data
                                        </small>
                                    </div>
                                    <div class="col-md-6 text-md-end">
                                        <small class="text-muted">
                                            <i class="bi bi-calculator me-1"></i>
                                            Total: <span class="fw-bold text-primary">Rp {{ number_format($total_modal, 0, ',', '.') }}</span>
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @else
                            <!-- Enhanced Empty State -->
                            <div class="text-center py-5">
                                <div class="mb-4">
                                    <div class="d-inline-flex align-items-center justify-content-center" 
                                         style="width: 80px; height: 80px; background: linear-gradient(135deg, #e3f2fd 0%, #f3e5f5 100%); border-radius: 50%;">
                                        <i class="bi bi-inbox" style="font-size: 2.5rem; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); -webkit-background-clip: text; -webkit-text-fill-color: transparent;"></i>
                                    </div>
                                </div>
                                <h5 class="text-muted mb-2">Belum Ada Data Modal Awal</h5>
                                <p class="text-muted mb-4">
                                    Mulai perjalanan bisnis Anda dengan input modal awal terlebih dahulu
                                </p>
                                <div class="d-flex justify-content-center">
                                    <div class="bg-light rounded-pill px-4 py-2">
                                        <small class="text-muted">
                                            <i class="bi bi-lightbulb me-1"></i>
                                            Tip: Modal awal membantu Anda melacak perkembangan bisnis
                                        </small>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Custom Styles -->
    <style>
        .card-hover:hover {
            transform: translateY(-5px);
            transition: all 0.3s ease;
        }
        
        .btn:hover {
            transform: translateY(-2px);
            transition: all 0.3s ease;
        }
        
        .table-hover tbody tr:hover {
            background-color: rgba(102, 126, 234, 0.05);
        }
        
        .badge {
            font-weight: 500;
        }
        
        .form-control:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.25);
        }
        
        .alert {
            border-radius: 10px;
        }
        
        .card {
            transition: all 0.3s ease;
        }
        
        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(20px); }
            to { opacity: 1; transform: translateY(0); }
        }
        
        .card {
            animation: fadeIn 0.5s ease-out;
        }
        
        /* Custom date input styling */
        input[type="date"]::-webkit-calendar-picker-indicator {
            opacity: 0.6;
        }
        
        input[type="date"]:hover::-webkit-calendar-picker-indicator {
            opacity: 1;
        }
        
        /* Button group styling */
        .btn-group .btn {
            border: 1px solid;
        }
        
        .btn-group .btn:hover {
            transform: none;
            z-index: 2;
        }
        
        /* Edit mode highlighting */
        .table tbody tr.table-warning {
            background-color: rgba(255, 193, 7, 0.1) !important;
        }
    </style>
</main>