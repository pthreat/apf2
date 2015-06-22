<?php

	namespace apf\type\validate\base{

		use apf\type\base\Str 								as StringType;
		use apf\type\parser\Parameter						as	ParameterParser;
		use apf\type\util\base\Str 						as Sutil;
		use apf\type\util\common\Variable				as	VarUtil;
		use apf\type\exception\base\str\EmptyString	as	EmptyStringException;

		class Str{

			public static function isString($val){

				return StringType::cast($val,$trim=FALSE,$throw=FALSE);

			}

			public static function isUppercase($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_upper($string);

			}

			public static function isLowercase($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_lower($string);

			}

			public static function isControl($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_cntrl($string);

			}

			public static function isPrintable($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_print($string);

			}

			public static function isSpace($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_space($string);

			}

			public static function isAlphanumeric($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_alnum($string);

			}

			public static function isDigit($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_digit($string);

			}

			public static function isPunctuation($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_punct($string);

			}

			public static function isHex($string,$parameters=NULL){

				$string	=	VarUtil::printVar($string,$parameters);
				return ctype_xdigit($string);

			}

			/**
			 *Hard validation
			 *Checks that a given value must be a string
			 *@throws \apf\exception\validate\StringException if argument given is not a string
			 */

			public static function mustBeString($val,$msg=NULL,$exCode=0){

				$isString	=	self::isString($val);

				if($isString){

					return $isString;

				}

				return parent::imperativeValidation($isString,$msg,$exCode);

			}

			/**
			 *Check if a string is empty.
			 *@param String $string The string to be checked.
			 *@param boolean $useTrim wether to trim the string or not.
			 *@param String $msg \apf\exception\Validate message.
			 *@param Int $exCode \apf\exception\Validate code.
			 *@throws \apf\exception\Validate in case the given string is effectively empty.
			 *@return String The string (trimmed or not, this is specified with the $useTrim parameter).
			 */

			public static function isEmpty($string,$parameters=NULL){

				$string	=	StringType::cast($string,$parameters)->valueOf();	
				return empty($string);

			}

			public static function mustBeNotEmpty($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$msg			=	varUtil::printVar($parameters->find('msg',"String value can not be empty"));
				$code			=	(int)$parameters->find('code',0)->valueOf();

				$parameters->findInsert('strim',TRUE);

				if(self::isEmpty($string,$parameters)){

					throw new EmptyStringException($msg,$code);

				}

			}

			/**
			 *Checks if a string begins with a certain string
			 *@param String $string String to be checked
			 *@param String $begin String to compare if $string begins with this very same string.
			 *@return boolean TRUE The $string begins with $begin
			 *@return boolean FALSE The $string doesn't begins with $begin
			 */

			public static function beginsWith($string,$begin){

				$string	=	substr($string,0,strlen($begin));

				return $string==$begin;

			}

			/**
			 *Check if the length of a string is between specified limits.
			 *@param Int $min Minimum limit
			 *@param Int $maximum Maximum limit
			 *@param String $string The string to be checked
			 *@param String $msg \apf\exception\Validate message.
			 *@param Int $exCode \apf\exception\Validate code.
			 *@throws \apf\exception\Validate in case the given string is not between specified limits
			 *@return Int The string length 
			 */

			public static function mustHaveLengthBetween($string,$min,$max,$useTrim=TRUE,$msg=NULL,$exCode=0){

				if($useTrim){

					$string	=	Sutil::trim(StringType::cast($string));

				}

				$min	=	(int)$min;
				$max	=	(int)$max;

				$msg	=	empty($msg) ? sprintf('String length has to be between %d and %d characteres. String "%s" has a length of %d characters',$min,$max,$string,$len) : $msg;


				return Int::mustBeBetween(strlen($string),$min,$max,$msg,$exCode);

			}

			/**
			 *Check if the length of a string has a minimum of $min characters
			 *@param Int $min Amount of minimum characters
			 *@param String $string The string to be checked
			 *@param String $msg \apf\exception\Validate message.
			 *@param Int $exCode \apf\exception\Validate code.
			 *@throws \apf\exception\Validate in case the given string has not the amount of minimum characters.
			 *@return Int The string length 
			 */

			public static function mustHaveMinLength($string,$min,$msg=NULL,$exCode=0){

				$min	=	(int)$min;
				$len	=	strlen($string);

				$msg	=	empty($msg) ? sprintf('String has to have a minimum of %d characteres. String "%s" has only %d characters',$min,$string,$len) : $msg;

				return Int::mustBeGreaterOrEqualThan($len,$min,$msg,$exCode);

			}

			/**
			 *Check if the length of a string exceeds a maximum amount of characters
			 *@param Int $max Maximum amount of characters
			 *@param String $string The string to be checked
			 *@param String $msg \apf\exception\Validate message.
			 *@param Int $exCode \apf\exception\Validate code.
			 *@throws \apf\exception\Validate in case the given string has exceeded the maximum amount of characters.
			 *@return Int The string length 
			 */

			public static function maxLength($max,$string,$msg=NULL,$exCode=0){

				$max	=	(int)$max;
				$len	=	strlen($string);

				$msg	=	empty($msg) ? sprintf('String has exceeded the amount of %d characteres. String "%s" has %d characters',$max,$string,$len) : $msg;

				return Int::isLowerOrEqualThan($max,$len,$msg,$exCode);

			}

			public static function hasLengthEqualTo($string,$length){

				$stdVal	=	self::parameterValidation($string);

				if(!Int::isPositive($length)){

					return -10;

				}

				$length	=	(int)$length;

				if(!($stdVal===TRUE)){

					return $stdVal;

				}

				return strlen($string)==$length;

			}

			public static function mustHaveLengthEqualTo($string,$length,$useTrim=TRUE,$msg=NULL,$exCode=0){

				if($useTrim){

					$string	=	Sutil::trim($string);

				}

				$hasLengthEqualTo	=	self::hasLengthEqualTo($string,$length);

				parent::imperativeValidation($string,$exCode,$msg);

				return $string;

			}

		}

	}

