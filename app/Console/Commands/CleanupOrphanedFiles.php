<?php

namespace App\Console\Commands;

use App\Models\Upload;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\Console\Command\Command as CommandAlias;

class CleanupOrphanedFiles extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'uploads:cleanup {--dry-run : Show what would be deleted without actually deleting}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up orphaned files and database records';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');

        $this->info('Starting cleanup process...');

        // Clean up database records for missing files
        $this->cleanupMissingFiles($dryRun);

        // Clean up orphaned files (files without database records)
        $this->cleanupOrphanedFiles($dryRun);

        $this->info('Cleanup process completed!');

        return CommandAlias::SUCCESS;
    }

    /**
     * Clean up database records for files that no longer exist on disk.
     *
     * @param bool $dryRun
     */
    protected function cleanupMissingFiles(bool $dryRun): void
    {
        $this->info('Checking for database records with missing files...');

        $uploads = Upload::all();
        $deletedCount = 0;

        foreach ($uploads as $upload) {
            if (!$upload->fileExists()) {
                if ($dryRun) {
                    $this->line("Would delete record: {$upload->original_name} (ID: {$upload->id})");
                } else {
                    $upload->delete();
                    $this->line("Deleted record: {$upload->original_name} (ID: {$upload->id})");
                }
                $deletedCount++;
            }
        }

        if ($deletedCount === 0) {
            $this->info('No orphaned database records found.');
        } else {
            $action = $dryRun ? 'Would delete' : 'Deleted';
            $this->info("{$action} {$deletedCount} orphaned database records.");
        }
    }

    /**
     * Clean up files that exist on disk but have no database record.
     *
     * @param bool $dryRun
     */
    protected function cleanupOrphanedFiles(bool $dryRun): void
    {
        $this->info('Checking for orphaned files...');

        $disks = ['public', 'local']; // Add other disks as needed
        $deletedCount = 0;

        foreach ($disks as $diskName) {
            $disk = Storage::disk($diskName);

            if (!$disk->exists('uploads')) {
                continue;
            }

            $files = $disk->allFiles('uploads');

            foreach ($files as $filePath) {
                // Skip thumbnail files
                if (str_contains($filePath, '/thumbnails/')) {
                    continue;
                }

                $upload = Upload::query()->where('disk', $diskName)
                    ->where('file_path', $filePath)
                    ->first();

                if (!$upload) {
                    if ($dryRun) {
                        $this->line("Would delete file: {$filePath}");
                    } else {
                        $disk->delete($filePath);
                        $this->line("Deleted file: {$filePath}");
                    }
                    $deletedCount++;
                }
            }
        }

        if ($deletedCount === 0) {
            $this->info('No orphaned files found.');
        } else {
            $action = $dryRun ? 'Would delete' : 'Deleted';
            $this->info("{$action} {$deletedCount} orphaned files.");
        }
    }
}
