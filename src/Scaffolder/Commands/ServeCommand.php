<?php

namespace Scaffolder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

use stdClass ;

// Support classes
use Scaffolder\Support\Directory;
use Scaffolder\Support\Json;
use Scaffolder\Support\Arrays;
use Scaffolder\Support\CamelCase;
use Scaffolder\Compilers\Support\PathParser;


class ServeCommand extends Command
{
	protected $signature = 'scaffolder:serve {app=webapp} {--o|overwrite : Overwrite generated files} {--g|generate : Generate files }';

	protected $description = 'Serve code for development purpose';

	// app config var
	private $scaffolderConfig ;

	/**
	 * Execute the Command.
	 */
	public function handle()
	{
		// Get app config
		$this->getScaffolderConfig();

		$overwrite = false;

		if($this->option('overwrite'))
			$overwrite = true;

		$generate = false;

		if($this->option('generate'))
			$generate = true;


		switch ($this->argument('app')) {
			case 'webapp':
				
				// gera código somente se houver a opcao
				if($generate) {
					// Gera codigo da api
					$this->call('scaffolder:generate', array('app' => 'api', '-c' => 'clear-all'));
					
					// Se parametro --overwrite selecionado, copia arquivos para seu respectivo destino
					$this->copyApiFiles($overwrite);
					
					// Gera codigo da pasta webapp
					$this->call('scaffolder:generate', array('app' => 'angularjs', '-c' => 'clear-all'));
					
					// Se parametro --overwrite selecionado, copia arquivos para seu respectivo destino
					$this->copyAngularjsFiles($overwrite);
				}
				
				$gulpCommand = sprintf('gulp serve --cwd "%s/codificar/scaffolder-theme-material/" > null', base_path('vendor'));

				$this->info('Running gulp in serve mode, wait your browser open...');	
				//$handle = popen($gulpCommand, 'r');

				$this->launchBackgroundProcess($gulpCommand);
				
				// php artisan serve
				$this->call('serve');

				break;

			default:
				$this->info('Invalid arguments');
				break;
		}
		
	}

	/**
	* Launch Background Process
	*
	* Launches a background process (note, provides no security itself, $call must be sanitized prior to use)
	* @param string $call the system call to make
	* @author raccettura
	*/
	private function launchBackgroundProcess($call) {
	 
		// Windows
		if($this->is_windows()){
			pclose(popen('start /b '.$call, 'r'));
		}
	 
		// Some sort of UNIX
		else {
			pclose(popen($call.' /dev/null &', 'r'));
		}
		return true;
	}
	 
	 
	/**
	* Is Windows
	*
	* Tells if we are running on Windows Platform
	* @author raccettura
	*/
	private function is_windows(){
		if(PHP_OS == 'WINNT' || PHP_OS == 'WIN32'){
			return true;
		}
		return false;
	}


	public function copyApiFiles($overwrite) {

		$command = sprintf('cp -r %s "%s/." "%s"', 
			(!$overwrite ? ' -u' : null) , 
			PathParser::parse($this->scaffolderConfig->generators->api->paths->base),
			base_path());
		
		shell_exec($command);

		$this->info('- Api files copied');	
	}

	public function copyAngularjsFiles($overwrite) {

		// resource angular js path
		Directory::createIfNotExists(PathParser::parse($this->scaffolderConfig->generators->angularjs->paths->resources), 0755, true);

		// copying page files
		$command = sprintf('cp -r %s "%s/." "%s/"', 
			(!$overwrite ? ' -u' : null) , 
			PathParser::parse($this->scaffolderConfig->generators->angularjs->paths->index),
			PathParser::parse($this->scaffolderConfig->generators->angularjs->paths->resources));

		shell_exec($command);
		
		$this->info('- Angularjs files copied');	
	}


	/**
	 * Get the app.json configuration and parse to an object
	 *
	 * @return void
	 */
	private function getScaffolderConfig(){
		// Get app config
		$this->scaffolderConfig = Json::decodeFile(base_path('scaffolder-config/app.json'));

	}

}