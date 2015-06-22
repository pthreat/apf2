<?php

	namespace apf\data\set{

		use apf\iface\data\set\Charset	as	CharsetInterface;

		abstract class Charset extends Common implements CharsetInterface{

			public static function fetch($set,$name=NULL){

				return parent::fetch($set,"charsets");

			}

		}

	}
