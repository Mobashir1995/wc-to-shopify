<?php
if(session_id() == '' || !isset($_SESSION)) {
    session_start();
}
require_once( 'vendor/autoload.php' );
require_once( 'autoload.php' );
use GuzzleHttp\Client;
$client = new Client();

if(!empty($_GET['shop'])){

$provider = new Pizdata\OAuth2\Client\Provider\Shopify([
    'clientId'                => API_KEY,    // The client ID assigned to you by the Shopify
    'clientSecret'            => API_SECRET,   // The client password assigned to you by the Shopify
    'redirectUri'             => APP_URL.'auth.php', // The redirect URI assigned to you
    'shop'                    =>  $_GET['shop'], // The Shop name
]);
}
// If we don't have an authorization code then get one
if (!isset($_GET['code'])) {

    // Setting up scope
    $options = [
        'scope' => [
            'read_products', 'write_products',
            'read_script_tags', 'write_script_tags'
        ]
    ];
    // Fetch the authorization URL from the provider; this returns the
    // urlAuthorize option and generates and applies any necessary parameters
    // (e.g. state).
    $authorizationUrl = $provider->getAuthorizationUrl($options);

    // Get the state generated for you and store it to the session.
    $_SESSION['oauth2state'] = $provider->getState();

    // Redirect the user to the authorization URL.
    header('Location: ' . $authorizationUrl);
    exit;

// Check given state against previously stored one to mitigate CSRF attack
} elseif (empty($_GET['state']) || (isset($_SESSION['oauth2state']) && $_GET['state'] !== $_SESSION['oauth2state'])) {

    if (isset($_SESSION['oauth2state'])) {
        unset($_SESSION['oauth2state']);
    }
    
    exit('Invalid state');

} else {

    try {
        // Try to get an access token using the authorization code grant.
        $accessToken = $provider->getAccessToken('authorization_code', [
            'code' => $_GET['code']
        ]);

        $store = $provider->getResourceOwner($accessToken);

        // Access to Store base information
        // echo $store->getName()."<br>";
        // echo $store->getEmail()."<br>";
        // echo $store->getDomain()."<br>";
        // Use this to interact with an API on the users behalf
        $access_token_no = $accessToken->getToken();
        $shop_domain = $store->getDomain();
        $shop_id = $store->getId();

        $_SESSION['access_token'] = $access_token_no;
        $_SESSION['shop_id'] = $shop_id;
        $_SESSION['first_app_shop_name']=$shop_domain;


        $response = $client->request(
                        'POST', 
                        "https://{$shop_domain}/admin/webhooks.json",
                        [
                            'form_params' => [
                                'access_token'  =>  $access_token_no,
                                'webhook'  => array(
                                    "topic" => "app/uninstalled",
                                    "address" => APP_URL."uninstall.php/",
                                    "format" => 'json',
                                ),
                            ]
                        ]
                    );
$result = json_decode($response->getBody()->getContents(), true);

        $response = $client->request(
                        'POST', 
                        "https://{$shop_domain}/admin/recurring_application_charges.json",
                        [
                            'form_params' => [
                                'access_token'  =>  $access_token_no,
                                'Content-Type' => 'application/json',
                                'Accept' => 'application/json',
                                'charset' => 'utf-8',
                                'recurring_application_charge'  => array(
                                    "name" => "Super Duper Plan",
                                    "price" => 10.0,
                                    "return_url" => APP_URL."charge.php",
                                    "test" => true,
                                ),
                            ]
                        ]
                    );




 $response = $client->request(
                        'GET', 
                        "https://{$shop_domain}/admin/recurring_application_charges.json",
                        [
                            'query' => [
                                'access_token'  =>  $access_token_no,
                            ]
                        ]
                    );


         $result = json_decode($response->getBody()->getContents(), true);

         $confirmation_url = $result['recurring_application_charges'][0]['confirmation_url'];

        if( $confirmation_url ){
            header("Location: {$confirmation_url}");
            //echo $shop_domain.'/admin/apps/';
        }

        //$sql = mysqli_query("")

    } catch (\League\OAuth2\Client\Provider\Exception\IdentityProviderException $e) {
        // Failed to get the access token or user details.
        exit($e->getMessage());

    }
}



