<?php

namespace App\Traits;

use Ramsey\Uuid\Uuid as PackageUuid;

trait Uuid
{
    public static function generate(): string
    {
        return PackageUuid::uuid4()->toString();
    }

    public function scopeUuid($query, $uuid)
    {
        return $query->where($this->getUuidName(), $uuid);
    }

    public function getUuidName()
    {
        return property_exists($this, 'slug') ? $this->slug : 'slug';
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($model) {
            $model->{$model->getUuidName()} = PackageUuid::uuid4()->toString();
        });
    }

    public function getRouteKeyName()
    {
        return 'slug';
    }
}
