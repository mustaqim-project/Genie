<?php

namespace App\Policies;

use App\Models\CeisaDocument;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class CeisaDocumentPolicy
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
        return $user->hasPermission('document.view') || $user->hasPermission('document.submit');
    }

    public function view(User $user, CeisaDocument $doc): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('document.create');
    }

    public function update(User $user, CeisaDocument $doc): bool
    {
        return $user->hasPermission('document.create');
    }

    public function submit(User $user, CeisaDocument $doc): bool
    {
        return $user->hasPermission('document.submit');
    }

    public function delete(User $user, CeisaDocument $doc): bool
    {
        return $user->isSuperAdmin();
    }
}
