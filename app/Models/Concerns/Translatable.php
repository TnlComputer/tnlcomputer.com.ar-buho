<?php

namespace App\Models\Concerns;

/** Campos con versión en inglés en una columna "{campo}_en"; si está vacía se usa el español. */
trait Translatable
{
    public function tr(string $field): ?string
    {
        if (app()->getLocale() === 'en' && filled($this->{$field.'_en'})) {
            return $this->{$field.'_en'};
        }

        return $this->{$field};
    }
}
