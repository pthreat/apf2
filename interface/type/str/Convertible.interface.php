<?php

	namespace apf\iface\type\str{

		interface Convertible{

			public function toString();
			public function toJSON();
			public function toChar();
			public function __toString();

		}

	}
