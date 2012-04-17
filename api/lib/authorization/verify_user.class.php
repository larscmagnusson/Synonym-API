<?php
/**
 * Används för att verifierar en användare mot ett register av användare.
 */
class VerifyUser{
	
	private $users_file;
	private $users;
	private $user_id;
	
	public $user_name;
	
	const SALT = "Sträng-att-salta-med-här";
	
	public function VerifyUser($users_file, $user_id){
		$this->users_file = $users_file;
		$this->user_id = $user_id;
		$this->users = $this->load_users();
	}
	
	/**
	 * Kollar om user-idt finns med bland dom godkända användarna.
	 */
	public function is_allowed(){
		
		$encrypted = $this->user_id_encrypted();
		
		foreach($this->users->user as $user){
			if($user->id == $encrypted){
				$this->user_name = (string)$user->name;
				return true;
			}
		}
			
		return false; 	// Om man kommer hit är man inte godkänd
	}
	
	/**
	 * Saltar namnet och krypterar det.
	 */
	private function user_id_encrypted(){
		return md5(self::SALT.$this->user_id.self::SALT.self::SALT);
	}
	
	/**
	 * Hämtar alla användare från filen.
	 */
	private function load_users(){
		return simplexml_load_file($this->users_file);
	}
}
?>