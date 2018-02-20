		
		// join {{related_table}}
		if(isset($filters["{{class_name_lw}}"]) && isset($filters["{{class_name_lw}}"]["{{related_table_pl}}"])){
			$query->join('{{foreign_table}}', '{{class_name_lw}}.id', '=', '{{foreign_table}}.{{foreign_key}}');

			$query->whereIn('{{foreign_table}}.{{related_field}}', $filters["{{class_name_lw}}"]["{{related_table_pl}}"]);

			$query->groupBy('{{class_name_lw}}.id');

		}