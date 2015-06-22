<?php

	namespace apf\type\util\base{

		use apf\type\parser\Parameter	as	ParameterParser;

		abstract class Common{

			protected static function returnValue($value,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);	
		
				//TODO:
				//THIS MUST BE TRUE BY DEFAULT BUT WE HAVE TO CHANGE 
				//EVERY CALL IN THE DATA TYPES!!!!

				if($parameters->find('cast',FALSE)->getValue()){

					$type	=	$parameters->demand('type')->valueOf();
					return $type::cast($value);

				}

				return $value;

			}

		}

	}
