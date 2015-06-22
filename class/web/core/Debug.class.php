<?php

	namespace apf\web\core{

		class Debug{

			public static function dumpConsole($var){

				static $numDump = NULL;

				$numDump++;

				$var = preg_replace("#\n#",'\n',addslashes(var_export($var,TRUE)));

				$pre = "";

				if($numDump==1){

					$pre = "var __apfDump = [];";

				}

				echo "<script>$pre"."__apfDump[$numDump]='$var';console.log('APF Console dump [$numDump]: '+__apfDump);</script>";

			}

		}

	}
