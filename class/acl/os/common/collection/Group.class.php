<?php

	namespace apf\acl\os\common\collection{

		use apf\type\parser\Parameter		as	ParameterParser;
		use apf\type\collection\Common;

		class Group extends Common{

			public function __construct($val=NULL,$parameters=NULL){


				$parameters	=	ParameterParser::parse($parameters);
				$parameters->replace('allowedTypes',[['type'=>'class','value'=>'apf\\acl\\os\\common\\Group']]);
				parent::__construct($val,$parameters);

			}

		}

	}
