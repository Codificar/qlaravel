	
	public function deleteAll{{related_table_pl_uc}}($id)
	{
		${{foreign_table_lw_pl}} = {{foreign_table}}::where('{{foreign_key}}' , '=', $id)->get();
		foreach (${{foreign_table_lw_pl}} as $key => $object) {
			$object->delete();
		}
	}
	