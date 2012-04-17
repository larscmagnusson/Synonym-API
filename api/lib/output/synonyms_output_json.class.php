<?php 
	include_once('i_synonyms_output.interface.php');
	
	class SynonymsOutputJSON implements ISynonymsOutput{
		
		private static $raw_data;
		private static $output_data;
		private static $extras;
		
		/**
		 * Skriver ut datan i $output_data som JSON;
		 */
		public static function output(){
			echo self::header();
			echo self::indent(self::$output_data);
		}
		
		/**
		 * Skapar ett JSON-objekt utifrån inskickad data från ett lyckat anrop;
		 */
		public static function success($data, $extras = null){
			self::$raw_data = $data;
			self::$extras = $extras;

			$data = self::$raw_data;
			
			foreach($data as &$word)
				$word = utf8_encode($word);
			
			$words = array('words'=>$data);
			$extras = array('settings'=>self::$extras);
			
			$merged_data = array_merge($extras, $words);
			
			self::$output_data = json_encode($merged_data);
			self::output();
		}
		
		/**
		 * Skapar ett JSON-objekt utifrån inskickat fel från ett misslyckat anrop;
		 */
		public static function error($error){
			self::header();
			self::$output_data = json_encode(array('error'=>$error));
			self::output();
		}
		
		/**
		 * Skriver ut en JSON-header.
		 */
		public static function header(){
			header('Content-Type: application/json;');
		}
	
		/**
		 * http://recursive-design.com/blog/2008/03/11/format-json-with-php/
		 * 
		 * Indents a flat JSON string to make it more human-readable.
		 *
		 * @param string $json The original JSON string to process.
		 *
		 * @return string Indented version of the original JSON string.
		 */
		private static function indent($json) {
		
			$result      = '';
			$pos         = 0;
			$strLen      = strlen($json);
			$indentStr   = '  ';
			$newLine     = "\n";
			$prevChar    = '';
			$outOfQuotes = true;
		
			for ($i=0; $i<=$strLen; $i++) {
		
				// Grab the next character in the string.
				$char = substr($json, $i, 1);
		
				// Are we inside a quoted string?
				if ($char == '"' && $prevChar != '\\') {
					$outOfQuotes = !$outOfQuotes;
				
				// If this character is the end of an element, 
				// output a new line and indent the next line.
				} else if(($char == '}' || $char == ']') && $outOfQuotes) {
					$result .= $newLine;
					$pos --;
					for ($j=0; $j<$pos; $j++) {
						$result .= $indentStr;
					}
				}
				
				// Add the character to the result string.
				$result .= $char;
		
				// If the last character was the beginning of an element, 
				// output a new line and indent the next line.
				if (($char == ',' || $char == '{' || $char == '[') && $outOfQuotes) {
					$result .= $newLine;
					if ($char == '{' || $char == '[') {
						$pos ++;
					}
					
					for ($j = 0; $j < $pos; $j++) {
						$result .= $indentStr;
					}
				}
				
				$prevChar = $char;
			}
		
			return $result;
		}
	}
?>