<?php
class ChatHandler {
	public $connections_array;
	
	public function __construct(){
		$this->connections_array = array();
	}
	
	function addClient($client_ip,$client_username,$veri_token,$resource_id){
		$_temp_connections_array = $this->connections_array;
		$_temp_connections_array[$client_username]=array("veri_token"=>$veri_token,"ip"=>$client_ip,"resource_id"=>$resource_id);
		$this->connections_array = $_temp_connections_array;
	}
	
	function send($message,$sender,$chat_target,$veri_token) {
		global $clientSocketArray; //references the global variable, not a local variable within the function
		$messageLength = strlen($message);

		$target = $this->connections_array[$chat_target]["resource_id"];
		$official_token = $this->connections_array[$sender]["veri_token"];
		
		if($official_token == $veri_token){
			foreach($clientSocketArray as $clientSocket)
			{
				$_to_socket = (int)$clientSocket;
				if($_to_socket == $target ){
					@socket_write($clientSocket,$message,$messageLength); //@ supresses error messages from php
				}
			}
			return true;
		}
		else{
			return false;
		}
	}
	
	function unseal($socketData) {
		$length = ord($socketData[1]) & 127;
		if($length == 126) {
			$masks = substr($socketData, 4, 4);
			$data = substr($socketData, 8);
		}
		elseif($length == 127) {
			$masks = substr($socketData, 10, 4);
			$data = substr($socketData, 14);
		}
		else {
			$masks = substr($socketData, 2, 4);
			$data = substr($socketData, 6);
		}
		$socketData = "";
		for ($i = 0; $i < strlen($data); ++$i) {
			$socketData .= $data[$i] ^ $masks[$i%4];
		}
		return $socketData;
	}
	
	function seal($socketData) {
		$b1 = 0x80 | (0x1 & 0x0f);
		$length = strlen($socketData);
		
		if($length <= 125)
			$header = pack('CC', $b1, $length);
			elseif($length > 125 && $length < 65536)
			$header = pack('CCn', $b1, 126, $length);
			elseif($length >= 65536)
			$header = pack('CCNN', $b1, 127, $length);
			return $header.$socketData;
	}
	
	function doHandshake($received_header,$client_socket_resource, $host_name, $port) {
		$headers = array();
		$lines = preg_split("/\r\n/", $received_header);
		foreach($lines as $line)
		{
			$line = chop($line); //removes \n\r
			if(preg_match('/\A(\S+): (.*)\z/', $line, $matches))
			{
				$headers[$matches[1]] = $matches[2]; //watch macthes the first parenthesis [1] and second parenthesis [2]
			}
		}
		
		$secKey = $headers['Sec-WebSocket-Key'];
		$secAccept = base64_encode(pack('H*', sha1($secKey . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11'))); //hashes data into SHA1 and then packs the data into a binary string, Hexadecimal for H*
		$buffer  = "HTTP/1.1 101 Web Socket Protocol Handshake\r\n" .
				"Upgrade: websocket\r\n" .
				"Connection: Upgrade\r\n" .
				"WebSocket-Origin: $host_name\r\n" .
				"WebSocket-Location: ws://$host_name:$port/includes/socket_handlers/php-socket.php\r\n".
				"Sec-WebSocket-Accept:$secAccept\r\n\r\n";
		// 		echo "buffer:" . $buffer;
		// 		echo "///strlen:" . strlen($buffer);
		socket_write($client_socket_resource,$buffer,strlen($buffer));
		//"WebSocket-Location: ws://$host_name:$port/confidr/socket_handlers/php-socket.php\r\n"
	}
	
	function newConnectionACK($client_ip_address) {
		$message = 'New client ' . $client_ip_address.' joined';
		$messageArray = array('message'=>$message,'message_type'=>'chat-connection-ack');
		$ACK = $this->seal(json_encode($messageArray));
		return $ACK;
	}
	
	function connectionDisconnectACK($client_ip_address) {
		$message = 'Client ' . $client_ip_address.' disconnected';
		$messageArray = array('message'=>$message,'message_type'=>'chat-connection-ack');
		$ACK = $this->seal(json_encode($messageArray));
		return $ACK;
	}
	
	function createChatBoxMessage($chat_user,$chat_box_message) {
		$message = $chat_box_message;
		$messageArray = array('sender'=>$chat_user,'message'=>$message);
		$chatMessage = $this->seal(json_encode($messageArray));
		return $chatMessage;
	}
}
?>