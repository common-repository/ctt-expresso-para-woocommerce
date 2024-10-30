<?php 
/**
* Plugin Name: CTT Expresso para WooCommerce
* Plugin URI:  https://thisfunctional.pt/Plugins/ctt-expresso-para-woocommerce
* Description: Integração WooCommerce para CTT Expresso
* Tags: ctt, cttexpresso, woocommerce
* Version:     3.2.13
* Author:      this.functional
* Author URI:  https://thisfunctional.pt/
* License:     GPL2
* License URI: https://www.gnu.org/licenses/gpl-2.0.html
* Text Domain: ctt-expresso-para-woocommerce
* License:     GPL2
* Requires at least: 4.6.1
* WC requires at least: 3.0
* WC tested up to: 9.1.4
*/

if( ! defined( 'ABSPATH' ) ){
    exit;
}

register_activation_hook( __FILE__, 'cepw_register_activation_hook' );
register_deactivation_hook( __FILE__, 'cepw_register_deactivation_hook' );

function cepw_register_activation_hook() {
  $cttExpressoURL = 'https://www.ctt.pt/feapl_2/app/open/objectSearch/objectSearch.jspx?lang=def&objects=';
  add_option( '_CTTExpresso_URL', $cttExpressoURL);
  $upload = wp_upload_dir();
  $upload_dir = $upload['basedir'];
  $upload_dir = $upload_dir . '/cepw';
  
  
  if (!is_dir($upload_dir)) {
    mkdir($upload_dir, 0777, true);
  }

  
  $index_file = $upload_dir . '/index.php';
  if (!file_exists($index_file)) {
    file_put_contents($index_file, "<?php\n// Silence is gold\n");
  }

  
  $htaccess_file = $upload_dir . '/.htaccess';
  if (!file_exists($htaccess_file)) {
    $htaccess_content = "Options -Indexes\n";
    file_put_contents($htaccess_file, $htaccess_content);
  }
}





function cepw_register_deactivation_hook() {
    delete_option('_CTTExpresso_URL');
    delete_option('_CTTExpresso_ClientID');
    delete_option('_CTTExpresso_ContractId');
    delete_option('_CTTExpresso_AuthenticationId');
    delete_option('_CTTExpresso_SenderPhone');
    delete_option('_CTTExpresso_SenderEmail' );
    delete_option('_CTTExpresso_SenderMobilePhone');
    delete_option('_CTTExpresso_UserId');
    delete_option('_CTTExpresso_SubProductId');
    delete_option('_CTTExpresso_EmailText');
    delete_option('_ShippingOptionsTwoDays');
    delete_option('_ShippingOptionsTomorrow');
    delete_option('_ShippingOptionsThirteenHours');
    delete_option('_ShippingOptionsFortyEightHours');
    delete_option('_ShippingOptionsTen');
    delete_option('_ShippingOptionsThirteen_Multi');
    delete_option('_ShippingOptionsNineteen');
    delete_option('_ShippingOptionsNineteen_Multi');
    delete_option('_ShippingOptionsCargo');
    delete_option('_ShippingOptionsEasyReturn24');
    delete_option('_ShippingOptionsEasyReturn48');
    delete_option('_ShippingOptionsEMS_Economy');
    delete_option('_ShippingOptionsEMS_International');
    delete_option('_ShippingOptionsRedeShopping');
    delete_option('_CTTExpresso_Debug');
    delete_option('_CTTExpresso_SendTracking');
}

function cepw_enqueue_scripts(){
    if ( ( function_exists( 'is_checkout' ) && is_checkout() ) || ( function_exists( 'is_cart' ) && is_cart() ) ) :
        if ( !function_exists( 'get_plugin_data' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
        $plugin_data = get_plugin_data( __FILE__ );
        wp_enqueue_style( 'cepw-css', plugins_url( '/assets/css/cepw_style.css', __FILE__ ), array(), $plugin_data['Version'] );
        if ( is_checkout() ) {
            wp_enqueue_script( 'cepw-js', plugins_url( '/assets/js/cepw_script.js', __FILE__ ), array( 'jquery' ), $plugin_data['Version'], true);
            wp_localize_script( 'cepw-js', 'cepw', array( 
                'shipping_methods' => cepw_get_shipping_methods(),
                'cepwajaxurl' => admin_url( 'admin-ajax.php' ),
                'cepwajaxnonce' => wp_create_nonce( 'cepw_ajax_nonce' )
            ));
        }
    endif;
}
add_action( 'wp_enqueue_scripts', 'cepw_enqueue_scripts' );



if (!defined('CTTExpresso_ASSETS')) {
    define('CTTExpresso_ASSETS', plugin_dir_url(__FILE__).'/assets/' );
}

function cepw_enqueue_scripts_admin( ) {
    if ( !function_exists( 'get_plugin_data' ) ) require_once( ABSPATH . 'wp-admin/includes/plugin.php' );
    $plugin_data = get_plugin_data( __FILE__ );
    /*** FontAwesome **/
    wp_enqueue_style('fontawesome', 'https://use.fontawesome.com/releases/v5.8.1/css/all.css', '', '5.8.1', 'all');
    /**** Styles ***/
    wp_enqueue_style( 'cepw-css-admin', plugins_url( '/assets/admin/cepw_style.css', __FILE__ ), array(), $plugin_data['Version']);
    wp_enqueue_script( 'cepw-js-admin', plugins_url( '/assets/admin/cepw_script.js', __FILE__ ), array( 'jquery' ), $plugin_data['Version'], true);
    /*** Jquery DateTimePicker ***/
    wp_enqueue_style('cepw-datetimepicker-css', plugins_url( '/assets/admin/datetimepicker/jquery.datetimepicker.min.css', __FILE__ ) );
    wp_enqueue_script('cepw-datetimepicker-js', plugins_url( '/assets/admin/datetimepicker/jquery.datetimepicker.full.min.js', __FILE__ ) );
    wp_enqueue_style('cepw-font-awesome',  'https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css');

}
add_action( 'admin_enqueue_scripts', 'cepw_enqueue_scripts_admin' );


function cepw_log_path(){
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/cepw';
    $log_path = $upload_dir.'/'.'cepw_log.txt';

    return $log_path;
}


function cepw_create_SOAP($wsdl){
    ini_set('default_socket_timeout', 5000);
    $client = new SoapClient(
        $wsdl, 
        array(
            'trace' => true, 
            'keep_alive' => false,
            'connection_timeout' => 5000,
        )
    );

    return $client;
}


//test
function cepw_get_post_files($post_id,$text = ''){
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/cepw/'.$post_id;
    if (is_dir($upload_dir)) {
      $upload_url = $upload['baseurl'] . '/cepw/'.$post_id . '/';
      if ($handle = opendir($upload_dir)) { 
        if($text != ''): ?>
            <br><br>
            <strong><?php echo esc_html__($text, 'ctt-expresso-para-woocommerce'); ?>:</strong>
            <br>
        <?php endif;
        while (false !== ($file = readdir($handle))){
            if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'pdf'){
                $title = cepw_get_title_filename($file);
                echo '<a href="'.$upload_url.$file.'" target="_blank" class="cepw_document" title="'.$title.'"><img src="'.esc_url( plugin_dir_url( __FILE__ ) . 'assets/img/icon.svg' ).'" style="height:30px;"></a>';
            }
        }
      closedir($handle);
      }
    }
}






function cepw_log($log_msg, $order_id ){
    $log = "\n[" .date('d-M-Y H:i')."] Order Id: #".$order_id.": \n";
    error_log($log, 3, cepw_log_path() );
    error_log(print_r($log_msg, TRUE), 3, cepw_log_path() );
} 

function cepw_create_files($file, $data, $order_id){
    if(!strpos($file->FileName, '.pdf')){
      $file = $file->FileName.'.pdf';
    }else{
      $file = $file->FileName;
    }
    $upload = wp_upload_dir();
    $upload_dir = $upload['basedir'];
    $upload_dir = $upload_dir . '/cepw/'.$order_id;
    if (! is_dir($upload_dir)) {
      mkdir( $upload_dir, 0777 );
    }
    file_put_contents($upload_dir.'/'.$file,$data);
}



function cepw_custom_action_order_status_email_notification($order_id){
    $order = new WC_Order( $order_id );
    $orderStatus = 'wc-'.$order->status;
    //Default trigger "wc-completed"
    $status_trigger = apply_filters( 'cepw_order_status_trigger', 'wc-completed' );
    
    if($orderStatus == $status_trigger){
        $order_meta = apply_filters( 'cepw_order_meta', $order );
        $cepw_option = $order_meta->cepw_option;
        $SubProductId = cepw_get_subproduct_id($cepw_option);

        $run = apply_filters( 'cepw_run_call', $order )->result;

        //check if order use ctt express shipping options
        if(!empty($SubProductId) && $run ){
            cepw_call_to_WS($order, $SubProductId);
        }
    }

}
add_action( 'woocommerce_order_status_changed', 'cepw_custom_action_order_status_email_notification', 10, 1);


function cepw_default_run_call($order){
    return (object) array('result' => true, 'order' => $order);
}
add_filter('cepw_run_call','cepw_default_run_call');



function CreateShipmentWithoutPrint($client,$Input){
    $result = array();
    $response = $client->CreateShipmentWithoutPrint($Input);
    $ErrorsList = $response->CreateShipmentWithoutPrintResult->ErrorsList;
    $trackingNumber = '';
    if(!empty( $response->CreateShipmentWithoutPrintResult->ShipmentData->ShipmentDataOutput->LastObject)){
        $trackingNumber = $response->CreateShipmentWithoutPrintResult->ShipmentData->ShipmentDataOutput->LastObject;
    }
    $result = array(
        'response' => $response,
        'trackingNumber' => $trackingNumber,
        'ErrorsList' => $ErrorsList
    );
    return $result;
}


function CreateShipment($client,$Input,$order_id){
    $result = array();
    $response = $client->CreateShipment($Input);
    $ErrorsList = $response->CreateShipmentResult->ErrorsList;
    $trackingNumber = '';
    if(!empty($response->CreateShipmentResult->ShipmentData->ShipmentDataOutput->DocumentsList->DocumentData)){
        $filesList = $response->CreateShipmentResult->ShipmentData->ShipmentDataOutput->DocumentsList->DocumentData;
        if(count($filesList) == 1){
            $data = $filesList->File;
            cepw_create_files($filesList,$data, $order_id);
        }else{
            foreach ($filesList as $file) {
                $data = $file->File;
                cepw_create_files($file,$data, $order_id);
            }
        }
    }
    if(!empty($response->CreateShipmentResult->ShipmentData->ShipmentDataOutput->LastObject)){
        $trackingNumber = $response->CreateShipmentResult->ShipmentData->ShipmentDataOutput->LastObject;
    }
    $result = array(
        'response' => $response,
        'trackingNumber' => $trackingNumber,
        'ErrorsList' => $ErrorsList
    );
    return $result;
}

function CompleteShipment($client,$Input,$order_id){
    $result = array();
    $response = $client->CompleteShipment($Input);
    $trackingNumber = '';
    $ErrorsList = $response->CompleteShipmentResult->ErrorsList;
    if(!empty($response->CompleteShipmentResult->ShipmentData->ShipmentDataOutput->DocumentsList->DocumentData)){
        $filesList = $response->CompleteShipmentResult->ShipmentData->ShipmentDataOutput->DocumentsList->DocumentData;
        if(count($filesList) == 1){
            $data = $filesList->File;
            cepw_create_files($filesList,$data, $order_id);
        }else{
            foreach ($filesList as $file) {
                $data = $file->File;
                cepw_create_files($file,$data, $order_id);
            }
        }
    }
    if(!empty($response->CompleteShipmentResult->ShipmentData->ShipmentDataOutput->LastObject)){
        $trackingNumber = $response->CompleteShipmentResult->ShipmentData->ShipmentDataOutput->LastObject;
    }
    $result = array(
        'response' => $response,
        'trackingNumber' => $trackingNumber,
        'ErrorsList' => $ErrorsList
    );
    return $result;
}



//Call to WS Cttexpresso
function cepw_call_to_WS($order, $SubProductId){

    //order_id
    $order_id = $order->get_id();
    //Option
    $ShippingOptions_Print = get_option('_ShippingOptions_Print');
    $debug = get_option('_CTTExpresso_Debug');
    $senderPhone = get_option('_CTTExpresso_SenderPhone');
    $senderMobilePhone = get_option('_CTTExpresso_SenderMobilePhone');
    $senderEmail = get_option('_CTTExpresso_SenderEmail');
    //Sender
    $countries = new WC_Countries();
    $senderCity = $countries->get_base_city();
    $senderCodPostal = $countries->get_base_postcode();

    $strSenderCodPostal = explode('-',$senderCodPostal);
    
    $senderPTZipCode4 = $strSenderCodPostal[0];
    $senderPTZipCode3 = $strSenderCodPostal[1];

    $senderCountry = $countries->get_base_country();
    $senderAddress = $countries->get_base_address();
    
    
    //Receiver
    $shipping_address = $order->get_address('shipping'); 
    $receiverShippingAddress = $shipping_address['address_1'].' '.$shipping_address['address_2'];
    $receiverCountry = $shipping_address['country'];
    $receiverCity = $shipping_address['city'];
    $receiverCodPostal = $shipping_address['postcode']; //Codigo postal de envio
    $strReceiverCodPostal = explode('-',$receiverCodPostal);
    $receiverPTZipCode4 = $strReceiverCodPostal[0];
    $receiverPTZipCode3 = $strReceiverCodPostal[1]; 

    $receiverNote = substr($order->get_customer_note(),0,50);


    $receiverName = $order->get_formatted_shipping_full_name();
    $receiverMobilePhone = $order->get_billing_phone();
    $receiverEmail = $order->get_billing_email();
    
    $total_weight = 0;
    foreach($order->get_items() as $item_id => $product_item) {
        $quantity = $product_item->get_quantity();
        $product = $product_item->get_product();
        $product_weight = $product->get_weight();
        $total_weight += $product_weight * $quantity;
    }

    $receiverReference = '#'.$order_id; 

    if(!empty($SubProductId)):
        try {
            
            $wsdl = 'http://cttexpressows.ctt.pt/cttewspool/CTTShipmentProviderWS.svc?wsdl';
            // $wsdl = 'http://cttexpressows.qa.ctt.pt/CTTEWSPool/CTTShipmentProviderWS.svc?wsdl';
            $client = cepw_create_SOAP($wsdl);
            
            // (13 Multiplo && 19 Multiplo && Rede Shopping) || 1
            $Quantity = (get_post_meta($order_id,'Quantity', true) ?: 1);

            $ShipmentData = array(
                'ClientReference' => $receiverReference,
                'IsDevolution' => false, 
                'Quantity' => $Quantity, 
                'Weight' => $total_weight, 
                'Observations' => $receiverNote
            );

            //Sender
            $SenderData = array('Type' => 'Sender', 
                                'Name' => get_bloginfo( 'name' ),
                                'Address' => $senderAddress, 
                                'City' => $senderCity, 
                                'Country' => $senderCountry,
                            );
            if(!empty($senderEmail)){
                $SenderData['Email'] = $senderEmail;
            }

            if(!empty($senderPhone)){
                $SenderData['Phone'] = $senderPhone;
            }

            if(!empty($senderMobilePhone)){
                $SenderData['MobilePhone'] = $senderMobilePhone;
            }

            if($senderCountry == 'PT'){
                $SenderData['PTZipCode3']  = $senderPTZipCode3;
                $SenderData['PTZipCode4']  = $senderPTZipCode4;
                $SenderData['PTZipCodeLocation']  = $senderCity;
            }else{
                $SenderData['NonPTZipCode']  = $senderCodPostal;
                $SenderData['NonPTZipCodeLocation']  = $senderCity;
            }

            $SenderData = apply_filters( 'cepw_SenderData', $SenderData, $order);
            

            //Receiver
            $ReceiverData = array('Type' => 'Receiver', 
                                  'Name' => $receiverName, 
                                  'Address' => $receiverShippingAddress, 
                                  'MobilePhone' => $receiverMobilePhone,
                                  'Email' => $receiverEmail, 
                                  'City' => $receiverCity, 
                                  'Country' => $receiverCountry );
            if($receiverCountry == 'PT'){
                $ReceiverData['PTZipCode3']  = $receiverPTZipCode3;
                $ReceiverData['PTZipCode4']  = $receiverPTZipCode4;
                $ReceiverData['PTZipCodeLocation']  = $receiverCity;
            }else{
                $ReceiverData['NonPTZipCode']  = $receiverCodPostal;
                $ReceiverData['NonPTZipCodeLocation']  = $receiverCity;
            }


            //internacional products
            if($receiverCountry != 'PT'){
                $ExportTypeValues = get_post_meta($order_id,'ExportTypeValues',true);
                $UPUCodeValues = get_post_meta($order_id,'UPUCodeValues',true);
                if(!empty($ExportTypeValues)){
                    $ShipmentData['ExportType'] = $ExportTypeValues;
                }
                if(!empty($UPUCodeValues)){
                    $ShipmentData['UPUCode'] = $UPUCodeValues;
                }
            }

            //customs Data
            if(!in_array($receiverCountry, WC()->countries->get_european_union_countries())){
                
                $VATExportDeclaration = get_post_meta($order_id,'VATExportDeclaration',true);
                $SachetDocumentation = get_post_meta($order_id,'SachetDocumentation',true);
                $NonDeliveryCase = get_post_meta($order_id,'NonDeliveryCase',true)[0];
                $VATRate = get_post_meta($order_id,'VATRate',true);

                $ReceiverTIN = get_post_meta($order_id,'ReceiverTIN',true);
                $Height = get_post_meta($order_id,'Height',true);
                $Length = get_post_meta($order_id,'Length',true);
                $Width = get_post_meta($order_id,'Width',true);
                $ClientCustomsCode = get_post_meta($order_id,'ClientCustomsCode',true);
                $SenderEmail = get_post_meta($order_id,'SenderEmail',true);
                if(empty($SenderEmail)){
                    $SenderEmail = get_bloginfo('admin_email');
                }
                $ComercialInvoice = get_post_meta($order_id,'ComercialInvoice',true);
                $ExportLicense = get_post_meta($order_id,'ExportLicense',true);
                $OriginCertificateNumber = get_post_meta($order_id,'OriginCertificateNumber',true);
                $Comments = get_post_meta($order_id,'Comments',true);
                $InsurancePremium = get_post_meta($order_id,'InsurancePremium',true);
                $InsuranceValue = get_post_meta($order_id,'InsuranceValue',true);
                $ServiceValue = get_post_meta($order_id,'ServiceValue',true);

                $CustomsItemsData = array();
                $ItemNumber = 1;
                $CustomsTotalValue = 0;
                foreach($order->get_items() as $item_id => $product_item) {
                    $product = $product_item->get_product();
                    $total =  round($product_item->get_total(),2);
                    $CustomsItemsDataElement = array(
                        'ItemNumber' => $ItemNumber,
                        'Detail' => substr(get_the_excerpt( $product->get_id() ), 0, 50),
                        'Quantity' =>  $product_item->get_quantity(),
                        'Value' => $total,
                        'Weight' => $product->get_weight(),
                        'HarmonizedCode' => get_post_meta( $item_id, 'harmonized_code',true ),
                        'Currency' => get_option('woocommerce_currency'),
                        'OriginCountry' =>  WC_Countries::get_base_country()
                    );
                    array_push($CustomsItemsData,$CustomsItemsDataElement);
                    $CustomsTotalValue += $total;
                    $ItemNumber ++;
                }

                $CustomsTotalItems = count($CustomsItemsData);

                $CustomsData = array(
                    'VATExportDeclaration' => $VATExportDeclaration,
                    'SachetDocumentation' => $SachetDocumentation,
                    'NonDeliveryCase' => $NonDeliveryCase,
                    'CustomsItemsData' => $CustomsItemsData,
                    'CustomsTotalItems' => $CustomsTotalItems,
                    'VATRate' => $VATRate,
                    'CustomsTotalValue' => $CustomsTotalValue
                );
                /*** Optional Fields ***/
                if(!empty($ReceiverTIN)){
                    $CustomsData['ReceiverTIN'] = $ReceiverTIN;
                }
                if(!empty($Height)){
                    $CustomsData['Height'] = $Height;
                }
                if(!empty($Length)){
                    $CustomsData['Length'] = $Length;
                }
                if(!empty($Width)){
                    $CustomsData['Width'] = $Width;
                }
                if(!empty($ClientCustomsCode)){
                    $CustomsData['ClientCustomsCode'] = $ClientCustomsCode;
                }
                if(!empty($SenderEmail)){
                    $CustomsData['SenderEmail'] = $SenderEmail;
                }
                if(!empty($ComercialInvoice)){
                    $CustomsData['ComercialInvoice'] = $ComercialInvoice;
                }
                if(!empty($ExportLicense)){
                    $CustomsData['ExportLicense'] = $ExportLicense;
                }
                if(!empty($OriginCertificateNumber)){
                    $CustomsData['OriginCertificateNumber'] = $OriginCertificateNumber;
                }
                if(!empty($Comments)){
                    $CustomsData['Comments'] = $Comments;
                }
                if(!empty($InsurancePremium)){
                    $CustomsData['InsurancePremium'] = $InsurancePremium;
                }
                if(!empty($InsuranceValue)){
                    $CustomsData['InsuranceValue'] = $InsuranceValue;
                }
                if(!empty($ServiceValue)){
                    $CustomsData['ServiceValue'] = $ServiceValue;
                }
                /*******************/
                $ShipmentData['CustomsData'] = $CustomsData;
            }

            //CargoData Add Extra Info to ShipmentData
            if($SubProductId == 'EMSF015.01'){
                $PartialDelivery = get_post_meta($order_id,'PartialDelivery',true);
                $SchedulingData = get_post_meta($order_id,'SchedulingData',true);
                $SchedulingHour = get_post_meta($order_id,'SchedulingHour',true);
                if(!empty($PartialDelivery) && $SchedulingData && $SchedulingHour){
                    $CagoDataInfo = array(
                        'PartialDelivery' => $PartialDelivery,
                        'SchedulingData' => $SchedulingData,
                        'SchedulingHour' => $SchedulingData.'-'.$SchedulingHour
                    );
                    $ShipmentData['CargoData'] = $CagoDataInfo;
                }
            }


            $ShipmentCTT = array(
                'HasSenderInformation' => true, 
                'SenderData' => $SenderData, 
                'ReceiverData' => $ReceiverData, 
                'ShipmentData' => $ShipmentData
            );

            $SpecialServices = get_post_meta($order_id,'SpecialServices',true);
            if(!empty($SpecialServices)){
                $ShipmentCTT['SpecialServices'] = $SpecialServices;
            }
            
            $DeliveryNote = array(
                'ClientId' => get_option('_CTTExpresso_ClientId'), 
                'ContractId' => get_option('_CTTExpresso_ContractId'), 
                'DistributionChannelId' => 99, 
                'SubProductId' => $SubProductId,
                'ShipmentCTT' => array($ShipmentCTT), 
                'ExtData' => '?'
            );

            $DeliveryNote = apply_filters( 'cepw_after_DeliveryNote', $DeliveryNote, $order );
            
            $RequestID = cepw_gen_uuid();

            $CreateShipmentInput = array(
                'AuthenticationID' => get_option('_CTTExpresso_AuthenticationId'),
                'RequestID' => $RequestID,
                'DeliveryNote' => $DeliveryNote
            );
            
            $Input = array('Input' => $CreateShipmentInput);

            if($ShippingOptions_Print == 'portal_ctt'){
                $result = CreateShipmentWithoutPrint($client,$Input);
            }else{
                $result = CreateShipment($client,$Input,$order_id);
            }


            $ErrorsList = $result['ErrorsList'];
            $trackingNumber = $result['trackingNumber'];

            //If Debug is true
            if($debug == "true"){
                if(!empty($ErrorsList->ErrorData)){
                    cepw_log($ErrorsList,$order_id);
                }else{
                    cepw_log(array('Status' => _e('Success','ctt-expresso-para-woocommerce')),$order_id);
                }
                
            }

            update_post_meta($order_id,'_cttExpresso_trackingNumber', $trackingNumber);
            
        } catch (SoapFault $fault) {
            return trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
        }
    endif;
}




function cepw_add_settings_tab($settings_tabs ){
    $settings_tabs['cttexpresso'] = __( "CTT Expresso for WooCommerce", "ctt-expresso-para-woocommerce" );
    return $settings_tabs;
}

function cepw_adding_ctt_expresso_tab_content() {
    // $UserID = get_option('_CTTExpresso_UserID');
    $ClientId = sanitize_text_field(get_option('_CTTExpresso_ClientID'));
    $ContractId = sanitize_text_field(get_option('_CTTExpresso_ContractId'));
    $AuthenticationId = sanitize_text_field(get_option('_CTTExpresso_AuthenticationId'));
    $CTTExpresso_SenderPhone = sanitize_text_field(get_option('_CTTExpresso_SenderPhone'));
    $CTTExpresso_SenderEmail = sanitize_text_field(get_option('_CTTExpresso_SenderEmail'));
    $CTTExpresso_SenderMobilePhone = sanitize_text_field(get_option('_CTTExpresso_SenderMobilePhone'));

    $EmailText = get_option('_CTTExpresso_EmailText') != null ? get_option('_CTTExpresso_EmailText') : __("O Tracking ID da sua encomenda é o [track_code].", "ctt-expresso-para-woocommerce" ); 
    $tomorrow = get_option('_ShippingOptionsTomorrow');
    $thirteen = get_option('_ShippingOptionsThirteenHours');
    $forty_eight = get_option('_ShippingOptionsFortyEightHours');
    $two_days = get_option('_ShippingOptionsTwoDays');
    $ten = get_option('_ShippingOptionsTen');
    $thirteen_multi = get_option('_ShippingOptionsThirteen_Multi');
    $nineteen = get_option('_ShippingOptionsNineteen');
    $nineteen_multi = get_option('_ShippingOptionsNineteen_Multi');
    $cargo = get_option('_ShippingOptionsCargo');
    $easy_return_24 = get_option('_ShippingOptionsEasyReturn24');
    $easy_return_48 = get_option('_ShippingOptionsEasyReturn48');
    $ems_economy = get_option('_ShippingOptionsEMS_Economy');
    $ems_international = get_option('_ShippingOptionsEMS_International');
    $rede_shopping = get_option('_ShippingOptionsRedeShopping');
    $print = get_option('_ShippingOptions_Print');
    $debug = get_option('_CTTExpresso_Debug');
    $sendtracking = get_option('_CTTExpresso_SendTracking');
?>
<h2 class="ctt_expresso_header">
    <img src="<?php echo plugins_url( '/assets/img/logo.png', __FILE__ ); ?>">
</h2>

    <form action="<?php echo esc_url( admin_url( 'admin-post.php' ) ); ?>" method="POST" >
        <?php wp_nonce_field( basename( __FILE__ ), 'cepw_nonce_save' ); ?>
        <table class="form-table">
            <tbody>
                <!--  
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_ClientId"> <?php echo esc_html__("User ID", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="ID do utilizador fornecido pelos serviços CTT Expresso"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_UserID" id="CTTExpresso_UserID" value="<?php echo $UserID; ?>">
                    </td>
                </tr>
                -->
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_ClientId"> <?php echo esc_html__("Client ID", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="ID do cliente fornecido pelos serviços CTT Expresso"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_ClientId" id="CTTExpresso_ClientId" value="<?php echo $ClientId; ?>">
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_ContractId"><?php echo esc_html__("Contract ID", "ctt-expresso-para-woocommerce")?>:</label>
                        <span class="woocommerce-help-tip" data-tip="ID do contrato fornecido pelos serviços CTT Expresso"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_ContractId" id="CTTExpresso_ContractId" value="<?php echo $ContractId; ?>">                
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_AuthenticationId"><?php echo esc_html__("Authentication ID", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="ID de Autenticação na API CTT Expresso"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_AuthenticationId" id="CTTExpresso_AuthenticationId" value="<?php echo $AuthenticationId; ?>">          
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_SenderEmail"><?php echo esc_html__("Sender Email", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Email do remetente"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="email" name="CTTExpresso_SenderEmail" id="CTTExpresso_SenderEmail" value="<?php echo $CTTExpresso_SenderEmail; ?>">          
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_SenderPhone"><?php echo esc_html__("Sender Phone", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Telefone do remetente ex: xxxxxxxxx"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_SenderPhone" id="CTTExpresso_SenderPhone" value="<?php echo $CTTExpresso_SenderPhone; ?>">          
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_SenderMobilePhone"><?php echo esc_html__("Sender Mobile Phone", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Telemóvel do remetente ex: xxxxxxxxx"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_SenderMobilePhone" id="CTTExpresso_SenderMobilePhone" value="<?php echo $CTTExpresso_SenderMobilePhone; ?>">          
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_SubProductId"><?php echo esc_html__("Completed order email text", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Texto a incluir no email de encomenda concluída."></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="text" name="CTTExpresso_EmailText" id="CTTExpresso_EmailText" value="<?php echo $EmailText; ?>">      
                        <br/><span><?php echo esc_html__("Use", "ctt-expresso-para-woocommerce")?> <strong>[track_code]</strong> <?php echo esc_html__("to show the order tracking number with hyperlink", "ctt-expresso-para-woocommerce") ?>
                        <br/>
                        <?php echo esc_html__("eg.", "ctt-expresso-para-woocommerce")?>
                        <i><?php echo esc_html__("Your order tracking number is", "ctt-expresso-para-woocommerce") ?><strong> [track_code]</strong>.</i></span>
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_SendTracking"><?php echo esc_html__("Skip tracking number in email", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="If checked, tracking number will not be sent in completed order email."></span>
                    </th>
                    <td class="forminp forminp-text"> 
                        <input type="checkbox" id="sendtracking" name="CTTExpresso_SendTracking" <?php checked( $sendtracking, "false"); ?> value="false">
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_ShippingOptions"><?php echo esc_html__("Shipping Options", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Seleccione as opções de envio contratadas que pretende utilizar"></span>
                    </th>
                    <td class="forminp forminp-text">
                        <input type="checkbox" id="ten" name="CTTExpresso_Ten" <?php checked( $ten, 'EMSF009.01'); ?> value="EMSF009.01">
                        <label for="ten"><?php echo esc_html__("10", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="thirteen" name="CTTExpresso_Thirteen" <?php checked( $thirteen, 'EMSF001.01'); ?> value="EMSF001.01">
                        <label for="thirteen"><?php echo esc_html__("13", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="thirteen_multi" name="CTTExpresso_Thirteen_Multi" <?php checked( $thirteen_multi, 'EMSF028.01'); ?> value="EMSF028.01">
                        <label for="thirteen_multi"><?php echo esc_html__("13 Múltiplo", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="nineteen" name="CTTExpresso_Nineteen" <?php checked( $nineteen, 'ENCF005.01'); ?> value="ENCF005.01">
                        <label for="nineteen"><?php echo esc_html__("19", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="nineteen_multi" name="CTTExpresso_Nineteen_Multi" <?php checked( $nineteen_multi, 'EMSF010.01'); ?> value="EMSF010.01">
                        <label for="nineteen_multi"><?php echo esc_html__("19 Múltiplo", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="forty_eight" name="CTTExpresso_FortyEight" <?php checked( $forty_eight, 'ENCF008.01'); ?> value="ENCF008.01">
                        <label for="forty_eight"><?php echo esc_html__("48", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="cargo" name="CTTExpresso_Cargo" <?php checked( $cargo, 'EMSF015.01'); ?> value="EMSF015.01">
                        <label for="cargo"><?php echo esc_html__("Cargo", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="easy_return_24" name="CTTExpresso_EasyReturn24" <?php checked( $easy_return_24, 'EMSF053.01'); ?> value="EMSF053.01">
                        <label for="easy_return_24"><?php echo esc_html__("Easy Return 24", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="easy_return_48" name="CTTExpresso_EasyReturn48" <?php checked( $easy_return_48, 'EMSF054.01'); ?> value="EMSF054.01">
                        <label for="easy_return_48"><?php echo esc_html__("Easy Return 48", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="two_days" name="CTTExpresso_TwoDays" <?php checked( $two_days, 'EMSF057.01'); ?> value="EMSF057.01">
                        <label for="two_days"><?php echo esc_html__("Em 2 dias", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="ems_economy" name="CTTExpresso_EMS_Economy" <?php checked( $ems_economy, 'ENCF008.02'); ?> value="ENCF008.02">
                        <label for="ems_economy"><?php echo esc_html__("EMS Economy", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="ems_international" name="CTTExpresso_EMS_International" <?php checked( $ems_international, 'EMSF001.02'); ?> value="EMSF001.02">
                        <label for="ems_international"><?php echo esc_html__("EMS International", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="tomorrow" name="CTTExpresso_Tomorrow" <?php checked( $tomorrow, 'EMSF056.01'); ?> value="EMSF056.01">
                        <label for="tomorrow"><?php echo esc_html__("Para Amanhã", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="checkbox" id="rede_shopping" name="CTTExpresso_RedeShopping" <?php checked( $rede_shopping, 'EMSF059.01'); ?> value="EMSF059.01">
                        <label for="rede_shopping"><?php echo esc_html__("Rede Shopping", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                    </td>
                </tr>
                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_UsageOptions"><?php echo esc_html__("Usage Options", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Escolha o tipo de impressão que pretende"></span>
                    </th>
                    <td class="forminp forminp-text"> 
                        <input type="radio" id="print_ctt_portal" name="CTTExpresso_Print"  <?php checked( $print, 'portal_ctt' ); ?>  value="portal_ctt">
                        <label for="print_ctt_portal"><?php echo esc_html__("Print using CTT Expresso Portal", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                        <input type="radio" id="print_website" name="CTTExpresso_Print"  <?php checked( $print, 'website'); ?>  value="website">
                        <label for="print_website"><?php echo esc_html__("Print on my Website", "ctt-expresso-para-woocommerce") ?></label>
                        <br>
                    </td>
                </tr>

                


                <!--  -->
                <tr valign="top">
                    <th scope="row" class="titledesc">
                        <label for="CTTExpresso_Debug"><?php echo esc_html__("Log File", "ctt-expresso-para-woocommerce") ?>:</label>
                        <span class="woocommerce-help-tip" data-tip="Seleccione esta opção para registar o log de debug das chamadas ao Webservice dos CTT"></span>
                    </th>
                    <td class="forminp forminp-text"> 
                        <!-- Debug -->
                        <input type="checkbox" id="debug" name="CTTExpresso_Debug" <?php checked( $debug, "true"); ?> value="true">
                        <br>
                        <?php if($debug == 'true'): ?>
                            <a href="admin.php?page=cepw_log" target="_blank">Log file</a>
                        <?php endif; ?>
                    </td>
                </tr>




            </tbody>
        <table>
    </form>


    <!--PayPal Donate-->
    <div style="position: absolute; margin-top: 100px;">
        <div><?php echo esc_html__("This plugin is made available free of charge. If it's really usefull for your business, please consider a donation. Thank you.","ctt-expresso-para-woocommerce") ?>
         </div>
         <br/>
        <form action="https://www.paypal.com/cgi-bin/webscr" method="post" target="_top">
            <input type="hidden" name="cmd" value="_s-xclick" />
            <input type="hidden" name="hosted_button_id" value="QWHVSQRWJVNXN" />
            <input type="image" src="https://www.paypalobjects.com/pt_PT/PT/i/btn/btn_donateCC_LG.gif" border="0" name="submit" title="PayPal - The safer, easier way to pay online!" alt="Faça donativos com o botão PayPal" />
            <img alt="" border="0" src="https://www.paypal.com/pt_PT/i/scr/pixel.gif" width="1" height="1" />
        </form>
        <br/><br/>
        <?php echo esc_html__("Support","ctt-expresso-para-woocommerce") ?>: <a href="mailto:suporte@thisfunctional.pt?subject=CTT Expresso para WooCommerce">suporte@thisfunctional.pt</a>
        <br/><br/>
    </div>
<?php
}
add_action( 'woocommerce_settings_cttexpresso', 'cepw_adding_ctt_expresso_tab_content' );

function cepw_save_hook(){ 

    // if(isset($_POST['CTTExpresso_UserID'])){
    //     update_option('_CTTExpresso_UserID',sanitize_text_field($_POST['CTTExpresso_UserID']));
    //     $ClientId = $_POST['CTTExpresso_UserID'];
    // }

    if (isset($_POST['CTTExpresso_ClientId'])) {

        update_option('_CTTExpresso_ClientId', intval($_POST['CTTExpresso_ClientId']));
        $ClientId = $_POST['CTTExpresso_ClientId'];
    } 


    if (isset($_POST['CTTExpresso_ContractId'])) {
        update_option('_CTTExpresso_ContractId', intval($_POST['CTTExpresso_ContractId']));
        $ContractId = $_POST['CTTExpresso_ContractId'];
    }

    if (isset($_POST['CTTExpresso_AuthenticationId'])) {
        update_option('_CTTExpresso_AuthenticationId', sanitize_text_field($_POST['CTTExpresso_AuthenticationId']));
        $AuthenticationId = $_POST['CTTExpresso_AuthenticationId'];
    } 

    if (isset($_POST['CTTExpresso_SenderEmail'])) {
        update_option('_CTTExpresso_SenderEmail', sanitize_text_field($_POST['CTTExpresso_SenderEmail']));
        $SenderEmail = $_POST['CTTExpresso_SenderEmail'];
    } 

    if (isset($_POST['CTTExpresso_SenderPhone'])) {
        update_option('_CTTExpresso_SenderPhone', sanitize_text_field($_POST['CTTExpresso_SenderPhone']));
        $SenderPhone = $_POST['CTTExpresso_SenderPhone'];
    } 

    if (isset($_POST['CTTExpresso_SenderMobilePhone'])) {
        update_option('_CTTExpresso_SenderMobilePhone', sanitize_text_field($_POST['CTTExpresso_SenderMobilePhone']));
        $SenderMobilePhone = $_POST['CTTExpresso_SenderMobilePhone'];
    }  

    if (isset($_POST['CTTExpresso_EmailText'])) {
        update_option('_CTTExpresso_EmailText', sanitize_text_field($_POST['CTTExpresso_EmailText']));
        $EmailText = $_POST['CTTExpresso_EmailText'];
    }


    if(!isset($_POST['CTTExpresso_TwoDays']) && !isset($_POST['CTTExpresso_Tomorrow']) && !isset($_POST['CTTExpresso_Thirteen']) && !isset($_POST['CTTExpresso_FortyEight']) ){
        update_option('_ShippingOptionsTwoDays', sanitize_text_field('EMSF057.01'));
    }else{
        if(isset($_POST['CTTExpresso_TwoDays'])) {
            update_option('_ShippingOptionsTwoDays', sanitize_text_field($_POST['CTTExpresso_TwoDays']));
        }else{
            delete_option('_ShippingOptionsTwoDays');
        }

        if (isset($_POST['CTTExpresso_Tomorrow'])) {
            update_option('_ShippingOptionsTomorrow', sanitize_text_field($_POST['CTTExpresso_Tomorrow']));
        }else{
            delete_option('_ShippingOptionsTomorrow');
        }

        if (isset($_POST['CTTExpresso_Thirteen'])) {
            update_option('_ShippingOptionsThirteenHours', sanitize_text_field($_POST['CTTExpresso_Thirteen']));
        }else{
            delete_option('_ShippingOptionsThirteenHours');
        }

        if (isset($_POST['CTTExpresso_FortyEight'])) {
            update_option('_ShippingOptionsFortyEightHours', sanitize_text_field($_POST['CTTExpresso_FortyEight']));
        }else{
            delete_option('_ShippingOptionsFortyEightHours');
        }
        if (isset($_POST['CTTExpresso_Ten'])) {
            update_option('_ShippingOptionsTen', sanitize_text_field($_POST['CTTExpresso_Ten']));
        }else{
            delete_option('_ShippingOptionsTen');
        }

        if (isset($_POST['CTTExpresso_Thirteen_Multi'])) {
            update_option('_ShippingOptionsThirteen_Multi', sanitize_text_field($_POST['CTTExpresso_Thirteen_Multi']));
        }else{
            delete_option('_ShippingOptionsThirteen_Multi');
        }
        
        if (isset($_POST['CTTExpresso_Nineteen'])) {
            update_option('_ShippingOptionsNineteen', sanitize_text_field($_POST['CTTExpresso_Nineteen']));
        }else{
            delete_option('_ShippingOptionsNineteen');
        }

        if (isset($_POST['CTTExpresso_Nineteen_Multi'])) {
            update_option('_ShippingOptionsNineteen_Multi', sanitize_text_field($_POST['CTTExpresso_Nineteen_Multi']));
        }else{
            delete_option('_ShippingOptionsNineteen_Multi');
        }

        if (isset($_POST['CTTExpresso_Cargo'])) {
            update_option('_ShippingOptionsCargo', sanitize_text_field($_POST['CTTExpresso_Cargo']));
        }else{
            delete_option('_ShippingOptionsCargo');
        }

        if (isset($_POST['CTTExpresso_EasyReturn24'])) {
            update_option('_ShippingOptionsEasyReturn24', sanitize_text_field($_POST['CTTExpresso_EasyReturn24']));
        }else{
            delete_option('_ShippingOptionsEasyReturn24');
        }
        
        if (isset($_POST['CTTExpresso_EasyReturn48'])) {
            update_option('_ShippingOptionsEasyReturn48', sanitize_text_field($_POST['CTTExpresso_EasyReturn48']));
        }else{
            delete_option('_ShippingOptionsEasyReturn48');
        }
        
        if (isset($_POST['CTTExpresso_EMS_Economy'])) {
            update_option('_ShippingOptionsEMS_Economy', sanitize_text_field($_POST['CTTExpresso_EMS_Economy']));
        }else{
            delete_option('_ShippingOptionsEMS_Economy');
        }
        
        if (isset($_POST['CTTExpresso_EMS_International'])) {
            update_option('_ShippingOptionsEMS_International', sanitize_text_field($_POST['CTTExpresso_EMS_International']));
        }else{
            delete_option('_ShippingOptionsEMS_International');
        }
        
        if (isset($_POST['CTTExpresso_RedeShopping'])) {
            update_option('_ShippingOptionsRedeShopping', sanitize_text_field($_POST['CTTExpresso_RedeShopping']));
        }else{
            delete_option('_ShippingOptionsRedeShopping');
        }
        
        
    }

    if (isset($_POST['CTTExpresso_Print'])) {
        update_option('_ShippingOptions_Print', sanitize_text_field($_POST['CTTExpresso_Print']));
    }


    if (isset($_POST['CTTExpresso_SendTracking'])) {
        update_option('_CTTExpresso_SendTracking', sanitize_text_field($_POST['CTTExpresso_SendTracking']));
    }else{
        delete_option('_CTTExpresso_SendTracking');
    }


    if (isset($_POST['CTTExpresso_Debug'])) {
        update_option('_CTTExpresso_Debug', sanitize_text_field($_POST['CTTExpresso_Debug']));
    }else{
        delete_option('_CTTExpresso_Debug');
    }
}
add_action( 'woocommerce_settings_save_cttexpresso', 'cepw_save_hook' );


//Delay Woocommerce email to process the tracking id
add_filter( 'woocommerce_defer_transactional_emails', '__return_true' );
function cepw_customer_completed_order_tracking_number( $order, $sent_to_admin, $plain_text, $email ) {
    $sendtracking = get_option('_CTTExpresso_SendTracking');

    if(empty($sendtracking)){
        if ( $email->id == 'customer_completed_order') {
            $cttExpressoURL = get_option('_CTTExpresso_URL');
            $order_id = $order->get_id();
            $trackingNumber = get_post_meta($order_id, '_cttExpresso_trackingNumber',true);
            if(!empty($trackingNumber)){
                $track_code = '<a href="'.$cttExpressoURL.$trackingNumber.'">'.$trackingNumber.'</a><br/>';
                echo str_replace('[track_code]', $track_code, __(get_option('_CTTExpresso_EmailText'), 'ctt-expresso-para-woocommerce'));
            }
        }
    }
}
add_action( 'woocommerce_email_after_order_table', 'cepw_customer_completed_order_tracking_number', 20, 4 );
  
function cepw_get_title_filename($filename){
    switch ($filename) {
        case 'CA1.pdf':
            return "Certificado de Aceitação";
            break;
        case 'GuiaTransportA4.pdf':
            return "Guia de Transporte A4";
            break;
        case 'GON.pdf':
            return "Guia de Transporte";
            break;
        default:
            return $filename;
            break;
    }
}

function cepw_add_meta_box() {
    //check if order use ctt express shipping options
    add_meta_box( 'cepw_get_trackingInfo', __('CTT Expresso','ctt-expresso-para-woocommerce'), 'cepw_get_trackingInfo', 'shop_order', 'side', 'core' );   
    
    add_meta_box( 'cepw_extraInformation', __('CTT Expresso Extra Information','ctt-expresso-para-woocommerce'), 'cepw_extraInformation', 'shop_order', 'normal', 'high' );   
}
add_action( 'add_meta_boxes', 'cepw_add_meta_box' );
  
function cepw_get_trackingInfo( $post )
{
    $order = new WC_Order($post->ID);
    $order_id = $order->get_id();
    $orderStatus = 'wc-'.$order->get_status();
    $ShippingOptions_Print = get_option('_ShippingOptions_Print');
    $cttExpressoURL = get_option('_CTTExpresso_URL');
    $trackingId = get_post_meta($order_id,'_cttExpresso_trackingNumber',true) ? get_post_meta($order_id,'_cttExpresso_trackingNumber',true) : 'N/A';
    $status_trigger = apply_filters( 'cepw_order_status_trigger', 'wc-completed' );
    if ($orderStatus == $status_trigger) {
        echo esc_html__('O Tracking ID desta encomenda é ', 'ctt-expresso-para-woocommerce'). ':<br/><a target="_blank" href='. esc_html($cttExpressoURL.$trackingId).'>'. esc_html($trackingId).'</a>';
        if($ShippingOptions_Print != "portal_ctt"):
            cepw_get_post_files($order_id,'Os ficheiros desta encomenda são ');
        endif;
    }else { 
        echo __('O Tracking ID da encomenda é disponibilizado após conclusão da mesma.', 'ctt-expresso-para-woocommerce');
    }
}


function cepw_disable_option($value,$SpecialServices_Use){
    if(!empty($SpecialServices_Use)){
        if(in_array($value, $SpecialServices_Use)){
            return 'disabled="disabled"';
        }
    }
    return '';
}




function cepw_add_SpecialService($post_id,$SubProductId,$i,$SpecialServices = null,$SpecialServices_Use = null){
    $readonly = '';
    $SpecialServicesTypes = '';
    $order = new WC_Order($post_id);
    $order_total = $order->get_total();
    $current_Special_Services = get_post_meta($post_id,'SpecialServices_count',true);
    $againstreimbursement = get_post_meta($post_id,'againstreimbursement',true);
    if(!empty($SpecialServices)){
        $SpecialServicesTypes = $SpecialServices[$i]['SpecialServiceType'];
    }

    //Para Amanhã e Em 2 dias always with MultipleHomeDelivery
    if( ($SubProductId == 'EMSF056.01' || $SubProductId == 'EMSF057.01') && $i == 0){
       $SpecialServicesTypes = 'MultipleHomeDelivery';
       $readonly = 'readonly="readonly"'; // returns true
    }

    //Para amanha e em 2 duas com cobrança
    if(($SubProductId == 'EMSF056.01' || $SubProductId == 'EMSF057.01') && $againstreimbursement == 'true' && $i == 1){
        $SpecialServicesTypes = 'AgainstReimbursement';
        $readonly = 'readonly="readonly"'; // returns true
    }

    //outro subproductid não para amanhã e em 2 dias com cobrança
    if($SubProductId != 'EMSF056.01' && $SubProductId != 'EMSF057.01'){
        if($againstreimbursement == 'true' && $i == 0){
            $SpecialServicesTypes = 'AgainstReimbursement';
            $readonly = 'readonly="readonly"'; // returns true
        }
    }
    
    ob_start();
    ?>
    <div class="SpecialServices" id="SpecialServices_<?php echo $i; ?>" SpecialServicesTypes="<?php echo $SpecialServicesTypes; ?>">
        <h3>
            <?php echo esc_html__("Special Services:", "ctt-expresso-para-woocommerce"); ?>
            <div class="actions">
                <?php if($readonly == ''): ?>
                    <a href="#" special_service="SpecialServices_<?php echo $i; ?>" class="remove">×</a>
                <?php endif; ?>
            </div>
        </h3>
        <div class="forminp forminp-text">

            <div class="extra_information_fields SpecialServicesTypes">
                <label for="PartialDelivery"><?php echo esc_html__("Tipo de Serviço Especial", "ctt-expresso-para-woocommerce") ?></label>
                <span class="extra_information_fields_input">
                    <p>
                        <select name="SpecialServicesTypes_<?php echo $i; ?>" class="SpecialServicesTypesValues" <?php  echo $readonly;?>>
                            <option value="" disabled <?php selected( $SpecialServicesTypes, '' );  ?>><?php echo esc_html__("Choose a Special Service", "ctt-expresso-para-woocommerce") ?></option>
                            <?php if($SubProductId != 'EMSF015.01'): ?>
                            <option value="PostalObject" <?php echo cepw_disable_option('PostalObject', $SpecialServices_Use); ?> class="PostalObject" <?php selected( $SpecialServicesTypes, 'PostalObject' );  ?>><?php echo esc_html__("Postal Object", "ctt-expresso-para-woocommerce") ?></option>
                            <?php endif; ?>
                            <?php if($SubProductId != 'EMSF053.01' || $SubProductId != 'EMSF054.01'): ?>
                            <option value="AgainstReimbursement" <?php echo cepw_disable_option('AgainstReimbursement', $SpecialServices_Use); ?> class="AgainstReimbursement" <?php selected( $SpecialServicesTypes, 'AgainstReimbursement' );  ?>><?php echo esc_html__("Against Reimbursement", "ctt-expresso-para-woocommerce") ?></option>
                            <?php endif; ?>
                            <option value="NominativeCheck" <?php echo cepw_disable_option('NominativeCheck', $SpecialServices_Use); ?> class="NominativeCheck" <?php selected( $SpecialServicesTypes, 'NominativeCheck' );  ?>><?php echo esc_html__("Nominative Check", "ctt-expresso-para-woocommerce") ?></option>
                            <?php if($SubProductId != 'EMSF015.01'): ?>
                            <option value="Saturday" <?php echo cepw_disable_option('Saturday', $SpecialServices_Use); ?> class="Saturday" <?php selected( $SpecialServicesTypes, 'Saturday' );  ?>><?php echo esc_html__("Saturday", "ctt-expresso-para-woocommerce") ?></option>
                            <?php endif; ?>
                            <option value="ReturnDocumentSigned" <?php echo cepw_disable_option('ReturnDocumentSigned', $SpecialServices_Use); ?> class="ReturnDocumentSigned" <?php selected( $SpecialServicesTypes, 'ReturnDocumentSigned' );  ?>><?php echo esc_html__("Return Document Signed", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="SpecialInsurance" <?php echo cepw_disable_option('SpecialInsurance', $SpecialServices_Use); ?> class="SpecialInsurance" <?php selected( $SpecialServicesTypes, 'SpecialInsurance' );  ?>><?php echo esc_html__("Special Insurance", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="Fragil" class="Fragil" <?php echo cepw_disable_option('Fragil', $SpecialServices_Use); ?> <?php selected( $SpecialServicesTypes, 'Fragil' );  ?>><?php echo esc_html__("Fragil", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="Back" class="Back" <?php echo cepw_disable_option('Back', $SpecialServices_Use); ?> <?php selected( $SpecialServicesTypes, 'Back' );  ?>><?php echo esc_html__("Back", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="SecondScheduledDelivery" <?php echo cepw_disable_option('SecondScheduledDelivery', $SpecialServices_Use); ?> class="SecondScheduledDelivery" <?php selected( $SpecialServicesTypes, 'SecondScheduledDelivery' );  ?>><?php echo esc_html__("Second Scheduled Delivery", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="SMS" class="SMS" <?php echo cepw_disable_option('SMS', $SpecialServices_Use); ?> <?php selected( $SpecialServicesTypes, 'SMS' );  ?>><?php echo esc_html__("SMS", "ctt-expresso-para-woocommerce") ?></option>
                            <?php if($SubProductId != 'EMSF015.01' && $SubProductId != 'ENCF008.02' && $SubProductId != 'EMSF001.02'): ?>
                                <option value="MultipleHomeDelivery" <?php echo cepw_disable_option('MultipleHomeDelivery', $SpecialServices_Use); ?> class="MultipleHomeDelivery" <?php selected( $SpecialServicesTypes, 'MultipleHomeDelivery' );  ?>><?php echo esc_html__("Multiple Home Delivery", "ctt-expresso-para-woocommerce") ?></option>
                            <?php endif; ?>
                            <option value="TimeWindow" <?php echo cepw_disable_option('TimeWindow', $SpecialServices_Use); ?> class="TimeWindow" <?php selected( $SpecialServicesTypes, 'TimeWindow' );  ?>><?php echo esc_html__("Time Window", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="CertainDay" <?php echo cepw_disable_option('CertainDay', $SpecialServices_Use); ?> class="CertainDay" <?php selected( $SpecialServicesTypes, 'CertainDay' );  ?>><?php echo esc_html__("Certain Day", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="PhoneContact" <?php echo cepw_disable_option('PhoneContact', $SpecialServices_Use); ?> class="PhoneContact" <?php selected( $SpecialServicesTypes, 'PhoneContact' );  ?>><?php echo esc_html__("Phone Contact", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="ContactoAgendamento" <?php echo cepw_disable_option('ContactoAgendamento', $SpecialServices_Use); ?> class="ContactoAgendamento" <?php selected( $SpecialServicesTypes, 'ContactoAgendamento' );  ?>><?php echo esc_html__("Contacto Agendamento", "ctt-expresso-para-woocommerce") ?></option>
                            <option value="DeliveryAggregation" <?php echo cepw_disable_option('DeliveryAggregation', $SpecialServices_Use); ?> class="DeliveryAggregation" <?php selected( $SpecialServicesTypes, 'DeliveryAggregation' );  ?>><?php echo esc_html__("Delivery Aggregation", "ctt-expresso-para-woocommerce") ?></option>
                        </select>
                    </p>
                </span>
            </div>

            <!-- PostalObject Value -->
            <div class="postalobject special_service_extra">
                <?php 
                $PostalObjectValue = 0;
                if(!empty($SpecialServices[$i]['Value'])){                    
                    $PostalObjectValue = floatval($SpecialServices[$i]['Value']);
                }
                ?>
                <h3><?php echo esc_html__("PostalObject Price", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="PostalObjectValue"><?php echo esc_html__("Price added to order total", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input maxlength="60" type="number" min="0" step=".01" class="halfwidth" id="PostalObjectValue" name="PostalObjectValue" value="<?php echo $PostalObjectValue ?>"></p>
                    </span>
                </div>
            </div>

            <!-- NominativeCheck Value -->
            <div class="nominativecheck special_service_extra">
                <?php 
                $NominativeCheckValue = 0;
                if(!empty($SpecialServices[$i]['Value'])){                    
                    $NominativeCheckValue = floatval($SpecialServices[$i]['Value']);
                }
                ?>
                <h3><?php echo esc_html__("Nominative Check Price", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="NominativeCheckValue"><?php echo esc_html__("Price added to order total", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input maxlength="60" type="number" min="0" step=".01" class="halfwidth" id="NominativeCheckValue" name="NominativeCheckValue" value="<?php echo $NominativeCheckValue ?>"></p>
                    </span>
                </div>
            </div>

            <!-- SpecialInsurance Value -->
            <div class="specialinsurance special_service_extra">
                <?php 
                $SpecialInsuranceValue = 0;
                if(!empty($SpecialServices[$i]['Value'])){                    
                    $SpecialInsuranceValue = floatval($SpecialServices[$i]['Value']) - $order_tota;
                }
                ?>
                <h3><?php echo esc_html__("SpecialInsurance Price", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="SpecialInsuranceValue"><?php echo esc_html__("Price added to order total", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input maxlength="60" type="number" min="0" step=".01" class="halfwidth" id="SpecialInsuranceValue" name="SpecialInsuranceValue" value="<?php echo $SpecialInsuranceValue ?>"></p>
                    </span>
                </div>
            </div>


            <!-- DDA -->
            <div class="dda special_service_extra">
                <?php 
                $ShipperInstructions = '';
                if(!empty($SpecialServices[$i]['DDA']['ShipperInstructions'])){
                    $ShipperInstructions = esc_html($SpecialServices[$i]['DDA']['ShipperInstructions']);
                }
                ?>
                <h3><?php echo esc_html__("DDA", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="ShipperInstructions"><?php echo esc_html__("Instruções do expedidor", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input maxlength="60" type="text" class="halfwidth" id="ShipperInstructions_<?php echo $i; ?>" name="ShipperInstructions_<?php echo $i; ?>" value="<?php echo $ShipperInstructions; ?>"></p>
                    </span>
                </div>
            </div>
            <!-- multiplehomedelivery -->
            <div class="multiplehomedelivery special_service_extra">
                <?php 
                    if(!empty($SpecialServices[$i]['MultipleHomeDelivery'])){
                        $AttemptsNumber = $SpecialServices[$i]['MultipleHomeDelivery']['AttemptsNumber'];
                        $InNonDeliveryCase = $SpecialServices[$i]['MultipleHomeDelivery']['InNonDeliveryCase'];
                    }else{
                        $AttemptsNumber = 1;
                        $InNonDeliveryCase = "PostOfficeNotiffied";
                    }
                ?>
                <h3><?php echo esc_html__("Multiple Home Delivery", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="AttemptsNumber"><?php echo esc_html__("Attempts Number", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="number" min="1" id="AttemptsNumber_<?php echo $i; ?>" name="AttemptsNumber_<?php echo $i; ?>" value="<?php echo $AttemptsNumber; ?>"></p>
                    </span>
                </div>
                <div class="extra_information_fields ">
                    <label for="InNonDeliveryCase"><?php echo esc_html__("In Non Delivery Case", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="InNonDeliveryCase_<?php echo $i; ?>" class="InNonDeliveryCase" id="InNonDeliveryCase_<?php echo $i; ?>">
                                <option value="PostOfficeNotiffied" id="PostOfficeNotiffied" <?php selected( $InNonDeliveryCase, 'PostOfficeNotiffied' ); ?>><?php echo esc_html__("Aviso na loja CTT", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="SendToSender" id="SendToSender" <?php selected( $InNonDeliveryCase, 'SendToSender' ); ?>><?php echo esc_html__("Encomenda é enviada para morada do remetente", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="SendToAddress" id="SendToAddress" <?php selected( $InNonDeliveryCase, 'SendToAddress' ); ?>><?php echo esc_html__("Encomenda é enviada para segunda morada especifica", "ctt-expresso-para-woocommerce") ?></option>
                            </select>
                        </p>
                    </span>
                </div>

            </div>

            <!-- SendToAddress -->
            <div class="seconddeliveryaddress special_service_extra">
                <?php 
                $Type = '';
                $Name = '';
                $Address = '';
                $PTZipCode3 = '';
                $PTZipCode4 = '';
                $CodeLocation = '';
                $City = '';
                if(!empty($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress'])){
                    $Type = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['Type']);
                    $Name = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['Name']);
                    $Address = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['Address']);
                    $PTZipCode3 = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['PTZipCode3']);
                    $PTZipCode4 = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['PTZipCode4']);
                    $CodeLocation = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['CodeLocation']);
                    $City = esc_html($SpecialServices[$i]['MultipleHomeDelivery']['SecondDeliveryAddress']['City']);
                }
                ?>
                <h3><?php echo esc_html__("Second Delivery Address", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="Type"><?php echo esc_html__("Tipo de Endereço", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="Type_<?php echo $i; ?>" id="Type_<?php echo $i; ?>">
                                <option value="Sender" id="Sender" disabled <?php selected( $Type, 'Sender' ) ?>><?php echo esc_html__("Endereço do remetente", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="Receiver" id="Receiver" disabled><?php echo esc_html__("Endereço do destinatário", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="Return" id="Return" disabled ><?php echo esc_html__("Endereço de devolução", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="SecondReceiver" id="SecondReceiver" <?php selected( $Type, 'SecondReceiver' ) ?>><?php echo esc_html__("Endereço de envio caso entrega no endereço original falhe", "ctt-expresso-para-woocommerce") ?></option>
                            </select>
                        </p>
                    </span>
                </div>
                <div class="extra_information_fields ">
                    <label for="Name"><?php echo esc_html__("Name", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" class="halfwidth" maxlength="60" id="Name_<?php echo $i; ?>" name="Name_<?php echo $i; ?>" value="<?php echo $Name; ?>"></p>
                    </span>
                </div>
                <div class="extra_information_fields ">
                    <label for="Address"><?php echo esc_html__("Address", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" class="halfwidth" maxlength="100" id="Address_<?php echo $i; ?>" name="Address_<?php echo $i; ?>" value="<?php echo $Address; ?>"></p>
                    </span>
                </div>
                <div class="extra_information_fields ">
                    <label for="PTZipCode4"><?php echo esc_html__("Zip Code", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" style="max-width: 110px;"  maxlength="4" id="PTZipCode4_<?php echo $i; ?>" name="PTZipCode4_<?php echo $i; ?>" value="<?php echo $PTZipCode4; ?>">
                            -
                            <input type="text" style="max-width: 45px;"  maxlength="3" id="PTZipCode3_<?php echo $i; ?>" name="PTZipCode3_<?php echo $i; ?>" value="<?php echo $PTZipCode3; ?>">
                        </p>
                    </span>
                </div>
                
                <div class="extra_information_fields ">
                    <label for="CodeLocation"><?php echo esc_html__("Code Location", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" class="halfwidth" placeholder="Lisboa" maxlength="50" id="CodeLocation_<?php echo $i; ?>" name="CodeLocation_<?php echo $i; ?>" value="<?php echo $CodeLocation; ?>"></p>
                    </span>
                </div>
                <div class="extra_information_fields ">
                    <label for="City"><?php echo esc_html__("City", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" class="halfwidth" maxlength="50" id="City_<?php echo $i; ?>" name="City_<?php echo $i; ?>" value="<?php echo $City; ?>"></p>
                    </span>
                </div>
            </div>

            <div class="TimeWindow special_service_extra">
                <?php 
                $TimeWindow = '';
                $DeliveryDate = '';
                if(!empty($SpecialServices[$i]['TimeWindow'])){
                    $TimeWindow = esc_html($SpecialServices[$i]['TimeWindow']['TimeWindow']);
                    $DeliveryDate = esc_html($SpecialServices[$i]['TimeWindow']['DeliveryDate']);
                }
                ?>
                <h3><?php echo esc_html__("Time Window", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields ">
                    <label for="City"><?php echo esc_html__("Time Window", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="TimeWindow_<?php echo $i; ?>" id="TimeWindow_<?php echo $i; ?>">
                                <option value="Delivery_08h00_10h00" <?php selected( $TimeWindow, 'Delivery_08h00_10h00' ) ?>><?php echo esc_html__("08h00 - 10h00", "ctt-expresso-para-woocommerce"); ?></option>
                                <option value="Delivery_10h00_13h00" <?php selected( $TimeWindow, 'Delivery_10h00_13h00' ) ?>><?php echo esc_html__("10h00 - 13h00", "ctt-expresso-para-woocommerce"); ?></option>
                                <option value="Delivery_13h00_16h00" <?php selected( $TimeWindow, 'Delivery_13h00_16h00' ) ?>><?php echo esc_html__("13h00 - 16h00", "ctt-expresso-para-woocommerce"); ?></option>
                                <option value="Delivery_16h00_19h00" <?php selected( $TimeWindow, 'Delivery_16h00_19h00' ) ?>><?php echo esc_html__("16h00 - 19h00", "ctt-expresso-para-woocommerce"); ?></option>
                                <option value="Delivery_19h00_22h00" <?php selected( $TimeWindow, 'Delivery_19h00_22h00' ) ?>><?php echo esc_html__("19h00 - 22h00", "ctt-expresso-para-woocommerce"); ?></option>
                                <option value="DeliverySaturday_10h00_14h00" <?php selected( $TimeWindow, 'DeliverySaturday_10h00_14h00' ) ?>><?php echo esc_html__("Sábado 10h00 - 14h00 (apenas válido para envios com entrega ao sábado)", "ctt-expresso-para-woocommerce"); ?></option>
                            </select>
                        </p>
                    </span>
                </div>
                <div class="extra_information_fields">
                    <label for="DeliveryDate"><?php echo esc_html__("Data do Agendamento", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" id="DeliveryDate_<?php echo $i; ?>" class="DatePicker" placeholder="ex: 2020-07-01" name="DeliveryDate_<?php echo $i; ?>" value="<?php echo $DeliveryDate; ?>"></p>
                    </span>
                </div>
            </div>

            <div class="CertainDay special_service_extra">
                <?php 
                $CertainDate = '';
                if(!empty($SpecialServices[$i]['CertainDate'])){
                    $CertainDate = esc_html($SpecialServices[$i]['CertainDate']);
                }
                ?>
                <h3><?php echo esc_html__("Certain Day", "ctt-expresso-para-woocommerce") ?></h3>
                <div class="extra_information_fields">
                    <label for="CertainDate"><?php echo esc_html__("Certain Day", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" id="CertainDate_<?php echo $i; ?>" class="DatePicker" placeholder="ex: 2020-07-01" name="CertainDate_<?php echo $i; ?>" value="<?php echo $CertainDate; ?>"></p>
                    </span>
                </div>
            </div>

        </div>
    </div>
    <?php 
    return ob_get_clean();
}



function cepw_add_more_SpecialService_request(){
    $post_id = $_POST['SpecialServices_order'];
    $SubProductId = $_POST['SubProductId'];
    $SpecialServices_count = $_POST['SpecialServices_count'];
    $SpecialServices_Use_array = json_decode(stripslashes($_POST['SpecialServices_Use']));
    $SpecialServices_Use = array();
    foreach ($SpecialServices_Use_array as $SpecialService) {
        $SpecialServices_Use[] = $SpecialService->SpecialService;
    }

    $response = cepw_add_SpecialService($post_id, $SubProductId,$SpecialServices_count,null,$SpecialServices_Use);

    wp_send_json(array('response' => $response));
}
add_action( 'wp_ajax_cepw_add_more_SpecialService', 'cepw_add_more_SpecialService_request' );
add_action( 'wp_ajax_nopriv_cepw_add_more_SpecialService', 'cepw_add_more_SpecialService_request' );



function cepw_change_subproductId_request(){
    $post_id = $_POST['SpecialServices_order'];
    wp_send_json_success('change');
}
add_action( 'wp_ajax_cepw_change_subproductId', 'cepw_change_subproductId_request' );
add_action( 'wp_ajax_nopriv_cepw_change_subproductId', 'cepw_change_subproductId_request' );




function cepw_extraInformation($post)
{
    $order = new WC_Order($post);
    $order_id = $order->get_id();
    $orderStatus = $order->get_status();
    
    $order_meta = apply_filters( 'cepw_order_meta', $order );
    //
    $cepw_option = $order_meta->cepw_option;
    $againstreimbursement = $order_meta->againstreimbursement;


    $SubProductId = cepw_get_subproduct_id($cepw_option);

    $shipping_address = $order->get_address('shipping');
    $receiverCountry = $shipping_address['country'];
    // Nonce field to validate form request came from current site
    wp_nonce_field( basename( __FILE__ ), 'cepw_extraInformation' );
    
    //Special Service
    $number_special_services = get_post_meta($order_id,'SpecialServices_count',true);


    if(empty($number_special_services)){
        if(($SubProductId == 'EMSF056.01' || $SubProductId == 'EMSF057.01') && $againstreimbursement == 'true'){
            $number_special_services = 2;
        }else{
            $number_special_services = 1;
        }
    }

    $SpecialServices = get_post_meta($order_id,'SpecialServices',true);
    ?>
    <div class="cepw_SpecialServices cepw_extraInformation_form">
        <div class="preloader"><div class="loader"></div></div>
        <div class="SpecialServicesList">
            <input type="hidden" name="SpecialServices_count" value="<?php echo $number_special_services; ?>">
            <input type="hidden" name="SpecialServices_order" value="<?php echo $order_id; ?>">
            <input type="hidden" name="SubProductId" value="<?php echo $SubProductId; ?>">
            <?php 
            for ($i=0; $i < $number_special_services; $i++) { 
               echo cepw_add_SpecialService($order_id, $SubProductId,$i,$SpecialServices);
            }?>
        </div>
        <a href="#" class="add_more_special_service">
            <?php echo esc_html__("Add More", "ctt-expresso-para-woocommerce"); ?>
            <span><i class="fas fa-plus"></i></span>
        </a>
    </div>
    <?php
    //13 && 19 && 13 Multiplo && 19 Multiplo
    if($SubProductId == 'EMSF001.01' || $SubProductId == 'ENCF005.01' || $SubProductId == 'EMSF028.01' || $SubProductId == 'EMSF010.01' ){ ?>
        <div class="change_subproductId cepw_extraInformation_form">

            <h3><strong><?php echo esc_html__("Change the SubProduct:", "ctt-expresso-para-woocommerce") ?></strong></h3>
            <div class="forminp forminp-text">
                <div class="extra_information_fields">
                    <label for="subproductid"><?php echo esc_html__("Subproduct", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="change_subproductId">
                            <?php if($SubProductId == 'EMSF001.01' || $SubProductId == 'EMSF028.01'): ?>
                                <option value="thirteen" <?php selected( $SubProductId, 'EMSF001.01' ); ?> ><?php echo esc_html__("13", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="thirteen_multi" <?php selected( $SubProductId, 'EMSF028.01' ); ?>><?php echo esc_html__("13 Múltiplo", "ctt-expresso-para-woocommerce") ?></option>
                            <?php else:?> 
                                <option value="nineteen" <?php selected( $SubProductId, 'ENCF005.01' ); ?>><?php echo esc_html__("19", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="nineteen_multi" <?php selected( $SubProductId, 'EMSF010.01' ); ?>><?php echo esc_html__("19 Múltiplo", "ctt-expresso-para-woocommerce") ?></option>
                            <?php endif;?>
                            </select>
                        </p>
                    </span>
                </div>
            </div>
        </div>
    <?php }

    //13 Multiplo && 19 Multiplo && 13 && 19 && Rede Shoping  
    if($SubProductId == 'EMSF028.01' || $SubProductId == 'EMSF010.01' || $SubProductId == 'EMSF001.01' || $SubProductId == 'ENCF005.01' || $SubProductId == 'EMSF059.01'){
        $Quantity = (get_post_meta($order_id,'Quantity',true) ?: 1);
        $hide = ($SubProductId == 'EMSF001.01' || $SubProductId == 'ENCF005.01') ? 'hide': 'show';
        ?>
        <div class="Quantity cepw_extraInformation_form <?php echo $hide; ?>">
            <h3><strong><?php echo esc_html__("Remessa", "ctt-expresso-para-woocommerce") ?>:</strong></h3>
            <div class="forminp forminp-text">
                <div class="extra_information_fields">
                    <label for="Quantity"><?php echo esc_html__("Quantity", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="number" id="Quantity" name="Quantity" value="<?php echo $Quantity; ?>"></p>
                    </span>
                </div>
            </div>
        </div>
        <?php
    }
    //Cargo
    if($SubProductId == 'EMSF015.01'){
        $PartialDelivery = esc_html(get_post_meta($order_id,'PartialDelivery',true));
        $SchedulingData = esc_html(get_post_meta($order_id,'SchedulingData',true));
        $SchedulingHour = esc_html(get_post_meta($order_id,'SchedulingHour',true));
        ?>
        <div class="CargoInfo cepw_extraInformation_form">
            <h3><strong><?php echo esc_html__("Cargo Info", "ctt-expresso-para-woocommerce") ?>:</strong></h3>
            <div class="forminp forminp-text">
                <div class="extra_information_fields">
                    <label for="PartialDelivery"><?php echo esc_html__("Tem entrega parcial?", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="checkbox" id="PartialDelivery" name="PartialDelivery" value="true" <?php checked( $PartialDelivery, 'true'); ?>></p>
                    </span>
                </div>
                <div class="extra_information_fields">
                    <label for="SchedulingData"><?php echo esc_html__("Data do Agendamento", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" id="SchedulingData" class="DatePicker" placeholder="ex: 2020-01-01" name="SchedulingData" value="<?php echo $SchedulingData; ?>"></p>
                    </span>
                </div>
                <div class="extra_information_fields">
                    <label for="SchedulingHour"><?php echo esc_html__("Hora do Agendamento", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p><input type="text" id="SchedulingHour" class="TimePicker" placeholder="ex: 09:00" name="SchedulingHour" value="<?php echo $SchedulingHour; ?>"></p>
                    </span>
                </div>
            </div>

        </div>
    <?php 
    }
    //EMS Economy && EMS Internacional
    if($receiverCountry != 'PT'):
        $ExportTypeValues = esc_html(get_post_meta($order_id,'ExportTypeValues',true));
        $UPUCodeValues = esc_html(get_post_meta($order_id,'UPUCodeValues',true));
        ?>
        <!-- ExportTypeValues  -->
        <div class="ExportTypeValues cepw_extraInformation_form">
            <h3><strong><?php echo esc_html__("Export Type", "ctt-expresso-para-woocommerce") ?>:</strong></h3>
            <div class="forminp forminp-text">
                <div class="extra_information_fields">
                    <label for="ExportTypeValues"><?php echo esc_html__("Export Type", "ctt-expresso-para-woocommerce") ?><span class="required">*</span></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="ExportTypeValues" id="ExportTypeValues">
                                <option id="Permanent" value="Permanent" <?php selected( $ExportTypeValues, 'Permanent' ); ?>><?php echo esc_html__("Permanent", "ctt-expresso-para-woocommerce") ?></option>
                                <option id="TemporaryPassiveImprovement" value="TemporaryPassiveImprovement" <?php selected( $ExportTypeValues, 'TemporaryPassiveImprovement' ); ?> ><?php echo esc_html__("Temporary Passive Improvement", "ctt-expresso-para-woocommerce") ?></option>
                                <option id="TemporaryExhibition" alue="TemporaryExhibition" <?php selected( $ExportTypeValues, 'TemporaryExhibition' ); ?> ><?php echo esc_html__("Temporary Exhibition", "ctt-expresso-para-woocommerce") ?></option>
                            </select>
                        </p>

                    </span>
                </div>

            </div>
        </div>
        <!-- UPUCodeValues  -->
        <div class="UPUCodeValues cepw_extraInformation_form">
            <h3><strong><?php echo esc_html__("UPU Code", "ctt-expresso-para-woocommerce") ?>:</strong></h3>
            <div class="forminp forminp-text">
                <div class="extra_information_fields">
                    <label for="Samples"><?php echo esc_html__("Samples", "ctt-expresso-para-woocommerce") ?><span class="required">*</span></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="UPUCodeValues" id="UPUCodeValues">
                                <option id="Samples" value="Samples" <?php selected( $UPUCodeValues, 'Samples' ); ?>> <?php echo esc_html__("Samples", "ctt-expresso-para-woocommerce") ?></option>
                                <option id="Documents" value="Documents" <?php selected( $UPUCodeValues, 'Documents' ); ?>> <?php echo esc_html__("Documents", "ctt-expresso-para-woocommerce") ?></option>
                                <option id="Goods" value="Goods" <?php selected( $UPUCodeValues, 'Goods' ); ?>> <?php echo esc_html__("Goods", "ctt-expresso-para-woocommerce") ?></option>
                                <option id="Others" value="Others" <?php selected( $UPUCodeValues, 'Others' ); ?>> <?php echo esc_html__("Others", "ctt-expresso-para-woocommerce") ?></option>
                                <option id="Devolution" value="Devolution" <?php selected( $UPUCodeValues, 'Devolution' ); ?>> <?php echo esc_html__("Devolution", "ctt-expresso-para-woocommerce") ?></option>
                            </select>
                        </p>
                    </span>
                </div>
            </div>
        </div>
    <?php endif;
    //EMS Internacional
    //Outsite EU
    if(!in_array($receiverCountry, WC()->countries->get_european_union_countries())){ 
        $VATExportDeclaration = get_post_meta($order_id,'VATExportDeclaration',true);
        $SachetDocumentation = get_post_meta($order_id,'SachetDocumentation',true);
        $NonDeliveryCase = get_post_meta($order_id,'NonDeliveryCase',true);
        $VATRate = get_post_meta($order_id,'VATRate',true);

        //Optional
        $ReceiverTIN = esc_html(get_post_meta($order_id,'ReceiverTIN',true));
        $Height = esc_html(get_post_meta($order_id,'Height',true));
        $Length = esc_html(get_post_meta($order_id,'Length',true));
        $Width = esc_html(get_post_meta($order_id,'Width',true));
        $ClientCustomsCode = esc_html(get_post_meta($order_id,'ClientCustomsCode',true));
        $SenderEmail = esc_html(get_post_meta($order_id,'SenderEmail',true));
        if(empty($SenderEmail)){
            $SenderEmail = esc_html(get_bloginfo('admin_email'));
        }
        $ComercialInvoice = esc_html(get_post_meta($order_id,'ComercialInvoice',true));
        $ExportLicense = esc_html(get_post_meta($order_id,'ExportLicense',true));
        $OriginCertificateNumber = esc_html(get_post_meta($order_id,'OriginCertificateNumber',true));
        $Comments = esc_html(get_post_meta($order_id,'Comments',true));
        $InsurancePremium = esc_html(get_post_meta($order_id,'InsurancePremium',true));
        $InsuranceValue = esc_html(get_post_meta($order_id,'InsuranceValue',true));
        $ServiceValue = esc_html(get_post_meta($order_id,'ServiceValue',true));
        ?>
        <!-- CustomsData  -->
        <div class="CustomsData cepw_extraInformation_form">
            <h3><strong><?php echo esc_html__("Customs data", "ctt-expresso-para-woocommerce") ?>:</strong></h3>
            <div class="forminp forminp-text">

                <div class="extra_information_fields">
                    <label for="ReceiverTIN"><?php echo esc_html__("Receiver TIN", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="ReceiverTIN" value="<?php echo $ReceiverTIN; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="Height"><?php echo esc_html__("Height", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="Height" value="<?php echo $Height; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="Length"><?php echo esc_html__("Length", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="Length" value="<?php echo $Length; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="Width"><?php echo esc_html__("Width", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="Width" value="<?php echo $Width; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="ClientCustomsCode"><?php echo esc_html__("Client Customs Code", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="10" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="ClientCustomsCode" value="<?php echo $ClientCustomsCode; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="VATExportDeclaration"><?php echo esc_html__("VAT Export Declaration", "ctt-expresso-para-woocommerce") ?><span class="required">*</span></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="checkbox" name="VATExportDeclaration" value="true" <?php checked( $VATExportDeclaration, 'true'); ?>>
                        </p>
                    </span>
                </div>
                <div class="extra_information_fields">
                    <label for="SachetDocumentation"><?php echo esc_html__("Sachet Documentation", "ctt-expresso-para-woocommerce") ?><span class="required">*</span></label>
                    <span class="extra_information_fields_input">
                        <p><input type="checkbox" name="SachetDocumentation" value="true" <?php checked( $SachetDocumentation, 'true'); ?>></p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="NonDeliveryCase"><?php echo esc_html__("Non Delivery Case", "ctt-expresso-para-woocommerce") ?><span class="required">*</span></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <select name="NonDeliveryCase" id="NonDeliveryCase">
                                <option value="GiveBack" id="GiveBack" <?php selected( $NonDeliveryCase, 'GiveBack' ); ?> ><?php echo esc_html__("Give Back", "ctt-expresso-para-woocommerce") ?></option>
                                <option value="ToAbandon" id="ToAbandon" <?php selected( $NonDeliveryCase, 'ToAbandon' ); ?> ><?php echo esc_html__("To Abandon", "ctt-expresso-para-woocommerce") ?></option>
                            </select>
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="SenderEmail"><?php echo esc_html__("Sender Email", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="email" maxlength="200" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="SenderEmail" value="<?php echo $SenderEmail; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="ComercialInvoice"><?php echo esc_html__("Comercial Invoice", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="ComercialInvoice" value="<?php echo $ComercialInvoice; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="ExportLicense"><?php echo esc_html__("Export License", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="ExportLicense" value="<?php echo $ExportLicense; ?>">
                        </p>
                    </span>
                </div>
                

                <div class="extra_information_fields">
                    <label for="OriginCertificateNumber"><?php echo esc_html__("Origin Certificate Number", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="OriginCertificateNumber" value="<?php echo $OriginCertificateNumber; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="Comments"><?php echo esc_html__("Comments", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="text" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="Comments" value="<?php echo $Comments; ?>">
                        </p>
                    </span>
                </div>


                <div class="extra_information_fields">
                    <label for="VATRate"><?php echo esc_html__("VAT Rate", "ctt-expresso-para-woocommerce") ?><span class="required">*</span></label>
                    <span class="extra_information_fields_input">
                        <p><input type="checkbox" name="VATRate" value="true" <?php checked( $VATRate, 'true'); ?>></p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="InsurancePremium"><?php echo esc_html__("Insurance Premium", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="number" min="0" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="InsurancePremium" value="<?php echo $InsurancePremium; ?>">
                        </p>
                    </span>
                </div>


                <div class="extra_information_fields">
                    <label for="InsuranceValue"><?php echo esc_html__("Insurance Value", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="number" min="0" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="InsuranceValue" value="<?php echo $InsuranceValue; ?>">
                        </p>
                    </span>
                </div>

                <div class="extra_information_fields">
                    <label for="ServiceValue"><?php echo esc_html__("Service Value", "ctt-expresso-para-woocommerce") ?></label>
                    <span class="extra_information_fields_input">
                        <p>
                            <input type="number" min="0" maxlength="50" placeholder="<?php echo esc_html__("(Optional)", "ctt-expresso-para-woocommerce") ?>" name="ServiceValue" value="<?php echo $ServiceValue; ?>">
                        </p>
                    </span>
                </div>

            </div>
        </div>
    <?php } 
}



function cepw_get_SpecialServices_value($SpecialService,$value){
    $return_value = 0;
    switch ($SpecialService) {
        case 'PostalObject':
            $return_value = $value;
            break;
        case 'AgainstReimbursement':
            $return_value = $value;
            break;
        case 'NominativeCheck':
            $return_value = $value;
            break;  
    }


    return $return_value;
}

/**
 * Check if SpecialServices Array contain Special Service */

function cepw_in_SpecialServices($array, $key, $val) {
    foreach ($array as $item)
        if (isset($item[$key]) && $item[$key] == $val)
            return true;
    return false;
}


/**
 * Save the Extra SubproductID Information */

function cepw_meta_save($post_id) {
    $is_autosave = wp_is_post_autosave($post_id);
    $is_revision = wp_is_post_revision($post_id);

    if ($is_autosave || $is_revision) {
        return;
    }

    if (isset($_POST['cepw_extraInformation']) && wp_verify_nonce($_POST['cepw_extraInformation'], basename(__FILE__))) {

        $order = new WC_Order($post_id);
        $order_total = $order->get_total();

        // SPECIAL SERVICES
        if (isset($_POST['SpecialServices_count'])) {
            $SpecialServices = array();
            $count = intval($_POST['SpecialServices_count']) + 1;
            for ($i = 0; $i < $count; $i++) {
                if (isset($_POST['SpecialServicesTypes_' . $i])) {
                    $SpecialService_Value = sanitize_text_field($_POST['SpecialServicesTypes_' . $i]);
                    $SpecialService = array(
                        'SpecialServiceType' => $SpecialService_Value,
                    );

                    /*** Values inserted in cepw_extraInformation ****/
                    if ($SpecialService_Value == 'PostalObject') {
                        $value = floatval($_POST['PostalObjectValue']);
                    }
                    if ($SpecialService_Value == 'AgainstReimbursement') {
                        $value = $order_total;
                    }
                    if ($SpecialService_Value == 'NominativeCheck') {
                        $value = floatval($_POST['NominativeCheckValue']);
                    }
                    if ($SpecialService_Value == 'SpecialInsurance') {
                        $value = floatval($_POST['SpecialInsuranceValue']);
                    }

                    if (!empty($value)) {
                        $SpecialService['Value'] = cepw_get_SpecialServices_value($SpecialService_Value, $value);
                    }

                    if ($SpecialService_Value == 'ReturnDocumentSigned') {
                        $DDA = array();
                        if (isset($_POST['ShipperInstructions_' . $i])) {
                            $DDA['ShipperInstructions'] = sanitize_text_field($_POST['ShipperInstructions_' . $i]);
                        }
                        $SpecialService['DDA'] = $DDA;
                    } elseif ($SpecialService_Value == 'MultipleHomeDelivery') {
                        $MultipleHomeDelivery = array();
                        if (isset($_POST['AttemptsNumber_' . $i])) {
                            $MultipleHomeDelivery['AttemptsNumber'] = intval($_POST['AttemptsNumber_' . $i]);
                        }
                        if (isset($_POST['InNonDeliveryCase_' . $i])) {
                            $MultipleHomeDelivery['InNonDeliveryCase'] = sanitize_text_field($_POST['InNonDeliveryCase_' . $i]);
                            if ($_POST['InNonDeliveryCase_' . $i] == 'SendToAddress') {
                                $SecondDeliveryAddress = array();
                                if (isset($_POST['Type_' . $i])) {
                                    $SecondDeliveryAddress['Type'] = sanitize_text_field($_POST['Type_' . $i]);
                                }
                                if (isset($_POST['Name_' . $i])) {
                                    $SecondDeliveryAddress['Name'] = sanitize_text_field($_POST['Name_' . $i]);
                                }
                                if (isset($_POST['ContactName_' . $i])) {
                                    $SecondDeliveryAddress['ContactName'] = sanitize_text_field($_POST['ContactName_' . $i]);
                                }
                                if (isset($_POST['Address_' . $i])) {
                                    $SecondDeliveryAddress['Address'] = sanitize_text_field($_POST['Address_' . $i]);
                                }
                                if (isset($_POST['PTZipCode3_' . $i])) {
                                    $SecondDeliveryAddress['PTZipCode3'] = sanitize_text_field($_POST['PTZipCode3_' . $i]);
                                }
                                if (isset($_POST['PTZipCode4_' . $i])) {
                                    $SecondDeliveryAddress['PTZipCode4'] = sanitize_text_field($_POST['PTZipCode4_' . $i]);
                                }
                                if (isset($_POST['CodeLocation_' . $i])) {
                                    $SecondDeliveryAddress['CodeLocation'] = sanitize_text_field($_POST['CodeLocation_' . $i]);
                                }
                                if (isset($_POST['City_' . $i])) {
                                    $SecondDeliveryAddress['City'] = sanitize_text_field($_POST['City_' . $i]);
                                }
                                $SecondDeliveryAddress['Country'] = 'PT';
                                $MultipleHomeDelivery['SecondDeliveryAddress'] = $SecondDeliveryAddress;
                            }
                        }
                        $SpecialService['MultipleHomeDelivery'] = $MultipleHomeDelivery;
                    } elseif ($SpecialService_Value == 'TimeWindow') {
                        if (isset($_POST['TimeWindow_' . $i])) {
                            $TimeWindow = array();
                            if (isset($_POST['TimeWindow_' . $i])) {
                                $TimeWindow['TimeWindow'] = sanitize_text_field($_POST['TimeWindow_' . $i]);
                            }
                            if (isset($_POST['DeliveryDate_' . $i])) {
                                $TimeWindow['DeliveryDate'] = sanitize_text_field($_POST['DeliveryDate_' . $i]);
                            }
                            $SpecialService['TimeWindow'] = $TimeWindow;
                        }
                    } elseif ($SpecialService_Value == 'CertainDay') {
                        if (isset($_POST['CertainDate_' . $i])) {
                            $CertainDate = sanitize_text_field($_POST['CertainDate_' . $i]);
                        }
                        $SpecialService['CertainDate'] = $CertainDate;
                    }
                    array_push($SpecialServices, $SpecialService);
                }
            }
            if (!empty($SpecialServices)) {
                update_post_meta($post_id, 'SpecialServices', $SpecialServices);
            }
            if (count($SpecialServices) > 0) {
                update_post_meta($post_id, 'SpecialServices_count', count($SpecialServices));
            }
        }

        // Quantity
        if (isset($_POST['Quantity'])) {
            update_post_meta($post_id, 'Quantity', intval($_POST['Quantity']));
        }

        // change_subproductId
        if (isset($_POST['change_subproductId'])) {
            update_post_meta($post_id, 'cepw_option', sanitize_text_field($_POST['change_subproductId']));
        }

        // Cargo
        if (isset($_POST['PartialDelivery'])) {
            update_post_meta($post_id, 'PartialDelivery', sanitize_text_field($_POST['PartialDelivery']));
        }
        if (isset($_POST['SchedulingData'])) {
            update_post_meta($post_id, 'SchedulingData', sanitize_text_field($_POST['SchedulingData']));
        }
        if (isset($_POST['SchedulingHour'])) {
            update_post_meta($post_id, 'SchedulingHour', sanitize_text_field($_POST['SchedulingHour']));
        }

        // EMS
        if (isset($_POST['ExportTypeValues'])) {
            update_post_meta($post_id, 'ExportTypeValues', sanitize_text_field($_POST['ExportTypeValues']));
        }
        if (isset($_POST['UPUCodeValues'])) {
            update_post_meta($post_id, 'UPUCodeValues', sanitize_text_field($_POST['UPUCodeValues']));
        }
        if (isset($_POST['VATExportDeclaration'])) {
            update_post_meta($post_id, 'VATExportDeclaration', sanitize_text_field($_POST['VATExportDeclaration']));
        } else {
            update_post_meta($post_id, 'VATExportDeclaration', 'false');
        }
        if (isset($_POST['SachetDocumentation'])) {
            update_post_meta($post_id, 'SachetDocumentation', sanitize_text_field($_POST['SachetDocumentation']));
        } else {
            update_post_meta($post_id, 'SachetDocumentation', 'false');
        }
        if (isset($_POST['NonDeliveryCase'])) {
            update_post_meta($post_id, 'NonDeliveryCase', sanitize_text_field($_POST['NonDeliveryCase']));
        }
        if (isset($_POST['VATRate'])) {
            update_post_meta($post_id, 'VATRate', sanitize_text_field($_POST['VATRate']));
        } else {
            update_post_meta($post_id, 'VATRate', 'false');
        }
        if (isset($_POST['ReceiverTIN'])) {
            update_post_meta($post_id, 'ReceiverTIN', sanitize_text_field($_POST['ReceiverTIN']));
        }
        if (isset($_POST['Height'])) {
            update_post_meta($post_id, 'Height', intval($_POST['Height']));
        }
        if (isset($_POST['Length'])) {
            update_post_meta($post_id, 'Length', intval($_POST['Length']));
        }
        if (isset($_POST['Width'])) {
            update_post_meta($post_id, 'Width', intval($_POST['Width']));
        }
        if (isset($_POST['ClientCustomsCode'])) {
            update_post_meta($post_id, 'ClientCustomsCode', sanitize_text_field($_POST['ClientCustomsCode']));
        }
        if (isset($_POST['SenderEmail'])) {
            update_post_meta($post_id, 'SenderEmail', sanitize_email($_POST['SenderEmail']));
        }
        if (isset($_POST['ComercialInvoice'])) {
            update_post_meta($post_id, 'ComercialInvoice', sanitize_text_field($_POST['ComercialInvoice']));
        }
        if (isset($_POST['ExportLicense'])) {
            update_post_meta($post_id, 'ExportLicense', sanitize_text_field($_POST['ExportLicense']));
        }
        if (isset($_POST['OriginCertificateNumber'])) {
            update_post_meta($post_id, 'OriginCertificateNumber', sanitize_text_field($_POST['OriginCertificateNumber']));
        }
        if (isset($_POST['Comments'])) {
            update_post_meta($post_id, 'Comments', sanitize_textarea_field($_POST['Comments']));
        }
        if (isset($_POST['OriginCertificateNumber'])) {
            update_post_meta($post_id, 'OriginCertificateNumber', sanitize_text_field($_POST['OriginCertificateNumber']));
        }
        if (isset($_POST['InsurancePremium'])) {
            update_post_meta($post_id, 'InsurancePremium', floatval($_POST['InsurancePremium']));
        }
        if (isset($_POST['InsuranceValue'])) {
            update_post_meta($post_id, 'InsuranceValue', floatval($_POST['InsuranceValue']));
        }
        if (isset($_POST['ServiceValue'])) {
            update_post_meta($post_id, 'ServiceValue', floatval($_POST['ServiceValue']));
        }
    }
}

add_action('save_post', 'cepw_meta_save', 1, 1);


function cepw_gen_uuid() {
    return sprintf( '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        // 32 bits for "time_low"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ),

        // 16 bits for "time_mid"
        mt_rand( 0, 0xffff ),

        // 16 bits for "time_hi_and_version",
        // four most significant bits holds version number 4
        mt_rand( 0, 0x0fff ) | 0x4000,

        // 16 bits, 8 bits for "clk_seq_hi_res",
        // 8 bits for "clk_seq_low",
        // two most significant bits holds zero and one for variant DCE1.1
        mt_rand( 0, 0x3fff ) | 0x8000,

        // 48 bits for "node"
        mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff ), mt_rand( 0, 0xffff )
    );
}


/**
 * Check if WooCommerce is active
 **/
// Get active network plugins - "Stolen" from Portugal Chronopost Pickup network for WooCommerce
function cepw_active_nw_plugins() {
    if ( !is_multisite() )
        return false;
    $cepw_activePlugins = ( get_site_option( 'active_sitewide_plugins' ) ) ? array_keys( get_site_option( 'active_sitewide_plugins' ) ) : array();
    return $cepw_activePlugins;
}
if ( in_array( 'woocommerce/woocommerce.php', ( array ) get_option( 'active_plugins' ) ) || in_array( 'woocommerce/woocommerce.php', ( array ) cepw_active_nw_plugins() ) ) {
    //Init everything
    add_action( 'plugins_loaded', 'cepw_init' );
    add_action( 'wp_loaded', 'cepw_fields_filters' );
    function cepw_init() {
      //Only on WooCommerce >= 2.6
      if ( version_compare( WC_VERSION, '2.6', '>=' ) ) {

        add_filter( 'woocommerce_settings_tabs_array', 'cepw_add_settings_tab', 50 );

        //WooCommerce Table Rate Shipping - http://bolderelements.net/plugins/table-rate-shipping-woocommerce/ - Not available at plugins_loaded time
        add_filter( 'woocommerce_shipping_instance_form_fields_betrs_shipping', 'cepw_woocommerce_shipping_instance_form_fields_betrs_shipping' );
        //WooCommerce Advanced Shipping - https://codecanyon.net/item/woocommerce-advanced-shipping/8634573 - Not available at plugins_loaded time
        add_filter( 'was_after_meta_box_settings', 'cepw_was_after_meta_box_settings' );

        //Add to checkout
        add_action( 'woocommerce_review_order_before_payment', 'cepw_woocommerce_review_order_before_payment' );

        //Save order meta
        // add_action( 'woocommerce_checkout_update_order_meta', 'cepw_save_extra_order_meta' );
        add_action('woocommerce_checkout_create_order', 'cepw_save_extra_order_meta', 20, 2);



        //Show order meta on order screen and order preview
        add_action( 'woocommerce_admin_order_data_after_shipping_address', 'cepw_woocommerce_admin_order_data_after_shipping_address' );
        add_action( 'woocommerce_admin_order_preview_end', 'cepw_woocommerce_admin_order_preview_end' );
        add_filter( 'woocommerce_admin_order_preview_get_order_details', 'cepw_woocommerce_admin_order_preview_get_order_details', 10, 2 );
      }
    }
}


//Add fields to settings
function cepw_fields_filters() {
    //Avoid fatal errors on some weird scenarios
    if ( is_null( WC()->countries ) ) WC()->countries = new WC_Countries();
    //Load our filters
    foreach ( WC()->shipping()->get_shipping_methods() as $method ) { //https://woocommerce.wp-a2z.org/oik_api/wc_shippingget_shipping_methods/
      if ( ! $method->supports( 'shipping-zones' ) ) {
        continue;
      }
      switch ( $method->id ) {
        // Flexible Shipping for WooCommerce - https://wordpress.org/plugins/flexible-shipping/
        case 'flexible_shipping':
        case 'flexible_shipping_single':
          add_filter( 'flexible_shipping_method_settings', 'cepw_woocommerce_shipping_instance_form_fields_flexible_shipping', 999, 2 );
          add_filter( 'flexible_shipping_process_admin_options', 'cepw_woocommerce_shipping_instance_form_fields_flexible_shipping_save',999 );
          break;
        // The WooCommerce or other standard methods that implement the 'woocommerce_shipping_instance_form_fields_' filter
        default:
          add_filter( 'woocommerce_shipping_instance_form_fields_'.$method->id, 'cepw_woocommerce_shipping_instance_form_fields' );
          break;
      }
    }
}
// Get Options base on configuration 
function cepw_get_option_fields(){
    $tomorrow = get_option('_ShippingOptionsTomorrow');
    $thirteen = get_option('_ShippingOptionsThirteenHours');
    $forty_eight = get_option('_ShippingOptionsFortyEightHours');
    $two_days = get_option('_ShippingOptionsTwoDays');
    $ten = get_option('_ShippingOptionsTen');
    $thirteen_multi = get_option('_ShippingOptionsThirteen_Multi');
    $nineteen = get_option('_ShippingOptionsNineteen');
    $nineteen_multi = get_option('_ShippingOptionsNineteen_Multi');
    $cargo = get_option('_ShippingOptionsCargo');
    $easy_return_24 = get_option('_ShippingOptionsEasyReturn24');
    $easy_return_48 = get_option('_ShippingOptionsEasyReturn48');
    $ems_economy = get_option('_ShippingOptionsEMS_Economy');
    $ems_international = get_option('_ShippingOptionsEMS_International');
    $rede_shopping = get_option('_ShippingOptionsRedeShopping');

    $options[''] = __( 'No', 'ctt-expresso-para-woocommerce' );
    if(!empty($tomorrow)){ $options['tomorrow'] = __("Para Amanhã", "ctt-expresso-para-woocommerce");  }
    if(!empty($thirteen)){ $options['thirteen'] = __("13", "ctt-expresso-para-woocommerce"); }
    if(!empty($forty_eight)){ $options['forty_eight'] = __("48", "ctt-expresso-para-woocommerce");  }
    if(!empty($two_days)){ $options['two_days'] = __("Em 2 dias", "ctt-expresso-para-woocommerce"); }
    if(!empty($ten)){ $options['ten'] = __("10", "ctt-expresso-para-woocommerce");  }
    if(!empty($thirteen_multi)){ $options['thirteen_multi'] = __("13 Múltiplo", "ctt-expresso-para-woocommerce"); }
    if(!empty($nineteen)){ $options['nineteen'] = __("19", "ctt-expresso-para-woocommerce");  }
    if(!empty($nineteen_multi)){ $options['nineteen_multi'] = __("19 Múltiplo", "ctt-expresso-para-woocommerce"); }
    if(!empty($cargo)){ $options['cargo'] = __("Cargo", "ctt-expresso-para-woocommerce");  }
    if(!empty($easy_return_24)){ $options['easy_return_24'] = __("Easy Return 24", "ctt-expresso-para-woocommerce"); }
    if(!empty($easy_return_48)){ $options['easy_return_48'] = __("Easy Return 48", "ctt-expresso-para-woocommerce");  }
    if(!empty($ems_economy)){ $options['ems_economy'] = __("EMS Economy", "ctt-expresso-para-woocommerce"); }
    if(!empty($ems_international)){ $options['ems_international'] = __("EMS International", "ctt-expresso-para-woocommerce");  }
    if(!empty($rede_shopping)){ $options['rede_shopping'] = __("Rede Shopping", "ctt-expresso-para-woocommerce"); }
    return $options;
}

//Field on each shipping method
function cepw_woocommerce_shipping_instance_form_fields( $settings ) {
    $options = cepw_get_option_fields();
    if ( !is_array( $settings ) ) $settings = array();
    $settings['cepw'] = array( 
        'title'         => __( 'CTT Expresso', 'ctt-expresso-para-woocommerce' ),
        'type'          => 'select',
        'description'   => __( 'Choose a CTT Expresso Shipping Option', 'ctt-expresso-para-woocommerce' ),
        'default'       => '',
        'options'       => $options,
        'desc_tip'      => true,
    );
    $settings['againstreimbursement'] = array( 
        'title'         => __( 'Against Reimbursement', 'ctt-expresso-para-woocommerce' ),
        'type'          => 'checkbox',
        'description'   => __( 'Choose a this option if you want that your shipping method have the Special Service "AgainstReimbursement"', 'ctt-expresso-para-woocommerce' ),
        'default'       => '',
        'desc_tip'      => true,
    );

    return $settings;
}

//Field on Flexible Shipping for WooCommerce - https://wordpress.org/plugins/flexible-shipping/
function cepw_woocommerce_shipping_instance_form_fields_flexible_shipping( $settings, $shipping_method ) {
    $options = cepw_get_option_fields();
    $cepw_options = array_filter(array_keys($options));
    $settings['cepw'] = array(
      'title'         => __( 'CTT Expresso', 'ctt-expresso-para-woocommerce' ),
      'type'          => 'select',
      'description' => __( 'Shows a field to select a use a CTT Expresso', 'ctt-expresso-para-woocommerce' ),
      'default'       => isset($shipping_method['cepw']) && in_array($shipping_method['cepw'],$cepw_options) ? $shipping_method['cepw'] : '',
      'options'   => $options,
      'desc_tip'    => true,
    );
    $settings['againstreimbursement'] = array( 
        'title'         => __( 'Against Reimbursement', 'ctt-expresso-para-woocommerce' ),
        'type'          => 'checkbox',
        'description'   => __( 'Choose a this option if you want that your shipping method have the Special Service "AgainstReimbursement"', 'ctt-expresso-para-woocommerce' ),
        'default'       => isset($shipping_method['againstreimbursement']) ? 'yes' : '',
        'desc_tip'      => true,
    );
    return $settings;
}
function cepw_woocommerce_shipping_instance_form_fields_flexible_shipping_save( $shipping_method ) {
  $shipping_method['cepw'] = $_POST['woocommerce_flexible_shipping_cepw'];
  $shipping_method['againstreimbursement'] = $_POST['woocommerce_flexible_shipping_againstreimbursement'];
  return $shipping_method;
}

//Field on WooCommerce Table Rate Shipping - http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
function cepw_woocommerce_shipping_instance_form_fields_betrs_shipping( $settings ) {
    $options = cepw_get_option_fields();
    $settings['general']['settings']['cepw'] = array(
        'title'         =>  __( 'CTT Expresso', 'ctt-expresso-para-woocommerce' ),
        'type'          => 'select',
        'description' => __( 'Choose a CTT Expresso Shipping Option', 'ctt-expresso-para-woocommerce' ),
        'default'       => '',
        'options'   => $options,
        'desc_tip'    => true,
    );
    $settings['general']['settings']['againstreimbursement'] = array( 
        'title'         => __( 'Against Reimbursement', 'ctt-expresso-para-woocommerce' ),
        'type'          => 'checkbox',
        'description'   => __( 'Choose a this option if you want that your shipping method have the Special Service "AgainstReimbursement"', 'ctt-expresso-para-woocommerce' ),
        'default'       => '',
        'desc_tip'      => true,
    );
    return $settings;
}

//Field on WooCommerce Advanced Shipping - https://codecanyon.net/item/woocommerce-advanced-shipping/8634573
function cepw_was_after_meta_box_settings( $settings ) {
    $options = cepw_get_option_fields();
  ?>
  <p class='was-option'>
    <label for='tax'><?php  _e( 'CTT Expresso', 'ctt-expresso-para-woocommerce' ) ?></label>
    <select name='_was_shipping_method[cepw]' style='width: 189px;'>
        <?php foreach ($options as $key => $value):?>
            <option value='<?php echo $key; ?>' <?php @selected( $settings['cepw'], $key ); ?>><?php echo $value; ?></option>
        <?php endforeach; ?>
    </select>
  </p>

  <p class='was-option'>
    <label for='tax'><?php  _e( 'Against Reimbursement', 'ctt-expresso-para-woocommerce' ) ?></label>
    <?php $value = isset($settings['againstreimbursement']) ? 'yes' : ''; ?>
    <input type="checkbox" name="_was_shipping_method[againstreimbursement]" <?php @checked( $settings['againstreimbursement'], 'true' ); ?> value="<?php echo $value; ?>" >
  </p>
  <?php
}


//Get all shipping methods available
function cepw_get_shipping_methods() {
    $shipping_methods = array();
    $cepw_options = array_filter(array_keys(cepw_get_option_fields()));
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods" );
    foreach ( $results as $method ) {
      switch ( $method->method_id ) {
        // Flexible Shipping for WooCommerce - https://wordpress.org/plugins/flexible-shipping/
        case 'flexible_shipping':
          $options = get_option( 'flexible_shipping_methods_'.$method->instance_id, array() );
          foreach ($options as $key => $fl_options) {
            if ( isset( $fl_options['cepw'] ) && in_array($fl_options['cepw'],$cepw_options)){
                $shipping_methods[] = $method->method_id.'_'.$method->instance_id.'_'.$fl_options['id'];
            }
          }
          break;
        // WooCommerce Table Rate Shipping - http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
        case 'betrs_shipping':
          $options = get_option( 'woocommerce_betrs_shipping_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['cepw'] ) && in_array( $options['cepw'],$cepw_options) ) {
            $options_instance = get_option( 'betrs_shipping_options-'.$method->instance_id, array() );
            if ( isset( $options_instance['settings'] ) && is_array( $options_instance['settings'] ) ) {
              foreach ( $options_instance['settings'] as $setting ) {
                if ( isset( $setting['option_id'] ) ) {
                    $shipping_methods[] = $method->method_id.':'.$method->instance_id.'-'.$setting['option_id'];
                }
              }
            }
          }
          break;
        // Table Rate Shipping - https://woocommerce.com/products/table-rate-shipping/
        case 'table_rate':
          $options = get_option( 'woocommerce_table_rate_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['cepw'] ) && in_array( $options['cepw'],$cepw_options) ) {
            $rates = $wpdb->get_results( sprintf( "SELECT rate_id FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d ORDER BY rate_order ASC", $method->instance_id ) );
            foreach ( $rates as $rate ) {
              $shipping_methods[] = $method->method_id.':'.$method->instance_id.':'.$rate->rate_id;
            }
          }
          break;


          
        // The WooCommerce or other standard methods that implement the 'woocommerce_shipping_instance_form_fields_' filter
        default:
          $options = get_option( 'woocommerce_'.$method->method_id.'_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['cepw'] ) && in_array( $options['cepw'],$cepw_options) ){
            $shipping_methods[] = $method->method_id.':'.$method->instance_id;
          }
          break;
      }
    }
    //WooCommerce Advanced Shipping - https://codecanyon.net/item/woocommerce-advanced-shipping/8634573
    if ( class_exists( 'WooCommerce_Advanced_Shipping' ) ) {
      $methods = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'was', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ) );
      foreach ( $methods as $method ) {
        $settings = get_post_meta( $method->ID, '_was_shipping_method', true );
        if ( is_array( $settings ) && isset( $settings['cepw'] ) && in_array( $settings['cepw'],$cepw_options) ) {
          $shipping_methods[] = (string)$method->ID;
        }
      }
    }

    //Filter and return them
    $shipping_methods = array_unique( apply_filters( 'cepw_get_shipping_methods', $shipping_methods ) );
    return $shipping_methods;
}

//Get List of all shipping method with the option cepw selected
function cepw_get_options_base_shipping_method(){
    $cepw_options = array_filter(array_keys(cepw_get_option_fields()));
    $cepw_options_return = array();
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods" );
    foreach ( $results as $method ) {
      switch ( $method->method_id ) {
        // Flexible Shipping for WooCommerce - https://wordpress.org/plugins/flexible-shipping/
        case 'flexible_shipping':
          $options = get_option( 'flexible_shipping_methods_'.$method->instance_id, array() );
          foreach ($options as $key => $fl_options) {
            if ( isset( $fl_options['cepw'] ) && in_array($fl_options['cepw'],$cepw_options)){
                $cepw_options_return[$method->method_id.'_'.$method->instance_id.'_'.$fl_options['id']] = $fl_options['cepw'];
            }
          }
          break;
        // WooCommerce Table Rate Shipping - http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
        case 'betrs_shipping':
          $options = get_option( 'woocommerce_betrs_shipping_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['cepw'] ) && in_array( $options['cepw'],$cepw_options) ) {
            $options_instance = get_option( 'betrs_shipping_options-'.$method->instance_id, array() );
            if ( isset( $options_instance['settings'] ) && is_array( $options_instance['settings'] ) ) {
              foreach ( $options_instance['settings'] as $setting ) {
                if ( isset( $setting['option_id'] ) ) {
                    $cepw_options_return[$method->method_id.':'.$method->instance_id.'-'.$setting['option_id']] = $options['cepw'];
                }
              }
            }
          }
          break;
        // Table Rate Shipping - https://woocommerce.com/products/table-rate-shipping/
        case 'table_rate':
          $options = get_option( 'woocommerce_table_rate_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['cepw'] ) && in_array( $options['cepw'],$cepw_options) ) {
            $rates = $wpdb->get_results( sprintf( "SELECT rate_id FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d ORDER BY rate_order ASC", $method->instance_id ) );
            foreach ( $rates as $rate ) {
              $cepw_options_return[$method->method_id.':'.$method->instance_id.':'.$rate->rate_id] = $options['cepw'];
            }
          }
          break;
        // The WooCommerce or other standard methods that implement the 'woocommerce_shipping_instance_form_fields_' filter
        default:
          $options = get_option( 'woocommerce_'.$method->method_id.'_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['cepw'] ) && in_array( $options['cepw'],$cepw_options) ){
            $cepw_options_return[$method->method_id.':'.$method->instance_id] = $options['cepw'];
          }
          break;
      }
    }
    //WooCommerce Advanced Shipping - https://codecanyon.net/item/woocommerce-advanced-shipping/8634573
    if ( class_exists( 'WooCommerce_Advanced_Shipping' ) ) {
      $methods = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'was', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ) );
      foreach ( $methods as $method ) {
        $settings = get_post_meta( $method->ID, '_was_shipping_method', true );
        if ( is_array( $settings ) && isset( $settings['cepw'] ) && in_array( $settings['cepw'],$cepw_options) ) {
          $cepw_options_return[(string)$method->ID] = $settings['cepw'];
        }
      }
    }

    //Filter and return them
    $cepw_options_return = apply_filters( 'cepw_get_options_base_shipping_method', $cepw_options_return );
    
    return $cepw_options_return;
}

//Get all shipping method with the option Against Reimbursement selected
function cepw_againstreimbursement_shipping_methods(){
    $againstreimbursement = array();
    global $wpdb;
    $results = $wpdb->get_results( "SELECT * FROM {$wpdb->prefix}woocommerce_shipping_zone_methods" );
    foreach ( $results as $method ) {
      switch ( $method->method_id ) {
        // Flexible Shipping for WooCommerce - https://wordpress.org/plugins/flexible-shipping/
        case 'flexible_shipping':
          $options = get_option( 'flexible_shipping_methods_'.$method->instance_id, array() );
          foreach ($options as $key => $fl_options) {
            if ( isset( $fl_options['againstreimbursement'] ) && ($fl_options['againstreimbursement'] == 'yes' || $fl_options['againstreimbursement'] == 1)){
                $againstreimbursement[] = $method->method_id.'_'.$method->instance_id.'_'.$fl_options['id'];
            }
          }
          break;
        // WooCommerce Table Rate Shipping - http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
        case 'betrs_shipping':
          $options = get_option( 'woocommerce_betrs_shipping_'.$method->instance_id.'_settings', array() );
           if ( isset( $options['againstreimbursement'] ) && ($options['againstreimbursement'] == 'yes' || $options['againstreimbursement'] == 1)){
            $options_instance = get_option( 'betrs_shipping_options-'.$method->instance_id, array() );
            if ( isset( $options_instance['settings'] ) && is_array( $options_instance['settings'] ) ) {
              foreach ( $options_instance['settings'] as $setting ) {
                if ( isset( $setting['option_id'] ) ) {
                    $againstreimbursement[] = $method->method_id.':'.$method->instance_id.'-'.$setting['option_id'];
                }
              }
            }
          }
          break;
        // Table Rate Shipping - https://woocommerce.com/products/table-rate-shipping/
        case 'table_rate':
          $options = get_option( 'woocommerce_table_rate_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['againstreimbursement'] ) && ($options['againstreimbursement'] == 'yes' || $options['againstreimbursement'] == 1)){
            $rates = $wpdb->get_results( sprintf( "SELECT rate_id FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d ORDER BY rate_order ASC", $method->instance_id ) );
            foreach ( $rates as $rate ) {
              $againstreimbursement[] = $method->method_id.':'.$method->instance_id.':'.$rate->rate_id;
            }
          }
          break;
        // The WooCommerce or other standard methods that implement the 'woocommerce_shipping_instance_form_fields_' filter
        default:
          $options = get_option( 'woocommerce_'.$method->method_id.'_'.$method->instance_id.'_settings', array() );
          if ( isset( $options['againstreimbursement'] ) && ($options['againstreimbursement'] == 'yes' || $options['againstreimbursement'] == 1)){
            $againstreimbursement[] = $method->method_id.':'.$method->instance_id;
          }
          break;
      }
    }
    //WooCommerce Advanced Shipping - https://codecanyon.net/item/woocommerce-advanced-shipping/8634573
    if ( class_exists( 'WooCommerce_Advanced_Shipping' ) ) {
        $methods = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'was', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ) );
        foreach ( $methods as $method ) {
            $settings = get_post_meta( $method->ID, '_was_shipping_method', true );
            if ( is_array( $settings ) && isset( $settings['againstreimbursement'] ) && ($settings['againstreimbursement'] == 'yes' || $settings['againstreimbursement'] == 1) ) {
                $againstreimbursement[] = (string)$method->ID;
            }
        }
    }


    //Filter and return them
    $againstreimbursement = apply_filters( 'cepw_get_options_base_shipping_method', $againstreimbursement );
    
    return $againstreimbursement;
}


//Get And Display Option in checkout
function cepw_get_display_option(){
    if(!empty($_POST['shipping_method'])){
        $options = cepw_get_options_base_shipping_method();
        $againstreimbursementlist = cepw_againstreimbursement_shipping_methods();
        if(!empty($options)){
            foreach ($options as $key => $value) {
                if($key == $_POST['shipping_method']){
                    $input  = '<div id="user_link_hidden_checkout_field">';
                    $input .= '<input type="hidden" class="input-hidden" name="cepw_option_'.$_POST['shipping_method'].'" id="cepw_option_'.$_POST['shipping_method'].'" value="'.$value.'">';
                    if(in_array($_POST['shipping_method'], $againstreimbursementlist)){
                        $input .= '<input type="hidden" class="input-hidden" name="againstreimbursement_'.$_POST['shipping_method'].'" id="againstreimbursement_'.$_POST['shipping_method'].'" value="true">';
                    }
                    $input .= '</div>';

                    wp_send_json(
                        array(
                            'value' => $value,
                            'input' => $input,
                            'post' => $_POST['shipping_method']
                        )
                    );
                }
            }
        }
    }
}
add_action( 'wp_ajax_cepw_display_option', 'cepw_get_display_option' );
add_action( 'wp_ajax_nopriv_cepw_display_option', 'cepw_get_display_option' );


//Add our DIV to the checkout
function cepw_woocommerce_review_order_before_payment() {
    $shipping_methods = cepw_get_shipping_methods();
    if ( count( $shipping_methods ) > 0 ) {?>
        <div id="cepw"></div>
    <?php
    }
}

//Save chosen cepw_option to the order
function cepw_save_extra_order_meta( $order, $data ) {

    foreach ( $_POST as $key => $value):
        if(strpos($key, 'cepw_option') !== false || strpos($key, 'againstreimbursement') !== false){
            $value = trim( sanitize_text_field( $value ) );
            $order->update_meta_data( $key, $value);
        }
    endforeach;

    if(get_option('_ShippingOptions_Print') == 'website'){
        $order->update_meta_data( 'cepw_recolhas', 'on');
    }

    $order->save();
}


//Get Subproduct Name
function cepw_get_subproduct_name($cepw_option){
    switch ($cepw_option) {
      case 'tomorrow':
        $result = __("Para Amanhã", "ctt-expresso-para-woocommerce");
        break;
      case 'thirteen':
        $result = __("13", "ctt-expresso-para-woocommerce");
        break;
      case 'forty_eight':
        $result = __("48", "ctt-expresso-para-woocommerce");
        break;
      case 'two_days':
        $result = __("Em 2 dias", "ctt-expresso-para-woocommerce");
        break;
      case 'ten':
        $result = __("10", "ctt-expresso-para-woocommerce");
        break;
      case 'thirteen_multi':
        $result = __("13 Múltiplo", "ctt-expresso-para-woocommerce");
        break;
      case 'nineteen':
        $result = __("19", "ctt-expresso-para-woocommerce");
        break;
      case 'nineteen_multi':
        $result = __("19 Múltiplo", "ctt-expresso-para-woocommerce");
        break;
      case 'cargo':
        $result = __("Cargo", "ctt-expresso-para-woocommerce");
        break;
      case 'easy_return_24':
        $result = __("Easy Return 24", "ctt-expresso-para-woocommerce");
        break;
      case 'easy_return_48':
        $result = __("Easy Return 48", "ctt-expresso-para-woocommerce");
        break;
      case 'ems_economy':
        $result = __("EMS Economy", "ctt-expresso-para-woocommerce");
        break;
      case 'ems_international':
        $result = __("EMS International", "ctt-expresso-para-woocommerce");
        break;
      case 'rede_shopping':
        $result = __("Rede Shopping", "ctt-expresso-para-woocommerce");
        break;

       default:
        $result = $cepw_option;
    }

    return $result;
}

//Get Subproduct ID
function cepw_get_subproduct_id($cepw_option){
    $SubProductId = '';
    switch ($cepw_option) {
        case 'tomorrow':
            $SubProductId = "EMSF056.01";
            break;
        case 'thirteen':
            $SubProductId = "EMSF001.01";
            break;
        case 'forty_eight':
            $SubProductId = "ENCF008.01";
            break;
        case 'two_days':
            $SubProductId = "EMSF057.01";
            break;
        case 'ten':
            $SubProductId = "EMSF009.01";
            break;
        case 'thirteen_multi':
            $SubProductId = "EMSF028.01";
            break;
        case 'nineteen':
            $SubProductId = "ENCF005.01";
            break;
        case 'nineteen_multi':
            $SubProductId = "EMSF010.01";
            break;
        case 'cargo':
            $SubProductId = "EMSF015.01";
            break;
        case 'easy_return_24':
            $SubProductId = "EMSF053.01";
            break;
        case 'easy_return_48':
            $SubProductId = "EMSF054.01";
            break;
        case 'ems_economy':
            $SubProductId = "ENCF008.02";
            break;
        case 'ems_international':
            $SubProductId = "EMSF001.02";
            break;
        case 'rede_shopping':
            $SubProductId = "EMSF059.01";
            break;
        default:
            $SubProductId = $cepw_option;
    }
    return $SubProductId;
}

//Show chosen subproductid at the order screen
function cepw_woocommerce_admin_order_data_after_shipping_address( $order ) {
    $order_meta = apply_filters( 'cepw_order_meta', $order );
    //
    $cepw_option = $order_meta->cepw_option;
    $againstreimbursement = $order_meta->againstreimbursement;


    if ( trim( $cepw_option ) != '' ) {
        $result = cepw_get_subproduct_name($cepw_option);?>
        <h3><?php _e( 'CTT Expresso', 'ctt-expresso-para-woocommerce' ); ?></h3> 
        <p><strong><?php echo $result; ?></strong></p>  <?php
        if($againstreimbursement == 'true'): ?>
            <p><strong><?php _e('Against Reimbursement', 'ctt-expresso-para-woocommerce'); ?></strong></p> 
        <?php endif;
    }
}


function cepw_get_option_based_shipping_info($order,$method_id,$instance_id){
    global $wpdb;
    $againstreimbursement = "";
    $cepw_option = "";
    switch ( $method_id ) {
        // Flexible Shipping for WooCommerce - https://wordpress.org/plugins/flexible-shipping/
        case 'flexible_shipping':
            $options = get_option( 'flexible_shipping_methods_'.$instance_id, array() );
            foreach ($options as $key => $fl_options) {
                $shipping_method = $method_id.'_'.$instance_id.'_'.$fl_options['id'];
                $cepw_option_check = $order->get_meta( 'cepw_option_'.$shipping_method );
                $againstreimbursement_check = $order->get_meta('againstreimbursement_'.$shipping_method);
                if(!empty($cepw_option_check)){
                    $cepw_option = $cepw_option_check;
                }
                if(!empty($againstreimbursement_check)){
                    $againstreimbursement = $againstreimbursement_check;
                }
              }
        break;

        // WooCommerce Table Rate Shipping - http://bolderelements.net/plugins/table-rate-shipping-woocommerce/
        case 'betrs_shipping':
            $options_instance = get_option( 'betrs_shipping_options-'.$instance_id, array() );
            if ( isset( $options_instance['settings'] ) && is_array( $options_instance['settings'] ) ) {
              foreach ( $options_instance['settings'] as $setting ) {
                if ( isset( $setting['option_id'] ) ) {
                    $shipping_method = $method_id.':'.$instance_id.'-'.$setting['option_id'];
                    $cepw_option_check = $order->get_meta( 'cepw_option_'.$shipping_method );
                    $againstreimbursement_check = $order->get_meta('againstreimbursement_'.$shipping_method);
                    if(!empty($cepw_option_check)){
                        $cepw_option = $cepw_option_check;
                    }
                    if(!empty($againstreimbursement_check)){
                        $againstreimbursement = $againstreimbursement_check;
                    }
                }
              }
            }    
        break;

        // Table Rate Shipping - https://woocommerce.com/products/table-rate-shipping/
        case 'table_rate':
            $rates = $wpdb->get_results( sprintf( "SELECT rate_id FROM {$wpdb->prefix}woocommerce_shipping_table_rates WHERE shipping_method_id = %d ORDER BY rate_order ASC", $instance_id ) );
            foreach( $rates as $rate) {
                $shipping_method = $method_id.':'.$instance_id.':'.$rate->rate_id;
                $cepw_option_check = $order->get_meta( 'cepw_option_'.$shipping_method );
                $againstreimbursement_check = $order->get_meta('againstreimbursement_'.$shipping_method);
                if(!empty($cepw_option_check)){
                    $cepw_option = $cepw_option_check;
                }
                if(!empty($againstreimbursement_check)){
                    $againstreimbursement = $againstreimbursement_check;
                }
            }
          break;
        
        default:
            $shipping_method = $method_id.':'.$instance_id;
            $cepw_option = $order->get_meta( 'cepw_option_'.$shipping_method );
            $againstreimbursement = $order->get_meta('againstreimbursement_'.$shipping_method);
          break;
    }

    if ( class_exists( 'WooCommerce_Advanced_Shipping' ) ) {
      $methods = get_posts( array( 'posts_per_page' => '-1', 'post_type' => 'was', 'orderby' => 'menu_order', 'order' => 'ASC', 'suppress_filters' => false ) );
      foreach ( $methods as $method ) {
        $settings = get_post_meta(  $method_id, '_was_shipping_method', true );
        if ( is_array( $settings ) ) {
          $shipping_method = (string)$method->ID;
          $cepw_option = $order->get_meta( 'cepw_option_'.$shipping_method );
          $againstreimbursement = $order->get_meta('againstreimbursement_'.$shipping_method);
        }
      }
    }
    
    return (object) array('cepw_option' => $cepw_option, "againstreimbursement" => $againstreimbursement);
}


function cepw_order_meta_filter( $order ) {
    /*** Legacy Version ***/
    $cepw_option = $order->get_meta( 'cepw_option' );
    $againstreimbursement = $order->get_meta('againstreimbursement');
    /******/
    if(empty($cepw_option)){
        foreach( $order->get_items( 'shipping' ) as $item_id => $item ):
            // Get the data in an unprotected array
            $item_data = $item->get_data();
            $method_id = $item_data['method_id'];
            $instance_id  = $item_data['instance_id'];

            $shipping_info = cepw_get_option_based_shipping_info($order, $method_id, $instance_id);
            $cepw_option = $shipping_info->cepw_option;
            $againstreimbursement = $shipping_info->againstreimbursement;
        endforeach;
    }

    return (object) array('order' => $order,'cepw_option' => $cepw_option, "againstreimbursement" => $againstreimbursement);
}
add_filter( 'cepw_order_meta', 'cepw_order_meta_filter' );


//Information on the admin order preview
function cepw_woocommerce_admin_order_preview_end() { ?>
   {{{ data.cepw_option }}}
<?php }

//Information on the admin order preview
function cepw_woocommerce_admin_order_preview_get_order_details( $data, $order ) {
    $order_meta = apply_filters( 'cepw_order_meta', $order );
    //
    $cepw_option = $order_meta->cepw_option;
    $againstreimbursement = $order_meta->againstreimbursement;


    if ( trim( $cepw_option ) != '' ) {
      $result = cepw_get_subproduct_name($cepw_option);
      ob_start();?>
        <div class="wc-order-preview-addresses cttexpresso">
          <div class="wc-order-preview-address">
              <strong><?php _e( 'CTT Expresso', 'ctt-expresso-para-woocommerce' ); ?></strong>
              <span><?php echo $result; ?></span>
            
            <?php
            if($againstreimbursement == 'true'): ?>
                <p><strong><?php _e('Against Reimbursement', 'ctt-expresso-para-woocommerce'); ?></strong></p> 
            <?php endif; ?>


          </div>
        </div>
        <?php
        $data['cepw_option'] = ob_get_clean();
    }

  return $data;
}


// ADDING "Acções" columns 
function cepw_add_order_column_actions($columns){
    $ShippingOptions_Print = get_option('_ShippingOptions_Print');
    $reordered_columns = array();
    // Inserting columns to a specific location
    foreach( $columns as $key => $column){
        $reordered_columns[$key] = $column;
        if( $key ==  'order_total' && $ShippingOptions_Print != 'portal_ctt'){
            $reordered_columns['order_actions'] = __( 'Actions', 'ctt-expresso-para-woocommerce' );
        }
    }
    return $reordered_columns;
}
add_filter( 'manage_edit-shop_order_columns', 'cepw_add_order_column_actions', 20 );

// Adding custom fields meta data for column
function cepw_orders_list_actions_content( $column, $post_id ){
  if($column == "order_actions"){
    cepw_get_post_files($post_id);
  }
}
add_action( 'manage_shop_order_posts_custom_column' , 'cepw_orders_list_actions_content', 20, 2 );



// Add settings link on plugin page
function cepw_settings_link($links) { 
  $settings_link = '<a href="admin.php?page=wc-settings&tab=cttexpresso">'.__('Settings','ctt-expresso-para-woocommerce').'</a>'; 
  array_unshift($links, $settings_link); 
  return $links; 
}
 
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'cepw_settings_link' );



function cepw_debug_link($links){
    $debug = get_option('_CTTExpresso_Debug');
    if($debug == "true"){
      $debug_link = '<a href="admin.php?page=cepw_log">'.__('Log','ctt-expresso-para-woocommerce').'</a>';
      array_push($links, $debug_link); 
    }
    
    return $links; 
}
add_filter("plugin_action_links_".plugin_basename(__FILE__), 'cepw_debug_link' );



function cepw_admin_pages() {
    add_submenu_page(
      null, 
      __('Log','ctt-expresso-para-woocommerce'),
      __('Log','ctt-expresso-para-woocommerce'), 
      'manage_options', 
      'cepw_log', 
      'cepw_log_callback'
     );
}
add_action( 'admin_menu', 'cepw_admin_pages' );

function cepw_log_callback(){?>
    <h1 class="wp-heading-inline"><?php _e('CTT Expresso Log page','ctt-expresso-para-woocommerce') ?></h1>
    <?php
    $file = cepw_log_path();
    $content = file_get_contents($file);

    if(!empty($content)):?>
        <div class="cepw_debug_file_content">
            <div class="header"><h4><?php _e('Log File','ctt-expresso-para-woocommerce') ?></h4></div>
           <pre><?php print_r($content); ?></pre>
       </div>
    <?php endif;
}

/*weight not in gram alert!*/
function weightAlert(){
    $weight_unit = get_option('woocommerce_weight_unit');
    global $pagenow;

    if ( ($pagenow == 'index.php') && ($weight_unit != 'g') ) {
         echo  '<div class="error error-warning is-dismissible">
                <p>'. __('In order to work, <b>CTT Expresso</b> requires your WooCommerce weight unit to be gram (g). Please change it <a href="/wp-admin/admin.php?page=wc-settings&tab=products">here</a>', 'ctt-expresso-para-woocommerce'). 
                '</p></div>';
    }
}
add_action('admin_notices', 'weightAlert');


/**
 * Display the custom text field
 * @since 1.0.0
 */
function cepw_product_HarmonizedCode_field() {
    $args = array(
        'id' => 'harmonized_code',
        'label' => __( 'Harmonized Code', 'ctt-expresso-para-woocommerce' ),
        'class' => 'woocommerce-harmonized_code',
        'desc_tip' => true,
        'description' => __( 'Insert the product Harmonized Code', 'ctt-expresso-para-woocommerce' ),
    );
    woocommerce_wp_text_input( $args );
}
add_action( 'woocommerce_product_options_general_product_data', 'cepw_product_HarmonizedCode_field' );

/**
 * Save the custom field
 * @since 1.0.0
 */
function cepw_product_HarmonizedCode_save( $post_id ) {
    $product = wc_get_product( $post_id );
    $title = isset( $_POST['harmonized_code'] ) ? $_POST['harmonized_code'] : '';
    $product->update_meta_data( 'harmonized_code', sanitize_text_field( $title ) );
    $product->save();
}
add_action( 'woocommerce_process_product_meta', 'cepw_product_HarmonizedCode_save' );


function cepw_SenderData($senderData,$order_id){
    return $senderData;
}
add_filter('cepw_SenderData','cepw_SenderData', 10, 2);



function cepw_order_test($atts,$content = none){
    ob_start();
    $atts = shortcode_atts(
    array(
        'order_id' => 0,
        'show_result' => "false"
    ), $atts);
    

    $order_id = $atts['order_id'];
    $order = new WC_Order($order_id);
    $order_meta = apply_filters( 'cepw_order_meta', $order );
    $cepw_option = $order_meta->cepw_option;
    $SubProductId = cepw_get_subproduct_id($cepw_option);


    //Option
    $ShippingOptions_Print = get_option('_ShippingOptions_Print');
    $debug = get_option('_CTTExpresso_Debug');
    $senderPhone = get_option('_CTTExpresso_SenderPhone');
    $senderMobilePhone = get_option('_CTTExpresso_SenderMobilePhone');
    $senderEmail = get_option('_CTTExpresso_SenderEmail');
    //Sender
    $countries = new WC_Countries();
    $senderCity = $countries->get_base_city();
    $senderCodPostal = $countries->get_base_postcode();

    $strSenderCodPostal = explode('-',$senderCodPostal);
    
    $senderPTZipCode4 = $strSenderCodPostal[0];
    $senderPTZipCode3 = $strSenderCodPostal[1];

    $senderCountry = $countries->get_base_country();
    $senderAddress = $countries->get_base_address();
    
    
    //Receiver
    $shipping_address = $order->get_address('shipping'); 
    $receiverShippingAddress = $shipping_address['address_1'].' '.$shipping_address['address_2'];
    $receiverCountry = $shipping_address['country'];
    $receiverCity = $shipping_address['city'];
    $receiverCodPostal = $shipping_address['postcode']; //Codigo postal de envio
    $strReceiverCodPostal = explode('-',$receiverCodPostal);
    $receiverPTZipCode4 = $strReceiverCodPostal[0];
    $receiverPTZipCode3 = $strReceiverCodPostal[1]; 

    $receiverNote = substr($order->get_customer_note(),0,50);

    $receiverName = $order->get_formatted_shipping_full_name();
    $receiverMobilePhone = $order->get_billing_phone();
    $receiverEmail = $order->get_billing_email();
    
    $total_weight = 0;
    foreach($order->get_items() as $item_id => $product_item) {
        $quantity = $product_item->get_quantity();
        $product = $product_item->get_product();
        $product_weight = $product->get_weight();
        $total_weight += $product_weight * $quantity;
    }

    $receiverReference = '#'.$order_id; 

    if(!empty($SubProductId)):

        $wsdl = 'http://cttexpressows.ctt.pt/cttewspool/CTTShipmentProviderWS.svc?wsdl';
        // $wsdl = 'http://cttexpressows.qa.ctt.pt/CTTEWSPool/CTTShipmentProviderWS.svc?wsdl';
        $client = cepw_create_SOAP($wsdl);
        
        // (13 Multiplo && 19 Multiplo && Rede Shopping) || 1
        $Quantity = (get_post_meta($order_id,'Quantity',true) ?: 1);

        $ShipmentData = array('IsDevolution' => false, 
                              'Weight' => $total_weight, 
                              'Quantity' => $Quantity, 
                              'ClientReference' => $receiverReference,
                              'Observations' => $receiverNote
                          );

        //Sender
        $SenderData = array('Type' => 'Sender', 
                            'Name' => get_bloginfo( 'name' ),
                            'Address' => $senderAddress, 
                            'City' => $senderCity, 
                            'Country' => $senderCountry,
                        );
        if(!empty($senderEmail)){
            $SenderData['Email'] = $senderEmail;
        }

        if(!empty($senderPhone)){
            $SenderData['Phone'] = $senderPhone;
        }

        if(!empty($senderMobilePhone)){
            $SenderData['MobilePhone'] = $senderMobilePhone;
        }
        if($senderCountry == 'PT'){
            $SenderData['PTZipCode3']  = $senderPTZipCode3;
            $SenderData['PTZipCode4']  = $senderPTZipCode4;
            $SenderData['PTZipCodeLocation']  = $senderCity;
        }else{
            $SenderData['NonPTZipCode']  = $senderCodPostal;
            $SenderData['NonPTZipCodeLocation']  = $senderCity;
        }
        
        $SenderData = apply_filters( 'cepw_SenderData', $SenderData, $order);

        //Receiver
        $ReceiverData = array('Type' => 'Receiver', 
                              'Name' => $receiverName, 
                              'Address' => $receiverShippingAddress, 
                              'MobilePhone' => $receiverMobilePhone, 
                              'Email' => $receiverEmail,
                              'City' => $receiverCity, 
                              'Country' => $receiverCountry );
        if($receiverCountry == 'PT'){
            $ReceiverData['PTZipCode3']  = $receiverPTZipCode3;
            $ReceiverData['PTZipCode4']  = $receiverPTZipCode4;
            $ReceiverData['PTZipCodeLocation']  = $receiverCity;
        }else{
            $ReceiverData['NonPTZipCode']  = $receiverCodPostal;
            $ReceiverData['NonPTZipCodeLocation']  = $receiverCity;
        }


        
        //Export Type if not Portugal
        if($receiverCountry != 'PT'){
            $ExportTypeValues = get_post_meta($order_id,'ExportTypeValues',true);
            $UPUCodeValues = get_post_meta($order_id,'UPUCodeValues',true);
            if(!empty($ExportTypeValues)){
                $ShipmentData['ExportType'] = $ExportTypeValues;
            }
            if(!empty($UPUCodeValues)){
                $ShipmentData['UPUCode'] = $UPUCodeValues;
            }
        }

        
        //CustomsData
        if(!in_array($receiverCountry, WC_Countries::get_european_union_countries())){
            
            $VATExportDeclaration = get_post_meta($order_id,'VATExportDeclaration',true);
            $SachetDocumentation = get_post_meta($order_id,'SachetDocumentation',true);
            $NonDeliveryCase = get_post_meta($order_id,'NonDeliveryCase')[0];

            $VATRate = get_post_meta($order_id,'VATRate',true);

            $ReceiverTIN = get_post_meta($order_id,'ReceiverTIN',true);
            $Height = get_post_meta($order_id,'Height',true);
            $Length = get_post_meta($order_id,'Length',true);
            $Width = get_post_meta($order_id,'Width',true);
            $ClientCustomsCode = get_post_meta($order_id,'ClientCustomsCode',true);
            $SenderEmail = get_post_meta($order_id,'SenderEmail',true);
            if(empty($SenderEmail)){
                $SenderEmail = get_bloginfo('admin_email');
            }
            $ComercialInvoice = get_post_meta($order_id,'ComercialInvoice',true);
            $ExportLicense = get_post_meta($order_id,'ExportLicense',true);
            $OriginCertificateNumber = get_post_meta($order_id,'OriginCertificateNumber',true);
            $Comments = get_post_meta($order_id,'Comments',true);
            $InsurancePremium = get_post_meta($order_id,'InsurancePremium',true);
            $InsuranceValue = get_post_meta($order_id,'InsuranceValue',true);
            $ServiceValue = get_post_meta($order_id,'ServiceValue',true);

            $CustomsItemsData = array();
            $ItemNumber = 1;
            $CustomsTotalValue = 0;
            foreach($order->get_items() as $item_id => $product_item) {
                $product = $product_item->get_product();
                $product_id = $product->get_id();
                $total = round($product_item->get_total(),2);
                $CustomsItemsDataElement = array(
                    'ItemNumber' => $ItemNumber,
                    'Detail' => substr(get_the_excerpt( $product->get_id() ), 0, 50),
                    'Quantity' =>  $product_item->get_quantity(),
                    'Value' => $total,
                    'Weight' => $product->get_weight(),
                    'HarmonizedCode' => get_post_meta( $product_id, 'harmonized_code',true ),
                    'Currency' => get_option('woocommerce_currency'),
                    'OriginCountry' =>  WC_Countries::get_base_country()
                );
                array_push($CustomsItemsData,$CustomsItemsDataElement);
                $CustomsTotalValue += $total;
                $ItemNumber ++;
            }

            $CustomsTotalItems = count($CustomsItemsData);

            $CustomsData = array(
                'VATExportDeclaration' => $VATExportDeclaration,
                'SachetDocumentation' => $SachetDocumentation,
                'NonDeliveryCase' => $NonDeliveryCase,
                'CustomsItemsData' => $CustomsItemsData,
                'CustomsTotalItems' => $CustomsTotalItems,
                'VATRate' => $VATRate,
                'CustomsTotalValue' => round($CustomsTotalValue,2)
            );
            /*** Optional Fields ***/
            if(!empty($ReceiverTIN)){
                $CustomsData['ReceiverTIN'] = $ReceiverTIN;
            }
            if(!empty($Height)){
                $CustomsData['Height'] = $Height;
            }
            if(!empty($Length)){
                $CustomsData['Length'] = $Length;
            }
            if(!empty($Width)){
                $CustomsData['Width'] = $Width;
            }
            if(!empty($ClientCustomsCode)){
                $CustomsData['ClientCustomsCode'] = $ClientCustomsCode;
            }
            if(!empty($SenderEmail)){
                $CustomsData['SenderEmail'] = $SenderEmail;
            }
            if(!empty($ComercialInvoice)){
                $CustomsData['ComercialInvoice'] = $ComercialInvoice;
            }
            if(!empty($ExportLicense)){
                $CustomsData['ExportLicense'] = $ExportLicense;
            }
            if(!empty($OriginCertificateNumber)){
                $CustomsData['OriginCertificateNumber'] = $OriginCertificateNumber;
            }
            if(!empty($Comments)){
                $CustomsData['Comments'] = $Comments;
            }
            if(!empty($InsurancePremium)){
                $CustomsData['InsurancePremium'] = $InsurancePremium;
            }
            if(!empty($InsuranceValue)){
                $CustomsData['InsuranceValue'] = $InsuranceValue;
            }
            if(!empty($ServiceValue)){
                $CustomsData['ServiceValue'] = $ServiceValue;
            }
            /*******************/
            $ShipmentData['CustomsData'] = $CustomsData;
        }

        //CargoData Add Extra Info to ShipmentData
        if($SubProductId == 'EMSF015.01'){
            $PartialDelivery = get_post_meta($order_id,'PartialDelivery',true);
            $SchedulingData = get_post_meta($order_id,'SchedulingData',true);
            $SchedulingHour = get_post_meta($order_id,'SchedulingHour',true);
            if(!empty($PartialDelivery) && $SchedulingData && $SchedulingHour){
                $CagoDataInfo = array(
                    'PartialDelivery' => $PartialDelivery,
                    'SchedulingData' => $SchedulingData,
                    'SchedulingHour' => $SchedulingData.'-'.$SchedulingHour
                );
                $ShipmentData['CargoData'] = $CagoDataInfo;
            }
        }


        $ShipmentCTT = array(
            'HasSenderInformation' => true, 
            'SenderData' => $SenderData, 
            'ReceiverData' => $ReceiverData, 
            'ShipmentData' => $ShipmentData
        );

        $SpecialServices = get_post_meta($order_id,'SpecialServices',true);
        if(!empty($SpecialServices)){
            $ShipmentCTT['SpecialServices'] = $SpecialServices;
        }

        
        
        $DeliveryNote = array(
            'ClientId' => get_option('_CTTExpresso_ClientId'), 
            'ContractId' => get_option('_CTTExpresso_ContractId'), 
            'DistributionChannelId' => 99, 
            'SubProductId' => $SubProductId,
            'ShipmentCTT' => array($ShipmentCTT), 
            'ExtData' => '?'
        );

        $DeliveryNote = apply_filters( 'cepw_after_DeliveryNote', $DeliveryNote, $order );
        
        $RequestID = cepw_gen_uuid();

        $CreateShipmentInput = array(
            'AuthenticationID' => get_option('_CTTExpresso_AuthenticationId'),
            'RequestID' => $RequestID,
            'DeliveryNote' => $DeliveryNote
        );
        
        $Input = array('Input' => $CreateShipmentInput);

        if($atts['show_result'] == "true"){
            if($SubProductId == 'EMSF053.01' || $SubProductId == 'EMSF054.01'){
                $result = CreateShipment($client,$Input);
            }elseif($ShippingOptions_Print == 'portal_ctt'){
                $result = CreateShipmentWithoutPrint($client,$Input);
            }else{
                $result = CreateShipment($client,$Input,$order_id);
            }
        }

    endif;

    echo "<pre>";print_r($Input);echo "</pre>";

    if(!empty($result)){
        echo "<pre>";print_r($result);echo "</pre>";
    }


    $ReturnString = ob_get_contents(); 
    ob_end_clean(); 
    return $ReturnString; 
}
add_shortcode('cepw_order_test','cepw_order_test');


/*** Recolhas ***/
if(get_option('_ShippingOptions_Print') == 'website'){
    require_once(dirname(__FILE__) . '/ctt-expresso-recolhas.php');
}

