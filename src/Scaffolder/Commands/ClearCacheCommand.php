<?php

namespace Scaffolder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class ClearCacheCommand extends Command
{
    protected $signature = 'scaffolder:clear {what=all}';

    protected $description = 'Clear generated files like cache, json, drafts';

    /**
     * Execute the Command.
     */
    public function handle()
    {
       

        switch ($this->argument('what')) {
            case 'cache':
                $this->handleCache();
                break;

            case 'drafts':
                $this->handleBlade();
                break;
            
            default:
                $this->handleCache();
                $this->handleDrafts();
                break;
        }

        
    }

    /**
     * Execute the command for compiled cache files.
     */
    public function handleCache()
    {
        // Get the compiled files
        $compiledFiles = File::glob(base_path('scaffolder-config/cache/*.scf'));

        // Start progress bar
        $this->output->progressStart(count($compiledFiles));

        foreach ($compiledFiles as $compiledFile)
        {
            File::delete($compiledFile);

            // Advance progress
            $this->output->progressAdvance();
        }

        // Finish progress
        $this->output->progressFinish();

        $this->info('Cache cleared');
    }


    /**
     * Execute the command for drafts files and folder.
     */
    public function handleDrafts()
    {
        $success = File::cleanDirectory(base_path('drafts'));

        $this->info('Drafts cleared');
    }
}