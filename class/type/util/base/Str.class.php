<?php

	namespace apf\type\util\base{

		use apf\type\base\Str							as	StringType;
		use apf\type\base\Vector						as	VectorType;
		use apf\type\base\IntNum						as	IntType;
		use apf\type\base\Char							as	CharType;
		use apf\type\collection\base\Str				as	StringCollection;
		use apf\type\collection\base\Char			as	CharCollection;
		use apf\type\parser\Parameter					as	ParameterParser;
		use apf\type\Boolean								as	BooleanType;
		use apf\type\Dataset								as	DatasetType;

		use apf\type\util\base\IntNum					as	IntUtil;
		use apf\type\util\common\Obj					as	ObjUtil;
		use apf\type\util\common\Variable			as	VarUtil;

		use apf\type\validate\base\Str				as	ValidateString;
		use apf\type\validate\common\Variable		as	ValidateVar;
		use apf\type\validate\common\Obj				as	ValidateObj;

		use apf\type\exception\base\str\Encoding	as	EncodingException;

		class Str{

			public static function convert($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$charset		=	$parameters->demand('charset')->toString()->valueOf();
				$set			=	Charset::fetch($charset);
				$string		=	StringType::cast($string,$parameters);
				$conversion	=	'';

				foreach($string as $char){

					foreach($set as $letter=>$value){

						if(sprintf('%s',$char)==$letter){
							$conversion.=$value;
							break;
						}

					}

				}

				return $conversion;

			}

			public static function sha1($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);

				$parameters	=	ParameterParser::parse($parameters);
				$raw			=	$parameters->findBool('raw',FALSE)->getValue();
				$times		=	$parameters->findBool('times',FALSE)->getValue();

				if($times>0){

					for($i=0;$i<$times;$i++){

						$string	=	sha1($string,$raw);

					}

					return $string;

				}

				return sha1($string,$raw);

			}

			public static function jsonDecode($val,$parameters=NULL){

				$decode	=	json_decode($val,$assoc=TRUE);

				if(!$decode){

					throw new \InvalidArgumentException("Invalid JSON string provided");

				}

				return VectorType::cast($decode,$parameters);

			}

			public static function md5($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string);
				$parameters	=	ParameterParser::parse($parameters);
				$raw			=	$parameters->findBool('raw',FALSE)->getValue();
				$times		=	IntType::cast($parameters->find('times',FALSE)->getValue())->valueOf();

				if($times>0){

					for($i=0;$i<$times;$i++){

						$string	=	md5($string,$raw);

					}

					return $string;

				}

				return md5($string,$raw);

			}

			public static function tokenize($string,$delimiter,$num=0,Array $options=Array()){

				ValidateString::mustBeNotEmpty($string,$trim=TRUE,"Must provide a string to find");
				ValidateString::mustBeNotEmpty($delimiter,$trim=TRUE,"Must provide a delimiter");

				$tok = strtok($string,$delimiter);

				$cnt	=	0;

				while ($tok !== FALSE) {

					if($cnt++==$num){

						return self::returnValue($tok,$options);
						break;

					}

					$tok = strtok($delimiter);

				}

			}

			public static function repeat($string,$parameters=NULL){

				$times			=	$parameters->demand('times')->toInt()->valueOf();
				$string			=	VarUtil::printVar($string,$parameters);
				$asCollection	=	$parameters->findBool('collection',TRUE)->valueOf();

				if($asCollection){

					$collection =	new StringCollection();

				}else{

					$result	=	'';

				}

				for($i=0;$i<$times->valueOf();$i++){
					
					if($asCollection){

						$collection->add(StringType::cast($string));
						continue;

					}

					$result.=$string;
					
				}

				return $asCollection	?	$collection	:	StringType::cast($result);

			}

			public static function replace($string,$parameters=NULL){

				$string			=	VarUtil::printVar($string,$parameters);

				$parameters		=	ParameterParser::parse($parameters);

				$replacement	=	$parameters->demand('replacement')->toString($parameters)->valueOf();
				$pattern			=	$parameters->demand('pattern')->toString($parameters)->valueOf();

				if($parameters->find('quoteRegex',TRUE)->getValue()){

					$pattern	=	preg_quote($pattern,'/');

				}

				$pattern	=	"/$pattern/u";

				$string	=	preg_replace($pattern,$replacement,$string);

				return self::returnValue($string,$parameters);

			}


			/**
			*Normalizes a string containing different encodings
			*/

			public static function normalizeSpaces($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$string		=	VarUtil::printVar($string,$parameters);

				$parameters->replace('quoteRegex',FALSE);
				$parameters->replace('pattern','[\s]{2,}');
				$parameters->replace('replacement',' ');

				return self::replace($string,$parameters);

			}

			public static function strim($string,$parameters=NULL){
			
				$string	=	self::normalizeSpaces($string,$parameters);
				return self::trim($string,$parameters);

			}

			public static function trim($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);
				$parameters	=	ParameterParser::parse($parameters);
				$chars		=	$parameters->find('chars','')->toString()->valueOf();

				$regex		=	'^\s+|\s+$';

				if(!empty($chars)){

					$regex	=	"^[$chars]|[$chars]$";

				}

				$parameters->replace('quoteRegex',FALSE);
				$parameters->replace('pattern',$regex);
				$parameters->replace('replacement','');

				return self::replace($string,$parameters);

			}

			public static function toBinary($string,$parameters=NULL){

				$strArray	=	self::toArray($string,$parameters);
				$binary		=	Array();

				foreach($strArray as $char){

					$binary[]	=	$char->toBinary($parameters);

				}

				return implode('',$binary);
				
			}

			public static function toArray($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);
				$parameters	=	ParameterParser::parse($parameters);
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString($parameters)->valueOf();
				$len 			=	self::length($string,$parameters)->valueOf();

				$chars		=	Array();

				while($len) { 

					$chars[] 	=	iconv_substr($string,0,1,$encoding);
					$string		=	iconv_substr($string,1,$len,$encoding);
					$len			=	mb_strlen($string,$encoding);

				} 

				return new CharCollection($chars,$parameters);

			}

			public static function explode($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);

				$parameters	=	ParameterParser::parse($parameters);

				$pattern		=	VarUtil::printVar($parameters->find('pattern',' ')->valueOf());

				if($parameters->find('quoteRegex',TRUE)->valueOf()){

					$pattern	=	preg_quote($pattern,'/');

				}

				$pattern	=	"/$pattern/u";
				$split	=	preg_split($pattern,$string);

				$stringCollection	=	new StringCollection();

				foreach($split as &$piece){

					$stringCollection->add(StringType::cast($piece));

				}

				return $stringCollection;

			}

			public static function toCamelCase($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				if(ValidateString::isEmpty($string,$parameters)){

					return;

				}

				$string		=	self::replace($string,['pattern'=>'[^A-Za-z0-9]','replacement'=>' ','quoteRegex'=>FALSE]);

				$string		=	self::normalizeSpaces($string,$parameters);
				$parameters->replace('pattern',' ');

				$pieces		=	self::explode($string,$parameters);

				$camelized	=	'';

				foreach($pieces as $key=>$value){

					if($key==0){

						$value[0]	=	CharType::cast(self::toLower($value[0],$parameters),$parameters);
						$camelized	=	sprintf('%s',$value);
						continue;

					}

					$camelized	=	sprintf('%s%s',$camelized,self::capitalize($value,$parameters));

				}

				return self::returnValue($camelized,$parameters);

			}

			public static function substr($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);

				$parameters	=	ParameterParser::parse($parameters,'start');

				$start		=	$parameters->find('start')->toInt()->valueOf();
				$length		=	$parameters->find('end',NULL)->valueOf();

				$length		=	is_null($length)	?	self::length($string,$parameters)	:
																IntType::cast($length);

				$length		=	$length->valueOf();

				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();

				return self::returnValue(iconv_substr($string,$start,$length,$encoding),$parameters);

			}

			public static function strpos($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);
				$parameters	=	ParameterParser::parse($string);
				$needle		=	$parameters->demand('needle')->toString()->valueOf();
				$offset		=	$parameters->find('offset',0)->toInt()->valueOf();

				return self::returnValue(iconv_strpos($string,$needle,$offset,'UTF-8'),$parameters);

			}

			public static function cutFirst($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);

				$parameters	=	ParameterParser::parse($parameters);
				$delimiter	=	$parameters->demand('delimiter')->toString()->valueOf();
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();

				$offset		=	$parameters->find('offset',0)->toInt()->valueOf();
				$pos			=	@iconv_strpos($string,$delimiter,$offset,$encoding);

				if($pos === FALSE){

					return FALSE;

				}

				return self::returnValue(iconv_substr($string,0,$pos,$encoding),$parameters);

			}

			public static function toKatakana($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				return mb_convert_kana($string,'C',$encoding);

			}

			public static function toHiragana($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				return mb_convert_kana($string,'c',$encoding);

			}

			public static function cutFirstToLast($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$delimiter	=	$parameters->demand('delimiter')->toString()->valueOf();
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();

				$string		=	VarUtil::printVar($string,$parameters);
				$pos			=	@iconv_strrpos($string,$delimiter,$encoding);

				if($pos === FALSE){

					return FALSE;

				}

				$len	=	self::length($string,$parameters)->valueOf();

				return self::returnValue(iconv_substr($string,0,$pos,$encoding),$parameters);

			}

			public static function cutLast($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$delimiter	=	$parameters->demand('delimiter')->toString()->valueOf();
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();

				$string		=	VarUtil::printVar($string,$parameters);
				$pos			=	@iconv_strrpos($string,$delimiter,$encoding);

				if($pos === FALSE){

					return FALSE;

				}

				$len	=	self::length($string,$parameters)->valueOf();

				return self::returnValue(iconv_substr($string,$pos+1,$len,$encoding),$parameters);

			}

			public static function cut($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$delimiter	=	$parameters->demand('delimiter')->toString($parameters)->valueOf();

				$string		=	VarUtil::printVar($string,$parameters);
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				$first		=	$parameters->find('first',TRUE)->toBoolean()->valueOf();
				$offset		=	$parameters->find('offset',0)->toInt()->valueOf();

				//If the offset doesn't exists a warning will be reported
				//This is the reason why we supress the error
				$pos			=	@iconv_strpos($string,$delimiter,$offset,$encoding);

				if($pos === FALSE){

					return FALSE;

				}

				$len		=	iconv_strlen($delimiter,$encoding);

				if($first){

					return self::returnValue(iconv_substr($string,$pos,$len,$encoding),$parameters);

				}

				$result	=	iconv_substr($string,$pos+$len,$len,$encoding);

				return self::returnValue($result,$parameters);

			}

			public static function length($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$string		=	VarUtil::printVar($string,$parameters);

				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();

				$len			=	iconv_strlen($string,$encoding);

				if($parameters->find('cast',FALSE)){

					return IntType::cast($len);

				}

				return $len;

			}

			//BE WARNED: String will not be auto printed!

			public static function encode($string,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters);

				$fromEncoding	=	$parameters->find('fromEncoding','')->valueOf();
				$parameters->findInsert('throw',TRUE);

				//careful! if you $fromEncoding->toString instead of valueOf recursion will kick in
				//This is why we use is_string

				if(!is_string($fromEncoding)){

					$type	=	gettype($fromEncoding);
					throw new \InvalidArgumentException("fromEncoding must be a string, \"$type\" given");

				}


				if(empty($fromEncoding)){

					$fromEncoding	=	self::autoDetectEncoding($string,$parameters);

				}

				$toEncoding	=	$parameters->find('toEncoding')->valueOf();

				if(!is_null($toEncoding)&&!is_string($toEncoding)){

					$type	=	gettype($toEncoding);

					throw new \UnexpectedValueException("toEncoding must be a string, \"$type\" given");

				}

				if(empty($toEncoding)){

					$type	=	gettype($toEncoding);
					throw new \UnexpectedValueException("toEncoding must be not empty, \"$type\" given");

				}

				//Refuse to double encode by default

				$doubleEncode	=	(boolean)$parameters->find('doubleEncode',FALSE)->valueOf();

				if(!$doubleEncode && $fromEncoding==$toEncoding){

					return $string;

				}

				//echo "$fromEncoding -> $toEncoding\n";

				$string	=	@iconv($fromEncoding,$toEncoding,$string);	

				if($string===FALSE && $parameters->find('throw')->valueOf()){

					$msg	=	"Could not encode from encoding \"$fromEncoding\" to encoding \"$toEncoding\"";
					$msg	=	sprintf('%s%s',$msg,' either the specified from and to encodings are wrong ');
					$msg	=	sprintf('%s%s',$msg,' or the encoding of the string is not');
					$msg	=	sprintf('%s%s',$msg,' supported by your iconv library');

					throw new EncodingException($msg);

				}

				return $string;

			}

			public static function isEmpty($val,$parameters=NULL){

				$val			=	VarUtil::printVar($val,$parameters);

				$parameters	=	ParameterParser::parse($parameters);

				if($parameters->find('trim',TRUE)->getValue()){

					$val	=	self::trim($val,$parameters);

				}

				if($parameters->find('strim',FALSE)->getValue()){

					$val	=	self::strim($val,$parameters);

				}

				return empty($val);

			}

			//ACCEPTS STRINGS ONLY
			//IF YOU WANT CASTING USE VarUtil::printVar

			public static function toUTF8($string,$parameters=NULL){

				//if we detect the string is UTF-8 we avoid any kind of double encoding
				//despite the user having specified fromEncoding.

				$autoDetect		=	self::autoDetectEncoding($string);

				if($autoDetect==='UTF-8'){

					return $string;

				}

				$parameters		=	ParameterParser::parse($parameters);
				$fromEncoding	=	$parameters->findPrint('fromEncoding',FALSE)->getValue();

				if($fromEncoding){

					$string	=	self::encode($string,[
																'fromEncoding'	=>	$fromEncoding,
																'toEncoding'	=>	'UTF-8'
					]);

					return $string;

				}

				//String encoding autodetection was succesful, convert to UTF-8
				$string	=	self::encode($string,['fromEncoding'=>$autoDetect,'toEncoding'=>'UTF-8']);

				return $string;

			}

			public static function isUTF8($string){

				$encoding	=	\mb_detect_encoding($string,'UTF-8',$strict=TRUE);

				return $encoding=='UTF-8';

			}

			public static function autoDetectEncoding($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$order		=	['UTF-8','UTF-7','ASCII','EUC-JP','SJIS','eucJP-win','SJIS-win','JIS','ISO-2022-JP'];

				\mb_detect_order($order);

				$result	=	\mb_detect_encoding($string,$order,$strict=TRUE);

				if($parameters->findBool('throw',FALSE)&&$result===FALSE){

					$msg	=	'Could not determine string encoding automatically ';
					$msg	=	sprintf('%s, please specify the encoding manually with fromEncoding',$msg);

					throw new EncodingException($msg);

				}

				return $result;

			}

			public static function toUpper($string,$parameters=NULL){
	
				$string		=	VarUtil::printVar($string,$parameters);
				$parameters	=	ParameterParser::parse($parameters);
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				$upper		=	mb_convert_case($string,\MB_CASE_UPPER,$encoding);

				return $upper;

			}

			public static function toLower($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				$string		=	VarUtil::printVar($string,$parameters);
				$lower		=	mb_convert_case($string,\MB_CASE_LOWER,$encoding);

				return $lower;

			}

			public static function capitalize($string,$parameters){

				$parameters		=	ParameterParser::parse($parameters);
				$encoding		=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				$string			=	VarUtil::printVar($string,$parameters);
				$capitalized	=	mb_convert_case($string,\MB_CASE_TITLE,$encoding);

				return self::returnValue($capitalized,$parameters);

			}

			public static function keyValuePair($string,$parameters=NULL){

				$parameters		=	ParameterParser::parse($parameters,'delimiter');
				$string			=	VarUtil::printVar($string);
				$delimiter		=	VarUtil::printVar($parameters->demand('delimiter'));

				$key				=	self::trim(self::cutFirst($string,$parameters));
				$val				=	self::trim(self::cutLast($string,$parameters));
				$keyValuePair	=	['key'=>$key,'value'=>$val];

				return VectorType::cast($keyValuePair,$parameters);

			}

			public static function has($haystack,$parameters=NULL){

				$haystack	=	VarUtil::printVar($haystack,$parameters);

				$parameters	=	ParameterParser::parse($parameters);
				$needle		=	$parameters->demand('needle')->toString($parameters)->valueOf();

				$encoding	=	$parameters->find('toEncoding','UTF-8')->toString()->valueOf();
				$pos			=	iconv_strpos($haystack,$needle,0,$encoding);

				return !($pos===FALSE);

			}

			public static function match($string,$parameters){

				$string		=	VarUtil::printVar($string,$parameters);

				$parameters	=	ParameterParser::parse($parameters);
				$match		=	$parameters->demand('match')->toString($parameters)->valueOf();

				if($parameters->find('quoteRegex',TRUE)){

					$match	=	preg_quote($match,'/');	

				}

				$match	=	"/$match/u";

				return preg_match($match,$string);

			}

			public static function beginsWith($string,$parameters=NULL){

				$string		=	VarUtil::printVar($string,$parameters);
				$parameters	=	ParameterParser::parse($parameters);
				$match		=	$parameters->demand('match')->toString($parameters)->valueOf();
				$match		=	preg_quote($match,'/');

				return self::match("^$match",$string);

			}

			//Use iconv with TRANSLIT ?
			public static function findTokenized($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				$string		=	VarUtil::printVar($string,$parameters);

				$delimiter	=	$parameters->demand('delimiter')->toString()->valueOf();
				$find			=	$parameters->demand('find')->toString()->valueOf();

				//Remove duplicates
				$quoted	=	preg_quote($delimiter,'/');
				$string	=	preg_replace("/[$quoted]{2,}/",$delimiter,$string);

				$tok = strtok($string,$delimiter);

				while ($tok !== FALSE) {

					if($tok==$find){
						return TRUE;
						break;
					}

					$tok = strtok($delimiter);

				}

			}

			public static function findTokenizedValue($string,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$delimiter	=	$parameters->demand('delimiter')->toString()->valueOf();
				$find			=	$parameters->demand('find')->toString()->valueOf();

				$string		=	VarUtil::printVar($string,$parameters);

				//Remove duplicated delimiters
				$quoted	=	preg_quote($delimiter,'/');
				$string	=	preg_replace("/[$quoted]{2,}/",$delimiter,$string);

				$tok 		=	strtok($string,$delimiter);

				while ($tok !== FALSE) {

					if($tok==$find){

						$tok = strtok($delimiter);
						return self::returnValue($tok);

					}

					$tok = strtok($delimiter);

				}

				return FALSE;

			}

			public static function toSlug($string,$parameters=NULL){

				ValidateString::mustBeNotEmpty($string,$trim=TRUE,"Must provide a string to slugify");

				$parameters	=	ParameterParser::parse($parameters);
				$char			=	$parameters->find('char','-')->toString()->valueOf();

				$string		=	VarUtil::printVar($string,$parameters);

				$string 		=	preg_replace('/&/u', '', $string);
				$string 		=	preg_replace('/\W/u',$char,self::toAscii($string));
				$string 		=	self::toLower(preg_replace("/[$char]{2,}/u",$char, $string),$parameters);
				$string		=	preg_replace('/[^a-zA-Z0-9\-]/u','',$string);

				return self::trim($string,$char,$options);

			}

			public static function deSlug($string,$parameters=NULL){

				StringValidate::mustBeNotEmpty($string,$trim=TRUE,"Must provide a string to slugify");

				$parameters	=	ParameterParser::parse($parameters);

				$string		=	VarUtil::printVar($string,$parameters);

				$parameters->findInsert('char','-');
				$parameters->findInsert('separator',' ');

				return self::replace($string,$parameters);

			}

			public static function toAscii($str=NULL, Array $options=Array()) {

				$string = @iconv('UTF-8', 'ASCII//TRANSLIT',VarUtil::printVar($string));
				return self::returnValue($string,$options);

			}

			public static function minify($buffer,$parameters=NULL){

				 $search = array(
					  '/\>[^\S ]+/s',  // strip whitespaces after tags, except space
					  '/[^\S ]+\</s',  // strip whitespaces before tags, except space
					  '/(\s)+/s',       // shorten multiple whitespace sequences
					  '/(\t)+/s'
				 );

				 $replace = array(
					  '>',
					  '<',
					  '\\1'
				 );

				 $buffer = self::replace($search, $replace, $buffer,$options);

				 return self::returnValue($buffer,$options);

			}

			private static function returnValue($val,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);

				if($parameters->find('cast',FALSE)->getValue()){

					return StringType::cast($val,$parameters);

				}

				return $val;

			}

		}

	}
