<?php
require 'vendor/autoload.php';
require_once("db.php");
use GuzzleHttp\Client;

$client = new Client();
$db = new DB();

$shop_domain = $_POST['X-Shopify-Shop-Domain'];
$sql = "DELETE FROM install WHERE SHOP_DOMAIN=$shop_domain";
$delete_data = $db->deleteTable($sql);
