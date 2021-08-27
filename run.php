<?php
require "func.php";

$key = "#####"; // Isi apikey kalian disini

$sms = new SMSActivate($key);

echo color('blue', "[+]")." =============================\n";
echo color('blue', "[+]")." SMS-Active.ru - By: GidhanB.A\n";
echo color('blue', "[+]")." =============================\n";
echo color('blue', "[+]")." 1. Cek Saldo\n";
echo color('blue', "[+]")." 2. Order Nomer Gojek\n";
echo color('blue', "[+]")." 3. Keluar\n";
echo color('blue', "[+]")." =============================\n";
echo color('blue', "[+]")." Silahkan pilih: ";
$tools = trim(fgets(STDIN));

echo "\n";
if ($tools == 1) {
    $saldo = $sms->getBalance();
    echo color('green', "[+]")." Sisa saldo: $saldo ₽\n";
} else if ($tools == 2) {
    echo color('blue', "[+]")." Order Nomer Gojek (Indosat Ooredoo)\n";
    Start:
    $getnum = $sms->getNumber("ni",6,0,"indosat");
    if (is_array($getnum)) {
        $id = $getnum["id"];
        $num = $getnum["number"];
        echo color('green', "[+]")." Nomer Hp: 0".substr($num,2)."\n";
        echo color('yellow', "[+]")." Cek Status => ";
        $headers = array();
        $headers[] = 'User-Agent: Mozilla/5.0 (Windows NT 10.0; Win64; x64; rv:91.0) Gecko/20100101 Firefox/91.0';
        $headers[] = 'Content-Type: application/x-www-form-urlencoded';
        $cek = curl('https://wirkel.site/gojek/', 'phone=0'.substr($num,2).'&submit=', $headers);
        if (strpos($cek[1], 'Nomor siap pakai')) {
            echo color('green', "Nomor siap pakai!\n");
            echo color('blue', "[+]")." Tekan enter untuk menerima otp..";
            $y = trim(fgets(STDIN));
            $status = $sms->setStatus($id,1);
            $pin = false;
            OTP:
            echo color('yellow', "[+]")." Menunggu OTP ";
            $aww = 0;
            do {
                $cek = curl('https://sms-activate.ru/stubs/handler_api.php', 'action=getCurrentActivationsDataTables&api_key='.$key.'&order=id&orderBy=asc&start=0&length=10', $headers);
                $code = json_decode($cek[1])->array[0]->code;
                $aww++;
                if ($aww == 10) {
                    echo ".";
                    $aww = 0;
                }
            } while (strlen($code) !== 4);
            if ($pin) $code = get_between(base64_decode(json_decode($cek[1])->array[0]->moreSms), 'OTP: ', ' gojek.com');
            echo color('green', " [$code]\n");
            if ($pin == false) {
                echo color('blue', "[+]")." Mau setpin? (y/n): ";
                $yn = trim(fgets(STDIN));
                if ($yn == "n") {
                    $status = $sms->setStatus($id,6);
                    echo color('green', "[+]")." Terima kasih!\n";
                } else {
                    $status = $sms->setStatus($id,3);
                    $pin = true;
                    goto OTP;
                }
            } else {
                $status = $sms->setStatus($id,6);
            }
        } else {
            echo color('red', "Nomor telah terdaftar!\n");
            $status = $sms->setStatus($id,8);
            goto Start;
        }
    } else {
        die($getnum);
    }
} else {
    die();
}

class SMSActivate {
    private $url = 'https://sms-activate.ru/stubs/handler_api.php';
    private $apiKey;

    public function __construct($apiKey) {
        $this->apiKey = $apiKey;
    }

    public function getBalance() {
        return $this->request(array('api_key' => $this->apiKey, 'action' => __FUNCTION__), 'GET');
    }
    
    public function getNumber($service, $country = null, $forward = 0, $operator = null, $ref = null){
        $requestParam = array('api_key' => $this->apiKey,'action' => __FUNCTION__,'service' => $service,'forward'=>$forward);
        if($country){
            $requestParam['country']=$country;
        }
        if($operator &&($country==0 || $country == 1 || $country == 2)){
            $requestParam['service'] = $operator;
        }
        if($ref){
            $requestParam['ref'] = $ref;
        }
        return $this->request($requestParam, 'POST',null,1);
    }

    public function setStatus($id, $status, $forward = 0){
        $requestParam = array('api_key' => $this->apiKey,'action' => __FUNCTION__,'id' => $id,'status' => $status);

        if($forward){
            $requestParam['forward'] = $forward;
        }

        return $this->request($requestParam,'POST',null,3);
    }

    private function request($data, $method, $parseAsJSON = null, $getNumber = null) {
        $method = strtoupper($method);

        if (!in_array($method, array('GET', 'POST')))
            throw new InvalidArgumentException('Method can only be GET or POST');

        $serializedData = http_build_query($data);

        if ($method === 'GET') {
            $result = file_get_contents("$this->url?$serializedData");
        } else {
            $options = array(
                'http' => array(
                    'header' => "Content-type: application/x-www-form-urlencoded\r\n",
                    'method' => 'POST',
                    'content' => $serializedData
                )
            );

            $context = stream_context_create($options);
            $result = file_get_contents($this->url, false, $context);
        }

        if ($parseAsJSON)
            return json_decode($result,true);

        $parsedResponse = explode(':', $result);

        if ($getNumber == 1) {
            $returnNumber = array('id' => $parsedResponse[1], 'number' => $parsedResponse[2]);
            return $returnNumber;
        }
        if ($getNumber == 2) {
            $returnStatus = array('status' => $parsedResponse[0], 'code' => $parsedResponse[1]);
            return $returnStatus;
        }
        if ($getNumber == 3) {
            $returnStatus = array('status' => $parsedResponse[0]);
            return $returnStatus;
        }

        return $parsedResponse[1];
    }

}
