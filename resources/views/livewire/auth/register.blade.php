{{-- resources/views/livewire/auth/register.blade.php --}}
<div class="min-h-screen flex items-center justify-center bg-gradient-to-br from-green-50 to-emerald-100 py-12 px-4 sm:px-6 lg:px-8">
    <div class="max-w-md w-full space-y-8">
        <div class="text-center">
            <div class="mx-auto h-16 w-16 bg-emerald-600 rounded-full flex items-center justify-center">
                <svg class="h-8 w-8 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 100 4m0-4v2m0-6V4"></path>
                </svg>
            </div>
            <h2 class="mt-6 text-3xl font-extrabold text-gray-900">
                Daftar Akun Baru
            </h2>
            <p class="mt-2 text-sm text-gray-600">
                Mulai kelola pembukuan UMKM Anda
            </p>
        </div>

        <div class="bg-white rounded-lg shadow-md p-8">
            <form wire:submit="register" class="space-y-6">
                @csrf
                
                <!-- Name -->
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Lengkap <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model.live="name" 
                        type="text" 
                        id="name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 @error('name') border-red-500 @enderror"
                        placeholder="Masukkan nama lengkap"
                    >
                    @error('name') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Business Name -->
                <div>
                    <label for="business_name" class="block text-sm font-medium text-gray-700 mb-2">
                        Nama Usaha <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model="business_name" 
                        type="text" 
                        id="business_name"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 @error('business_name') border-red-500 @enderror"
                        placeholder="Nama usaha/bisnis Anda"
                    >
                    @error('business_name') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="block text-sm font-medium text-gray-700 mb-2">
                        Email <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model.live="email" 
                        type="email" 
                        id="email"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 @error('email') border-red-500 @enderror"
                        placeholder="Masukkan email"
                    >
                    @error('email') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Phone -->
                <div>
                    <label for="phone" class="block text-sm font-medium text-gray-700 mb-2">
                        Nomor Telepon
                    </label>
                    <input 
                        wire:model="phone" 
                        type="text" 
                        id="phone"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="08xxxxxxxxxx (opsional)"
                    >
                </div>

                <!-- Password -->
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 mb-2">
                        Password <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model.live="password" 
                        type="password" 
                        id="password"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500 @error('password') border-red-500 @enderror"
                        placeholder="Minimal 6 karakter"
                    >
                    @error('password') 
                        <span class="text-red-500 text-sm mt-1 block">{{ $message }}</span> 
                    @enderror
                </div>

                <!-- Confirm Password -->
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-gray-700 mb-2">
                        Konfirmasi Password <span class="text-red-500">*</span>
                    </label>
                    <input 
                        wire:model.live="password_confirmation" 
                        type="password" 
                        id="password_confirmation"
                        class="w-full px-3 py-2 border border-gray-300 rounded-md shadow-sm placeholder-gray-400 focus:outline-none focus:ring-emerald-500 focus:border-emerald-500"
                        placeholder="Ulangi password"
                    >
                </div>

                <!-- Submit Button -->
                <div>
                    <button 
                        type="submit" 
                        class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-emerald-600 hover:bg-emerald-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-emerald-500 transition duration-200 disabled:opacity-50 disabled:cursor-not-allowed"
                        wire:loading.attr="disabled"
                    >
                        <span wire:loading.remove>Daftar Sekarang</span>
                        <span wire:loading class="flex items-center">
                            <svg class="animate-spin -ml-1 mr-2 h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Memproses...
                        </span>
                    </button>
                </div>

                <!-- Login Link -->
                <div class="text-center">
                    <p class="text-sm text-gray-600">
                        Sudah punya akun? 
                        <a href="{{ route('login') }}" class="font-medium text-emerald-600 hover:text-emerald-500">
                            Masuk di sini
                        </a>
                    </p>
                </div>
            </form>
        </div>
    </div>
</div>