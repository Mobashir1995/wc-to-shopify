<?php
ini_set('display_errors', 1);
session_start();

require_once( 'autoload.php' );
use GuzzleHttp\Client;

$client = new Client();
$db = new DB();

$charge_id = $_GET['charge_id'];
$shop_domain = $_SESSION['first_app_shop_name'];
$access_token_no = $_SESSION['access_token'];
$shop_id = $_SESSION['shop_id'];

 $response = $client->request(
                        'GET', 
                        "https://{$shop_domain}/admin/recurring_application_charges/{$charge_id}.json",
                        [
                            'query' => [
                                'access_token'  =>  $access_token_no,
                            ]
                        ]
                    );
$result = json_decode($response->getBody()->getContents(), true);

if( $result['recurring_application_charge']['status'] == 'accepted' ){
	$sql = "INSERT INTO install(ACCESS_TOKEN, SHOP_ID, SHOP_DOMAIN) VALUES( '$access_token_no', $shop_id, '$shop_domain'  )";
    $insert_data = $db->insertRow($sql);
    $response = $client->request(
                'POST', 
                "https://{$shop_domain}/admin/recurring_application_charges/{$charge_id}/activate.json",
                [
                    'form_params' => [
                        'access_token'  =>  $access_token_no,
                    ]
                ]
            );
	header("Location: index.php");
}else{
	echo "You have need to accept the charge";
}