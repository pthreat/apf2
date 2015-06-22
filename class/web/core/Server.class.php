<?php

	namespace apf\web\core{

		use apf\core\File;

		class Server{

			public static function redirect($location){

				die(header("Location: $location"));

			}

			public static function headerJSON(){

				return header("Content-type: application/json");

			}

			public static function sendExcel(\PHPExcel_Writer_IWriter $writer,$fileName=NULL){

				header("Pragma: public");
				header("Expires: 0");
				header("Cache-Control: must-revalidate, post-check=0, pre-check=0"); 
				header("Content-Type: application/force-download");
				header("Content-Type: application/octet-stream");
				header("Content-Type: application/download");;
				header("Content-Disposition: attachment;filename=$fileName"); 
				header("Content-Transfer-Encoding: binary ");

				//Seems that there's a bug on PHP excel 2007 and we can't write directly
				//to stdout. Actually the code attempts to make the EXACT same thing 
				//we are doing here when the string equals to php://stdout or php://output

				$file	=	File::makeTemporary('__apf_excel_download');
				$writer->save($file);
				$file->chunkedOutput();

			}

			public static function isXhr(){

				return	!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
							(strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');

			}

			public static function isAjax(){

				return self::isXhr();

			}

			public static function getRemoteAddr(){

				return $_SERVER["REMOTE_ADDR"];

			}

		}

	}

?>
