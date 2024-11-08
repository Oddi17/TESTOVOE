<?php
require_once __DIR__ . '/lib/DataBase.php';
require_once __DIR__ . '/lib/MockApi.php';

function genBarcode() {
    return random_int(10000000,99999999);
}

function BookAndSave($event_id=0,$event_date=0,$ticket_adult_price=0,$ticket_adult_quantity=0,$ticket_kid_price=0,$ticket_kid_quantity=0){
    $data_book = [
        'event_id' => (string)$event_id,
        'event_date' => (string)$event_date,
        'ticket_adult_price' => (string)$ticket_adult_price,
        'ticket_adult_quantity' => (string)$ticket_adult_quantity,
        'ticket_kid_price' => (string)$ticket_kid_price,
        'ticket_kid_quantity' => (string)$ticket_kid_quantity,
        'barcode' => ''
    ];
    $response = [];
    while (!isset($response['message']) or $response['message'] != 'order successfully booked'){
        $barcode = genBarcode();
        $data_book['barcode'] = (string)$barcode;
        $response = postSite($data_book,'https://api.site.com/book');
        print_r($response) ."\n";
    }
    $data_approve = ['barcode'=>(string)$data_book['barcode']];
    $response = postSite($data_approve,'https://api.site.com/approve');
    if (isset($response['error'])){
        return $response['error'];
    }elseif (isset($response['message']) && $response['message'] == 'order successfully aproved'){
        echo $response['message']."\n";
        $equal_price = $data_book['ticket_adult_price']*$data_book['ticket_adult_quantity'] + $data_book['ticket_kid_price']*$data_book['ticket_kid_quantity'];
        $created = new DateTime('now', new DateTimeZone('Europe/Moscow'));
        $created = (string)$created->format('Y-m-d H:i:s');
        $data_book['equal_price'] = (string)$equal_price;
        $data_book['created'] = $created;
        $response = sendDataBase($data_book);
        //return $res;
        return $response;
    }else{
        return "error of request";
    }
}

function postSite($data,$url){
    $headers = stream_context_create(
        array(
            'http' => [
                'method' => 'POST',
                'header' => 'Content-Type: application/json',
                'content' => json_encode($data)
                ]
            )
        );
    $result = file_get_contents($url, false, $headers);
    $result = json_decode($result,true);
    return $result;
}

function sendDataBase($data){
    while(true){
        try{
            $SQL_INSERT_ORDER = "insert into `order` (event_id,event_date,ticket_adult_price,".
                                "ticket_adult_quantity,ticket_kid_price,ticket_kid_quantity,".
                                "barcode,equal_price,created) values (:event_id,:event_date,".
                                ":ticket_adult_price,:ticket_adult_quantity,:ticket_kid_price,".
                                ":ticket_kid_quantity,:barcode,:equal_price,:created)";
            $db = new DataBase();
            $db->setBasePrepare($SQL_INSERT_ORDER,$data);
            $mes = "order successfully added to database";
            break;
        }catch (Exception $e){
            echo 'Error order:'. $e->getMessage(). "\n";
            if ($e->getCode() === '23000') {
                $barcode = genBarcode();
                $data['barcode'] = (string)$barcode;
                continue;
            } else {
                $mes = $e->getMessage();
                return $mes;
                //throw $e;
            }
        }
        }
        return $mes;
    }
    

if (in_array("https", stream_get_wrappers())) {
        stream_wrapper_unregister("https");
}
stream_wrapper_register("https", "MockStreamWrapper");

$res = BookAndSave("003","2021-08-21 13:00:00","700", "1","450","0");
echo "Итоговый результат: ".$res;

stream_wrapper_restore("https");