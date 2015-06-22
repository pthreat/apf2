<?php

	namespace apf\parser\math\operator{

		use apf\parser\math\Stack;

		class Subtraction extends Common{

			 protected $precedence = 4;

			 public function operate(Stack $stack) {

				  $left = $stack->pop()->operate($stack);
				  $right = $stack->pop()->operate($stack);

				  return $right - $left;

			 }

		}

	}
