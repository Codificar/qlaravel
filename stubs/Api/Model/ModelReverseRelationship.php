	
	/**
	 * Gets {{foreign_table}} associated record  .
	 */
	public function {{foreign_table}}()
	{
		return $this->{{type}}('{{model_namespace}}\{{foreign_model}}', '{{field}}', '{{foreign_field}}');
	}