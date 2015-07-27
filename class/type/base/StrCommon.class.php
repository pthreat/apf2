<?php

	namespace apf\type\base{

		use apf\type\Common;
		use apf\type\util\base\Str						as	StringUtil;
		use apf\type\validate\base\Str				as	ValidateString;

		abstract class StrCommon extends Common{

			public function isUppercase(){

				return ValidateString::isUppercase($this);

			}

			public function isLower(){

				return ValidateString::isLowercase($this);

			}

			public function isDigit(){

				return ValidateString::isDigit($this);

			}

			public function isHex(){

				return ValidateString::isHex($this);

			}

			public function isControl(){

				return ValidateString::isControl($this);

			}

			public function isPrintable(){

				return ValidateString::isPrintable($this);

			}

			public function isSpace(){

				return ValidateString::isSpace($this);

			}

			public function isAlphanumeric(){

				return ValidateString::isAlphanumeric($this);

			}

			public function isPunctuation(){

				return ValidateString::isPunctuation($this);

			}

			public function repeat($parameters){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				$value		=	StringUtil::repeat($this->value,$parameters);

				if($parameters->find('collection')->toBoolean()->valueOf()){

					return $value;

				}

				return new static($value,$parameters);

			}

			public function sha1($raw=FALSE){

				if(is_bool($raw)){

					$parameters	=	['raw'=>$raw];	

				}

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);

				return new static(StringUtil::sha1($this->value,$parameters),$parameters);		

			}

			public function md5($parameters=NULL){

				if(is_bool($raw)){

					$parameters	=	['raw'=>$raw];	

				}

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);

				return new static(StringUtil::md5($this->value,$parameters),$parameters);		

			}

			public function trim($chars=NULL){

				if(is_string($chars)){

					$chars	=	['chars'=>$chars];

				}

				$parameters	=	$this->parseParameters($chars,$merge=FALSE);

				return new static(StringUtil::trim($this->value,$parameters),$parameters);

			}

			/**
			*Method     : strim
			*Description: Special trim, removes duplicated spaces from a string and trims given string
			*Note       : This method works well with strings that have mixed character sets.
			*Example		:	
			*	<code>
			*
			*		use apf\type\Str as StringType;
			*
			*		/////////////////////////////////////////////////////////////////
			*		//This string has Japanese and ASCII spaces plus a Nepali string
			*		/////////////////////////////////////////////////////////////////
			*
			*		$mixedString	=	'    　परीक्षण परीक्षण    ascii text  \' \' まんだれはぽねす　　';
			*		$str 				= new StringType($mixedString);
			*		$str->strim()->println();
			*
			*		//Output would be: "परीक्षण परीक्षण ascii text ' ' まんだれはぽねす"
			*		//With a regular multibyte trim this wouldn't be the same!
			*
			*	</code>
			*
			*	@return apf\type\String	The space normalized and trimmed string
			*
			*	@see self::normalizeSpaces
			*	@see self::trim
			*
			*/

			public function strim($chars=NULL){

				$parameters	=	$this->parseParameters(['chars'=>$chars],$merge=FALSE);
				return new static(StringUtil::strim($this->value));

			}

			public function has($word){

				$parameters	=	$this->parseParameters(['needle'=>$word],$merge=FALSE);
				return StringUtil::has($this->value,$word);

			}

			public function match($regex){

				$parameters	=	$this->parseParameters(['match'=>$regex],$merge=FALSE);
				return StringUtil::match($this->value,$parameters);

			}

			public function getEncoding(){

				return new static(StringUtil::autoDetectEncoding($this->value));

			}

			public function println($parameters=NULL){

				return printf("%s\n",VarUtil::printVar($this->value,$parameters));

			}

			public function printl($parameters=NULL){

				return printf("%s",VarUtil::printVar($this->value,$parameters));

			}

			public function sprintf($parameters=NULL){

				return sprintf('%s',VarUtil::printVar($this->value,$parameters));

			}

			public function replace($pattern,$replacement){

				return new static(StringUtil::replace($pattern,$replacemenet,$this->value,['cast'=>FALSE]));

			}

			public function toAscii($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::toAscii($this->value,$parameters),$parameters);

			}

			public function toLower($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::toLower($this->value,$parameters),$parameters);

			}

			public function toUpper($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::toUpper($this->value,$parameters),$parameters);

			}

			public function capitalize($parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters,$merge=FALSE);
				return new static(StringUtil::capitalize($this->value,$parameters),$parameters);

			}

			public function toKatakana($parameters=NULL){

				$parameters	=	$this->parseParameters($parseParameters,$merge=FALSE);
				return new static(StringUtil::toKatakana($this->value,$parameters),$parameters);

			}

			public function toHiragana($parameters=NULL){

				$parameters	=	$this->parseParameters($parameters,$merge=FALSE);
				return new static(StringUtil::toHiragana($this->value,$parameters),$parameters);

			}

			public function toCharset($charset){

				if(is_string($charset)){

					$charset	=	['charset'=>$charset];

				}

				$parameters	=	$this->parameters($charset,$merge=FALSE);

				return new static(StringUtil::convert($this->value,$parameters),$parameters);

			}

			public function isEmpty($parameters=NULL){

				return ValidateString::isEmpty($this->value,$parameters);

			}

			public function __toString(){

				return sprintf("%s",$this->value);

			}

		}

	}
