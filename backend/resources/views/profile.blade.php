<x-layouts.erp title="Profil">
    <div class="mx-auto max-w-3xl space-y-5">
        <x-erp.card>
            <livewire:profile.update-profile-information-form />
        </x-erp.card>

        <x-erp.card>
            <livewire:profile.update-password-form />
        </x-erp.card>

        @if(auth()->user()->hasModule('finance'))
            <livewire:settings.api-keys />
        @endif
    </div>
</x-layouts.erp>