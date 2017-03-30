<?php

namespace Scaffolder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use stdClass ;

// Support classes
use Scaffolder\Support\Directory;


class BuildCommand extends Command
{
	protected $signature = 'scaffolder:build {app=webapp}';

	protected $description = 'Build the generated code to public folder';

	/**
	 * Execute the Command.
	 */
	public function handle()
	{


		switch ($this->argument('app')) {
			case 'webapp':
				
				$this->cleanPublicFolder();

				$gulpCommand = sprintf('gulp build --cwd "%s/codificar/scaffolder-theme-material/"', base_path('vendor'));

				//$this->info('- gulpCommand: '. $gulpCommand);	

				$handle = popen($gulpCommand, 'r');

				while(!feof($handle)) 
				{ 
					// send the current file part to the browser 
					$this->info(fread($handle, 1024)); 
				} 

				fclose($handle); 
				
				// php artisan serve
				$this->call('serve');

				break;

			default:
				$this->info('Invalid arguments');
				break;
		}
		
	}

	private function cleanPublicFolder(){
		$this->info('Cleaning public directory');

		File::deleteDirectory(sprintf("%s/app", base_path('public')));
		File::deleteDirectory(sprintf("%s/assets", base_path('public')));
		File::deleteDirectory(sprintf("%s/fonts", base_path('public')));
		File::deleteDirectory(sprintf("%s/maps", base_path('public')));
		File::deleteDirectory(sprintf("%s/scripts", base_path('public')));
		File::deleteDirectory(sprintf("%s/styles", base_path('public')));

		File::delete(sprintf("%s/index.html", base_path('public')));
	}


}