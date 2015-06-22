<?php

	namespace apf\iface\type{

		interface Convertible{

			public function toString();
			public function toJSON();
			public function toArray();
			public function toInt();
			public function toReal();
			public function toBoolean();
			public function toChar();
			public function __toString();

		}

	}
