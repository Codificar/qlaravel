<?php

namespace {{namespace}};

use {{namespace_model_extend}} as Model;
use Illuminate\Support\Facades\Storage;

class {{class_name}} extends Model
{
	/**
	 * The database table used by the model.
	 *
	 * @var string
	 */
	protected $table = '{{table_name}}';
	
	{{primaryAttribute}}
	public $fillable = [
		{{fillable}}
	];

	public static function boot()
	{
		parent::boot();

		static::deleting(function($model)
		{
			Storage::disk($model->disk)->delete($model->file_path);
		});
	}
	
}