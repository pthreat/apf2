<?php

	namespace apf\type\collection\common{

		use apf\type\collection\Common;

		abstract class UniqueValue extends Common{

			public function add($item,$parameters=NULL){

				if(parent::inArray($item)){

					return;

				}

				parent::add($item,$parameters);

			}

		}

	}
