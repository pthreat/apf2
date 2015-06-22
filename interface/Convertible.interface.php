<?php

	namespace apf\iface{

		use apf\iface\convertible\IntNum;
		use apf\iface\convertible\RealNum;
		use apf\iface\convertible\Char;
		use apf\iface\convertible\Boolean;
		use apf\iface\convertible\Str;
		use apf\iface\convertible\Vector;
		use apf\iface\convertible\JSON;

		interface Convertible extends Str,IntNum,Char,Vector,Boolean,RealNum,JSON{

		}

	}
