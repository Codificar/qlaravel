<?php

namespace Scaffolder\Support;

class CamelCase
{
	/**
	 * Given an underscore_separated_string, this will convert the string
	 * to CamelCaseNotation.  Note that this will ignore any casing in the
	 * underscore separated string.
	 * 
	 * @param string $strString
	 * @return string
	 */
	public static function convertToCamelCase($strString) {
		return str_replace(' ', '', ucwords(str_replace('_', ' ', strtolower($strString))));
	}
    
	public static function underscoreFromCamelCase($strName) {
		if (strlen($strName) == 0) return '';

		$strToReturn = self::FirstCharacter($strName);

		for ($intIndex = 1; $intIndex < strlen($strName); $intIndex++) {
			$strChar = substr($strName, $intIndex, 1);
			if (strtoupper($strChar) == $strChar)
				$strToReturn .= '_' . $strChar;
			else
				$strToReturn .= $strChar;
		}
		
		return strtolower($strToReturn);
	}   

	/**
	 * Returns the first character of a given string, or null if the given
	 * string is null.
	 * @param string $strString 
	 * @return string the first character, or null
	 */
	public final static function firstCharacter($strString) {
		if (strlen($strString) > 0)
			return substr($strString, 0 , 1);
		else
			return null;
	}    


	public static function pluralize($strName) {
			// Special Rules go Here
			switch (true) {	
				case (strtolower($strName) == 'play'):
					return $strName . 's';
			}

			$intLength = strlen($strName);
			if (substr($strName, $intLength - 1) == "y")
				return substr($strName, 0, $intLength - 1) . "ies";
			if (substr($strName, $intLength - 1) == "s")
				return $strName . "es";
			if (substr($strName, $intLength - 1) == "x")
				return $strName . "es";
			if (substr($strName, $intLength - 1) == "z")
				return $strName . "zes";
			if (substr($strName, $intLength - 2) == "sh")
				return $strName . "es";
			if (substr($strName, $intLength - 2) == "ch")
				return $strName . "es";

			return $strName . "s";
		}
	
}