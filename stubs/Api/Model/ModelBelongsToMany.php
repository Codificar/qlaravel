	
	/**
	 * Gets {{foreign_table}} associated record  .
	 */
	public function {{foreign_table}}()
	{
		return $this->belongsToMany('{{model_namespace}}\{{foreign_model}}', '{{table_name}}', '{{foreign_key}}', '{{related_field}}');
	}