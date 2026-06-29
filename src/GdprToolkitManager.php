<?php

namespace Kartinov\GdprToolkit;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class GdprToolkitManager
{
    /**
     * Scan the database for personal data linked to the configured subject models.
     *
     * Produces a nested RoPA JSON mapping each subject model to its own
     * personal fields and to any related tables that contain personally identifiable information
     * via foreign key detection.
     * @return string Summary message with the number of flagged tables
     */
    public function scan(): string
    {
        // Read configuration values
        $subjectModels = config('gdpr-toolkit.subject_models');
        $excludeModels = config('gdpr-toolkit.exclude_models', []);
        $personalFields = config('gdpr-toolkit.personal_data_columns');
        $fkSuffix = config('gdpr-toolkit.foreign_key_suffix');
        $fkOverrides = config('gdpr-toolkit.foreign_key_overrides');

        $excludeTables = [];
        $tablesWithPersonalData = 0;
        $results = [];

        // Process each configured subject model (e.g. User, Admin)
        foreach ($subjectModels as $subjectClass) {
            // Skip if the class does not exist (typo in config)
            if (! class_exists($subjectClass)) {
                continue;
            }

            // Skip entirely if the model is in the exclude list
            if (in_array($subjectClass, $excludeModels)) {
                $excludeTables[] = (new $subjectClass)->getTable();
                continue;
            }

            $subject = new $subjectClass;
            $subjectTable = $subject->getTable();
            $subjectColumns = Schema::getColumnListing($subjectTable);

            // Collect personal fields that exist on the subject's own table
            $subjectFields = array_values(array_intersect($subjectColumns, $personalFields));

            $entry = [];
            
            if (! empty($subjectFields)) {
                $entry['fields'] = $subjectFields;
                $tablesWithPersonalData++;
            }

            // Retrieve every table in the database
            $tables = DB::select('SHOW TABLES');
            $tableNames = array_map('current', $tables);

            // Look for tables that reference this subject via foreign key
            foreach ($tableNames as $table) {
                // Skip the subject's own table and any excluded tables
                if ($table === $subjectTable || in_array($table, $excludeTables)) {
                    continue;
                }

                $columns = Schema::getColumnListing($table);
                $matchedFk = null;

                // Check each column for a foreign key matching this subject
                foreach ($columns as $col) {
                    $expectedFk = strtolower(class_basename($subjectClass)).$fkSuffix;

                    if ($col === $expectedFk) {
                        $matchedFk = $col;
                        break;
                    }

                    if (isset($fkOverrides[$col]) && $fkOverrides[$col] === $subjectClass) {
                        $matchedFk = $col;
                        break;
                    }
                }

                // No matching FK on this table — move on
                if ($matchedFk === null) {
                    continue;
                }

                // Find personal data columns on the related table
                $relatedPersonal = array_values(array_intersect($columns, $personalFields));

                if (! empty($relatedPersonal)) {
                    $entry[$table] = $relatedPersonal;
                    $tablesWithPersonalData++;
                }
            }

            if (! empty($entry)) {
                $results[class_basename($subjectClass)] = $entry;
            }
        }

        // Build the final RoPA payload
        $ropa = [
            'generated_at' => now()->toIso8601String(),
            'total_tables_with_personal_data' => $tablesWithPersonalData,
            'data_map' => $results,
        ];

        // Persist the RoPA to storage so it can be downloaded or reviewed
        file_put_contents(
            storage_path('app/gdpr-ropa.json'),
            json_encode($ropa, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES)
        );

        return 'RoPA generated with '.$tablesWithPersonalData.' table(s) containing personally identifiable information.';
    }
}
