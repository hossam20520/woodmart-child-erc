<?php
/**
 * Enqueue script and styles for child theme
 */



if (!session_id()) {
    session_start();
}





add_filter( 'woocommerce_thankyou_order_received_text', 'misha_thank_you_title', 20, 2 );

function misha_thank_you_title( $thank_you_title, $order ){
$items = $order->get_items();
$idCourse = array();
foreach ( $items as $item ) {
        $pid    = $item['product_id'];
        // $patt   = $pid->get_attribute( 'pa_myattrname' );
        // echo  $pid;
        $attributes = get_post_meta($pid);
        array_push($idCourse ,$attributes['Course'][0]);
        
}

global $wpdb;

$course_id_num = $idCourse[0];
$tablename = $wpdb->prefix.'erc_courses';
$result = $wpdb->get_results("SELECT * FROM $tablename where id ='$course_id_num' " );
// print_r($result[0]->id_course );
$plat_course_id = get_course_moodle_id($result[0]->id_course);
$endDate = get_end_time($result[0]->days);
$userId = get_user_by_email_moodle($order->get_billing_email());


enrolle_uer($userId ,$plat_course_id , $endDate , 0 );


	return 'Oh ' . $order->get_billing_first_name() . ', thank you so much for your order!';

}


 

// Display admin product custom setting field(s)
add_action('woocommerce_product_options_general_product_data', 'woocommerce_product_custom_fields');
function woocommerce_product_custom_fields() {

    global $product_object;

    echo '<div class=" product_custom_field ">';

    // Custom Product Text Field
$valll = 0;
    if(empty(get_post_meta( get_the_ID(), 'Course', true ))){
        $valll = 0;  
    }else{
        $valll  = get_post_meta( get_the_ID(), 'Course', true );
    }
    woocommerce_wp_text_input( array( 
        'id'          => 'Course',
        'label'       => __('Course ID:', 'woocommerce'),
        'value'       =>  $valll ,
        'placeholder' => '',
        'desc_tip'    => 'true' // <== Not needed as you don't use a description
    ) );

    echo '</div>';
}

// Save admin product custom setting field(s) values
add_action('woocommerce_admin_process_product_object', 'woocommerce_product_custom_fields_save');
function woocommerce_product_custom_fields_save( $product ) {
    if ( isset($_POST['Course']) ) {
        $product->update_meta_data( 'Course', sanitize_text_field( $_POST['Course'] ) );
    }
}




function woodmart_child_enqueue_styles() {
    wp_enqueue_style( 'child-style', get_stylesheet_directory_uri() . '/style.css', array( 'woodmart-style' ), woodmart_get_theme_info( 'Version' ) );
}
add_action( 'wp_enqueue_scripts', 'woodmart_child_enqueue_styles', 10010 );





// add_action( 'woocommerce_payment_complete', 'so_payment_complete' );
// function so_payment_complete( $order_id ){
//     $order = wc_get_order( $order_id );
//     $user = $order->get_user();
//     if( $user ){
       


//     }
// }


add_action( 'woocommerce_checkout_process', 'custom_checkout_field_validation' );

function custom_checkout_field_validation() {
    global $wpdb;
    if ( isset( $_POST['billing_email'] )  ){
       $code =  get_user_by_email_moodle($_POST['billing_email']);
       if($code == 0){
          wc_add_notice( __( 'please create account first <a href="https://volunteer.ercelearning.com/volunteer/no/register?checkout=true"> create account</a>', 'woocommerce' ), 'error' );
       }


    }
       
}


add_filter( 'woocommerce_checkout_fields' , 'quadlayers_remove_checkout_fields' ); 

function quadlayers_remove_checkout_fields( $fields ) { 

// unset($fields['billing']['billing_first_name']);
// unset($fields['billing']['billing_last_name']);
// unset($fields['billing']['billing_company']);
// unset($fields['billing']['billing_address_1']);
// unset($fields['billing']['billing_address_2']);
// unset($fields['billing']['billing_city']);
// unset($fields['billing']['billing_postcode']);
// unset($fields['billing']['billing_country']);
// unset($fields['billing']['billing_state']);
// unset($fields['billing']['billing_phone']);
// unset($fields['order']['order_comments']);
unset($fields['account']['account_password']);
unset($fields['account']['account_password-2']);
return $fields; 
}


// echo $_SESSION['have_account'];

function Register_user_moodle($id){



    $url = "https://ercelearning.com/webservice/rest/server.php?moodlewsrestformat=json";
    $response = wp_remote_post( $url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array(
        'wstoken' => '53497a42c19c91fd64d3b65be615a5ca' , 
        'moodlewsrestformat' => 'json'  , 
        'wsfunction' => 'auth_email_signup_user' , 
        'username' => 'idnumber',
        'password' => $id,
        'password' => $id,
        'password' => $id,
        'password' => $id,

    ),
        'cookies' => array()
        )
    );
    
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    //    echo "Something went wrong: $error_message";
    return 0;
    } else {
    //    echo 'Response:<pre>';
    
    $body = wp_remote_retrieve_body( $response  );
    $data = json_decode( $body );
      return $data->courses[0]->id;
     
    }


}


function get_course_moodle_id($id){

    $url = "https://ercelearning.com/webservice/rest/server.php?moodlewsrestformat=json";
    $response = wp_remote_post( $url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array(
         'wstoken' => 'e79186b3bb28e8e3a82788817ec8a1d3' , 
        'moodlewsrestformat' => 'json'  , 
        'wsfunction' => 'core_course_get_courses_by_field' , 
        'field' => 'idnumber',
        'value' => $id
    
    ),
        'cookies' => array()
        )
    );
    
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    //    echo "Something went wrong: $error_message";
    return 0;
    } else {
    //    echo 'Response:<pre>';
    
    $body = wp_remote_retrieve_body( $response  );
    $data = json_decode( $body );
      return $data->courses[0]->id;
     
    }


}













function enrolle_uer($userId ,$course_id , $endTime , $isSus ){

    $url = "https://ercelearning.com/webservice/rest/server.php?moodlewsrestformat=json";
    $response = wp_remote_post( $url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array(
         'wstoken' => 'e79186b3bb28e8e3a82788817ec8a1d3' , 
        'moodlewsrestformat' => 'json'  , 
        'wsfunction' => 'enrol_manual_enrol_users' , 
        'enrolments[0][roleid]' => 5,
        'enrolments[0][userid]' => $userId,
        'enrolments[0][courseid]' =>$course_id,
        'enrolments[0][timeend]' => $endTime
    
    
    ),
        'cookies' => array()
        )
    );
    
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    //    echo "Something went wrong: $error_message";
    return 0;
    } else {
    //    echo 'Response:<pre>';
    
    $body = wp_remote_retrieve_body( $response  );
    $data = json_decode( $body );
    if($data == null){
      return 1;
    }else{
       return 0;
    }
      
     
    }


}










function get_user_by_email_moodle($email){



    $url = "https://ercelearning.com/webservice/rest/server.php?moodlewsrestformat=json";
    $response = wp_remote_post( $url, array(
        'method' => 'POST',
        'timeout' => 45,
        'redirection' => 5,
        'httpversion' => '1.0',
        'blocking' => true,
        'headers' => array(),
        'body' => array(
        'wstoken' => 'e79186b3bb28e8e3a82788817ec8a1d3' , 
        'moodlewsrestformat' => 'json'  , 
        'wsfunction' => 'core_user_get_users_by_field' , 
        'field' => 'email',
        'values[5]' => $email
    
    ),
        'cookies' => array()
        )
    );
    
    if ( is_wp_error( $response ) ) {
       $error_message = $response->get_error_message();
    //    echo "Something went wrong: $error_message";
    return 0;
    } else {

    //    echo 'Response:<pre>';
    
    $body = wp_remote_retrieve_body( $response  );
    $data = json_decode( $body );
    if(empty($data[0])){
      return 0;
    }else{
        return $data[0]->id;
    }
      
     
    }


}




function get_end_time($end_date){
$Today=date('y:m:d');
$NewDate=Date('Y-m-d', strtotime('+'.$end_date.' days'));
$time = current_time('h:i:s', false);
$long = strtotime($NewDate.' '.$time); //--> which results to 1332866820
return $long;
}


