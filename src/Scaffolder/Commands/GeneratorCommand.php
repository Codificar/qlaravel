<?php

namespace Scaffolder\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
// API classes
use Scaffolder\Compilers\Core\ControllerCompiler;
use Scaffolder\Compilers\Core\MigrationCompiler;
use Scaffolder\Compilers\Core\ModelCompiler;
use Scaffolder\Compilers\Core\RouteCompiler;

// BLADE classes
use Scaffolder\Compilers\Blade\CreateViewCompiler;
use Scaffolder\Compilers\Blade\EditViewCompiler;
use Scaffolder\Compilers\Blade\IndexViewCompiler;
use Scaffolder\Compilers\Blade\PageLayoutCompiler;
// AngularJs classes
use Scaffolder\Compilers\AngularJs\ResourceCompiler;
use Scaffolder\Compilers\AngularJs\ModuleCompiler;
use Scaffolder\Compilers\AngularJs\TranslateCompiler;
use Scaffolder\Compilers\AngularJs\IndexModuleCompiler;
use Scaffolder\Compilers\AngularJs\IndexApiCompiler;

// register
use Scaffolder\Compilers\AngularJs\RegisterModuleCompiler;
use Scaffolder\Compilers\AngularJs\RegisterControllerCompiler;
use Scaffolder\Compilers\AngularJs\RegisterTemplateCompiler;

// list
use Scaffolder\Compilers\AngularJs\ListModuleCompiler;
use Scaffolder\Compilers\AngularJs\ListControllerCompiler;
use Scaffolder\Compilers\AngularJs\ListTemplateCompiler;
use Scaffolder\Compilers\AngularJs\ListDetailCompiler;
use Scaffolder\Compilers\AngularJs\ListChooseColumnsCompiler;

// Support classes
use Scaffolder\Support\Directory;
use Scaffolder\Support\Json;
use Scaffolder\Support\Arrays;
use Scaffolder\Support\CamelCase;
use Scaffolder\Themes\IScaffolderThemeLayouts;
use Scaffolder\Themes\IScaffolderThemeViews;
use Scaffolder\Compilers\Support\PathParser;

use stdClass ;

class GeneratorCommand extends Command
{
	protected $signature = 'scaffolder:generate {app=laravel} {--c|clear-all : Clears cache and drafts before generate}';

	protected $description = 'Scaffold an application';

	protected $stubsDirectory;

	public function __construct()
	{
		parent::__construct();

		$this->stubsDirectory = __DIR__ . '/../../../stubs/';
	}

	/**
	 * Execute the Command.
	 * @return void
	 */
	public function handle()
	{
		// check if is to clear cache
		if($this->option('clear-all')){
			$this->call('scaffolder:clear');
		}

		// Create drafs directory
		Directory::createIfNotExists(base_path('drafts'));

		switch ($this->argument('app')) {
			case 'api':
				$this->handleApi();
				break;

			case 'angularjs':
				$this->handleAngularJs();
				break;

			case 'ionic':
				# code...
				break;

			case 'android':
				#TODO implement code...
				break;

			case 'ios':
				#TODO implement code...
				break;
			
			case 'blade':
				$this->handleBlade();
				break;
				
			default:
				$this->handleLaravel();
				break;
		}

		
	}

	/**
	 * API Generation command for API files.  
	 * @return void
	 */
	private function handleApi(){
		// Get all the models
		$modelsData = $this->getAllModelsData();

		// Start progress bar
		$this->output->progressStart(count($modelsData));

		// Get app config
		$scaffolderConfig = $this->getScaffolderConfig();

		// Compiler output
		$modelCompilerOutput = [];
		$controllerCompilerOutput = [];
		$migrationCompilerOutput = [];

		// Sidenav links
		$sidenavLinks = [];

		// Compiled routes
		$compiledRoutes = '';

		// Create route compiler
		$routeCompiler = new RouteCompiler($scaffolderConfig);

		// Create models directory
		Directory::createIfNotExists(app_path('Models'));

		// Create drafts directory
		// migrations
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->migrations), 0755, true);
		// models
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->models), 0755, true);
		// repositories
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->repositories), 0755, true);
		// controllers
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->controllers), 0755, true);
		// routes
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->routes), 0755, true);
		
		// Iterate over models data
		foreach ($modelsData as $modelData)
		{
			
			// Create Compilers
			$stubModel = null;
			$stubController = null;
			
			if ($modelData->tableName == "file") {
				$stubModel = 'Model/FileModel.php';
				$stubController = 'Controller/FileController.php';
			}

			$modelCompiler = new ModelCompiler($scaffolderConfig, $modelData, $stubModel);
			$migrationCompiler = new MigrationCompiler($scaffolderConfig, $modelData);
			$controllerCompiler = new ControllerCompiler($scaffolderConfig, $modelData, $stubController);

			// Compile stubs
			array_push($modelCompilerOutput, $modelCompiler->compile());
			array_push($controllerCompilerOutput, $controllerCompiler->compile());
			array_push($migrationCompilerOutput, $migrationCompiler->compile());
			
			$compiledRoutes .= $routeCompiler->replaceResource($modelData);
			//

			// Add entity link
			array_push($sidenavLinks, $modelData->modelName);

			// Advance progress
			$this->output->progressAdvance();
		}

		// Store compiled routes
		$routeCompiler->compileGroup($compiledRoutes);

		// Finish progress
		$this->output->progressFinish();

		// Summary
		$this->comment('- Files created');

		$this->comment('- - Controllers');
		foreach ($controllerCompilerOutput as $controllerFile)
		{
			$this->info('- - - ' . $controllerFile);
		}

		$this->comment('- - Migrations');
		foreach ($migrationCompilerOutput as $migrationFile)
		{
			$this->info('- - - ' . $migrationFile);
		}

		$this->comment('- - Models');
		foreach ($modelCompilerOutput as $modelFile)
		{
			$this->info('- - - ' . $modelFile);
		}
	}

	/**
	 * Code Generation command for AngularJs Material design files.  
	 * @return void
	 */
	private function handleAngularJs(){
		// Get all the models
		$modelsData = $this->getAllModelsData();

		// Start progress bar
		$this->output->progressStart((count($modelsData) * 2) + (count($modelsData) * 2 * 4) + 1);

		// Get app config
		$scaffolderConfig = $this->getScaffolderConfig();

		// Compiler output
		$resourceCompilerOutput = [];
		$moduleCompilerOutput = [];
		$translateCompilerOutput = [];
		// register
		$registerModuleCompilerOutput = [];
		$registerControllerCompilerOutput = [];
		$registerTemplateCompilerOutput = [];
		
		// list
		$listModuleCompilerOutput = [];
		$listControllerCompilerOutput = [];
		$listTemplateCompilerOutput = [];
		//$listDetailCompilerOutput = [];
		//$listChooseColumnsCompilerOutput = [];

		// Compiled indexes
		$compiledIndexes = '';
	
		// Create index compiler
		$indexModuleCompiler = new IndexModuleCompiler($scaffolderConfig);

		$indexApiCompiler = new IndexApiCompiler($scaffolderConfig);

		// Create drafts directory
		// pages
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->pages), 0755, true);
		
		$intKey = 1;
		// Iterate over models data
		foreach ($modelsData as $modelData)
		{
			
			// Create Compilers
			$resourceCompiler = new ResourceCompiler($scaffolderConfig, $modelData);
			$moduleCompiler = new ModuleCompiler($scaffolderConfig, $modelData);
			// register
			$registerModuleCompiler = new RegisterModuleCompiler($scaffolderConfig, $modelData);
			$registerControllerCompiler = new RegisterControllerCompiler($scaffolderConfig, $modelData);
			$registerTemplateCompiler = new RegisterTemplateCompiler($scaffolderConfig, $modelData);
			// translate
			$translateCompiler = new TranslateCompiler($scaffolderConfig, $modelData);
			// list
			$listModuleCompiler = new ListModuleCompiler($scaffolderConfig, $modelData);
			$listControllerCompiler = new ListControllerCompiler($scaffolderConfig, $modelData);
			$listTemplateCompiler = new ListTemplateCompiler($scaffolderConfig, $modelData);
			//$listDetailCompiler = new ListDetailCompiler($scaffolderConfig, $modelData);
			//$listChooseColumnsCompiler = new ListChooseColumnsCompiler($scaffolderConfig, $modelData);
			
			// Compile stubs
			array_push($resourceCompilerOutput, $resourceCompiler->compile());
			array_push($moduleCompilerOutput, $moduleCompiler->compile());
			array_push($translateCompilerOutput, $translateCompiler->compile());
			// register
			array_push($registerModuleCompilerOutput, $registerModuleCompiler->compile());
			array_push($registerControllerCompilerOutput, $registerControllerCompiler->compile());
			array_push($registerTemplateCompilerOutput, $registerTemplateCompiler->compile());
			// list
			array_push($listModuleCompilerOutput, $listModuleCompiler->compile());
			array_push($listControllerCompilerOutput, $listControllerCompiler->compile());
			array_push($listTemplateCompilerOutput, $listTemplateCompiler->compile());
			//array_push($listDetailCompilerOutput, $listDetailCompiler->compile());
			//array_push($listChooseColumnsCompilerOutput, $listChooseColumnsCompiler->compile());

			$compiledIndexes .= $indexModuleCompiler->replaceResource($modelData);
			if ($intKey < count($modelsData))
				$compiledIndexes .= ",";

			$intKey++;
			// Advance progress
			$this->output->progressAdvance();
		}

		// Store compiled indexes
		$indexModuleCompiler->compileGroup($compiledIndexes);
		
		// store compiled api
		$fileApi = $indexApiCompiler->compile();

		// Advance progress
		$this->output->progressAdvance();
	
		// Finish progress
		$this->output->progressFinish();

		// Summary
		$this->comment('- Files created');

		$this->comment('- - Index Api');

		$this->info('- - - ' . $fileApi);

		$this->comment('- - Resources');
		foreach ($resourceCompilerOutput as $file)
		{
			$this->info('- - - ' . $file);
		}

		$this->comment('- - Modules');
		foreach ($moduleCompilerOutput as $file)
		{
			$this->info('- - - ' . $file);
		}

		$this->comment('- - Translate');
		foreach ($translateCompilerOutput as $file)
		{
			$this->info('- - - ' . $file);
		}

		$this->comment('- - Register');

		$this->comment('- - - Modules');
		foreach ($registerModuleCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}

		$this->comment('- - - Controllers');
		foreach ($registerControllerCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}

		$this->comment('- - - Templates');
		foreach ($registerTemplateCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}

		$this->comment('- - List');

		$this->comment('- - - Modules');
		foreach ($listModuleCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}


		$this->comment('- - - Controllers');
		foreach ($listControllerCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}

		$this->comment('- - - Templates');
		foreach ($listTemplateCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}
		/*
		$this->comment('- - - Detail Dialog');
		foreach ($listDetailCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}

		$this->comment('- - - Choose Columns Dialog');
		foreach ($listChooseColumnsCompilerOutput as $file)
		{
			$this->info('- - - - ' . $file);
		}
		*/
		
	}

	/**
	 * Generation command for Blade. 
	 * Generates Blade templates
	 * @return void
	 */
	private function handleBlade(){
		// Get all the models
		$modelsData = $this->getAllModelsData();

		// Start progress bar
		$this->output->progressStart(count($modelsData));

		// Get app config
		$scaffolderConfig = $this->getScaffolderConfig();

		// Compilers
		$indexViewCompiler = new IndexViewCompiler();
		$createViewCompiler = new CreateViewCompiler();
		$editViewCompiler = new EditViewCompiler();
		$pageLayoutViewCompiler = new PageLayoutCompiler();

		// Compiler output
		$viewCompilerOutput = [];
		
		// Sidenav links
		$sidenavLinks = [];

		// Compiled routes
		$compiledRoutes = '';

		// Get stubs
		$indexViewStub = File::get($this->themeViews->getIndexPath());
		$createViewStub = File::get($this->themeViews->getCreatePath());
		$editViewStub = File::get($this->themeViews->getEditPath());
	
		// views
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->views), 0755, true);
		// layouts
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->layouts), 0755, true);
		// assets
		Directory::createIfNotExists(PathParser::parse($scaffolderConfig->generator->paths->assets), 0755, true);
	
		
		// Iterate over model files
		foreach ($modelsData as $modelData)
		{
			// Get model name
			$modelName = $modelData->modelName;

			// Create views directory
			Directory::createIfNotExists(base_path('resources/views/' . strtolower($modelName)));

			//set hash
			$modelHash = $modelData->modelHash;


			// Compile stubs
			array_push($viewCompilerOutput, $indexViewCompiler->compile($indexViewStub, $modelName, $modelData, $scaffolderConfig, $modelHash));
			array_push($viewCompilerOutput, $createViewCompiler->compile($createViewStub, $modelName, $modelData, $scaffolderConfig, $modelHash));
			array_push($viewCompilerOutput, $editViewCompiler->compile($editViewStub, $modelName, $modelData, $scaffolderConfig, $modelHash));
			// Add entity link
			array_push($sidenavLinks, $modelName);

			// Advance progress
			$this->output->progressAdvance();
		}

		// Create layouts directory
		Directory::createIfNotExists(base_path('resources/views/layouts'), 0755, true);

		// Store compiled page layout
		array_push($viewCompilerOutput, $pageLayoutViewCompiler->compile(File::get($this->themeLayouts->getPagePath()), null, null, $scaffolderConfig, null, ['links' => $sidenavLinks]));

		// Store create layout
		$createLayout = PathParser::parse($scaffolderConfig->generator->paths->views). 'layouts/create.blade.php' ;
		File::copy($this->themeLayouts->getCreatePath(), $createLayout);
		array_push($viewCompilerOutput, $createLayout);

		// Store edit layout
		$editLayout = PathParser::parse($scaffolderConfig->generator->paths->views). 'layouts/edit.blade.php' ;
		File::copy($this->themeLayouts->getCreatePath(), $editLayout);
		array_push($viewCompilerOutput, $editLayout);

		// Store dashboard
		$dashboardLayout = PathParser::parse($scaffolderConfig->generator->paths->views). 'layouts/dashboard.blade.php' ;
		File::copy($this->themeLayouts->getCreatePath(), $dashboardLayout);
		array_push($viewCompilerOutput, $dashboardLayout);

		// Finish progress
		$this->output->progressFinish();

		// Summary
		$this->comment('- Files created');

		$this->comment('- - Views');
		foreach ($viewCompilerOutput as $viewFile)
		{
			$this->info('- - - ' . $viewFile);
		}

	}

	/**
	 * Generation command for laravel. 
	 * Generates API Code 
	 * Generates Blade templates
	 * @return void
	 */
	private function handleLaravel(){
		// generate API
		$this->handleApi();

		// generate BLADE
		$this->handleApi();

	}

	/**
	 * Get the app.json configuration and parse to an object
	 *
	 * @return void
	 */
	private function getScaffolderConfig(){
		// Get app config
		$scaffolderConfig = Json::decodeFile(base_path('scaffolder-config/app.json'));

		$scaffolderConfig->generator = $scaffolderConfig->generators->{$this->argument('app')};

		return $scaffolderConfig ;
	}

	/**
	 * Get all model files and ordenates by
	 *
	 * @return object
	 */
	private function getAllModelsData(){

		$modelFiles = File::allFiles(base_path('scaffolder-config/models/'));

		$modelsData = [];
		$orderedModelsData = [];

		// set indexes
		//$firstKey = 
		foreach ($modelFiles as $modelFile)
		{

			// Get model data
			$modelData = Json::decodeFile($modelFile->getRealPath());


			// Get model name
			$modelName = CamelCase::convertToCamelCase(($modelFile->getBasename('.' . $modelFile->getExtension())));

			// Get model hash
			$modelHash = md5_file($modelFile->getRealPath());

			// Set model name
			$modelData->modelName = $modelName ;

			// Set model name
			$modelData->modelHash = $modelHash ;

			// get primary field or create 
			$primaryField = $this->getPrimaryKeyField($modelData);

			// put primary key at first position
			if(isset($primaryField->declared) && !$primaryField->declared){
				$modelData->fields = array_pad($modelData->fields, -(count($modelData->fields)+1), $primaryField);
			}

			// set timestamps
			if(isset($modelData->timeStamps) && $modelData->timeStamps){
				$createdAtField = new stdClass;
				$createdAtField->name = "created_at" ;
				$createdAtField->index = "none" ;
				$createdAtField->declared =  false ;
				$createdAtField->type = new stdClass ;
				$createdAtField->type->ui = 'timestamp' ;
				$createdAtField->type->db = 'datetime' ;
				$createdAtField->foreignKey = [];
				$createdAtField->validations = "required" ;
				$updatedAtField = clone($createdAtField);
				$updatedAtField->name = "updated_at" ;

				array_push($modelData->fields, $createdAtField);
				array_push($modelData->fields, $updatedAtField);
			}

			$modelsData[$modelData->tableName] = $modelData ;

			// put all migrations pre-requisites in top of generation hieraquical
			if(isset($modelData->migrationPreRequisites) && count($modelData->migrationPreRequisites) == 0){
				$modelsData = Arrays::moveElement($modelsData, $modelData->tableName, 0);
			}

		}

		// let put all pre-requisites in order
		$actualTablePosition = 0 ;
		foreach ($modelsData as $key => $modelData)
		{
			// set migration order
			$modelData->migrationOrder = isset($modelData->migrationOrder) ? $modelData->migrationOrder : $actualTablePosition ;

			$positions = array_keys($modelsData);

			if(isset($modelData->migrationPreRequisites)){
				
				foreach($modelData->migrationPreRequisites as $preRequiste){
					$preRequisitePosition = array_search($preRequiste, $positions);
					// change positions
					if( $preRequisitePosition >  $actualTablePosition){
						$modelData->migrationOrder = $preRequisitePosition ;
						$modelsData[$preRequiste]->migrationOrder = $actualTablePosition ;
						$modelsData = Arrays::moveElement($modelsData, $preRequiste, $actualTablePosition);
						
					}
				}
			}
	
			// search for other fields relationships
			foreach($modelData->fields as $field){
				

				if(isset($field->foreignKey->relationship)){
					switch ($field->foreignKey->relationship) {
						case 'belongsTo':
							// hasOne or hasMay are the inverse relationship for belongsTo
							$relationship = new stdClass ;
							$relationship->type  = $field->foreignKey->reverse ;
							$relationship->foreignKey = $field->name ;
							$relationship->localKey = $field->foreignKey->field ;
							$relationship->modelName = $modelData->modelName ;
							array_push($modelsData[$field->foreignKey->table]->reverseRelationships, $relationship);
							break;
						case 'belongsToMany':
							// belongsToMany is the inverse relationship for belongsTo
							$relationship = new stdClass ;
							$relationship->type  = $field->foreignKey->reverse ;
							$relationship->foreignKey = $field->name ;
							$relationship->relatedTable = isset($field->relatedTable) ? $field->relatedTable : '';
							$relationship->relatedField = isset($field->relatedField) ? $field->relatedField : '';
							$relationship->ui = $field->type->ui;
							$relationship->localKey = $field->foreignKey->field ;
							$relationship->modelName = $modelData->modelName ;
							$relationship->tableName = $modelData->tableName ;
							array_push($modelsData[$field->foreignKey->table]->reverseRelationships, $relationship);
							break;
						default:
							# code...
							break;
					}
				
				}
				
			}
			

			$actualTablePosition++ ;
		}
	
		// $this->info(print_r($modelsData, 1));
	
		return $modelsData ;
	
	}

	protected function getPrimaryKeyField($modelData){
		$primaryKey = new stdClass;
		$primaryKey->name = "id" ;
		$primaryKey->index = "primary" ;
		$primaryKey->declared =  false ;
		$primaryKey->type = new stdClass ;
		$primaryKey->type->ui = 'label' ;
		$primaryKey->type->db = 'integer' ;
		$primaryKey->foreignKey = [];
		$primaryKey->validations = "required" ;

		foreach ($modelData->fields as $field)
		{
			if ($field->index == 'primary')
			{
				$primaryKey = $field ;
				break;
			}
		}

		return $primaryKey ;
	}

}