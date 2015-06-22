<?php

	namespace apf\data\set\charset{

		use apf\data\set\Charset;
		use apf\type\base\Str											as	StringType;
		use apf\type\base\Char											as	CharType;
		use apf\type\util\base\Str										as	StringUtil;
		use apf\type\util\common\Variable							as	VarUtil;
		use apf\type\parser\Parameter									as	ParameterParser;
		use apf\data\set\charset\exception\UndefinedCharacter	as	UndefinedCharacterException;

		class Morse extends Charset{

			public static function convert($str,$parameters=NULL){

				$var				=	VarUtil::printVar($str,$parameters);
				$parameters		=	ParameterParser::parse($parameters);
				$ignoreUnknown	=	$parameters->find('ignoreUnknown',FALSE)->toBoolean()->valueOf();
				$words			=	StringUtil::explode($str);
				$charset			=	parent::fetch("morse");

				$morse			=	Array();
				$separator		=	' /';

				foreach($words as $word){

					$finalWord	=	"";

					foreach($word as $v){

						if($v->isSpace()){

							continue;

						}

						$v	=	$v->toLower()->valueOf();

						if(!$ignoreUnknown&&!$charset->offsetExists($v)){

							$msg	=	"Character $v is not defined in charset. If you'd like to skip unknown";
							$msg	=	sprintf('%s %s',$msg,"characters please set the ignoreUnknown parameter");
							$msg	=	sprintf('%s %s',$msg,"to TRUE");

							throw new UndefinedCharacterException($msg);

						}

						$finalWord	=	sprintf('%s %s',$finalWord,$charset[$v]);

					}

					$morse[]	=	$finalWord;

				}

				return StringType::cast(implode($separator,$morse));

			}

			public static function decode($str,$parameters=NULL){

				$var				=	VarUtil::printVar($str,$parameters);
				$parameters		=	ParameterParser::parse($parameters);
				$ignoreUnknown	=	$parameters->find('ignoreUnknown',FALSE)->toBoolean()->valueOf();
				$separator		=	'/';
				$words			=	StringUtil::explode($str,['pattern'=>$separator]);
				$charset			=	parent::fetch("morse");
				$regularText	=	Array();

				foreach($words as $word){

					$word	=	$word->explode();
					$size	=	$word->size()->valueOf();

					foreach($word as $k=>$v){

						if($v==""){

							if($k==0||$k==$size){
								continue;
							}

							$regularText[]	=	" ";

							continue;

						}

						$v	=	$v->trim()->valueOf();

						try{

							$index	=	$charset->indexOf($v);

						}catch(\Exception $e){

							if(!$ignoreUnknown){

								$msg	=	"Character $v is not defined in charset. If you'd like to skip unknown";
								$msg	=	sprintf('%s %s',$msg,"characters please set the ignoreUnknown parameter");
								$msg	=	sprintf('%s %s',$msg,"to TRUE");

								throw new UndefinedCharacterException($msg);

							}

							continue;

						}

						$regularText[]	=	StringUtil::trim($index);

					}

				}
	
				return StringType::cast(implode('',$regularText));

			}


		}

	}
