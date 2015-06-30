<?php
if(!isset($shub_ucm_id) || !isset($shub_ucm_message_id)){
	exit;
} ?>

	<?php

if($shub_ucm_id && $shub_ucm_message_id){
	$ucm = new shub_ucm_account($shub_ucm_id);
    if($shub_ucm_id && $ucm->get('shub_ucm_id') == $shub_ucm_id){
	    $ucm_message = new shub_ucm_message( $ucm, false, $shub_ucm_message_id );
	    if($shub_ucm_message_id && $ucm_message->get('shub_ucm_message_id') == $shub_ucm_message_id && $ucm_message->get('shub_ucm_id') == $shub_ucm_id){

		    $comments         = $ucm_message->get_comments();
		    $ucm_message->mark_as_read();

		    $shub_product_id = $ucm_message->get('ucm_product')->get('shub_product_id');
		    $product_data = array();
			if($shub_product_id) {
				$shub_product = new SupportHubProduct();
				$shub_product->load( $shub_product_id );
				$product_data = $shub_product->get( 'product_data' );
			}
		    ?>

			<form action="" method="post" id="ucm_edit_form">
				<section class="message_sidebar">
					<header>
						<a href="<?php echo $ucm_message->get('link');?>" class="socialucm_view_external btn btn-default btn-xs button" target="_blank"><?php _e( 'View Ticket' ); ?></a>
					    <?php if($ucm_message->get('status') == _shub_MESSAGE_STATUS_ANSWERED){  ?>
						    <a href="#" class="socialucm_message_action shub_message_action btn btn-default btn-xs button"
						       data-action="set-unanswered" data-post="<?php echo esc_attr(json_encode(array(
								'network' => 'ucm',
								'shub_ucm_message_id' => $ucm_message->get('shub_ucm_message_id'),
							)));?>"><?php _e( 'Inbox' ); ?></a>
					    <?php }else{ ?>
						    <a href="#" class="socialucm_message_action shub_message_action btn btn-default btn-xs button"
						       data-action="set-answered" data-post="<?php echo esc_attr(json_encode(array(
								'network' => 'ucm',
								'shub_ucm_message_id' => $ucm_message->get('shub_ucm_message_id'),
							)));?>"><?php _e( 'Archive' ); ?></a>
					    <?php } ?>
					</header>

					<img src="<?php echo plugins_url('networks/ucm/ucm-logo.png', _DTBAKER_SUPPORT_HUB_CORE_FILE_);?>" class="shub_message_account_icon"> <br/>

				    <strong><?php _e('Account:');?></strong> <a href="<?php echo $ucm_message->get_link(); ?>" target="_blank"><?php echo htmlspecialchars( $ucm_message->get('ucm_account') ? $ucm_message->get('ucm_account')->get( 'ucm_name' ) : 'N/A' ); ?></a> <br/>

					<strong><?php _e('Date:');?></strong> <?php echo shub_print_date( $ucm_message->get('last_active'), false ); ?>  <br/>

				    <?php

					// find out the user details, purchases and if they have any other open messages.
				    $user_hints = array();
				    $user_hints['shub_user_id'] = $ucm_message->get('shub_user_id');
					SupportHub::getInstance()->message_user_summary($user_hints, 'ucm', $ucm_message);
					do_action('supporthub_message_header', 'ucm', $ucm_message);
					?>
				</section>
				<section class="message_content">
				    <?php
				    $ucm_message->full_message_output(true);
				    ?>
				</section>
		    </form>

	    <?php }
    }
}

if($shub_ucm_id && !(int)$shub_ucm_message_id){
	$ucm = new shub_ucm_account($shub_ucm_id);
    if($shub_ucm_id && $ucm->get('shub_ucm_id') == $shub_ucm_id){

	    /* @var $groups shub_ucm_product[] */
	    $groups = $ucm->get('groups');
	    //print_r($groups);
	    ?>
	    <form action="" method="post" enctype="multipart/form-data">
		    <input type="hidden" name="_process" value="send_ucm_message">
			<?php wp_nonce_field( 'send-ucm' . (int) $ucm->get( 'shub_ucm_id' ) ); ?>
		    <?php
		    $fieldset_data = array(
			    'heading' => array(
				    'type' => 'h3',
				    'title' => 'Compose message',
				),
			    'class' => 'tableclass tableclass_form tableclass_full',
			    'elements' => array(
			       'ucm_product' => array(
			            'title' => __('ucm Group', 'support_hub'),
			            'fields' => array(),
			        ),
				    'message' => array(
					    'title' => __('message', 'support_hub'),
					    'field' => array(
						    'type' => 'textarea',
						    'name' => 'message',
						    'id' => 'ucm_compose_message',
						    'value' => '',
					    ),
				    ),
				    'type' => array(
					    'title' => __('Type', 'support_hub'),
					    'fields' => array(
						    '<input type="radio" name="post_type" id="post_type_wall" value="wall" checked> ',
						    '<label for="post_type_wall">',
						    __('Wall Post', 'support_hub'),
						    '</label>',
						    '<input type="radio" name="post_type" id="post_type_link" value="link"> ',
						    '<label for="post_type_link">',
						    __('Link Post', 'support_hub'),
						    '</label>',
						    '<input type="radio" name="post_type" id="post_type_picture" value="picture"> ',
						    '<label for="post_type_picture">',
						    __('Picture Post', 'support_hub'),
						    '</label>',
					    ),
				    ),
				    'link' => array(
					    'title' => __('Link', 'support_hub'),
					    'fields' => array(
						    array(
							    'type' => 'text',
							    'name' => 'link',
							    'id' => 'message_link_url',
							    'value' => '',
						    ),
						    '<div id="ucm_link_loading_message"></div>',
						    '<span class="ucm-type-link ucm-type-option"></span>', // flag for our JS hide/show hack
					    ),
				    ),
				    'link_picture' => array(
					    'title' => __('Link Picture', 'support_hub'),
					    'fields' => array(
						    array(
							    'type' => 'text',
							    'name' => 'link_picture',
							    'value' => '',
						    ),
						    ('Full URL (eg: http://) to the picture to use for this link preview'),
						    '<span class="ucm-type-link ucm-type-option"></span>', // flag for our JS hide/show hack
					    ),
				    ),
				    'link_name' => array(
					    'title' => __('Link Title', 'support_hub'),
					    'fields' => array(
						    array(
							    'type' => 'text',
							    'name' => 'link_name',
							    'value' => '',
						    ),
						    ('Title to use instead of the automatically generated one from the Link page'),
						    '<span class="ucm-type-link ucm-type-option"></span>', // flag for our JS hide/show hack
					    ),
				    ),
				    'link_caption' => array(
					    'title' => __('Link Caption', 'support_hub'),
					    'fields' => array(
						    array(
							    'type' => 'text',
							    'name' => 'link_caption',
							    'value' => '',
						    ),
						    ('Caption to use instead of the automatically generated one from the Link page'),
						    '<span class="ucm-type-link ucm-type-option"></span>', // flag for our JS hide/show hack
					    ),
				    ),
				    'link_description' => array(
					    'title' => __('Link Description', 'support_hub'),
					    'fields' => array(
						    array(
							    'type' => 'text',
							    'name' => 'link_description',
							    'value' => '',
						    ),
						    ('Description to use instead of the automatically generated one from the Link page'),
						    '<span class="ucm-type-link ucm-type-option"></span>', // flag for our JS hide/show hack
					    ),
				    ),
				    /*'track' => array(
					    'title' => __('Track clicks', 'support_hub'),
					    'field' => array(
						    'type' => 'check',
						    'name' => 'track_links',
						    'value' => '1',
						    'help' => 'If this is selected, the links will be automatically shortened so we can track how many clicks are received.',
						    'checked' => false,
					    ),
				    ),*/
				    'picture' => array(
					    'title' => __('Picture', 'support_hub'),
					    'fields' => array(
						    '<input type="file" name="picture" value="">',
						    '<span class="ucm-type-picture ucm-type-option"></span>', // flag for our JS hide/show hack
					    ),
				    ),
				    'schedule' => array(
					    'title' => __('Schedule', 'support_hub'),
					    'fields' => array(
						    array(
							    'type' => 'date',
							    'name' => 'schedule_date',
							    'value' => '',
						    ),
						    array(
							    'type' => 'time',
							    'name' => 'schedule_time',
							    'value' => '',
						    ),
						    ' ',
						    sprintf(__('Currently: %s','support_hub'),date('c')),
						    ' (Leave blank to send now, or pick a date in the future.)',
					    ),
				    ),
				    'debug' => array(
					    'title' => __('Debug', 'support_hub'),
					    'field' => array(
						    'type' => 'check',
						    'name' => 'debug',
						    'value' => '1',
						    'checked' => false,
						    'help' => 'Show debug output while posting the message',
					    ),
				    ),
			    )
			);
		    foreach($groups as $ucm_product_id => $group){
			    $fieldset_data['elements']['ucm_product']['fields'][] =
				    '<div id="ucm_compose_group_select">' .
				    '<input type="checkbox" name="compose_group_id['.$ucm_product_id.']" value="1" checked> ' .
				    '<img src="//graph.ucm.com/'.$ucm_product_id.'/picture"> ' .
				    htmlspecialchars($group->get('product_name')) .
				    '</div>'
			    ;
		    }
			echo shub_module_form::generate_fieldset($fieldset_data);


		    ?>
	    </form>

	    <script type="text/javascript">
		    function change_post_type(){
			    var currenttype = jQuery('[name=post_type]:checked').val();
			    jQuery('.ucm-type-option').each(function(){
				    jQuery(this).parents('tr').first().hide();
			    });
			    jQuery('.ucm-type-'+currenttype).each(function(){
				    jQuery(this).parents('tr').first().show();
			    });

		    }
		    jQuery(function(){
			    jQuery('[name=post_type]').change(change_post_type);
			    jQuery('#message_link_url').change(function(){
				    jQuery('#ucm_link_loading_message').html('<?php _e('Loading URL information...');?>');
				    jQuery.ajax({
					    url: '<?php echo '';?>',
					    data: {_process:'ajax_ucm_url_info', url: jQuery(this).val()},
					    dataType: 'json',
					    success: function(res){
						    jQuery('.ucm-type-link').each(function(){
							    var elm = jQuery(this).parent().find('input');
							    if(res && typeof res[elm.attr('name')] != 'undefined'){
								    elm.val(res[elm.attr('name')]);
							    }
						    });
					    },
					    complete: function(){
						    jQuery('#ucm_link_loading_message').html('');
					    }
				    });
			    });
			    change_post_type();
		    });
	    </script>

	    <?php
    }
}
?>