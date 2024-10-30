//Hide / Show
jQuery( document ).on( 'updated_checkout', function( e, data ) {
	jQuery( function( $ ) {
		var shipping_methods = {};
		$( 'select.shipping_method, input[name^="shipping_method"][type="radio"]:checked, input[name^="shipping_method"][type="hidden"]' ).each( function() {
			shipping_methods[ $( this ).data( 'index' ) ] = $( this ).val();
		} );
		if ( $( "#user_link_hidden_checkout_field" ).length ) {
		    $( "#user_link_hidden_checkout_field" ).remove();
		}

		//Only one shipping method chosen?

		$.each(shipping_methods, function( index, value ) {
			var shipping_method = $.trim( value );
			if ( $.inArray( shipping_method, cepw.shipping_methods ) >= 0 ) {
				var data = {
					action: 'cepw_display_option',
					shipping_method: shipping_method,
					index: index
				};
				$.ajax( {
					type:		'POST',
					url:		cepw["cepwajaxurl"],
					data:		data,
					success:	function( data ) {
						console.log(data);
						$("#cepw").append(data['input']);
					}
				} );
			}
		});



	} );
	
} );
