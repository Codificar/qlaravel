<?php

namespace Scaffolder\Support;

class Arrays
{
	public static function moveElement($array, $toMove, $targetIndex) {
		if (count($array) == 1)
			return $array;
		if (is_int($toMove)) {
			$tmp = array_splice($array, $toMove, 1);
			array_splice($array, $targetIndex, 0, $tmp);
			$output = $array;
		}
		elseif (is_string($toMove)) {
			$indexToMove = array_search($toMove, array_keys($array));
			$itemToMove = $array[$toMove];
			array_splice($array, $indexToMove, 1);
			$i = 0;
			$output = Array();
			foreach($array as $key => $item) {
				if ($i == $targetIndex) {
					$output[$toMove] = $itemToMove;
				}
				$output[$key] = $item;
				$i++;
			}
		}
		return $output;
	}
}

