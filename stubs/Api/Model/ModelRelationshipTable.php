	// belongs to many logic
	public function save{{related_table_pl_uc}}($vars, ${{class_name_lw}}) 
	{
		$arr{{related_table_pl_uc}} = array();
		foreach ($vars['{{related_table_pl}}'] as $key => $value) {
			$data = array('{{foreign_key}}' => ${{class_name_lw}}['id'], '{{related_field}}' => $value);
			${{foreign_table_lw}} = new {{foreign_table}};
			${{foreign_table_lw}}->fill($data);
			${{foreign_table_lw}}->save();
		}
	}