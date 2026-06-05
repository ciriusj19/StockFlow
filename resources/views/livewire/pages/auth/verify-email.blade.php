<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;

use function Livewire\Volt\layout;

layout('layouts.guest');

$sendVerification = function () {
    if (Auth::user()->hasVerifiedEmail()) {
        $this->redirectIntended(default: route('dashboard', absolute: false), navigate: true);

        return;
    }

    Auth::user()->sendEmailVerificationNotification();

    Session::flash('status', 'verification-link-sent');
};

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<div>
    <div class="mb-4 text-sm text-gray-600">
        Avant de continuer, vérifiez votre adresse email avec le lien envoyé. Vous pouvez demander un nouvel envoi si vous ne l'avez pas reçu.
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-4 font-medium text-sm text-green-600">
            Un nouveau lien de vérification a été envoyé à votre adresse email.
        </div>
    @endif

    <div class="mt-4 flex items-center justify-between">
        <x-primary-button wire:click="sendVerification">
            Renvoyer l'email
        </x-primary-button>

        <button wire:click="logout" type="submit" class="underline text-sm text-gray-600 hover:text-gray-900 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
            Se déconnecter
        </button>
    </div>
</div>
