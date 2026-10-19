<?php

namespace App\Policies;

use App\Models\Shipment;
use App\Models\User;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShipmentPolicy
{
    use HandlesAuthorization;

    /**
     * Super admin bypass.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->isSuperAdmin()) {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->hasPermission('document.view') 
            || $user->hasPermission('do.view') 
            || $user->hasPermission('traffic.view');
    }

    public function view(User $user, Shipment $shipment): bool
    {
        return $this->viewAny($user);
    }

    public function create(User $user): bool
    {
        return $user->hasPermission('document.create');
    }

    public function update(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('document.create') 
            || $user->hasPermission('do.process') 
            || $user->hasPermission('traffic.process');
    }

    public function delete(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('setting.manage') || $user->isSuperAdmin();
    }

    public function uploadDocument(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('document.create') || $user->hasPermission('document.verify');
    }

    public function verifyDocument(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('document.verify');
    }

    public function submitCeisa(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('document.submit');
    }

    public function requestDo(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('do.process');
    }

    public function releaseDo(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('do.release');
    }

    public function updateTraffic(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('traffic.process');
    }

    public function gateOut(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('traffic.gateout');
    }

    public function deliverPod(User $user, Shipment $shipment): bool
    {
        return $user->hasPermission('traffic.process');
    }
}
