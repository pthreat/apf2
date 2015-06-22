<?php

	namespace apf\parser\math\operator{

		use apf\parser\math\Stack;

		class Multiplication extends Common{

			 protected $precedence = 5;

			 public function operate(Stack $stack) {

				  return $stack->pop()->operate($stack) * $stack->pop()->operate($stack);

			 }

		}

	}
