<?php

declare(strict_types=1);

namespace Workbench\App\Filament\Pages\Auth;

use Filament\Auth\Pages\Login as BaseLogin;

/**
 * Development-only login page. It prefills the Workbench's seeded credentials so
 * `composer serve` opens straight into the panel. Authentication itself is
 * unchanged — the form is still submitted and validated normally.
 */
class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        $this->form->fill([
            'email' => 'test@example.com',
            'password' => 'password',
        ]);
    }
}
