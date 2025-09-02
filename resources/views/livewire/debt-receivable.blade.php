<main id="main" class="main">
    <div class="pagetitle">
        <h1>Buku Utang & Piutang</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Buku Utang & Piutang</li>
            </ol>
        </nav>
    </div>

    <section class="section">
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title">Ringkasan Utang & Piutang</h5>
                        
                        <!-- Summary Cards -->
                        <div class="row mb-4">
                            <div class="col-xl-3 col-md-6">
                                <div class="card info-card sales-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Utang</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-arrow-down-circle"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>Rp {{ number_format($totalDebts, 0, ',', '.') }}</h6>
                                                <span class="text-danger small">Sisa: Rp {{ number_format($totalDebtsRemaining, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card info-card customers-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Total Piutang</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-arrow-up-circle"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>Rp {{ number_format($totalReceivables, 0, ',', '.') }}</h6>
                                                <span class="text-success small">Sisa: Rp {{ number_format($totalReceivablesRemaining, 0, ',', '.') }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="col-xl-3 col-md-6">
                                <div class="card info-card revenue-card">
                                    <div class="card-body">
                                        <h5 class="card-title">Posisi Net</h5>
                                        <div class="d-flex align-items-center">
                                            <div class="card-icon rounded-circle d-flex align-items-center justify-content-center">
                                                <i class="bi bi-calculator"></i>
                                            </div>
                                            <div class="ps-3">
                                                @php
                                                    $netPosition = $totalReceivablesRemaining - $totalDebtsRemaining;
                                                @endphp
                                                <h6 class="{{ $netPosition >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $netPosition >= 0 ? '+' : '' }}Rp {{ number_format($netPosition, 0, ',', '.') }}
                                                </h6>
                                                <span class="small {{ $netPosition >= 0 ? 'text-success' : 'text-danger' }}">
                                                    {{ $netPosition >= 0 ? 'Positif' : 'Negatif' }}
                                                </span>
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
                                                <i class="bi bi-exclamation-triangle"></i>
                                            </div>
                                            <div class="ps-3">
                                                <h6>{{ $overdueDebts + $overdueReceivables }}</h6>
                                                <span class="text-warning small">Utang: {{ $overdueDebts }} | Piutang: {{ $overdueReceivables }}</span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Tab Navigation -->
                        <ul class="nav nav-tabs nav-tabs-bordered" id="borderedTab" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button wire:click="setTab('debts')" class="nav-link {{ $activeTab === 'debts' ? 'active' : '' }}" type="button" role="tab">
                                    <i class="bi bi-arrow-down-circle me-1"></i>
                                    Buku Utang
                                    @if($overdueDebts > 0)
                                        <span class="badge bg-danger ms-1">{{ $overdueDebts }}</span>
                                    @endif
                                </button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button wire:click="setTab('receivables')" class="nav-link {{ $activeTab === 'receivables' ? 'active' : '' }}" type="button" role="tab">
                                    <i class="bi bi-arrow-up-circle me-1"></i>
                                    Buku Piutang
                                    @if($overdueReceivables > 0)
                                        <span class="badge bg-danger ms-1">{{ $overdueReceivables }}</span>
                                    @endif
                                </button>
                            </li>
                        </ul>

                        <!-- Tab Content -->
                        <div class="tab-content pt-2" id="borderedTabContent">
                            <div class="tab-pane fade {{ $activeTab === 'debts' ? 'show active' : '' }}" role="tabpanel">
                                @if($activeTab === 'debts')
                                    @livewire('debts')
                                @endif
                            </div>
                            <div class="tab-pane fade {{ $activeTab === 'receivables' ? 'show active' : '' }}" role="tabpanel">
                                @if($activeTab === 'receivables')
                                    @livewire('receivables')
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
</main>
