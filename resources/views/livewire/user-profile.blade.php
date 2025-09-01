<main id="main" class="main">

    <div class="pagetitle">
        <h1>Profil</h1>
        <nav>
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Dashboard</a></li>
                <li class="breadcrumb-item active">Profil Pengguna</li>
            </ol>
        </nav>
    </div><!-- End Page Title -->

    <section class="section profile">
        <div class="row">
            <div class="col-xl-4">

                <div class="card">
                    <div class="card-body profile-card pt-4 d-flex flex-column align-items-center">
                        @if (Auth::user()->profile_photo_path)
                            <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" class="rounded-circle profile-picture-circle">
                        @else
                            <img src="/assets/img/profile-img.jpg" alt="Profile" class="rounded-circle profile-picture-circle">
                        @endif
                        <h2>{{ $name }}</h2>
                        <h3>{{ $business_name ?? 'Pengguna' }}</h3>
                    </div>
                </div>

            </div>

            <div class="col-xl-8">

                <div class="card">
                    <div class="card-body pt-3">
                        <!-- Bordered Tabs -->
                        <ul class="nav nav-tabs nav-tabs-bordered">

                            <li class="nav-item">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#profile-edit">Edit Profil</button>
                            </li>

                            <li class="nav-item">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#profile-change-password">Ubah Password</button>
                            </li>

                        </ul>
                        <div class="tab-content pt-2">

                            <div class="tab-pane fade show active profile-edit pt-3" id="profile-edit">

                                <!-- Profile Edit Form -->
                                <form wire:submit.prevent="saveProfile">
                                    @if (session()->has('message'))
                                        <div class="alert alert-success">
                                            {{ session('message') }}
                                        </div>
                                    @endif

                                    <!-- Profile Picture -->
                                    <div class="row mb-3">
                                        <label for="profileImage" class="col-md-4 col-lg-3 col-form-label">Foto Profil</label>
                                        <div class="col-md-8 col-lg-9">
                                            @if ($photo)
                                                <img src="{{ $photo->temporaryUrl() }}" alt="Preview" class="rounded-circle profile-picture-circle mb-2">
                                            @elseif (Auth::user()->profile_photo_path)
                                                <img src="{{ asset('storage/' . Auth::user()->profile_photo_path) }}" alt="Profile" class="rounded-circle profile-picture-circle mb-2">
                                            @else
                                                <img src="/assets/img/profile-img.jpg" alt="Profile" class="rounded-circle profile-picture-circle mb-2">
                                            @endif
                                            <div class="pt-2">
                                                <input type="file" wire:model="photo" id="profileImage" class="form-control @error('photo') is-invalid @enderror">
                                                @error('photo') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                                <div wire:loading wire:target="photo" class="text-sm text-muted">Mengunggah...</div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="fullName" class="col-md-4 col-lg-3 col-form-label">Nama Lengkap</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="name" type="text" class="form-control @error('name') is-invalid @enderror" id="fullName">
                                            @error('name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="businessName" class="col-md-4 col-lg-3 col-form-label">Nama Usaha</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="business_name" type="text" class="form-control @error('business_name') is-invalid @enderror" id="businessName">
                                            @error('business_name') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="nib" class="col-md-4 col-lg-3 col-form-label">NIB</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="nib" type="text" class="form-control @error('nib') is-invalid @enderror" id="nib">
                                            @error('nib') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="address" class="col-md-4 col-lg-3 col-form-label">Alamat Usaha</label>
                                        <div class="col-md-8 col-lg-9">
                                            <textarea wire:model="address" class="form-control @error('address') is-invalid @enderror" id="address" style="height: 100px"></textarea>
                                            @error('address') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="phone" class="col-md-4 col-lg-3 col-form-label">Telepon</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="phone" type="text" class="form-control @error('phone') is-invalid @enderror" id="phone">
                                            @error('phone') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="Email" class="col-md-4 col-lg-3 col-form-label">Email</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="email" type="email" class="form-control @error('email') is-invalid @enderror" id="Email">
                                            @error('email') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Simpan Perubahan</button>
                                    </div>
                                </form><!-- End Profile Edit Form -->

                            </div>

                            <div class="tab-pane fade pt-3" id="profile-change-password">
                                <!-- Change Password Form -->
                                <form wire:submit.prevent="savePassword">
                                     @if (session()->has('password_message'))
                                        <div class="alert alert-success">
                                            {{ session('password_message') }}
                                        </div>
                                    @endif

                                    <div class="row mb-3">
                                        <label for="currentPassword" class="col-md-4 col-lg-3 col-form-label">Password Saat Ini</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="current_password" type="password" class="form-control @error('current_password') is-invalid @enderror" id="currentPassword">
                                            @error('current_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="newPassword" class="col-md-4 col-lg-3 col-form-label">Password Baru</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="new_password" type="password" class="form-control @error('new_password') is-invalid @enderror" id="newPassword">
                                            @error('new_password') <span class="invalid-feedback">{{ $message }}</span> @enderror
                                        </div>
                                    </div>

                                    <div class="row mb-3">
                                        <label for="renewPassword" class="col-md-4 col-lg-3 col-form-label">Konfirmasi Password Baru</label>
                                        <div class="col-md-8 col-lg-9">
                                            <input wire:model="new_password_confirmation" type="password" class="form-control" id="renewPassword">
                                        </div>
                                    </div>

                                    <div class="text-center">
                                        <button type="submit" class="btn btn-primary">Ubah Password</button>
                                    </div>
                                </form><!-- End Change Password Form -->

                            </div>

                        </div><!-- End Bordered Tabs -->

                    </div>
                </div>

            </div>
        </div>
    </section>

</main><!-- End #main -->