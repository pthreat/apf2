<?php

	namespace apf\type\collection\base{

		use apf\type\base\Str				as	StringType;
		use apf\type\parser\Parameter		as	ParameterParser;
		use apf\type\collection\Common;

		class Str extends Common{

			public function __construct($val=NULL,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('allowedTypes',[['type'=>'class','value'=>'apf\\type\\base\\Str']]);
				parent::__construct($val,$parameters);

			}

		}

	}
