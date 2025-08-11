<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class Register extends Component
{
    public $name = '';
    public $email = '';
    public $password = '';
    public $password_confirmation = '';
    public $business_name = '';
    public $phone = '';

    protected function rules()
    {
        return [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => ['required', 'confirmed', Password::defaults()],
            'business_name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
        ];
    }

    protected $messages = [
        'name.required' => 'Nama lengkap wajib diisi.',
        'email.required' => 'Email wajib diisi.',
        'email.email' => 'Format email tidak valid.',
        'email.unique' => 'Email sudah terdaftar.',
        'password.required' => 'Password wajib diisi.',
        'password.confirmed' => 'Konfirmasi password tidak cocok.',
        'business_name.required' => 'Nama usaha wajib diisi.',
    ];

    public function updatedEmail()
    {
        $this->validateOnly('email');
    }

    public function updatedPassword()
    {
        $this->validateOnly('password');
    }

    public function updatedPasswordConfirmation()
    {
        $this->validateOnly('password');
    }

    public function register()
    {
        $validated = $this->validate();

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'business_name' => $validated['business_name'],
            'phone' => $validated['phone'],
        ]);

        Auth::login($user);

        session()->flash('message', 'Registrasi berhasil! Selamat datang di aplikasi pembukuan UMKM.');

        return redirect('/dashboard');
    }

    public function render()
    {
        return view('livewire.auth.register')
            ->layout('layouts.auth');
    }
}