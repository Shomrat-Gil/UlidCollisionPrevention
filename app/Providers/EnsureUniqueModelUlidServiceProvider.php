<?php

namespace App\Providers;

use Illuminate\Support\Facades\Cache;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\Model;

class EnsureUniqueModelUlidServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     */
    public function boot()
    {
        Model::creating(function ($model) {
            $keyName = $model->getKeyName();
            $keyValue = $model->getKey();

            if ($model->getKeyType() === 'string' && (is_null($keyValue) || Str::isUlid($keyValue))) {
                if (is_null($keyValue) || Cache::has($keyValue)) {
                    do {
                        $keyValue = (string) Str::ulid();  // Generate a lowercase ULID
                    } while (Cache::has($keyValue));

                    $model->setAttribute($keyName, $keyValue);
                }

                // Cache the ULID for 1 second to prevent duplicates
                Cache::put($keyValue, true, 1);
            }
        });
    }
}