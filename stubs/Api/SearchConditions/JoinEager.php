
		// join {{foreign_model}}
		if(isset($filters["{{foreign_table}}"])){
			$query->join('{{foreign_table}}', '{{table_name}}.{{field}}', '=', '{{foreign_table}}.{{foreign_field}}');
		}