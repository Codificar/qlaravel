		
		if(isset($filters["{{table_name}}"])){
			${{table_name}}Conditions = $filters["{{table_name}}"] ;

			if($params["simpleFilter"])
			{
				{{simple_filter}}
			} else {
				// field by field condition
				{{field_conditions}}
			}

		}