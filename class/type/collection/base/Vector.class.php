<?php

	namespace apf\type\collection\base{

		use apf\common\type\Vector	as	VectorType;
		use apf\type\parser\Parameter		as	ParameterParser;
		use apf\type\collection\Common;

		class Vector extends Common{

			public function __construct($val=NULL,$parameters=NULL){

				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('allowedtypes',[['type'=>'class','value'=>'apf\\type\\base\\Vector']]);
				parent::__construct($val,$parameters);

			}



		}

	}
