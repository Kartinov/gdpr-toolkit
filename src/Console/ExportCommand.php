<?php

namespace Kartinov\GdprToolkit\Console;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class ExportCommand extends Command
{
    protected $signature = 'gdpr:export {model} {identifier} {--output= : Output file path (defaults to storage/app/gdpr-export.json)}';

    protected $description = 'Export all personal data for a given subject model instance';

    public function handle()
    {
        $modelClass = $this->argument('model');
        $identifier = $this->argument('identifier');
        $outputPath = $this->option('output') ?? storage_path('app/gdpr-export.json');

        if (! class_exists($modelClass)) {
            $this->error("Model class {$modelClass} does not exist.");
            return 1;
        }

        // Find the subject by ID or email
        $subject = $modelClass::query()
            ->where('id', $identifier)
            ->orWhere('email', $identifier)
            ->first();

        if (! $subject) {
            $this->error("No {$modelClass} found with identifier: {$identifier}");
            return 1;
        }

        $export = $this->collectPersonalData($subject);

        file_put_contents($outputPath, json_encode($export, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));

        $this->info("Export written to {$outputPath}");

        // Log audit entry
        $this->logAudit($subject, 'export', array_keys($export));

        return 0;
    }

    protected function collectPersonalData($subject): array
    {
        $data = [];

        // Subject's own personal data as direct key-value pairs
        if (method_exists($subject, 'exportPersonalData')) {
            $subjectBasename = class_basename($subject);
            $data[$subjectBasename] = $subject->exportPersonalData();
        }

        // Scan related tables via foreign keys
        $personalFields = config('gdpr-toolkit.personal_data_columns', []);
        $fkSuffix = config('gdpr-toolkit.foreign_key_suffix', '_id');
        $fkOverrides = config('gdpr-toolkit.foreign_key_overrides', []);

        $subjectTable = $subject->getTable();
        $subjectClass = get_class($subject);
        $subjectBasename = class_basename($subjectClass);
        $expectedFk = strtolower($subjectBasename) . $fkSuffix;

        $tables = DB::select('SHOW TABLES');
        $tableNames = array_map('current', $tables);

        foreach ($tableNames as $table) {
            if ($table === $subjectTable) {
                continue;
            }

            $columns = Schema::getColumnListing($table);
            $matchedFk = null;

            foreach ($columns as $col) {
                if ($col === $expectedFk) {
                    $matchedFk = $col;
                    break;
                }
                if (isset($fkOverrides[$col]) && $fkOverrides[$col] === $subjectClass) {
                    $matchedFk = $col;
                    break;
                }
            }

            if (! $matchedFk) {
                continue;
            }

            // Find related records
            $related = DB::table($table)
                ->where($matchedFk, $subject->getKey())
                ->get();

            foreach ($related as $record) {
                $personal = [];
                foreach ($columns as $col) {
                    if (in_array($col, $personalFields)) {
                        $personal[$col] = $record->$col;
                    }
                }

                if (! empty($personal)) {
                    $data[$subjectBasename][$table][] = $personal;
                }
            }
        }

        return $data;
    }

    protected function logAudit($subject, string $action, array $fields): void
    {
        if (! Schema::hasTable('audit_logs')) {
            return;
        }

        DB::table('audit_logs')->insert([
            'subject_type' => get_class($subject),
            'subject_id' => $subject->getKey(),
            'action' => $action,
            'fields_affected' => json_encode($fields),
            'performed_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }
}