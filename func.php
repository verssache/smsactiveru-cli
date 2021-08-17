<?php

function guid() 
    {
        $randomString = openssl_random_pseudo_bytes(16);
        $time_low = bin2hex(substr($randomString, 0, 4));
        $time_mid = bin2hex(substr($randomString, 4, 2));
        $time_hi_and_version = bin2hex(substr($randomString, 6, 2));
        $clock_seq_hi_and_reserved = bin2hex(substr($randomString, 8, 2));
        $node = bin2hex(substr($randomString, 10, 6));
        $time_hi_and_version = hexdec($time_hi_and_version);
        $time_hi_and_version = $time_hi_and_version >> 4;
        $time_hi_and_version = $time_hi_and_version | 0x4000;
        $clock_seq_hi_and_reserved = hexdec($clock_seq_hi_and_reserved);
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved >> 2;
        $clock_seq_hi_and_reserved = $clock_seq_hi_and_reserved | 0x8000;
        return sprintf('%08s-%04s-%04x-%04x-%012s', $time_low, $time_mid, $time_hi_and_version, $clock_seq_hi_and_reserved, $node);
    }

function gendata($domain = "sitik.site")
    {
        $data = json_decode(file_get_contents("https://wirkel.site/data.php?qty=1&domain=".$domain))->result[0];
        return $data;
    }

function curl($url, $post, $headers, $follow = false, $method = null)
	{
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, $url);
		curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
		curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
		if ($follow == true) curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
		curl_setopt($ch, CURLOPT_HEADER, 1);
		if ($method !== null) curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $method);
		if ($headers !== null) curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
		if ($post !== null) curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		$result = curl_exec($ch);
		$header = substr($result, 0, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		$body = substr($result, curl_getinfo($ch, CURLINFO_HEADER_SIZE));
		preg_match_all('/^Set-Cookie:\s*([^;]*)/mi', $result, $matches);
		$cookies = array();
		foreach ($matches[1] as $item) {
			parse_str($item, $cookie);
			$cookies = array_merge($cookies, $cookie);
		}
		return array(
			$header,
			$body,
			$cookies
		);
	}

function random($length, $a)
	{
		$str = "";
		if ($a == 0) {
			$characters = array_merge(range('0', '9'));
		} elseif ($a == 1) {
			$characters = array_merge(range('a', 'z'));
		} elseif ($a == 2) {
			$characters = array_merge(range('A', 'Z'));
		} elseif ($a == 3) {
			$characters = array_merge(range('0', '9'), range('a', 'z'));
		} elseif ($a == 4) {
			$characters = array_merge(range('0', '9'), range('A', 'Z'));
		} elseif ($a == 5) {
			$characters = array_merge(range('a', 'z'), range('A', 'Z'));
		} elseif ($a == 6) {
			$characters = array_merge(range('0', '9'), range('a', 'z'), range('A', 'Z'));
		}
		$max = count($characters) - 1;
		for ($i = 0; $i < $length; $i++) {
			$rand = mt_rand(0, $max);
			$str .= $characters[$rand];
		}
		return $str;
	}

function get_between($string, $start, $end) 
    {
        $string = " ".$string;
        $ini = strpos($string,$start);
        if ($ini == 0) return "";
        $ini += strlen($start);
        $len = strpos($string,$end,$ini) - $ini;
        return substr($string,$ini,$len);
    }

function get_between_array($string, $start, $end) 
    {
        $aa = explode($start, $string);
        for ($i=0; $i < count($aa) ; $i++) { 
            $su = explode($end, $aa[$i]);
            $uu[] = $su[0];
        }
        unset($uu[0]);
        $uu = array_values($uu);
        return $uu;
    }

function color($color, $text)
    {
        $arrayColor = array(
            'grey'      => '1;30',
            'red'       => '1;31',
            'green'     => '1;32',
            'yellow'    => '1;33',
            'blue'      => '1;34',
            'purple'    => '1;35',
            'nevy'      => '1;36',
            'white'     => '1;0',
        );  
        return "\033[".$arrayColor[$color]."m".$text."\033[0m";
    }

function remove_space($var) {
        $new = str_replace("\n", "", $var);
        $new = str_replace("\t", "", $new);
        $new = str_replace(" ", "", $new);
        return $new;
    }

function getHeaders($result)
    {
        if (!preg_match_all('/([A-Za-z\-]{1,})\:(.*)\\r/', $result, $matches) 
                || !isset($matches[1], $matches[2])){
            return false;
        }

        $headers = [];

        foreach ($matches[1] as $index => $key){
            $headers[$key] = substr($matches[2][$index], 1);
        }

        return $headers;
    }