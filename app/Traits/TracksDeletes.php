<?php

namespace App\Traits;

use Illuminate\Support\Facades\Auth;

trait TracksDeletes
{
    public static function bootTracksDeletes()
    {
        static::deleting(function ($model) {
            if (Auth::check()) {
                // We set attributes; SoftDeletes::runSoftDelete() will call save() subsequently.
                $model->deleted_by = Auth::user()->email;
                $model->deleted_by_name = Auth::user()->name;
            }
        });
    }
}
