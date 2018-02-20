<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Pagination\Paginator;
use App\Http\Controllers\Controller;
use App\Models\{{class_name}} as {{class_name}}Model;;
use App\Http\Controllers\File as ScaffolderFile;
use Log;
use Flow\Config as FlowConfig;
use Flow\Request as FlowRequest;
use Flow\ConfigInterface;
use Flow\RequestInterface;

class {{class_name}}Controller extends Controller
{
	// blade views
	// index view

	public function show($id)
	{
		$file = {{class_name}}Model::find($id);
		return $file ;
	}

	public function upload()
	{
		$config = new FlowConfig();
		$config->setTempDir(storage_path() . '/tmp');
		$config->setDeleteChunksOnSave(true);

		$request = new FlowRequest();

		$totalSize = $request->getTotalSize();

		if ($totalSize && $totalSize > (1024 * 1024 * 4))
		{
			return $this->responseWithError('File size exceeds 4MB');
		}

		$requestFile = $request->getFile();

		$file = new ScaffolderFile($config, $request);

		if ($file->validateChunk()) {
			$file->saveChunk();
		}

		if ($file->save(storage_path() . '/tmp/' .  $request->getFileName()))
		{
			$file = FileModel::create([
				'mime_type' => $requestFile['type'],
				'size' => $requestFile['size'],
				'file_path' => storage_path() . '/tmp/',
				'filename' => $requestFile['name'],
				'disk' => 'local',
				'status' => false,
			]);

			return $file->id;
		}
		else
		{
			// Indicate that we are not done with all the chunks.
			return null;
		}
	}

}
