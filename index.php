<?php
ini_set('display_errors', 1);
session_start();

require_once( 'wc-rest-api/lib/woocommerce-api.php' );
require_once( 'autoload.php' );
use GuzzleHttp\Client;
$client = new Client();

$db = new DB();

$query = array("
			ID   			INT(6)          UNSIGNED    AUTO_INCREMENT PRIMARY KEY,
	        ACCESS_TOKEN    VARCHAR(250)                NOT NULL,
	        SHOP_ID         INT(250)        UNSIGNED    NOT NULL,
	        SHOP_DOMAIN     VARCHAR(250)                NOT NULL,
	        INSTALLED_ON	DATETIME		 	 		NOT NULL,
	        SHOP_INFO		TEXT 						NOT NULL
        ");
$create_table = $db->createTable('install', $query);

if(ERROR_REPORT){
	echo $create_table;
}
$store = isset($_GET['shop']) ? $_GET['shop'] : $_SESSION['shop_name'];

$_SESSION['shop_name']=$store;
$access_token = $db->selectRow("SELECT ACCESS_TOKEN FROM install WHERE SHOP_DOMAIN='$store' ");

if($access_token){
	//for($i = 0; $i < count($access_token); $i++){
		foreach( $access_token as $access_token_val ){
			$access_token_code= $access_token_val['ACCESS_TOKEN'];
			//$access_token_code = $access_token_val['ACCESS_TOKEN'];
		}
	//}
}else{
  require_once( 'auth.php' );
}


$get_script = $client->request(
                'GET', 
                "https://{$store}/admin/script_tags.json",
                [
                    'query' => [
                        'access_token'  =>  $access_token_code,
                    ]
                ]
            );
$get_script_result = json_decode($get_script->getBody()->getContents(), true);
if(empty($get_script_result['script_tags'])){
    $create_script = $client->request(
                'POST', 
                "https://{$store}/admin/script_tags.json",
                [
                    'form_params' => [
                        'access_token'  =>  $access_token_code,
                        'script_tag'    =>  array(
                            "event" => "onload",
                            "src"   => APP_URL."assets/js/custom.js"
                        ),
                    ]
                ]
            );
}


$wc_api_options = array(
  'debug'           => true,
  'return_as_array' => false,
  'validate_url'    => false,
  'timeout'         => 30,
  'ssl_verify'      => false,
);

if (isset($_POST['submit'])) {
  $wc_shop = $_POST['wc_shop'];
  $wc_ck = $_POST['wc_ck'];
  $wc_cs = $_POST['wc_cs'];
  $wc_client = new WC_API_Client( $wc_shop, $wc_ck, $wc_cs, $wc_api_options );
  print_r( $wc_client->products->get() );
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <script src="https://cdn.shopify.com/s/assets/external/app.js"></script>
  <script type="text/javascript">
    ShopifyApp.init({
      apiKey: '<?php echo API_KEY; ?>',
      shopOrigin: 'https://<?php echo $store; ?>'
    });
  </script>

  <link rel='stylesheet' href='<?php echo APP_URL; ?>assets/css/app.css'>
</head>
<body>
  <form action="" method="POST">
    <div>
      <label for="">
        Store: <input type="text" name="wc_shop" value="http://localhost/test-wp/">
      </label>
    </div>
    <div>
      <label for="">
        Consumer Key: <input type="text" name="wc_ck" value="ck_002161cb45a2a2d26371610f82c0c8bdfc9b7a0d">
      </label>
    </div>
    <div>
      <label for="">
        Consumer Secret: <input type="text" name="wc_cs" value="cs_5345659593ac74eb08cd60beb2c7302339600373">
      </label>
    </div>
    <br>
    <input type="submit" name="submit" value="submit">
  </form>
<script src="assets/js/jquery.min.js"></script>

</body>
</html>