<?php
	/**
		The MIT License (MIT)

		Copyright (c) 2011 The Authors

		Permission is hereby granted, free of charge, to any person obtaining a copy
		of this software and associated documentation files (the "Software"), to deal
		in the Software without restriction, including without limitation the rights
		to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
		copies of the Software, and to permit persons to whom the Software is
		furnished to do so, subject to the following conditions:

		The above copyright notice and this permission notice shall be included in
		all copies or substantial portions of the Software.

		THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
		IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
		FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
		AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
		LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
		OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
		THE SOFTWARE.

		Author		: ircmaxell	<Anthony Ferrara>
		Modified By	: pthreat	<Federico Stange>

	*/

	namespace apf\parser{

		use apf\parser\math\Stack;
		use apf\parser\math\expression\Terminal	as	TerminalExpression;

		class Math {

			protected $variables = array();

			public function evaluate($string) {

				$stack = $this->parse($string);
				return $this->run($stack);

			}

			public function parse($string) {

				$tokens = $this->tokenize($string);
				$output = new Stack();
				$operators = new Stack();
				foreach ($tokens as $token) {
					$token = $this->extractVariables($token);
					$expression = TerminalExpression::factory($token);
					if ($expression->isOperator()) {
						$this->parseOperator($expression, $output, $operators);
					} elseif ($expression->isParenthesis()) {
						$this->parseParenthesis($expression, $output, $operators);
					} else {
						$output->push($expression);
					}
				}
				while (($op = $operators->pop())) {
					if ($op->isParenthesis()) {
						throw new \RuntimeException('Mismatched Parenthesis');
					}
					$output->push($op);
				}
				return $output;
			}

			public function registerVariable($name, $value) {
				$this->variables[$name] = $value;
			}

			public function run(Stack $stack) {
				while (($operator = $stack->pop()) && $operator->isOperator()) {
					$value = $operator->operate($stack);
					if (!is_null($value)) {
						$stack->push(TerminalExpression::factory($value));
					}
				}
				return $operator ? $operator->render() : $this->render($stack);
			}

			protected function extractVariables($token) {
				if ($token[0] == '$') {
					$key = substr($token, 1);
					return isset($this->variables[$key]) ? $this->variables[$key] : 0;
				}
				return $token;
			}

			protected function render(Stack $stack) {
				$output = '';
				while (($el = $stack->pop())) {
					$output .= $el->render();
				}
				if ($output) {
					return $output;
				}
				throw new \RuntimeException('Could not render output');
			}

			protected function parseParenthesis(TerminalExpression $expression, Stack $output, Stack $operators) {
				if ($expression->isOpen()) {
					$operators->push($expression);
				} else {
					$clean = false;
					while (($end = $operators->pop())) {
						if ($end->isParenthesis()) {
							$clean = true;
							break;
						} else {
							$output->push($end);
						}
					}
					if (!$clean) {
						throw new \RuntimeException('Mismatched Parenthesis');
					}
				}
			}

			protected function parseOperator(TerminalExpression $expression, Stack $output, Stack $operators) {
				$end = $operators->poke();
				if (!$end) {
					$operators->push($expression);
				} elseif ($end->isOperator()) {
					do {
						if ($expression->isLeftAssoc() && $expression->getPrecedence() <= $end->getPrecedence()) {
							$output->push($operators->pop());
						} elseif (!$expression->isLeftAssoc() && $expression->getPrecedence() < $end->getPrecedence()) {
							$output->push($operators->pop());
						} else {
							break;
						}
					} while (($end = $operators->poke()) && $end->isOperator());
					$operators->push($expression);
				} else {
					$operators->push($expression);
				}
			}

			protected function tokenize($string) {
				$parts = preg_split('#(\d+\.\d+)|(\d+|\+|-|\(|\)|\*|/|\^)|\s+#', $string, null, PREG_SPLIT_NO_EMPTY | PREG_SPLIT_DELIM_CAPTURE);
				var_dump($parts);
				$parts = array_map('trim', $parts);
				return $parts;
			}

		}

	}
