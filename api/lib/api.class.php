<?php
	include_once('simplehtmldom_1_5/simple_html_dom.php');
	include_once('output/synonyms_output_xml.class.php');
	include_once('output/synonyms_output_json.class.php');
	include_once('authorization/verify_user.class.php');
	
	class SynonymAPI{
		
		private static $word;
		private static $html_dom_parser_data;
		private static $found_synomyms;
		private static $parameters;
		
		const URL_TO_DATA  = "http://www.synonymer.se/?query=";
		
		/**
		 * Skickar en query till servern och hämtar ut resultatet utifrån angivna parametrar.
		 */
		public static function query($data, $output = true){
			self::$parameters = self::get_parameters($data);
			
			if(! self::$parameters["user_passed"])
				self::error("Du angav ett felaktigt id");
			
			if(! self::$parameters["word"])
				self::error("Du måste ange ett ord");		
			
			self::$word = self::encode(self::$parameters['word']);
			self::get_data();
			self::$found_synomyms = self::extract_standard_data();
			
			if(self::$parameters["include_user_submits"])
				self::$found_synomyms = array_merge(self::$found_synomyms, self::extract_user_submited_data());
			
			if(self::$parameters["sort"])
				self::sort(self::$parameters["sort"]);
				
			if(self::$parameters["text_transform"])
				self::transform(self::$parameters["text_transform"]);
			
			if($output)
				self::success();

			
			return self::$found_synomyms;
		}
		
		/**
		 * Hämtar parametrar ur medskickad data. 
		 * Anger default-värden om en viss parameter inte skickats med.
		 */
		private function get_parameters($data){
			
			if(isset($data["id"])){
				$user = new VerifyUser("lib/authorization/users.xml", $data["id"]);
				
				if($user->is_allowed()){
					$data["user_passed"] = true;
					$data["user_name"] = $user->user_name;
				}
			} else
				$data["user_passed"] = false;

			if(! isset($data["word"])  ||  $data["word"] == "")
				$data["word"] = false;
			
			if(isset($data["language"])){
				if($data["language"] != "swe" && $data["language"] != "eng" )
					$data["language"] = "swe";
			} else
				$data["language"] = "swe";
			
			if(isset($data["include_user_submits"])){
				if($data["include_user_submits"] == "true" || $data["include_user_submits"] == "1")
					$data["include_user_submits"] = true;
				else
					$data["include_user_submits"] = false;
			} else
				$data["include_user_submits"] = false;
			
			if(isset($data["sort"])){
				
				if($data["sort"] != "desc" && $data["sort"] != "asc")
					$data["sort"] = "asc";
			} else
				$data["sort"] = false;
			
			if(isset($data["text_transform"])){
				if($data["text_transform"] != "uppercase"  && $data["text_transform"] != "lowercase")
					$data["text_transform"] = "normal";
			} else
				$data["text_transform"] = false;
			
			if(isset($data["format"])){
			
				if($data["format"] != "json"  && $data["format"] != "xml")
					$data["format"] = "json";
			} else
				$data["format"] = "json";

			return $data;
		}
		
		/**
		 * Sorterar på alfabetisk ordning, stigande eller fallande.
		 */
		private static function sort($order){
			if($order == "asc")
				sort(self::$found_synomyms);
			if($order == "desc")
				rsort(self::$found_synomyms);
		}
		
		/**
		 * Gör om alla ord till stora/små bokstäver
		 */
		private static function transform($type){

			foreach(self::$found_synomyms as &$word){
				if($type == 'lowercase')
					$word = strtolower($word);
				elseif($type == 'uppercase'){
					$word = strtoupper($word);
				} else
					$word = ucfirst(strtolower($word));
			}
		}
		
		/**
		 * Gör om å ä ö till det format som www.synonymer.se kan tolka
		 */
		private static function encode($str){
			return str_replace(array('å','ä','ö', 'Å','Ä','Ö'), array('%E5','%E4','%F6','%E5','%E4','%F6'), $str);
		}
		
		/**
		 * Hämtar datat från www.synonymer.se.
		 */
		private static function get_data(){
			$language = (self::$parameters['language'] == "swe") ? "" : "&lang=engsyn";		// Sätter språket. Svenska anges genom att inte skicka med variabeln alls.
			
			self::$html_dom_parser_data = file_get_html(self::URL_TO_DATA.self::$word.$language); 
		}
		
		/**
		 * Hämtar ut alla ord som hittats, dock bara ord som INTE HAR skickats in av användare av tjänsten.
		 */
		private function extract_standard_data(){
			$standard_data_row = self::$html_dom_parser_data->find('table.synonymer tbody tr', 0);	// Hämtar datat
			$words = array();
		
			if(! is_null($standard_data_row)){
				$word_list = $standard_data_row->find('td.synonym a');
				
				foreach($word_list as $word)
					array_push($words, $word->plaintext);
			}
				return $words;
		}
		
		/**
		 * Hämtar ut alla ord som hittats, dock bara ord som HAR skickats in av användare av tjänsten.
		 */
		private function extract_user_submited_data(){
			$standard_data_row = self::$html_dom_parser_data->find('table.synonymer tbody tr', 1);	// Hämtar datat
			$words = array();
			
			if(! is_null($standard_data_row)){
				$word_list = $standard_data_row->find('td.synonym');
			
				$words = explode(",", $word_list[0]->plaintext);
			}
			return $words;
		}	
		
		/**
		 * Kollar om en användare har tillgång till API't, d.vs. en godkänd API-nyckel.
		 */
		private function user_has_access(){
			self::$user = new VerifyUser("lib/users.xml", "lars");
		}
		
		/**
		 * Skriver ut ett lyckat resultatet i angivet format.
		 */
		private function success(){
			if(self::$parameters["format"] == 'json')
				SynonymsOutputJSON::success(self::$found_synomyms, self::$parameters);		
			elseif(self::$parameters["format"] == 'xml')
				SynonymsOutputXML::success(self::$found_synomyms, self::$parameters);		
		}	
		
		/**
		 * Skriver ut fel i angivet format.
		 * Avbryter efter anrop.
		 */
		private function error($error){
			if(self::$parameters["format"] == 'json')
				SynonymsOutputJSON::error($error);		
			elseif(self::$parameters["format"] == 'xml')
				SynonymsOutputXML::error($error);	
				
			exit;	
		}
	}