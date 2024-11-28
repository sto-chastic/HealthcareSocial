<?php


//TODO: COmment next line this for deployment, change back for testing
// define('HOST_NAME',"localhost"); //Defines the constants
// define('PORT',"8090"); //Defines the constants

define('HOST_NAME',"http://www.confidr.com"); //Defines the constants
define('PORT',"8090"); //Defines the constants

$null = NULL;

if($argv[1] !== 'neuronaptic2017'){
	//TODO: program an email alert if tried to be accessed incorrectly
	exit("Incorrect password, unathorized access.");
}

require_once("../classes/Crypt.php");
require_once("../classes/class.chathandler.php");

$database_name='confidrV5';
$timezone = date_default_timezone_set("America/Bogota");
//NOTE, you need to specify the address of the local host and the port to the database due to the use of different protocols in webbased php and unix
//TODO: Comment the following line for deplyment, or uncomment it for testing

//$con = mysqli_connect("127.0.0.1:3308","root","",$database_name); //connection variable

$database_name='confidrV5';
$db_username= 'basic_users';
$db_passwrd = '<-></|/euro#Naptic-20!7Bas!c_Usr<-';
$con = mysqli_connect("localhost",$db_username,$db_passwrd,$database_name); //connection variable

if(mysqli_connect_errno()){
	echo "Failed to connect:" . mysqli_connect_errno(); // dot is add to string echo is print on screen.
}

$chatHandler = new ChatHandler();
$crypt = new Crypt();

$socketResource = socket_create(AF_INET, SOCK_STREAM, SOL_TCP);//socket_create ( int $domain , int $type , int $protocol ) --- Creates and returns a socket resource, also referred to as an endpoint of communication. A typical network connection is made up of 2 sockets, one performing the role of the client, and another performing the role of the server.
socket_set_option($socketResource, SOL_SOCKET, SO_REUSEADDR, 1);//bool socket_set_option ( resource $socket , int $level , int $optname , mixed $optval ) --- function sets the option specified by the optname parameter, at the specified protocol level, to the value pointed to by the optval parameter for the socket.
socket_bind($socketResource, 0, PORT); //bool socket_bind ( resource $socket , string $address [, int $port = 0 ] ) --- http://php.net/manual/en/function.socket-bind.php
socket_listen($socketResource); //tell socket to listen

$clientSocketArray = array($socketResource);
while (true) {
	$newSocketArray = $clientSocketArray; //has the list of connections to the socket, this is reestablished every iteration
	socket_select($newSocketArray, $null, $null, 0, 10); //int socket_select ( array &$read , array &$write , array &$except , int $tv_sec [, int $tv_usec = 0 ] ) --- socket_select() accepts arrays of sockets and waits for them to change status.
	
	if (in_array($socketResource, $newSocketArray)) { //when a new connection occurs the resource is present in $newSocketArray both before and after socket_select
		
		//THIS HANDLES NEW CONNECTIONS
		
		$newSocket = socket_accept($socketResource); //Once a successful connection is made, a new socket resource is returned, which may be used for communication. If there are multiple connections queued on the socket, the first will be used.
		$clientSocketArray[] = $newSocket;
		
		$header = socket_read($newSocket, 1024); //string socket_read ( resource $socket , int $length [, int $type = PHP_BINARY_READ ] ) --- length: The maximum number of bytes read is specified by the length parameter. Otherwise you can use \r, \n, or \0 to end reading (depending on the type parameter, see below). The function socket_read() reads from the socket resource socket created by the socket_create() or socket_accept() functions.
		$chatHandler->doHandshake($header, $newSocket, HOST_NAME, PORT);
		
		socket_getpeername($newSocket, $client_ip_address); //gets the ip address of the client and stores it in $client_ip_address
		
		while(socket_recv($newSocket, $socketData, 1024, 0) >= 1){ //The socket_recv() function receives len bytes of data in buf from socket.
			
			$socketInitialMessage = $chatHandler->unseal($socketData);
			$initialMessageObj = json_decode($socketInitialMessage);
			
			$username_e_h = $initialMessageObj->chat_user_username;
			$username_e = pack("H*",$username_e_h);
			$username = $crypt->Decrypt($username_e);
			$veri_token = $initialMessageObj->veri_token;
			
			$stmt = $con->prepare("SELECT email FROM users WHERE username=? AND messages_token=?");
// 			echo "cli_usrnm:" . $username_e;
// 			echo "cli_usrnm_unc:" . $username;
// 			echo "cli_token:" . $veri_token;
			$stmt->bind_param("ss", $username_e, $veri_token);
			$stmt->execute();
			$verification_query = $stmt->get_result();
			
			if(mysqli_num_rows($verification_query) != 1){
				echo "Bad client request from:";
				echo $client_ip_address;
				$badSocketIndex = array_search($newSocket, $clientSocketArray);
				unset($clientSocketArray[$badSocketIndex]); //removes the new socket from the array of new sockets
				continue 2;
			}
			
			$chatHandler->addClient($client_ip_address,$username_e_h,$veri_token,(int)$newSocket);
			echo "chathandl_arr: " ;
			print_r($chatHandler->connections_array);
			break;
		}
		
		$connectionACK = $chatHandler->newConnectionACK($client_ip_address);
		
		//$chatHandler->send($connectionACK); //broadcastst the aknowledge of connection to all parties
		
		$newSocketIndex = array_search($socketResource, $newSocketArray);
		unset($newSocketArray[$newSocketIndex]); //removes the new socket from the array of new sockets
	}
	
	foreach ($newSocketArray as $newSocketArrayResource) {
		while(socket_recv($newSocketArrayResource, $socketData, 1024, 0) >= 1){ //The socket_recv() function receives len bytes of data in buf from socket.
			
			$socketMessage = $chatHandler->unseal($socketData);
			$messageObj = json_decode($socketMessage);
			
			$chat_box_message = $chatHandler->createChatBoxMessage($messageObj->chat_user_username,$messageObj->chat_message);
			if($chatHandler->send($chat_box_message,$messageObj->chat_user_username,$messageObj->chat_target,$messageObj->veri_token)){
			}else{
				echo "MESSAGE NOT SENT, ATTEMPTED:";
				print_r(array("chat_user_username" => $messageObj->chat_user_username,"veri_token" => $messageObj->veri_token,"chat_message" => $messageObj->chat_message,"chat_target" => $messageObj->chat_target));
			}
			break 2;
		}
		
		//this is when the webpage is reloaded
		$socketData = @socket_read($newSocketArrayResource, 1024, PHP_NORMAL_READ);
		if ($socketData === false) {
			socket_getpeername($newSocketArrayResource, $client_ip_address);
			$connectionACK = $chatHandler->connectionDisconnectACK($client_ip_address);
			//$chatHandler->send($connectionACK);
			$newSocketIndex = array_search($newSocketArrayResource, $clientSocketArray);
			unset($clientSocketArray[$newSocketIndex]);
		}
	}
}
socket_close($socketResource);