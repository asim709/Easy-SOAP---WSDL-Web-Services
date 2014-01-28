<?php

//
// class.strings.php
// version 1.4.2, 2004-08-16
//
// Description
//
// A PHP library of helpful string manipulation methods. This class does not
// need to be assigned to an object, as you could use the :: access method,
// such as Strings::validateString
//
// Author
//
// Andrew Collington, 2004
// php@amnuts.com, http://php.amnuts.com/
//
// Feedback
//
// There is message board at the following address:
//
// http://php.amnuts.com/forums/index.php
//
// Please use that to post up any comments, questions, bug reports, etc. You
// can also use the board to show off your use of the script.
//
// License
//
// This class is available free of charge for personal or non-profit work. If
// you are using it in a commercial setting, please contact php@amnuts.com for
// payment and licensing terms.
//
// Support
//
// If you like this script, or any of my others, then please take a moment
// to consider giving a donation. This will encourage me to make updates and
// create new scripts which I would make available to you. If you would like
// to donate anything, then there is a link from my website to PayPal.
//

class Strings {
	
	public function WEB_SERVICE_INFO() {
		$info = new WSDLInfo ( "Text" );
		
		$info->addMethod ( "validateEmail", "validateEmail", array (array ('email', DTYPE_STRING ) ), DTYPE_INT );
		$info->addMethod ( "convertSymbolsToEntities", "convertSymbolsToEntities", array (array ('string', DTYPE_STRING ) ), DTYPE_STRING );
		$info->addMethod ( "convertTextToHTML", "convertTextToHTML", array (array ('element', DTYPE_STRING ) ), DTYPE_INT );
		$info->addMethod ( "trimStringToLength", "trimStringToLength", array (array ('string', DTYPE_STRING ), array ('length', DTYPE_INT ) ), DTYPE_STRING );
		$info->addMethod ( "trimWordFromString", "trimWordFromString", array (array ('string', DTYPE_STRING ) ), DTYPE_STRING );
		$info->addMethod ( "trimFirstWordFromString", "trimFirstWordFromString", array (array ('string', DTYPE_STRING ) ), DTYPE_STRING );
		$info->addMethod ( "trimLastWordFromString", "trimLastWordFromString", array (array ('string', DTYPE_STRING ) ), DTYPE_STRING );
		$info->addMethod ( "getOrdinalString", "getOrdinalString", array (array ('value', DTYPE_INT ) ), DTYPE_STRING );
		$info->addMethod ( "countSentences", "countSentences", array (array ('value', DTYPE_STRING ) ), DTYPE_INT );
		$info->addMethod ( "countParagraphs", "countParagraphs", array (array ('string', DTYPE_STRING ) ), DTYPE_INT );
		$info->addMethod ( "getStringInformation", "getStringInformation", array (array ('string', DTYPE_STRING ) ), DTYPE_STRING );
		
		return $info;
	}
	
	/**
	 * Validates whether the given element is a string.
	 *
	 * @return bool
	 * @param $element string       	
	 * @param $require_content bool
	 *       	 If the string can be empty or not
	 */
	function validateString($element, $require_content = true) {
		return (! is_string ( $element )) ? false : ($require_content && $element == '' ? false : true);
	}
	
	/**
	 * Validates whether the given element is an array.
	 *
	 * @return bool
	 * @param $element array       	
	 * @param $require_content bool
	 *       	 If the array can be empty or not
	 */
	function validateArray($element, $require_content = true) {
		return (! is_array ( $element )) ? false : ($require_content && empty ( $element ) ? false : true);
	}
	
	/**
	 * Validates whether an email address has a valid format.
	 *
	 * @return bool
	 * @param $email string       	
	 */
	function validateEmail($email) {
		return (eregi ( "[0-9a-z]([-_.]?[0-9a-z])*@[0-9a-z]([-.]?[0-9a-z])*\\.[a-z]{2,3}", $email )) ? true : false;
	}
	
	/**
	 * Converts high-character symbols into their respective html entities.
	 *
	 * @return string
	 * @param $string string       	
	 */
	function convertSymbolsToEntities($string) {
		static $symbols = array ('‚', 'ƒ', '„', '…', '†', '‡', 'ˆ', '‰', 'Š', '‹', 'Œ', '‘', '’', '“', '”', '•', '–', '—', '˜', '™', 'š', '›', 'œ', 'Ÿ', '€', 'Æ', 'Á', 'Â', 'À', 'Å', 'Ã', 'Ä', 'Ç', 'Ð', 'É', 'Ê', 'È', 'Ë', 'Í', 'Î', 'Ì', 'Ï', 'Ñ', 'Ó', 'Ô', 'Ò', 'Ø', 'Õ', 'Ö', 'Þ', 'Ú', 'Û', 'Ù', 'Ü', 'Ý', 'á', 'â', 'æ', 'à', 'å', 'ã', 'ä', 'ç', 'é', 'ê', 'è', 'ð', 'ë', 'í', 'î', 'ì', 'ï', 'ñ', 'ó', 'ô', 'ò', 'ø', 'õ', 'ö', 'ß', 'þ', 'ú', 'û', 'ù', 'ü', 'ý', 'ÿ', '¡', '£', '¤', '¥', '¦', '§', '¨', '©', 'ª', '«', '¬', '­', '®', '¯', '°', '±', '²', '³', '´', 'µ', '¶', '·', '¸', '¹', 'º', '»', '¼', '½', '¾', '¿', '×', '÷', '¢', '…', 'µ' );
		static $entities = array ('&#8218;', '&#402;', '&#8222;', '&#8230;', '&#8224;', '&#8225;', '&#710;', '&#8240;', '&#352;', '&#8249;', '&#338;', '&#8216;', '&#8217;', '&#8220;', '&#8221;', '&#8226;', '&#8211;', '&#8212;', '&#732;', '&#8482;', '&#353;', '&#8250;', '&#339;', '&#376;', '&#8364;', '&aelig;', '&aacute;', '&acirc;', '&agrave;', '&aring;', '&atilde;', '&auml;', '&ccedil;', '&eth;', '&eacute;', '&ecirc;', '&egrave;', '&euml;', '&iacute;', '&icirc;', '&igrave;', '&iuml;', '&ntilde;', '&oacute;', '&ocirc;', '&ograve;', '&oslash;', '&otilde;', '&ouml;', '&thorn;', '&uacute;', '&ucirc;', '&ugrave;', '&uuml;', '&yacute;', '&aacute;', '&acirc;', '&aelig;', '&agrave;', '&aring;', '&atilde;', '&auml;', '&ccedil;', '&eacute;', '&ecirc;', '&egrave;', '&eth;', '&euml;', '&iacute;', '&icirc;', '&igrave;', '&iuml;', '&ntilde;', '&oacute;', '&ocirc;', '&ograve;', '&oslash;', '&otilde;', '&ouml;', '&szlig;', '&thorn;', '&uacute;', '&ucirc;', '&ugrave;', '&uuml;', '&yacute;', '&yuml;', '&iexcl;', '&pound;', '&curren;', '&yen;', '&brvbar;', '&sect;', '&uml;', '&copy;', '&ordf;', '&laquo;', '&not;', '&shy;', '&reg;', '&macr;', '&deg;', '&plusmn;', '&sup2;', '&sup3;', '&acute;', '&micro;', '&para;', '&middot;', '&cedil;', '&sup1;', '&ordm;', '&raquo;', '&frac14;', '&frac12;', '&frac34;', '&iquest;', '&times;', '&divide;', '&cent;', '...', '&micro;' );
		
		if (Strings::validateString ( $string, false )) {
			return str_replace ( $symbols, $entities, $string );
		} else {
			return $string;
		}
	}
	
	/**
	 * Converts all strings, or all elements of an array, no matter how nested
	 * the array is, to html entities.
	 *
	 * @return string array
	 * @param $element string|array       	
	 */
	function convertTextToHTML($element) {
		return Strings::processFunction ( $element, 'htmlentities' );
	}
	
	/**
	 * Cuts the given string to a certain length without breaking a word.
	 *
	 * @return string
	 * @param $string string       	
	 * @param $length int
	 *       	 number of maximum characters leave remaining
	 * @param $more bool
	 *       	 whether to display '...' on the end of the trimmed string
	 */
	function trimStringToLength($string, $length, $more = true) {
		if (Strings::validateString ( $string )) {
			$trimmed = str_replace ( '<br>', '... ', ereg_replace ( '(<br>)+', '<br>', $string ) );
			$trimmed = strip_tags ( $trimmed );
			if (strlen ( $trimmed ) > $length) {
				$trimmed = substr ( $trimmed, 0, strrpos ( substr ( $trimmed, 0, $length ), ' ' ) );
				if ($more === true) {
					$trimmed .= '...';
				}
			}
			return $trimmed;
		}
	}
	
	/**
	 * Removes the first or last word from a string.
	 *
	 * @return string
	 * @param $string string       	
	 * @param $start bool
	 *       	 whether to trim at start (true) or end (false) of string
	 */
	function trimWordFromString($string, $start = true) {
		if (Strings::validateString ( $string )) {
			$trimmed = trim ( $string );
			if (! substr_count ( $trimmed, ' ' )) {
				return $trimmed;
			} else {
				return ($start) ? substr ( $trimmed, strpos ( $trimmed, ' ' ) + 1, strlen ( $trimmed ) ) : substr ( $trimmed, 0, strrpos ( $trimmed, ' ' ) );
			}
		}
	}
	
	/**
	 * Removes the first word from a string.
	 *
	 * @return string
	 * @param $string string       	
	 * @see trimWordFromString()
	 */
	function trimFirstWordFromString($string) {
		return Strings::trimWordFromString ( $string, true );
	}
	
	/**
	 * Removes the last word from a string.
	 *
	 * @return string
	 * @param $string string       	
	 * @see trimWordFromString()
	 */
	function trimLastWordFromString($string) {
		return Strings::trimWordFromString ( $string, false );
	}
	
	/**
	 * Can perform a full trim a string, or all elements of an array, no
	 * matter how nested the array is.
	 *
	 * @return string array
	 * @param $element string|array       	
	 */
	function trimString($element) {
		return Strings::processFunction ( $element, 'trim' );
	}
	
	/**
	 * Can left-trim a string, or all elements of an array, no matter
	 * how nested the array is.
	 *
	 * @return string array
	 * @param $element string|array       	
	 */
	function trimStringLeft($element) {
		return Strings::processFunction ( $element, 'ltrim' );
	}
	
	/**
	 * Can right-trim a string, or all elements of an array, no matter
	 * how nested the array is.
	 *
	 * @return string array
	 * @param $element string|array       	
	 */
	function trimStringRight($element) {
		return Strings::processFunction ( $element, 'rtrim' );
	}
	
	/**
	 * Adds slashes to a string, or all elements of an array, no matter
	 * how nested the array is.
	 *
	 * If the 'check_gpc' parameter is true then slashes will be applied
	 * depending on magic_quotes setting.
	 *
	 * @return string array
	 * @param $element string|array       	
	 * @param $check_gpc bool       	
	 */
	function addSlashesToString($element, $check_gpc = true) {
		return ($check_gpc && get_magic_quotes_gpc ()) ? $element : Strings::processFunction ( $element, 'addslashes' );
	}
	
	/**
	 * Removes slashes from a string, or all elements of an array, no matter
	 * how nested the array is.
	 *
	 * If the 'check_gpc' parameter is true then slashes will be removed
	 * depending on magic_quotes setting.
	 *
	 * @return string array
	 * @param $element string|array       	
	 * @param $check_gpc bool       	
	 */
	function trimSlashesFromString($element, $check_gpc = true) {
		return ($check_gpc && ! get_magic_quotes_gpc ()) ? $element : Strings::processFunction ( $element, 'stripslashes' );
	}
	
	/**
	 * Performs the passed function recursively.
	 *
	 * @return string array
	 * @param $element string|array       	
	 * @param $function string       	
	 */
	function processFunction($element, $function) {
		if (function_exists ( $function ) === true) {
			if (Strings::validateArray ( $element, false ) === false) {
				return $function ( $element );
			} else {
				foreach ( $element as $key => $val ) {
					if (Strings::validateArray ( $element [$key], false )) {
						$element [$key] = Strings::processFunction ( $element [$key], $function );
					} else {
						$element [$key] = $function ( $element [$key] );
					}
				}
			}
		}
		return $element;
	}
	
	/**
	 * Get the ordinal value of a number (1st, 2nd, 3rd, 4th).
	 *
	 * @return string
	 * @param $value int       	
	 */
	function getOrdinalString($value) {
		static $ords = array ('th', 'st', 'nd', 'rd' );
		if ((($value %= 100) > 9 && $value < 20) || ($value %= 10) > 3) {
			$value = 0;
		}
		return $ords [$value];
	}
	
	/**
	 * Returns the plural appendage, handy for instances like: 1 file,
	 * 5 files, 1 box, 3 boxes.
	 *
	 * @return string
	 * @param $value int       	
	 * @param $append string
	 *       	 what value to append to the string
	 */
	function getPluralString($value, $append = 's') {
		return ($value == 1 ? '' : $append);
	}
	
	/**
	 * Strips all newline characters (\n) from a string or recursively
	 * through a multi-dimensional array.
	 *
	 * @return string array
	 * @param $element string|array       	
	 */
	function trimNewlinesFromString($element) {
		if (Strings::validateArray ( $element, false ) === false) {
			return str_replace ( "\n", '', $element );
		} else {
			foreach ( $element as $key => $val ) {
				if (Strings::validateArray ( $element [$key], false )) {
					$element [$key] = Strings::trimNewlinesFromString ( $element [$key] );
				} else {
					$element [$key] = str_replace ( "\n", '', $element [$key] );
				}
			}
		}
		return $element;
	}
	
	/**
	 * Strips all carriage return characters (\r) from a string or
	 * recursively through a multi-dimensional array.
	 *
	 * @return string array
	 * @param $element string|array       	
	 */
	function trimCarriageReturnsFromString($element) {
		if (Strings::validateArray ( $element, false ) === false) {
			return str_replace ( "\r", '', $element );
		} else {
			foreach ( $element as $key => $val ) {
				if (Strings::validateArray ( $element [$key], false )) {
					$element [$key] = Strings::trimCarriageReturnsFromString ( $element [$key] );
				} else {
					$element [$key] = str_replace ( "\r", '', $element [$key] );
				}
			}
		}
		return $element;
	}
	
	/**
	 * Returns the extension of a file (ie.
	 * anything that appears after
	 * the last '.')
	 *
	 * @return string null
	 * @param $string string       	
	 */
	function getFileExtension($string) {
		if (Strings::validateString ( $string )) {
			return substr ( $string, (strrpos ( $string, '.' ) ? strrpos ( $string, '.' ) + 1 : strlen ( $string )), strlen ( $string ) );
		} else {
			return null;
		}
	}
	
	/**
	 * Returns the name of a file (ie.
	 * anything that appears before
	 * the last '.')
	 *
	 * @return string null
	 * @param $string string       	
	 */
	function getFileName($string) {
		if (Strings::validateString ( $string )) {
			return substr ( $string, 0, (strrpos ( $string, '.' ) ? strrpos ( $string, '.' ) : strlen ( $string )) );
		} else {
			return null;
		}
	}
	
	/**
	 * Displays a human readable file size.
	 *
	 * @return string null
	 * @param $file string       	
	 * @param $round bool       	
	 */
	function getFileSize($file, $round = false) {
		if (@file_exists ( $file )) {
			$value = 0;
			$size = filesize ( $file );
			if ($size >= 1073741824) {
				$value = round ( $size / 1073741824 * 100 ) / 100;
				return ($round) ? round ( $value ) . 'Gb' : "{$value}Gb";
			} else if ($size >= 1048576) {
				$value = round ( $size / 1048576 * 100 ) / 100;
				return ($round) ? round ( $value ) . 'Mb' : "{$value}Mb";
			} else if ($size >= 1024) {
				$value = round ( $size / 1024 * 100 ) / 100;
				return ($round) ? round ( $value ) . 'kb' : "{$value}kb";
			} else {
				return "$size bytes";
			}
		} else {
			return null;
		}
	}
	
	/**
	 * Counts number of words in a string.
	 *
	 * if $real_words == true then remove things like '-', '+', that
	 * are surrounded with white space.
	 *
	 * @return string null
	 * @param $string string       	
	 * @param $real_words bool       	
	 */
	function countWords($string, $real_words = true) {
		if (Strings::validateString ( $string )) {
			if ($real_words == true) {
				$string = preg_replace ( '/(\s+)[^a-zA-Z0-9](\s+)/', ' ', $string );
			}
			return (count ( split ( '[[:space:]]+', $string ) ));
		} else {
			return null;
		}
	}
	
	/**
	 * Counts number of sentences in a string.
	 *
	 * @return string null
	 * @param $string string       	
	 */
	function countSentences($string) {
		if (Strings::validateString ( $string )) {
			return preg_match_all ( '/[^\s]\.(?!\w)/', $string, $matches );
		} else {
			return null;
		}
	}
	
	/**
	 * Counts number of sentences in a string.
	 *
	 * @return string null
	 * @param $string string       	
	 */
	function countParagraphs($string) {
		if (Strings::validateString ( $string )) {
			$string = str_replace ( "\r", "\n", $string );
			return count ( preg_split ( '/[\n]+/', $string ) );
		} else {
			return false;
		}
	}
	
	/**
	 * Gather information about a passed string.
	 *
	 * If $real_words == true then remove things like '-', '+', that are
	 * surrounded with white space.
	 *
	 * @return string null
	 * @param $string string       	
	 * @param $real_words bool       	
	 */
	function getStringInformation($string, $real_words = true) {
		if (Strings::validateString ( $string )) {
			$info = array ();
			$info ['character'] = ($real_words) ? preg_match_all ( '/[^\s]/', $string, $matches ) : strlen ( $string );
			$info ['word'] = Strings::countWords ( $string, $real_words );
			$info ['sentence'] = Strings::countSentences ( $string );
			$info ['paragraph'] = Strings::countParagraphs ( $string );
			return $info;
		} else {
			return null;
		}
	}

}

?>