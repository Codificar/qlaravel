	// return enum field options
	public function get{{field_name_uc}}Options() {
		$options = {{model_name}}::${{field_name}};

		return $options;
	}
	