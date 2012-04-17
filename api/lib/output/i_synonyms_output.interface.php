<?php 
	interface ISynonymsOutput{
		public static function header();
		public static function error($error);
		public static function success($data);
		public static function output();
	}
?>