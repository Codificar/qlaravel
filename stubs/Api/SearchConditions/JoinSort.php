if(strpos($sort["field"], "{{foreign_table}}") !== false){
					$query->join('{{foreign_table}}', '{{field}}', '=', '{{foreign_table}}.id');
				}
