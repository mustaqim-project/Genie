<?php

namespace App\Policies;

use App\Models\ClientInvoice;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ClientInvoicePolicy
{
    use HandlesAuthorization;

    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('finance.view') || $user->hasPermission('accounting.view');
    }

    public function view(User $user, ClientInvoice $invoice): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('finance.invoice');
    }

    public function update(User $user, ClientInvoice $invoice): bool
    {
        return $user->hasPermission('finance.invoice');
    }

    public function delete(User $user, ClientInvoice $invoice): bool
    {
        return $user->isSuperAdmin();
    }

    public function pay(User $user, ClientInvoice $invoice): bool
    {
        return $user->hasPermission('finance.pay');
    }
}
