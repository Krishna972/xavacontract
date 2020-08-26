<?php
/**
 * Template Name: API Call
 */
?>
<?php
global $wpdb;
header('Content-Type: application/json');
$auth_hash = '3353038BF508E5608FF6C789639299D2B25F38438F6AD79CADE5';

$body = file_get_contents('php://input');
$jsondec =  json_decode($body, TRUE);

$reqhash = trim($jsondec['authkey']['key']);
if ($reqhash !== $auth_hash)
{
    $data = array(
        'status' => "403",
        'message' => "Error.!! You are not authorized Forbidden",
        "flag" => "0"
    );
    echo json_encode($data); //Send Error response.
    
}
else
{	
	for( $i = 0;  $i< count($jsondec['Item']); $i++){ 
		$page = get_page_by_title($jsondec['Item'][$i]['title'], null, 'design_collection'); // get page id.
		$findexistingquantity = get_post_meta($page->ID, 'quantity', true);
		$getstatusupdate = update_post_meta($post_id = $page->ID, $key = 'quantity', $value = $jsondec['Item'][$i]['quantity']); // update post meta

	}
    
    if ($getstatusupdate)
    {
        $data = array(
            'status' => "200",
            'message' => "Quantity Updated",
            "flag" => "1"
        ); // Send success response
        echo json_encode($data);
    }
    else
    {
		$data = array(
			'status' => "400",
			'message' => "Error",
			"flag" => "2"
		);
		echo json_encode($data); //Send Error response.
            
      
    }
}

?>