<?php

namespace Kartinov\GdprToolkit\Traits;

trait HasPersonalData
{
    public function exportPersonalData(): array
    {
        $data = [];

        foreach ($this->personalData ?? [] as $field) {
            $data[$field] = $this->{$field};
        }

        return $data;
    }
}
