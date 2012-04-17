<?php 
	include_once('i_synonyms_output.interface.php');
	
	class SynonymsOutputXML implements ISynonymsOutput{
	
		private static $raw_data;
		private static $output_data;
		private static $extras;
		
		/**
		 * Skriver ut datan i $output_data som XML;
		 */
		public static function output(){
			self::header();
			echo self::$output_data;
		}
		
		/**
		 * Skapar ett XML-dokument utifrån inskickat data från ett lyckat anrop;
		 */
		public static function success($data, $extras = null){
			self::$raw_data = $data;
			self::$extras = $extras;
			
			$xml = new DomDocument();
			$root = $xml->appendChild($xml->createElement('response'));
			
			if(! is_null(self::$extras)){
				$settings_tag = $root->appendChild($xml->createElement("settings"));
				
				foreach(self::$extras as $key => $value){
					if(is_bool($value) && ! $value)
						$value = "false";
						
					$settings_tag->appendChild($xml->createElement($key,  utf8_encode( $value )));
				}
			}
			
			$nr_of_hits =  count(self::$raw_data);
			$synonyms_tag = $root->appendChild($xml->createElement("synonyms"));
			$hits_attr = $xml->createAttribute('hits');
			$hits_attr->value = $nr_of_hits;
			$synonyms_tag->appendChild($hits_attr);
			
			for($i=0; $i < $nr_of_hits; $i++){
				$synonyms_tag->appendChild($xml->createElement('synonym',  utf8_encode(self::$raw_data[$i])));
			}	
			
			$xml->formatOutput = true; 
			self::$output_data = $xml->saveXML();
			
			self::output();
		}
		
		/**
		 * Skapar ett XML-dokument utifrån inskickat fel från ett misslyckat anrop;
		 */
		public static function error($error){
			self::header();
			
			$xml = new DomDocument();
			$root = $xml->appendChild($xml->createElement('error', $error));
			
			$xml->formatOutput = true; 
			self::$output_data = $xml->saveXML();
			
			self::output();
		}
		
		/**
		 * Skriver ut en XML-header.
		 */
		public static function header(){
			header('Content-type: text/xml');
		}
	}
?>