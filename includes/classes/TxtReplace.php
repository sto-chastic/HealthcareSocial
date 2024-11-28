<?php  
	class TxtReplace{

		public function __construct(){
		}

		public function replace($text){

			$new_text=preg_replace("/\\\'/","'",$text);
			$new_text=preg_replace("/\\\\\"/","\"",$new_text);

			return $new_text;
		}

		public function replaceLineBreak_OLD($text){

			$new_text=preg_replace("/\\\\r\\\\n/","<br>",$text);
			$new_text=preg_replace("/\\\\\\\\/",'&bsol;',$new_text);

			return $new_text;
		}
		
		public function replaceLineBreak($text){
			
			$new_text=preg_replace("/\\\\n/","<br>",$text);
			$new_text=preg_replace("/\\\\/",'&bsol;',$new_text);
			
			return $new_text;
		}
		
		public function removeLineBreak($text){
			
			$new_text=preg_replace("/\\\\n/"," ",$text);
			$new_text=preg_replace("/\\\\/",'',$new_text);
			
			return $new_text;
		}

		public function entities($text){
	
/* 			$new_text = $this->xMLClean($text);
			$new_text = htmlentities($new_text,ENT_COMPAT,'ISO-8859-1'); */
			$new_text = htmlentities($text,ENT_COMPAT, 'UTF-8', false);
/* 			$new_text = $this->xMLClean($new_text); */
			return $new_text;
		}
		
		public function xMLClean($strin) {
			$strout = null;
			
			for ($i = 0; $i < strlen($strin); $i++) {
				$ord = ord($strin[$i]);
				
				if (($ord > 0 && $ord < 32) || ($ord >= 127)) {
					$strout .= "&amp;#{$ord};";
				}
				else {
					switch ($strin[$i]) {
						case '&':
							$strout .= '&amp;';
							break;
						case '"':
							$strout .= '&quot;';
							break;
						case '<':
							$strout .= '&lt;';
							break;
						case '>':
							$strout .= '&gt;';
							break;
						default:
							$strout .= $strin[$i];
					}
				}
			}
			
			return $strout;
		}
		/**
		 * Removes accents from a text
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function removeAccents($strin){
			$normalizeChars = array( 
				'Á'=>'A', 'À'=>'A', 'Â'=>'A', 'Ã'=>'A', 'Å'=>'A', 'Ä'=>'A', 'Æ'=>'AE', 'Ç'=>'C', 
				'É'=>'E', 'È'=>'E', 'Ê'=>'E', 'Ë'=>'E', 'Í'=>'I', 'Ì'=>'I', 'Î'=>'I', 'Ï'=>'I', 'Ð'=>'Eth', 
				'Ñ'=>'N', 'Ó'=>'O', 'Ò'=>'O', 'Ô'=>'O', 'Õ'=>'O', 'Ö'=>'O', 'Ø'=>'O', 
				'Ú'=>'U', 'Ù'=>'U', 'Û'=>'U', 'Ü'=>'U', 'Ý'=>'Y',
				'á'=>'a', 'à'=>'a', 'â'=>'a', 'ã'=>'a', 'å'=>'a', 'ä'=>'a', 'æ'=>'ae', 'ç'=>'c', 
				'é'=>'e', 'è'=>'e', 'ê'=>'e', 'ë'=>'e', 'í'=>'i', 'ì'=>'i', 'î'=>'i', 'ï'=>'i', 'ð'=>'eth', 
				'ñ'=>'n', 'ó'=>'o', 'ò'=>'o', 'ô'=>'o', 'õ'=>'o', 'ö'=>'o', 'ø'=>'o', 
				'ú'=>'u', 'ù'=>'u', 'û'=>'u', 'ü'=>'u', 'ý'=>'y', 
				'ß'=>'ss', 'þ'=>'thorn', 'ÿ'=>'y' 
	        );
	        $strout = strtr($strin,$normalizeChars);
	        return $strout;
		}
		/**
		 * Removes spaces from a text
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function removeSpaces($strin){
        	$normalizeChars = array(' '=>'');
        	$strout = strtr($strin,$normalizeChars);
        	return $strout;
		}
		/**
		 * Removes commas from a text
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function removeCommas($strin){
		    $normalizeChars = array(','=>'');
		    $strout = strtr($strin,$normalizeChars);
		    return $strout;
		}
		/**
		 * Removes punctuation from a text, all except commas
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function removePunctuation($strin){
			$normalizeChars = array( 
				'<'=>'', '>'=>'','{'=>'', '}'=>'', '['=>'', ']'=>'', '('=>'', ')'=>'',
				
				'¿'=>'', '?'=>'', '¡'=>'', '!'=>'', ':'=>'', ';'=>'', '.'=>'',

				'´'=>'', '`'=>'', '¨'=>'', '~'=>'', '^'=>'','\''=>'','"'=>'',
				
				'%'=>'', '&'=>'and', '#'=>'', '@'=>'','$'=>'',
				
				'='=>'', '*'=>'', '+'=>'', '-'=>'','÷'=>'',

				'_'=>'', '|'=>'', '/'=>''
	        );
	        $strout = strtr($strin,$normalizeChars);
	        $strout = stripslashes($strout);
			return $strout;
		}
		/**
		 * Prepares a text for search: removes accents, punctuantion (except commas), spaces makes lowercase
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function prepareForSearch($input_text){
			if(is_string($input_text)){
				$output_text=$input_text;
				$output_text= $this->removeAccents($output_text);
				$output_text=$this->removeSpaces($output_text);
				$output_text=$this->removePunctuation($output_text);
				$output_text=strtolower($output_text);
				return $output_text;
			}
		}
		/**
		 * Prepares a text for search: removes accents, punctuantion (except commas), makes lowercase
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function prepareForSearchExceptSpaces($input_text){
		    if(is_string($input_text)){
		        $output_text=$input_text;
		        $output_text= $this->removeAccents($output_text);
		        $output_text=$this->removePunctuation($output_text);
		        $output_text=strtolower($output_text);
		        return $output_text;
		    }
		}
		/**
		 * Prepares a text for search: removes accents, punctuantion (including commas), makes lowercase
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function prepareForSearchNoCommas($input_text){
		    if(is_string($input_text)){
		        $output_text=$input_text;
		        $output_text= $this->removeAccents($output_text);
		        $output_text=$this->removeSpaces($output_text);
		        $output_text=$this->removePunctuation($output_text);
		        $output_text=$this->removeCommas($output_text);
		        $output_text=strtolower($output_text);
		        return $output_text;
		    }
		}
		
		/**
		 * Prepares a code for search: removes accents, punctuantion (including commas)
		 * @param string $input_text Input text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function prepareCodeForSearch($input_text){
		    if(is_string($input_text)){
		        $output_text=$input_text;
		        $output_text= $this->removeAccents($output_text);
		        $output_text=$this->removeSpaces($output_text);
		        $output_text=$this->removePunctuation($output_text);
		        $output_text=$this->removeCommas($output_text);
		        return $output_text;
		    }
		}
		
		/**
		 * Removes all the text left of a period (including the period)
		 * @param string $initial_text Initial text
		 * @author JMZAM
		 * @return string $output_text
		 **/
		public function ignoreLeftOfPeriod($input_text) {
		   
		   if (strpos($input_text, '.') !== FALSE)
		       $output_text = substr(strrchr($input_text,"."),1);
		   else 
		       $output_text=$input_text;
		   return $output_text;
		}
		
		public function reHash($input){
			$result = 0;
			for ($i = 0; $i < strlen($input); $i++) {
				$result += ord($input[$i]);
			}
			return (string) ($result % 256);
			//Now it should be pretty obvious what this hash function does. It sums together the ASCII values of each character of input, and then takes the modulo of that result with 256.
		}

	}

?>