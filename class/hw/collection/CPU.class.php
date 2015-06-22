<?php

	namespace apf\hw\collection{

		use apf\type\parser\Parameter						as	ParameterParser;
		use apf\type\collection\common\UniqueValue	as	UniqueValueCollection;

		class CPU extends UniqueValueCollection{

			public function __construct($val=NULL,$parameters=NULL){
			
				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('allowedTypes',[['type'=>'class','value'=>'apf\\hw\\CPU']]);
				parent::__construct($val,$parameters);

			}

			public function getAmountOfCores(){

				return parent::count();

			}

			public function getCPU($num){

				return parent::offsetGet((int)$num);

			}

		}

	}
