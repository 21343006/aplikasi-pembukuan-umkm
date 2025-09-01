<?php

namespace App\Livewire;

use Livewire\Component;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Livewire\WithFileUploads;

class UserProfile extends Component
{
    use WithFileUploads;

    // User profile fields
    public $name;
    public $email;
    public $business_name;
    public $nib;
    public $address;
    public $phone;

    // For photo upload
    public $photo;

    // Password fields
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function mount()
    {
        $user = Auth::user();
        $this->name = $user->name;
        $this->email = $user->email;
        $this->business_name = $user->business_name;
        $this->nib = $user->nib;
        $this->address = $user->address;
        $this->phone = $user->phone;
    }

    public function saveProfile()
    {
        $user = Auth::user();

        $this->validate([
            'name' => 'required|string|max:255',
            'email' => ['required', 'email', 'max:255', Rule::unique('users')->ignore($user->id)],
            'business_name' => 'nullable|string|max:255',
            'nib' => 'nullable|string|max:255',
            'address' => 'nullable|string',
            'phone' => 'nullable|string|max:20',
            'photo' => 'nullable|image|max:1024', // 1MB Max
        ]);

        if ($this->photo) {
            $user->profile_photo_path = $this->photo->store('profile-photos', 'public');
        }

        $user->name = $this->name;
        $user->email = $this->email;
        $user->business_name = $this->business_name;
        $user->nib = $this->nib;
        $user->address = $this->address;
        $user->phone = $this->phone;
        $user->save();

        // Reset the photo input
        $this->photo = null;

        session()->flash('message', 'Profil berhasil diperbarui.');
        
        // Dispatch event to update header
        $this->dispatch('profile-updated');
    }

    public function savePassword()
    {
        $this->validate([
            'current_password' => ['required', 'string', function ($attribute, $value, $fail) {
                if (!Hash::check($value, Auth::user()->password)) {
                    $fail('Password saat ini tidak cocok.');
                }
            }],
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = Auth::user();
        $user->password = Hash::make($this->new_password);
        $user->save();

        $this->reset('current_password', 'new_password', 'new_password_confirmation');

        session()->flash('password_message', 'Password berhasil diubah.');
    }

    public function render()
    {
        return view('livewire.user-profile')
            ->layout('components.layouts.app', ['title' => 'Profil Pengguna']);
    }
}