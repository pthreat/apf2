<?php

	namespace apf\util\convert\meassure{

		use apf\type\base\Vector		as	VectorType;
		use apf\type\parser\Parameter	as	ParameterParser;

		class Byte{

			public static function convert($value,$parameters=NULL){

				$parameters			=	ParameterParser::parse($parameters);

				$validMeassures	=	[
												-1	=>	'bit',	
												0	=>	'byte',
												1	=>	'kilobyte',
												2	=>	'megabyte',
												3	=>	'gigabyte',
												4	=>	'terabyte',
												5	=>	'petabyte',
												6	=>	'exabyte',
												7	=>	'zettabyte'
				];

				$parameters->replace('to',$parameters->findOneOf('to','in')->valueOf());

				$to	=	$parameters->selectCase('to',$validMeassures)->valueOf();
				$from	=	$parameters->selectCase('from',$validMeassures)->valueOf();

				$validMeassures	=	VectorType::cast($validMeassures);

				$powFrom	=	$validMeassures->indexOf($from)*10;
				$powTo	=	$validMeassures->indexOf($to)*10;

				if($to=='byte'&&$from=='bit'){

					return $value / 8;

				}

				//Transform always to byte
				$bytes	=	$value * pow(2,$powFrom);

				if($from=='bit'){

					return $bytes / 8;

				}

				if($to=='byte'){

					return $bytes;

				}

				if($to=='bit'){

					return $bytes*8;

				}

				return $bytes / pow(2,$powTo);

			}

		}

	}
