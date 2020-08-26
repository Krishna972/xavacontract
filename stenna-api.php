<?php
/**
 * Template Name: API Call
 * @author Krishna Gupta
 * @package Stenna
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
	$status = 0 ;
	for( $i = 0;  $i< count($jsondec['Item']); $i++){
		$postTitle = $jsondec['Item'][$i]['title'];
		$product_id = $wpdb->get_var( $wpdb->prepare( "SELECT post_id FROM $wpdb->postmeta WHERE meta_key='_sku' AND meta_value='%s' LIMIT 1", $postTitle));
		// Check if product id exist inside inventory or not.
		if(!empty($product_id)|| $product_id != ""){
			wc_update_product_stock($product_id, $jsondec['Item'][$i]['quantity'], 'set');
			$status = 1;
		} else{
			// Insert title and quantity if not present in products.
			$post_id = wp_insert_post( array(
			    'post_title' => $jsondec['Item'][$i]['title'],
			    'post_content' => '',
			    'post_status' => 'publish',
			    'post_type' => "product",
			));

			$status = 1;
			if ($post_id) {
			   // set product is simple/variable/grouped and their variants.
				wp_set_object_terms( $post_id, 'simple', 'product_type' );
				update_post_meta( $post_id, '_visibility', 'visible' );
				update_post_meta( $post_id, '_stock_status', 'instock');
				update_post_meta( $post_id, 'total_sales', '0' );
				update_post_meta( $post_id, '_downloadable', 'no' );
				update_post_meta( $post_id, '_virtual', 'yes' );
				update_post_meta( $post_id, '_regular_price', '' );
				update_post_meta( $post_id, '_sale_price', '' );
				update_post_meta( $post_id, '_purchase_note', '' );
				update_post_meta( $post_id, '_featured', 'no' );
				update_post_meta( $post_id, '_weight', '11' );
				update_post_meta( $post_id, '_length', '11' );
				update_post_meta( $post_id, '_width', '11' );
				update_post_meta( $post_id, '_height', '11' );
				update_post_meta( $post_id, '_sku', $jsondec['Item'][$i]['title']);
				update_post_meta( $post_id, '_product_attributes', array() );
				update_post_meta( $post_id, '_sale_price_dates_from', '' );
				update_post_meta( $post_id, '_sale_price_dates_to', '' );
				update_post_meta( $post_id, '_price', '0' );
				update_post_meta( $post_id, '_sold_individually', '' );
				update_post_meta( $post_id, '_manage_stock', 'yes' );
				wc_update_product_stock($post_id, $jsondec['Item'][$i]['quantity'], 'set');
				update_post_meta( $post_id, '_backorders', 'no' );
			}
		}
		
		
	}
    
    if ($status == 1)
    {
        $data = array(
            'status' => "200",
            'message' => "Backend Succesfully updated",
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