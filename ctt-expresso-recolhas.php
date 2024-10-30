<?php

/******************/
/**** Recolhas ****/
/******************/

if ( ! function_exists( 'cepw_pagination' ) ) :
	function cepw_pagination( $max_num_pages = null, $query = false ) {
		global $wp_query, $wp_rewrite;

		if ( ! $query ) {
			$query = $wp_query;
		}
		$max_num_pages = ( $max_num_pages ) ? $max_num_pages : $query->max_num_pages;

		// Don't print empty markup if there's only one page.
		if ( $max_num_pages < 2 ) {
			return;
		}

		$paged = 1;
		if(!empty($_GET['paged'])){
			$paged = $_GET['paged'];
		}
		$page_num_link = html_entity_decode( get_pagenum_link() );
		$query_args    = array();
		$url_parts     = explode( '?', $page_num_link );

		if ( isset( $url_parts[1] ) ) {
			wp_parse_str( $url_parts[1], $query_args );
		}


		$page_num_link = add_query_arg(array('page'=>'cepw_recolhas'), admin_url('admin.php'));
		$page_num_link = trailingslashit( $page_num_link ) . '%_%';


		$format  = $wp_rewrite->using_index_permalinks() && ! strpos( $page_num_link, 'index.php' ) ? 'index.php/' : '';
		$format .= '&paged=%#%';

		// Set up paginated links.
		$links = paginate_links(
			array(
				'base'      => $page_num_link,
				'format'    => $format,
				'total'     => $max_num_pages,
				'current'   => $paged,
				'end_size'  => 1,
				'mid_size'  => 1,
				'add_args'  => array_map( 'urlencode', $query_args ),
				'prev_text' => __( 'Previous', 'default' ),
			)
		);

		if ( $links ) :?>
			<div class="clearfix"></div>
			<div class="pagination-wrap">
				<div class="pagination" role="navigation">
					<?php echo preg_replace( '/^\s+|\n|\r|\s+$/m', '', $links ); ?>
				</div>
			</div>
			<?php
		endif;
	}
endif;


if ( ! function_exists( 'cepw_get_display_name' ) ) :
function cepw_get_display_name($user_id) {
    if (!$user = get_userdata($user_id))
        return false;
    return $user->data->display_name;
}
endif;

if ( ! function_exists( 'cepw_create_recolhas_post_type' ) ) :
function cepw_create_recolhas_post_type(){
    $args = array( 
        'public'                => false,
        'publicly_queryable'    => true,
        'exclude_form_search'   => false,
        'show_in_nav_menus'     => false,
        'show_ui'               => false,
        'show_in_menu'          => false,
        'show_in_admin_bar'     => false,
        'can_export'            => true,
        'has_archive'           => false,
        'query_var'             => true,
        'capability_type'       => 'post',
    );
    register_post_type( 'cepw_recolha', $args);      
}
add_action( 'init','cepw_create_recolhas_post_type' );
endif;



function cepw_marcarRecolhas_page() {
	add_menu_page(
    	__('Recolhas','ctt-expresso-para-woocommerce'),
    	__('Recolhas','ctt-expresso-para-woocommerce'), 
    	'manage_options', 
    	'cepw_book_recolhas', 
    	'cepw_book_recolhas_callback',
    	plugins_url( '/assets/img/menu_icon.png', __FILE__  )
    );

	add_submenu_page(
		'cepw_book_recolhas', 
        __( 'History', 'ctt-expresso-para-woocommerce' ),
        __( 'History', 'ctt-expresso-para-woocommerce' ),
        'manage_options',
        'cepw_recolhas_history', 
        'cepw_recolhas_callback'
    );

  	add_submenu_page(
    	'cepw_recolhas_history', 
    	__('Create CTT Expresso Recolhas','ctt-expresso-para-woocommerce'),
    	__('Create CTT Expresso Recolhas','ctt-expresso-para-woocommerce'), 
    	'manage_options', 
    	'cepw_create_recolha', 
    	'cepw_create_recolha_callback'
    );
}
add_action( 'admin_menu', 'cepw_marcarRecolhas_page' );


function cepw_recolhas_callback(){ ?>
	<div class="ctt_expresso_recolhas">
		<h2 class="ctt_expresso_header">
		    <img src="<?php echo plugins_url( '/assets/img/logo.png', __FILE__ ); ?>">
		    <span><?php echo __('Recolhas','ctt-expresso-para-woocommerce'); ?></span>
		</h2>
		<?php 
		$paged = 1;
		if(!empty($_GET['paged'])){
			$paged = $_GET['paged'];
		}
		$args = array(
			'post_type'   => 'cepw_recolha', 
			'orderby' => 'date',
			'order' => 'DESC',
			'paged' => $paged,
		);

		$wp_query = new WP_Query( $args );
		if ( $wp_query->have_posts() ) : ?>
			<div class="tablenav top">
				

				<div class="tablenav-pages">
					<span class="displaying-num"><?php echo $wp_query->found_posts ?> <?php _e('items','ctt-expresso-para-woocommerce');?></span>
					<div class="pagination-links">
						<?php cepw_pagination($wp_query->max_num_pages, $wp_query); ?>
					</div>
				</div>
			</div>
			<table class="wp-list-table widefat fixed striped table-view-list posts">
				<thead>
				<tr>
					<td  class="manage-column check-column">
						<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','ctt-expresso-para-woocommerce'); ?></label>
						<input id="cepw_select-all" type="checkbox">
					</td>
					<th scope="col" id="order_number" class="manage-column column-order_number column-primary sortable desc">
						<a href="#">
							<span><?php _e('PickUp ID','ctt-expresso-para-woocommerce'); ?></span>
						</a>
					</th>
					<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">
						<a href="#">
							<span><?php _e('Date of PickUp','ctt-expresso-para-woocommerce'); ?></span>
						</a>
					</th>
					<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">
						<a href="#">
							<span><?php _e('Contact Name','ctt-expresso-para-woocommerce'); ?></span>
						</a>
					</th>
					<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">
						<a href="#">
							<span><?php _e('Phone','ctt-expresso-para-woocommerce'); ?></span>
						</a>
					</th>
					<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">
						<a href="#">
							<span><?php _e('Mobile Phone','ctt-expresso-para-woocommerce'); ?></span>
						</a>
					</th>
					<th scope="col" id="documents" class="manage-column column-order_documents">
						<a href="#"><?php _e('Documents','ctt-expresso-para-woocommerce'); ?></a>
					</th>
				</tr>
				
				</thead>
				<tbody id="the-list">
					<?php while ( $wp_query->have_posts() ):
						$wp_query->the_post();
						$recolha_id = get_the_id();
						$recolha_PickUpID = get_post_meta($recolha_id,'_PickUpID',true);
						$recolha_Date = get_post_meta($recolha_id,'_Date',true);
						$recolha_ContactName = get_post_meta($recolha_id,'_ContactName',true);
						$recolha_Phone = get_post_meta($recolha_id,'_Phone',true);
						$recolha_MobilePhone = get_post_meta($recolha_id,'_MobilePhone',true);
					?>
					<tr id="post-<?php echo $order_id; ?>" class="iedit type-shop_order status-wc-completed hentry">
						<th scope="row" class="check-column">
							<label class="screen-reader-text" for="cepw_recolhas-select-<?php echo $recolha_id; ?>">
								<?php _e('Select Order'); ?>
							</label>
							<input type="checkbox" class="check_input" name="orders[]">
						</th>
						<td class="order_number column-order_number has-row-actions column-primary" data-colname="Encomenda">
							<strong><?php echo $recolha_PickUpID; ?></strong>
						</td>
						<td>
							<?php echo $recolha_Date; ?>
						</td>
						<td>
							<?php echo $recolha_ContactName; ?>
						</td>
						<td>
							<?php echo $recolha_Phone; ?>
						</td>
						<td>
							<?php echo $recolha_MobilePhone; ?>
						</td>
						<td>
							<?php cepw_get_post_files($recolha_id); ?>
						</td>
					</tr>
					<?php endwhile ?>
				</tbody>
			</table>
		<?php 
		endif;
		?>



	</div>
	<?php 
}


function cepw_book_recolhas_callback(){ 
	$paged = 1;
	if(!empty($_GET['paged'])){
		$paged = $_GET['paged'];
	}


	$args = array(
		'post_type'   => 'shop_order', 
		'post_status' => 'wc-completed', 
		'posts_per_page' => 22,
		'paged' => $paged,
		'meta_query' => array(
		    array(
		    	'key' => 'cepw_recolhas',
		    	'value' => 'on'
		    ),
		    array(
		        'key' => '_cttExpresso_trackingNumber',
		    )
		)
	);

	$args = apply_filters( 'cepw_get_recolhas', $args );


	?>
	<div class="ctt_expresso_recolhas">
		<h2 class="ctt_expresso_header">
		    <img src="<?php echo plugins_url( '/assets/img/logo.png', __FILE__ ); ?>">
		    <span><?php echo __('Recolhas','ctt-expresso-para-woocommerce'); ?></span>
		</h2>
<?php
	$wp_query = new WP_Query( $args );
	if ( $wp_query->have_posts() ) : ?>
	<div class="tablenav top">
		<div class="alignleft actions bulkactions">
			<label for="bulk-action-selector-top" class="screen-reader-text">Seleccionar acção por lotes</label>
			<select name="action" id="bulk-action-selector-top">
				<option disabled selected><?php _e('Bulk Actions','ctt-expresso-para-woocommerce'); ?></option>
				<option value="NewOfferPickUp"><?php _e('New Offer PickUp','ctt-expresso-para-woocommerce'); ?></option>
				<option value="DeletePickUp"><?php _e('Delete PickUp','ctt-expresso-para-woocommerce'); ?></option>
			</select>
			<input type="submit" id="doaction" class="button action" value="<?php _e('Apply','ctt-expresso-para-woocommerce'); ?>">
		</div>

		<div class="tablenav-pages">
			<span class="displaying-num"><?php echo $wp_query->found_posts ?> <?php _e('items','ctt-expresso-para-woocommerce');?></span>
			<div class="pagination-links">
				<?php cepw_pagination($wp_query->max_num_pages, $wp_query); ?>
			</div>
		</div>
	</div>
	<form method="post" id="cepw_recolhas_form" action="<?php echo add_query_arg(array('page'=>'cepw_create_recolha'), admin_url('admin.php')); ?>"> 
		<input type="hidden" name="user_id" value="<?php echo get_current_user_id(); ?>">
		<input type="hidden" name="cepw_marcarRecolha_none" value="<?php echo wp_create_nonce(); ?>">
		<table class="wp-list-table widefat fixed striped table-view-list posts">
			<thead>
			<tr>
				<td  class="manage-column check-column">
					<label class="screen-reader-text" for="cb-select-all-1"><?php _e('Select All','ctt-expresso-para-woocommerce'); ?></label>
					<input id="cepw_select-all" type="checkbox">
				</td>
				<th scope="col" id="order_number" class="manage-column column-order_number column-primary sortable desc">
					<a href="#">
						<span><?php _e('Order','ctt-expresso-para-woocommerce'); ?></span>
					</a>
				</th>
				<th scope="col" id="order_date" class="manage-column column-order_date sortable desc">
					<a href="#">
						<span><?php _e('Date','ctt-expresso-para-woocommerce'); ?></span>
					</a>
				</th>
				<th scope="col" id="order_total" class="manage-column column-order_total sortable">
					<a href="#"><span><?php _e('Total','ctt-expresso-para-woocommerce'); ?></span><span class="sorting-indicator"></span></a>
				</th>
				<th scope="col" id="cepw_option" class="manage-column column-order_cepw_option"><?php _e('CTT Expresso','ctt-expresso-para-woocommerce'); ?></th>
				<th scope="col" id="documents" class="manage-column column-order_documents">
					<?php _e('Documents','ctt-expresso-para-woocommerce'); ?>	
				</th>
			</tr>
			</thead>
			<tbody id="the-list">
		<?php while ( $wp_query->have_posts() ):
			$wp_query->the_post();
			$order_id = get_the_id();
			$order = wc_get_order( $order_id );
			$order_meta = apply_filters( 'cepw_order_meta', $order );
		    $cepw_option = $order_meta->cepw_option;
		    $SubProductId = cepw_get_subproduct_id($cepw_option);
			$cepw_tracking = get_post_meta($order_id,'_cttExpresso_trackingNumber',true);
			$billing_first_name = get_post_meta($order_id,'_billing_first_name',true);
			$billing_last_name  = get_post_meta($order_id,'_billing_last_name',true);
			$name = $billing_first_name . ' '. $billing_last_name;
			$order_timestamp = $order->get_date_created()->getTimestamp();
			if ( $order_timestamp > strtotime( '-1 day', time() ) && $order_timestamp <= time() ) {
				$show_date = sprintf(
					/* translators: %s: human-readable time difference */
					_x( '%s ago', '%s = human-readable time difference', 'woocommerce' ),
					human_time_diff( $order_timestamp, time() )
				);
			} else {
				$show_date = $order->get_date_created()->date_i18n( apply_filters( 'woocommerce_admin_order_date_format', __( 'M j, Y', 'woocommerce' ) ) );
			}
			?>
			<tr id="post-<?php echo $order_id; ?>" class="iedit type-shop_order status-wc-completed hentry">
				<th scope="row" class="check-column">
					<label class="screen-reader-text" for="cepw_recolhas-select-<?php echo $order_id; ?>">
						<?php _e('Select Order'); ?>
					</label>
					<input type="checkbox" class="check_input" name="orders[]" order_id="<?php echo $order_id; ?>" value="<?php echo $cepw_tracking; ?>">
				</th>
				<td class="order_number column-order_number has-row-actions column-primary" data-colname="Encomenda">
					<a href="/wp-admin/post.php?post=<?php echo $order_id; ?>&action=edit" class="order-view">
						<strong>#<?php echo $order_id; ?> <?php echo $name; ?></strong>
					</a>
					<div class="row-actions">
						<span class="individual"><a href="#" order_id="<?php echo $order_id; ?>"  class="cepw_create_single_order_recolha"><?php _e('Booking','ctt-expresso-para-woocommerce'); ?></a></span>
					</div>
				</td>
				<td>
					<?php printf(
						'<time datetime="%1$s" title="%2$s">%3$s</time>',
						esc_attr( $order->get_date_created()->date( 'c' ) ),
						esc_html( $order->get_date_created()->date_i18n( get_option( 'date_format' ) . ' ' . get_option( 'time_format' ) ) ),
						esc_html( $show_date )
					); ?>
				</td>
				<td class="order_total column-order_total" data-colname="Total">
					<?php echo wc_price($order->get_total()); ?>
				</td>
				<td class="column-order_cepw_option"> 
					<span><?php echo cepw_get_subproduct_name($cepw_option); ?></span>
				</td>
				<td class="documents column-order_documents">
					<?php cepw_get_post_files($order_id); ?>
				</td>
			</tr>
		<?php endwhile; ?>
			</tbody>
		</table>
	</form>
	<?php else: ?>
		<h2><?php _e('Not have any order to pickup','ctt-expresso-para-woocommerce'); ?></h2>
	<?php endif;?>
	</div>
	<?php
}

function cepw_create_recolha_callback(){ 
	?>
	<div class="ctt_expresso_recolhas">
		<h2 class="ctt_expresso_header">
		    <img src="<?php echo plugins_url( '/assets/img/logo.png', __FILE__ ); ?>">
		    <span><?php echo __('Recolhas','ctt-expresso-para-woocommerce'); ?></span>
		</h2>
		<?php 
		if(!empty($_POST['orders']) && wp_verify_nonce($_POST['cepw_marcarRecolha_none'])): 
			$orders = $_POST['orders'];
			$user_id = $_POST['user_id'];
			//
			$phoneSender = str_replace("+", "00",get_option('_CTTExpresso_SenderPhone'));
			$mobilePhoneSender = str_replace("+", "00",get_option('_CTTExpresso_SenderMobilePhone'));
			?>
			<form method="POST" class="cepw_create_recolha" action="">
				<!-- Loader Start -->
				<div class="preloader"><div class="loader"></div></div>
				<!-- Loader End -->
				<input type="hidden" name="action" value="cepw_createRecolha" />
				<input type="hidden" name="user_id" value="<?php echo $user_id; ?>">
				<!-- Form Info -->
				<div class="info">
					<h3><?php _e("Sender's Info",'ctt-expresso-para-woocommerce'); ?></h3>
					<div class="actions"></div>

					<div class="forminp forminp-text">
            			<!-- ContactName -->
						<div class="form_group required">
                			<label for="ContactName" ><?php echo esc_html__("Contact Name", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="text" name="ContactName" maxlength="50" id="ContactName" value="<?php echo cepw_get_display_name($user_id); ?>" required placeholder="<?php _e('Pickup contact name','ctt-expresso-para-woocommerce'); ?>">
			                    </p>
			                </span>
            			</div>
            			<!-- Phone -->
						<div class="form_group required">
                			<label for="Phone" ><?php echo esc_html__("Phone", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="text" name="Phone" maxlength="15" id="Phone" value="<?php echo $phoneSender; ?>" placeholder="<?php _e('Pickup contact phone','ctt-expresso-para-woocommerce'); ?>" required>
			                    </p>
			                </span>
            			</div>
            			<!-- MobilePhone -->
						<div class="form_group">
                			<label for="MobilePhone" ><?php echo esc_html__("MobilePhone", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="text" name="MobilePhone" maxlength="15" id="MobilePhone" value="<?php echo $mobilePhoneSender; ?>" placeholder="<?php _e('Pickup contact mobile phone','ctt-expresso-para-woocommerce'); ?>">
			                    </p>
			                </span>
            			</div>


            			<!-- Email -->
						<div class="form_group">
                			<label for="Phone" ><?php echo esc_html__("Email", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="email" name="Email" maxlength="50" id="Email" value="<?php echo get_bloginfo('admin_email'); ?>" placeholder="<?php _e('Pickup contact email','ctt-expresso-para-woocommerce'); ?>">
			                    </p>
			                </span>
            			</div>

            			
					</div>
				</div>

				<!-- PickUpData -->
				<div class="info">
					<h3><?php _e("PickUp Info",'ctt-expresso-para-woocommerce'); ?></h3>
					<div class="actions"></div>
					<div class="forminp forminp-text">
            			<!-- Date -->
						<div class="form_group required">
                			<label for="Date" ><?php echo esc_html__("Date", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="text" name="Date" id="Date" class="DatePicker" placeholder="ex: <?php echo date('Y-m-d'); ?>"  value="" required autocomplete="off">
			                    </p>
			                </span>
            			</div>


            			<!-- BiggerObjectLenght -->
						<div class="form_group required">
                			<label for="BiggerObjectLenght" ><?php echo esc_html__("Bigger Object Lenght", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="number" name="BiggerObjectLenght" id="BiggerObjectLenght" value="" placeholder="(cm)" required>
			                    </p>
			                </span>
            			</div>

            			<!-- BiggerObjectHeight -->
						<div class="form_group required">
                			<label for="BiggerObjectHeight" ><?php echo esc_html__("Bigger Object Height", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="number" name="BiggerObjectHeight" id="BiggerObjectHeight" value="" placeholder="(cm)" required>
			                    </p>
			                </span>
            			</div>

            			<!-- BiggerObjectWidth -->
						<div class="form_group required">
                			<label for="BiggerObjectWidth" ><?php echo esc_html__("Bigger Object Width", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="number" name="BiggerObjectWidth" id="BiggerObjectWidth" value="" placeholder="(cm)" required>
			                    </p>
			                </span>
            			</div>

            			<!-- TransportDocument -->
            			<div class="form_group required">
                			<label for="TransportDocument" ><?php echo esc_html__("Transport Document", "ctt-expresso-para-woocommerce") ?></label>
			                <span class="field">
			                    <p>
			                        <input type="checkbox" name="TransportDocument" id="TransportDocument" value="yes">
			                    </p>
			                </span>
            			</div>
					</div>
				</div>

				<input type="hidden" name="ShipmentList" value="<?php echo implode(',', $orders); ?>">

				<!-- Observations -->
				<div class="info">
					<h3><?php _e("Observations",'ctt-expresso-para-woocommerce'); ?></h3>
					<div class="actions"></div>
					<div class="forminp forminp-text">
						<div class="form_group">
                			<textarea maxlength="50" name="Observation" id="Observation"></textarea>
            			</div>
					</div>
				</div>

				<div class="info">
					<button type="submit" class="btn btn_cepw_create_recolha"><?php _e('Book PickUp','ctt-expresso-para-woocommerce'); ?></button>
				</div>
			</form>


			<div class="modal">
				<div class="modal_wrapper">
					<div class="modal-header">
						<h4>Marcação de Recolha</h4>
						<span class="PickUpDate"><?php echo date("Y-m-d"); ?></span>
					</div>
					<div class="modal-content">
						<pre class="resultfailure"></pre>
						<table class="resultsuccess">
							<tbody>
								<tr>
									<td><?php _e('Pickup ID','ctt-expresso-para-woocommerce'); ?></td>
									<th id="pickup_id"></th>
								</tr>
								<tr>
									<td><?php _e('Documents List','ctt-expresso-para-woocommerce'); ?></td>
									<th id="documents_list"></th>
								</tr>
							</tbody>
						</table>
						
					</div>
					<div class="modal-footer">
						<?php $view_all = add_query_arg(array('page'=>'cepw_recolhas_history'), admin_url('admin.php')); ?>
						<a href="<?php echo $view_all; ?>"><?php _e('View all Pickups','ctt-expresso-para-woocommerce'); ?></a>	
					</div>
				</div>
			</div>


		<?php else: ?>
			<h2><?php _e('Please select at least one order','ctt-expresso-para-woocommerce'); ?></h2>
		<?php endif;?>
	</div>
	<?php 
}

function getOrderIdByTracking($tracking){
	global $wpdb;
	$order_id = $wpdb->get_var("SELECT post_id FROM ".$wpdb->postmeta." WHERE (meta_key = '_cttExpresso_trackingNumber' AND meta_value='".$tracking."')");
	return $order_id;
}

function createPickUp($ShipmentList,$post,$response,$PickupDatePost){
	foreach ($ShipmentList as $trackingNumber) {
		global $wpdb;
		$postmeta = $wpdb->prefix . "postmeta";
		$order_id = $wpdb->get_var('SELECT post_id FROM '. $postmeta .' WHERE (meta_key = "_cttExpresso_trackingNumber" AND meta_value="'.$trackingNumber.'")');
		update_post_meta($order_id,'cepw_recolhas','off');
		$orders[] = $order_id;
	}

	$new_cepw_recolha = array(
		'post_title'    => 'Recolha '. $PickupDatePost,
		'post_status'   => 'publish',
		'post_type' => 'cepw_recolha',
	);
	 
	$cepw_recolhaID = wp_insert_post( $new_cepw_recolha );
	update_post_meta($cepw_recolhaID,'_Observation',$post['Observation']);
	update_post_meta($cepw_recolhaID,'_ShipmentList',$orders);
	update_post_meta($cepw_recolhaID,'_BiggerObjectLenght',$post['BiggerObjectLenght']);
	update_post_meta($cepw_recolhaID,'_BiggerObjectHeight',$post['BiggerObjectHeight']);
	update_post_meta($cepw_recolhaID,'_BiggerObjectWidth',$post['BiggerObjectWidth']);
	update_post_meta($cepw_recolhaID,'_Date',$PickupDatePost);
	update_post_meta($cepw_recolhaID,'_Phone',$post['Phone']);
	update_post_meta($cepw_recolhaID,'_MobilePhone',$post['MobilePhone']);
	update_post_meta($cepw_recolhaID,'_Email',$post['Email']);
	update_post_meta($cepw_recolhaID,'_ContactName',$post['ContactName']);
	update_post_meta($cepw_recolhaID,'_PickUpID',$response->NewOfferPickUpResult->PickUpID);

	if(!empty($response->NewOfferPickUpResult->DocumentList->DocumentData)){
	    $filesList = $response->NewOfferPickUpResult->DocumentList->DocumentData;
	    if(count($filesList) == 1){
	        $data = $filesList->File;
	        cepw_create_files($filesList,$data, $cepw_recolhaID);
	    }else{
	        foreach ($filesList as $file) {
	            $data = $file->File;
	            cepw_create_files($file,$data, $cepw_recolhaID);
	        }
	    }

	    $upload = wp_upload_dir();
	    $upload_dir = $upload['basedir'];
	    $upload_dir = $upload_dir . '/cepw/'.$cepw_recolhaID;
	    if (is_dir($upload_dir)):
	    	$upload_url = $upload['baseurl'] . '/cepw/'.$cepw_recolhaID . '/';
	    	if ($handle = opendir($upload_dir)) {
	    	    while (false !== ($file = readdir($handle))){
	    	        if ($file != "." && $file != ".." && strtolower(substr($file, strrpos($file, '.') + 1)) == 'pdf'){
	    	          $title = cepw_get_title_filename($file);
	    	          $files[] =  '<a href="'.$upload_url.$file.'" target="_blank" title="'.$title.'"><img src="'.esc_url( plugin_dir_url( __FILE__ ) . 'assets/img/icon.svg' ).'" style="height:30px;"></a>';
	    	        }
	    	    }
	    	}
	    	closedir($handle);
	    endif;  
	}

	return $files;
}


function cepw_createRecolha_request(){
    $countries = new WC_Countries();
    $senderCity = $countries->get_base_city();
    $senderCodPostal = $countries->get_base_postcode();

    $strSenderCodPostal = explode('-',$senderCodPostal);
    
    $senderPTZipCode4 = $strSenderCodPostal[0];
    $senderPTZipCode3 = $strSenderCodPostal[1];

    $senderCountry = $countries->get_base_country();
    $base_address = get_option( 'woocommerce_store_address', '' );
    $base_address_2 = get_option( 'woocommerce_store_address_2', '' );
    $senderAddress = $base_address . " ".$base_address_2;

    $Phone = $_POST['Phone'];




	//PickupAddress
	$PickupAddress = array();
	$PickupAddress['Address'] = $senderAddress;
	if($senderCountry == 'PT'){
        $PickupAddress['CP4']  = $senderPTZipCode4;
        $PickupAddress['CP3']  = $senderPTZipCode3;
    }else{
        $PickupAddress['CPInt']  = $senderCodPostal;
    }
    $PickupAddress['City'] = $senderCity;
    $PickupAddress['Contact'] = $_POST['ContactName'];
    $PickupAddress['Country'] = $senderCountry;
    if(!empty($senderEmail)){
    	$PickupAddress['Email'] = $senderEmail;
    }
    if(!empty($_POST['MobilePhone'])){
    	$PickupAddress['MobilePhone']  = $_POST['MobilePhone'];
    }
    $PickupAddress['Name']  = $_POST['ContactName'];
    $PickupAddress['Phone']  = $Phone;
    
    //
	
	$ClientData = array(
		'ClientID' => get_option('_CTTExpresso_ClientId'),
		'ContractID' => get_option('_CTTExpresso_ContractId'),
		'RequestedBy' => get_bloginfo('name'),
		'ContactName' => $_POST['ContactName'],
		'Phone' => $Phone,
		'Email' => $_POST['Email']
	);

	$ShipmentList = explode(',', $_POST['ShipmentList']);
	if(is_array( $ShipmentList ) && count( $ShipmentList ) > 0){
		foreach($ShipmentList as $tracking):
			$order_id = getOrderIdByTracking($tracking);
			$PickupAddress = apply_filters( 'cepw_after_PickupAddress', $PickupAddress, $order_id );
			$ClientData = apply_filters( 'cepw_after_ClientData', $ClientData, $order_id );
		endforeach;
	}

	$PickupDatePost = date( "Y-m-d", strtotime( $_POST['Date'] ) );

	if(isset($_POST['TransportDocument'])){
		$transportDocument = 1;
	}else{
		$transportDocument = 0;
	}

	$PickupData = array(
		'ClientData' => $ClientData,
		'ClientRef' => get_bloginfo('blogname'),
		'Date' => $PickupDatePost,
		'BiggerObjectLenght' => $_POST['BiggerObjectLenght'],
		'BiggerObjectHeight' => $_POST['BiggerObjectHeight'],
		'BiggerObjectWidth' => $_POST['BiggerObjectWidth'],
		'PickupAddress' => $PickupAddress,
		'ShipmentList' => $ShipmentList,
		'TransportDocument' => $transportDocument
	);

	if(!empty($_POST['Observation'])){
		$PickupData['Observation'] = $_POST['Observation'];
	}


	$NewOfferPickUpInput = array(
		'AuthenticationID' => get_option('_CTTExpresso_AuthenticationId'),
		'PickUpData' => $PickupData
	);

	$Input = array('Input' => $NewOfferPickUpInput);

	$debug = get_option('_CTTExpresso_Debug');

	try {
		$wsdl = 'http://cttexpressows.ctt.pt/CTTEWSPool/Recolhasws.svc?wsdl';
    	// $wsdl = 'http://cttexpressows.qa.ctt.pt/CTTEWSPool/Recolhasws.svc?wsdl';
    	$client = cepw_create_SOAP($wsdl);
        $response = $client->NewOfferPickUp($Input);
        $ErrorsList =  $response->NewOfferPickUpResult->ErrorList;

        if($response->NewOfferPickUpResult->Status == "Success"){
			//var_dump
        	$files = createPickUp($ShipmentList,$_POST,$response,$PickupDatePost);
        	//
			wp_send_json(
        		array(
        			'status' => $response->NewOfferPickUpResult->Status,
        			'PickUpID' => $response->NewOfferPickUpResult->PickUpID,
        			'files' => $files,
        		)
        	);

        }else{

        	if($debug != "true"){
		    	$PickUpDate = 'Pickup ' . $PickupDatePost;
                cepw_log($ErrorsList,$PickUpDate);
		    }

        	wp_send_json(
        		array(
        			'status' => $response->NewOfferPickUpResult->Status,
        			'ErrorList' => $ErrorsList
        		)
        	);
        }
    } catch (SoapFault $fault) {
        return trigger_error("SOAP Fault: (faultcode: {$fault->faultcode}, faultstring: {$fault->faultstring})", E_USER_ERROR);
    }
}
add_action( 'wp_ajax_cepw_createRecolha', 'cepw_createRecolha_request' );
add_action( 'wp_ajax_nopriv_cepw_createRecolha', 'cepw_createRecolha_request' );

function cepw_RemoveOrderFromRecolhaList_request(){
	if(!empty($_POST['orders'])){
		$orders = $_POST['orders'];
		foreach ($orders as $order_id) {
			update_post_meta($order_id,'cepw_recolhas','off');
		}
	}
}
add_action( 'wp_ajax_cepw_RemoveOrderFromRecolhaList', 'cepw_RemoveOrderFromRecolhaList_request' );
add_action( 'wp_ajax_nopriv_cepw_RemoveOrderFromRecolhaList', 'cepw_RemoveOrderFromRecolhaList_request' );