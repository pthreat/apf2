<?php

	namespace apf\parser\math\operator{

		use apf\parser\math\expression\Terminal	as	TerminalExpression;

		abstract class Common extends TerminalExpression {

			protected $precedence = 0;
			protected $leftAssoc = true;

			public function getPrecedence() {

				return $this->precedence;

			}

			public function isLeftAssoc() {

				return $this->leftAssoc;

			}

			public function isOperator() {

				return true;

			}

		}

	}
