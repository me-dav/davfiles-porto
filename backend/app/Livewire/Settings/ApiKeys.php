<?php

// app/Livewire/Settings/ApiKeys.php
namespace App\Livewire\Settings;

use Livewire\Component;

class ApiKeys extends Component
{
    public string $name = 'hermes';
    public ?string $newToken = null;

    public function create(): void
    {
        $this->validate(['name' => 'required|string|max:40']);

        $token = auth()->user()->createToken(
            $this->name,
            ['finance:read', 'finance:write'],
            now()->addDays(90),
        );
        $this->newToken = $token->plainTextToken;   // ditampilkan sekali
    }

    public function revoke(int $id): void
    {
        auth()->user()->tokens()->whereKey($id)->delete();
    }

    public function render()
    {
        return view('livewire.settings.api-keys', [
            'tokens' => auth()->user()->tokens()->latest()->get(),
        ]);
    }
}
