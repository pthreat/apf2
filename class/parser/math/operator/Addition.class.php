<?php

	namespace apf\parser\math\operator{

		use apf\parser\math\Stack;

		class Addition extends Common{

			 protected $precedence = 4;

			 public function operate(Stack $stack) {

				  return $stack->pop()->operate($stack) + $stack->pop()->operate($stack);

			 }

		}

	}
