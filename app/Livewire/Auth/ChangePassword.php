<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rules\Password;

class ChangePassword extends Component
{
    public $current_password;
    public $new_password;
    public $new_password_confirmation;

    public function rules()
    {
        return [
            'current_password' => 'required',
            'new_password' => ['required', Password::min(8)->uncompromised()],
            'new_password_confirmation' => 'required|same:new_password',
        ];
    }

    public function updatePassword()
    {
        $this->validate();

        if (!Hash::check($this->current_password, Auth::user()->password)) {
            $this->addError('current_password', 'La contraseña actual es incorrecta.');
            return;
        }

        Auth::user()->update([
            'password' => Hash::make($this->new_password),
        ]);

        session()->flash('success', 'La contraseña ha sido actualizada correctamente.');
        
        $this->reset(['current_password', 'new_password', 'new_password_confirmation']);
    }

    public function render()
    {
        return view('livewire.auth.change-password');
    }
}
