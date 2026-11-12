<?php

namespace App\Traits;

use App\Models\AuditLog;
use Illuminate\Database\Eloquent\Model;

trait Auditable
{
    public static function bootAuditable(): void
    {
        static::created(function (Model $model) {
            self::logModelActivity('CREATE', $model, null, $model->getAuditableAttributes());
        });

        static::updated(function (Model $model) {
            $dirty = $model->getDirty();
            if (empty($dirty)) {
                return;
            }

            $old = [];
            $new = [];
            foreach ($dirty as $key => $newValue) {
                if ($model->isAuditableAttribute($key)) {
                    $old[$key] = $model->getOriginal($key);
                    $new[$key] = $newValue;
                }
            }

            if (!empty($new)) {
                self::logModelActivity('UPDATE', $model, $old, $new);
            }
        });

        static::deleted(function (Model $model) {
            self::logModelActivity('DELETE', $model, $model->getAuditableAttributes(), null);
        });
    }

    protected static function logModelActivity(string $action, Model $model, ?array $old, ?array $new): void
    {
        try {
            AuditLog::record(
                action: $action,
                objectType: class_basename($model),
                objectId: $model->getKey(),
                oldValue: $old,
                newValue: $new,
                actorId: auth()->id(),
                actorType: auth()->check() ? 'USER' : 'SYSTEM'
            );
        } catch (\Throwable $e) {
            // Jangan menghentikan transaksi utama jika audit logging mengalami kendala
            \Illuminate\Support\Facades\Log::warning("Gagal menulis audit log untuk " . class_basename($model) . ": " . $e->getMessage());
        }
    }

    public function getAuditableAttributes(): array
    {
        $hidden = array_merge($this->getHidden(), ['password', 'remember_token', 'token', 'secret']);
        $attributes = $this->attributesToArray();

        foreach ($hidden as $key) {
            unset($attributes[$key]);
        }

        return $attributes;
    }

    public function isAuditableAttribute(string $key): bool
    {
        $sensitive = ['password', 'remember_token', 'token', 'secret', 'updated_at'];
        return !in_array($key, $sensitive, true);
    }
}
