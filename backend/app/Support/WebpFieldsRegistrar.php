<?php

namespace App\Support;

use Illuminate\Database\Eloquent\Model;

/**
 * Hooks model save events to convert uploaded image fields to WebP.
 * Supports both single-path string fields and array (multiple file
 * upload) fields.
 */
class WebpFieldsRegistrar
{
    /**
     * @param  class-string<Model>  $modelClass
     * @param  string[]  $fields
     */
    public static function register(string $modelClass, array $fields, string $disk = 'public', int $maxDimension = 1600): void
    {
        $convert = function (Model $model) use ($modelClass, $fields, $disk, $maxDimension) {
            $updates = [];

            foreach ($fields as $field) {
                $value = $model->{$field};

                if (is_array($value)) {
                    $converted = array_map(fn ($path) => WebpConverter::convert($path, $disk, $maxDimension), $value);
                    if ($converted !== $value) {
                        $updates[$field] = $converted;
                    }
                } elseif (is_string($value) && $value !== '') {
                    $converted = WebpConverter::convert($value, $disk, $maxDimension);
                    if ($converted !== $value) {
                        $updates[$field] = $converted;
                    }
                }
            }

            if (!empty($updates)) {
                $modelClass::withoutEvents(fn () => $model->updateQuietly($updates));
            }
        };

        $modelClass::created($convert);

        $modelClass::updated(function (Model $model) use ($fields, $convert) {
            if (array_intersect($fields, array_keys($model->getChanges()))) {
                $convert($model);
            }
        });
    }
}
