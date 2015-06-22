<?php

	/**
	*A classes collection can be hold accountable for handling MORE than ONE type of object
	*/

	namespace apf\common\type\collection{

		use apf\common\validate\Vector	as	VectorValidate;

		abstract class Classed extends Base{

			public function add($item,$class=NULL){

				if(empty($class)){

					$msg		=	'Missing class key when adding item to classed collection';
					throw new \InvalidArgumentException($msg);

				}

				$this->isValidItem($item,$class);

				parent::add($item);

			}

			protected function isValidItem($item,$class){

				$class	=	is_array($class)	?	$class	:	[$class];

				foreach($class as $className){

					if($item instanceof $className){

						return TRUE;

					}

				}

				$type		=	get_class($item);
				$class	=	implode(' OR ',$class);
				$msg		=	"$class collection must be composed only of objects of class $class, $type was given";

				throw new \InvalidArgumentException($msg);

			}

		}

	}
