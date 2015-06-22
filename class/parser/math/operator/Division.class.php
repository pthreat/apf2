<?php
	
	namespace apf\parser\math\operator{

		use apf\parser\math\Stack;

		class Division extends Common {

			protected $precedence = 5;

			public function operate(Stack $stack) {

				$left		= $stack->pop()->operate($stack);
				$right	= $stack->pop()->operate($stack);

				return $right / $left;

			}

		}

	}
