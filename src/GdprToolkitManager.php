<?php

namespace Kartinov\GdprToolkit;

class GdprToolkitManager
{
    /**
     * Scan all Eloquent models for personal data columns
     * and generate a draft Record of Processing Activities (RoPA).
     *
     * Excluded models can be defined in config('gdpr-toolkit.exclude_models').
     * The resulting JSON file is stored in storage/app/gdpr-ropa.json.
     */
    public function scan()
    {
        $ropa = [];

        $modelFiles = glob(app_path('Models').'/*.php');

        foreach ($modelFiles as $file) {
            $class = 'App\\Models\\'.basename($file, '.php');

            if (! class_exists($class)) {
                continue;
            }

            // Skip excluded models
            if (in_array($class, config('gdpr-toolkit.exclude_models'))) {
                continue;
            }

            $model = new $class;
            $table = $model->getTable();
            $columns = \Schema::getColumnListing($table);

            $personal = [];

            foreach ($columns as $col) {
                if (in_array($col, config('gdpr-toolkit.personal_data_columns'))) {
                    $personal[] = $col;
                }
            }

            if ($personal) {
                $ropa[class_basename($class)] = $personal;
            }
        }

        file_put_contents(
            storage_path('app/gdpr-ropa.json'),
            json_encode($ropa, JSON_PRETTY_PRINT)
        );

        return 'RoPA generated with '.count($ropa).' models flagged.';
    }
}
