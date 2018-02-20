<?php

namespace Scaffolder\Support;

use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class Validator
{
	/**
	 * Parse a string based rule.
	 *
	 * @param  string  $rules
	 * @return array
	 */
	public static function parseStringRule($rules)
	{
		$parameters = [];

		// The format for specifying validation rules and parameters follows an
		// easy {rule}:{parameters} formatting convention. For instance the
		// rule "Max:3" states that the value may only be three letters.
		if (strpos($rules, ':') !== false) {
			list($rules, $parameter) = explode(':', $rules, 2);

			$parameters = self::parseParameters($rules, $parameter);
		}

		return [(trim($rules)), $parameters];
	}

	/**
	 * Parse a parameter list.
	 *
	 * @param  string  $rule
	 * @param  string  $parameter
	 * @return array
	 */
	public static function parseParameters($rule, $parameter)
	{
		if (strtolower($rule) == 'regex') {
			return [$parameter];
		}

		return str_getcsv($parameter);
	}

	/**
	 * Explode the rules into an array of rules.
	 *
	 * @param  string  $rules
	 * @return array
	 */
	public static function explodeRule($rule)
	{
		return explode('|', $rule);
	}

	/**
	 * Convert laravel validations to theme validation angular js.
	 *
	 * @param  string  $validations
	 * @return array
	 */
	public static function convertValidations($validations, $blnSearch = false){
		$validationsConverted = [];
		foreach (self::explodeRule($validations) as $validation) {
			$validation = self::parseStringRule($validation);

			if(isset($validation[0])){	

				$rule = $validation[0];
				$values = $validation[1];

				switch ($rule) {
					case 'required':
						if($blnSearch) {
							$attribute = null ;
							$attributeValue = null ;
						}
						else {
							$attribute = "required" ;
							$attributeValue = null ;
						}
						
						break;

					case 'max':
					case 'min':
						$attribute = $rule.'length' ;
						$attributeValue = $values[0] ;
						break;
					
					default:
						$attribute = null  ;
						$attributeValue = null ;
						break;
				}

				if($attribute)
					$validationsConverted[$attribute] = $attributeValue ;

			}
			
		}

		return $validationsConverted ;
	}

}