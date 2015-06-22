<?php

	namespace apf\parser\math\expression{

		use apf\parser\math\Stack;

		class Parenthesis extends Terminal {

			 protected $precedence = 6;

			 public function operate(Stack $stack) {
			 }

			 public function getPrecedence() {
				  return $this->precedence;
			 }

			 public function isNoOp() {
				  return true;
			 }

			 public function isParenthesis() {
				  return true;
			 }

			 public function isOpen() {
				  return $this->value == '(';
			 }

		}

	}
