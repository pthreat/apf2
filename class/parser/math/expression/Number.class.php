<?php

	namespace apf\parser\math\expression{

		use apf\parser\math\Stack;

		class Number extends Terminal{

			 public function operate(Stack $stack) {

				  return $this->value;

			 }

		}
		
	}
