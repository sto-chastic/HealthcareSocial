<?php
/**
 * The MIT License (MIT)
 * Copyright (c) 2016 ionCube Ltd.
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy of this software and associated
 * documentation files (the "Software"), to deal in the Software without restriction, including without limitation the
 * rights to use, copy, modify, merge, publish, distribute, sublicense, and/or sell copies of the Software, and to
 * permit persons to whom the Software is furnished to do so, subject to the following conditions:
 */
class Crypt
{
    private $cipher_algo;
    private $hash_algo;
    private $iv_num_bytes;
    private $format;
    private $default_key;
    const FORMAT_RAW = 0;
    const FORMAT_B64 = 1;
    const FORMAT_HEX = 2;
    /**
     * Construct a Crypt, using aes256 encryption, sha256 key hashing and base64 encoding.
     * @param string $cipher_algo The cipher algorithm.
     * @param string $hash_algo   Key hashing algorithm.
     * @param [type] $fmt         Format of the encrypted data.
     */
    public function __construct($key = NULL, $key_m = NULL,$cipher_algo = 'aes-256-ctr', $hash_algo = 'sha256', $fmt = Crypt::FORMAT_B64)
    {
        if($key == NULL){
            $this->default_key = "hasg78dhjkdiy23HJGJYGjv5643476jhvajhJF39346vygduyegew6785324765JHGJVyjhavsdhgjvdgvjvdayvyvjyavd";
        } else{
            $this->default_key = $key;
        }
        if($key_m== NULL){
            $this->default_key_m = "khabsdjhbsd65465465HFDHG7897642335SPOLKHGC34837jbsfug8763487hjsdjhfbsft6237854";
        } else{
            $this->default_key_m = $key_m;
        }
        
        $this->cipher_algo = $cipher_algo;
        $this->hash_algo = $hash_algo;
        $this->format = $fmt;
        if (!in_array($cipher_algo, openssl_get_cipher_methods(true)))
        {
            throw new \Exception("Crypt:: - unknown cipher algo {$cipher_algo}");
        }
        if (!in_array($hash_algo, openssl_get_md_methods(true)))
        {
            throw new \Exception("Crypt:: - unknown hash algo {$hash_algo}");
        }
        $this->iv_num_bytes = openssl_cipher_iv_length($cipher_algo);
    }
    /**
     * Encrypt a string.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public function encryptString($in, $key = null, $fmt = null)
    {
        if ($key === null) {
            $key = $this->default_key;
        }
        if ($fmt === null) {
            $fmt = $this->format;
        }
        // Build an initialisation vector
        $iv = openssl_random_pseudo_bytes($this->iv_num_bytes, $isStrongCrypto);
        
        //echo $isStrongCrypto;
        if (!$isStrongCrypto) {
            throw new \Exception("Crypt::encryptString() - Not a strong key");
        }
        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);
        // and encrypt
        $opts =  OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($in, $this->cipher_algo, $keyhash, $opts, $iv);
        if ($encrypted === false)
        {
            throw new \Exception('Crypt::encryptString() - Encryption failed: ' . openssl_error_string());
        }
        // The result comprises the IV and encrypted data
        $res = $iv . $encrypted;
        // and format the result if required.
        if ($fmt == Crypt::FORMAT_B64)
        {
            $res = base64_encode($res);
        }
        else if ($fmt == Crypt::FORMAT_HEX)
        {
            $res = unpack('H*', $res)[1];
        }
        return $res;
    }
    /**
     * Encrypt a string uniquely.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public function encryptStringU($in, $key = null, $fmt = null)
    {
        if ($key === null) {
            $key = $this->default_key;
        }
        if ($fmt === null) {
            $fmt = $this->format;
        }
        // Build an initialisation vector
        $iv = openssl_digest($in, $this->hash_algo, true);
        $iv = substr($iv, 11, 16);
        
        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);
        // and encrypt
        $opts =  OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($in, $this->cipher_algo, $keyhash, $opts, $iv);
        if ($encrypted === false)
        {
            throw new \Exception('Crypt::encryptString() - Encryption failed: ' . openssl_error_string());
        }
        // The result comprises the IV and encrypted data
        $res = $iv . $encrypted;
        // and format the result if required.
        if ($fmt == Crypt::FORMAT_B64)
        {
            $res = base64_encode($res);
        }
        else if ($fmt == Crypt::FORMAT_HEX)
        {
            $res = unpack('H*', $res)[1];
        }
        return $res;
    }
    /**
     * Encrypt a string uniquely. Designed for messaging.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public function encryptStringM($in, $from, $to, $creation, $key = null, $fmt = null)
    {
        if ($key === null) {
	        	$creation_int= str_replace("-","",$creation);
	        	$creation_int= str_replace(" ","",$creation_int);
	        	$creation_int= str_replace(":","",$creation_int);
	        	$creation_int= substr($creation_int,5);
	        	
	        	$salt = "tinY8!7aumOun70fS@@lT!oTheRS!235alT";
	        	$key = $from.$salt.$to;
	        	$key = $this->seeded_shuffle($key, $creation_int);
        }
        if ($fmt === null) {
            $fmt = $this->format;
        }
        // Build an initialisation vector
        //$iv = openssl_digest($time, $this->hash_algo, true);
        //$iv = substr($iv, 11, 16);
        $iv = openssl_random_pseudo_bytes($this->iv_num_bytes, $isStrongCrypto);
        //$iv="qwertyuiopasdfgh";
        //echo $isStrongCrypto;
        if (!$isStrongCrypto) {
            throw new \Exception("Crypt::encryptString() - Not a strong key");
        }
        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);
        // and encrypt
        $opts =  OPENSSL_RAW_DATA;
        $encrypted = openssl_encrypt($in, $this->cipher_algo, $keyhash, $opts, $iv);
        if ($encrypted === false)
        {
            throw new \Exception('Crypt::encryptString() - Encryption failed: ' . openssl_error_string());
        }
        // The result comprises the IV and encrypted data
        $res = $iv . $encrypted;
        // and format the result if required.
        if ($fmt == Crypt::FORMAT_B64)
        {
            $res = base64_encode($res);
        }
        else if ($fmt == Crypt::FORMAT_HEX)
        {
            $res = unpack('H*', $res)[1];
        }
        return $res;
    }
    
    /**
     * Encrypt a string uniquely. Designed for personal_info.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public function encryptStringPI($in, $username, $creation, $fmt = null)
    {
    	
    	$creation_int= str_replace("-","",$creation);
    	$creation_int= str_replace(" ","",$creation_int);
    	$creation_int= str_replace(":","",$creation_int);
    	$creation_int= substr($creation_int,5);
    	
    	$salt = "toTy!tinY8!7auMOun70fS@@lT!salTm(8!";
    	$key = $username.$salt.$creation;
    	$key = $this->seeded_shuffle($key, $creation_int);

    	if ($fmt === null) {
    		$fmt = $this->format;
    	}
    	// Build an initialisation vector
    	//$iv = openssl_digest($time, $this->hash_algo, true);
    	//$iv = substr($iv, 11, 16);
    	$iv = openssl_random_pseudo_bytes($this->iv_num_bytes, $isStrongCrypto);
    	//$iv="qwertyuiopasdfgh";
    	//echo $isStrongCrypto;
    	if (!$isStrongCrypto) {
    		throw new \Exception("Crypt::encryptString() - Not a strong key");
    	}
    	// Hash the key
    	$keyhash = openssl_digest($key, $this->hash_algo, true);
    	// and encrypt
    	$opts =  OPENSSL_RAW_DATA;
    	$encrypted = openssl_encrypt($in, $this->cipher_algo, $keyhash, $opts, $iv);
    	if ($encrypted === false)
    	{
    		throw new \Exception('Crypt::encryptString() - Encryption failed: ' . openssl_error_string());
    	}
    	// The result comprises the IV and encrypted data
    	$res = $iv . $encrypted;
    	// and format the result if required.
    	if ($fmt == Crypt::FORMAT_B64)
    	{
    		$res = base64_encode($res);
    	}
    	else if ($fmt == Crypt::FORMAT_HEX)
    	{
    		$res = unpack('H*', $res)[1];
    	}
    	return $res;
    }
    /**
     * Decrypt a string.
     * @param  string $in  String to decrypt personal info.
     * @param  string $key Decryption key.
     * @param  int $fmt Optional override for the input encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The decrypted string.
     */
    public function decryptStringPI($in, $username, $creation, $fmt = null)
    {
    	
    	$creation_int= str_replace("-","",$creation);
    	$creation_int= str_replace(" ","",$creation_int);
    	$creation_int= str_replace(":","",$creation_int);
    	$creation_int= substr($creation_int,5);
    	
    	$salt = "toTy!tinY8!7auMOun70fS@@lT!salTm(8!";
    	$key = $username.$salt.$creation;
    	$key = $this->seeded_shuffle($key, $creation_int);
    	
    	if ($fmt === null)
    	{
    		$fmt = $this->format;
    	}
    	$raw = $in;
    	// Restore the encrypted data if encoded
    	if ($fmt == Crypt::FORMAT_B64)
    	{
    		$raw = base64_decode($in);
    	}
    	else if ($fmt == Crypt::FORMAT_HEX)
    	{
    		$raw = pack('H*', $in);
    	}
    	// and do an integrity check on the size.
    	if (strlen($raw) < $this->iv_num_bytes)
    	{
    		throw new \Exception('Crypt::decryptString() - ' .
    				'data length ' . strlen($raw) . " is less than iv length {$this->iv_num_bytes}");
    	}
    	// Extract the initialisation vector and encrypted data
    	$iv = substr($raw, 0, $this->iv_num_bytes);
    	$raw = substr($raw, $this->iv_num_bytes);
    	// Hash the key
    	$keyhash = openssl_digest($key, $this->hash_algo, true);
    	// and decrypt.
    	$opts = OPENSSL_RAW_DATA;
    	$res = openssl_decrypt($raw, $this->cipher_algo, $keyhash, $opts, $iv);
    	if ($res === false)
    	{
    		throw new \Exception('Crypt::decryptString - decryption failed: ' . openssl_error_string());
    	}
    	return $res;
    }
    /**
     * Decrypt a string.
     * @param  string $in  String to decrypt.
     * @param  string $key Decryption key.
     * @param  int $fmt Optional override for the input encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The decrypted string.
     */
    public function decryptString($in, $key = null, $fmt = null)
    {
        if ($key === null) {
            $key = $this->default_key;
        }
        if ($fmt === null)
        {
            $fmt = $this->format;
        }
        $raw = $in;
        // Restore the encrypted data if encoded
        if ($fmt == Crypt::FORMAT_B64)
        {
            $raw = base64_decode($in);
        }
        else if ($fmt == Crypt::FORMAT_HEX)
        {
            $raw = pack('H*', $in);
        }
        // and do an integrity check on the size.
        if (strlen($raw) < $this->iv_num_bytes)
        {
            throw new \Exception('Crypt::decryptString() - ' .
                'data length ' . strlen($raw) . " is less than iv length {$this->iv_num_bytes}");
        }
        // Extract the initialisation vector and encrypted data
        $iv = substr($raw, 0, $this->iv_num_bytes);
        $raw = substr($raw, $this->iv_num_bytes);
        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);
        // and decrypt.
        $opts = OPENSSL_RAW_DATA;
        $res = openssl_decrypt($raw, $this->cipher_algo, $keyhash, $opts, $iv);
        if ($res === false)
        {
            throw new \Exception('Crypt::decryptString - decryption failed: ' . openssl_error_string());
        }
        return $res;
    }
    /**
     * Decrypt a string.
     * @param  string $in  String to decrypt.
     * @param  string $key Decryption key.
     * @param  int $fmt Optional override for the input encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The decrypted string.
     */
    public function decryptStringM($in, $from, $to, $creation, $key = null, $fmt = null)
    {
        if ($key === null) {
	        	$creation_int= str_replace("-","",$creation);
	        	$creation_int= str_replace(" ","",$creation_int);
	        	$creation_int= str_replace(":","",$creation_int);
	        	$creation_int= substr($creation_int,5);
	        	
	        	$salt = "tinY8!7aumOun70fS@@lT!oTheRS!235alT";
	        	$key = $from.$salt.$to;
	        	$key = $this->seeded_shuffle($key, $creation_int);
        }
        if ($fmt === null)
        {
            $fmt = $this->format;
        }
        $raw = $in;
        // Restore the encrypted data if encoded
        if ($fmt == Crypt::FORMAT_B64)
        {
            $raw = base64_decode($in);
        }
        else if ($fmt == Crypt::FORMAT_HEX)
        {
            $raw = pack('H*', $in);
        }
        // and do an integrity check on the size.
        if (strlen($raw) < $this->iv_num_bytes)
        {
            throw new \Exception('Crypt::decryptString() - ' .
                'data length ' . strlen($raw) . " is less than iv length {$this->iv_num_bytes}");
        }
        // Extract the initialisation vector and encrypted data
        $iv = substr($raw, 0, $this->iv_num_bytes);
        $raw = substr($raw, $this->iv_num_bytes);
        // Hash the key
        $keyhash = openssl_digest($key, $this->hash_algo, true);
        // and decrypt.
        $opts = OPENSSL_RAW_DATA;
        $res = openssl_decrypt($raw, $this->cipher_algo, $keyhash, $opts, $iv);
        if ($res === false)
        {
            throw new \Exception('Crypt::decryptString - decryption failed: ' . openssl_error_string());
        }
        return $res;
    }
    /**
     * Static convenience method for encrypting.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public static function Encrypt($in, $key = null, $fmt = null)
    {
        $c = new Crypt();
        return $c->encryptString($in, $key, $fmt);
    }
    /**
     * Static convenience method for encryptingU.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public static function EncryptU($in, $key = null, $fmt = null)
    {
        $c = new Crypt();
        return $c->encryptStringU($in, $key, $fmt);
    }
    /**
     * Static convenience method for encryptingUs.
     * @param  string $in  String to encrypt.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public static function EncryptUs($in_array, $key = null, $fmt = null)
    {
        $c = new Crypt();
        return array_map(array('Crypt', 'EncryptU'), $in_array);
    }
    /**
     * Static convenience method for encryptingM.
     * @param  string $in  String to encrypt.
     * @param  string $creation creation date of the $from user.
     * @param  string $key Encryption key.
     * @param  int $fmt Optional override for the output encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The encrypted string.
     */
    public static function EncryptM($in, $from, $to, $creation, $key = null, $fmt = null)
    {
        $c = new Crypt();
        return $c->encryptStringM($in, $from, $to, $creation, $key, $fmt);
    }
    /**
     * Static convenience method for decrypting.
     * @param  string $in  String to decrypt.
     * @param  string $key Decryption key.
     * @param  int $fmt Optional override for the input encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The decrypted string.
     */
    public static function Decrypt($in, $key = null, $fmt = null)
    {
        $c = new Crypt();
        return $c->decryptString($in, $key, $fmt);
    }
    /**
     * Static convenience method for decryptingM.
     * @param  string $in  String to decrypt.
     * @param  string $key Decryption key.
     * @param  string $creation creation date of the $from user.
     * @param  int $fmt Optional override for the input encoding. One of FORMAT_RAW, FORMAT_B64 or FORMAT_HEX.
     * @return string      The decrypted string.
     */
    public static function DecryptM($in, $from, $to, $creation, $key = null, $fmt = null)
    {
        $c = new Crypt();
        return $c->decryptStringM($in, $from, $to, $creation, $key, $fmt);
    }
    /**
     * Static convenience method for Semi-deterministic shuffling strings.
     * @param  string $stringline	string to shuffle.
     * @param  int $seed		random seed, just an integer.
     * @return string	 The shuffled string.
     */
    public static function seeded_shuffle($stringline, $seed){
    		$items = str_split($stringline);
	    	$items = array_values($items);
	    	mt_srand($seed);
	    	for ($i = count($items) - 1; $i > 0; $i--) {
	    		$j = mt_rand(0, $i);
	    		list($items[$i], $items[$j]) = array($items[$j], $items[$i]);
	    	}
	    	return implode("",$items);
    }
    /**
     * Static convenience method for reconstructing Semi-deterministic shuffled strings.
     * @param  string $stringline	string to shuffle.
     * @param  int $seed		random seed, just an integer.
     * @return string	 The shuffled string.
     */
    public static function seeded_unshuffle($stringline, $seed){
    		$items = str_split($stringline);
	    	$items = array_values($items);
	    	
	    	mt_srand($seed);
	    	$indices = [];
	    	for ($i = count($items) - 1; $i > 0; $i--) {
	    		$indices[$i] = mt_rand(0, $i);
	    	}
	    	
	    	foreach (array_reverse($indices, true) as $i => $j) {
	    		list($items[$i], $items[$j]) = [$items[$j], $items[$i]];
	    	}
	    	return implode("",$items);
    }
}