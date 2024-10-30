//Hide / Show

function cepw_get_Special_Services_Use(){
	var SpecialServices = [];
	jQuery('.SpecialServices').each(function(){
		var SpecialService = {
			SpecialService: jQuery(this).attr('specialservicestypes'),
			id: jQuery(this).attr('id')
		}
		SpecialServices.push(SpecialService);
	});
	return SpecialServices;
}


function cepw_disable_Special_Services_Used(){
	var SpecialServices = cepw_get_Special_Services_Use();
	jQuery('.SpecialServices .SpecialServicesTypesValues').each(function(){
		var parent = jQuery(this).closest('.SpecialServices');
		jQuery.each(SpecialServices, function( index, value ) {
			if(value.SpecialService){
				if(parent.attr('id') != value.id){
					parent.find('.'+value.SpecialService).attr("disabled", "disabled");
				}else{
					parent.find('.'+value.SpecialService).removeAttr( "disabled" );
				}
			}
		});
	});
}


jQuery( function( $ ) {

	if($('.TimePicker').length){
		$('body').on('focus',".TimePicker", function(){
			$('.TimePicker').datetimepicker({
				datepicker:false,
				format:'H:i'
			});
		});	
	}

	if($('.DatePicker').length){
		var dateToday = new Date();
		$('body').on('focus',".DatePicker", function(){
			$(this).datetimepicker({
				i18n:{
					de:{
						months:[
						 'Janeiro','Fevereiro','Março','Abril',
						 'Maio','Junho','Julho','Agosto',
						 'Setembro','Outobro','Novembro','Dezembro',
						],
						dayOfWeek:[
						 "Dom", "Seg", "Ter", "Qua", 
						 "Qui", "Sex", "Sáb",
						]
					}
				},
				minDate: dateToday,
				disabledWeekDays:[0,6],
				timepicker:false,
				format:'Y-m-d'
			});
		});
	}





	$('body').on('change', '.SpecialServices .SpecialServicesTypesValues', function (event) {
		var parent = $(this).closest('.SpecialServices');
		parent.find('.special_service_extra').removeClass('open');
		switch($(this).val()) {		
			case 'PostalObject':
				parent.find('.postalobject').addClass('open');
			 	break;
			case 'NominativeCheck':
				parent.find('.nominativecheck').addClass('open');
				break;
			case 'SpecialInsurance':
				parent.find('.specialinsurance').addClass('open');
				break;
			case 'ReturnDocumentSigned':
			  	parent.find('.dda').addClass('open');
			 	break;
			case 'MultipleHomeDelivery':
			  	parent.find('.multiplehomedelivery').addClass('open');
			  	if(parent.find('.multiplehomedelivery .InNonDeliveryCase').val() == 'SendToAddress'){
					parent.find('.seconddeliveryaddress').addClass('open');
				}
			 	break;
			case 'TimeWindow':
			  	parent.find('.TimeWindow').addClass('open');
			 	break;
			case 'CertainDay':
			  	parent.find('.CertainDay').addClass('open');
			 	break;
		}
		parent.attr('specialservicestypes',$(this).val());

		cepw_disable_Special_Services_Used();	
	});
	
	$('select[readonly="readonly"] option:not(:selected)').attr('disabled',true);


			
	$('body').on('click', '.multiplehomedelivery .InNonDeliveryCase', function (event) {
		var parent = $(this).closest('.SpecialServices');
		if($(this).val() == 'SendToAddress'){
			parent.find('.seconddeliveryaddress').addClass('open');
		}else{
			parent.find('.seconddeliveryaddress').removeClass('open');
		}
	});

	$(window).on("load", function(){
		//Disable Special Services Used
		cepw_disable_Special_Services_Used();
		if($('.SpecialServices').length){
			$('.SpecialServices .SpecialServicesTypesValues').each(function(index){
				var parent = $(this).closest('.SpecialServices');
				parent.find('.special_service_extra').removeClass('open');
				switch($(this).val()) {
					case 'PostalObject':
						parent.find('.postalobject').addClass('open');
					 	break;
					case 'NominativeCheck':
						parent.find('.nominativecheck').addClass('open');
					 	break;
					case 'SpecialInsurance':
						parent.find('.specialinsurance').addClass('open');
						break;
					case 'ReturnDocumentSigned':
					  	parent.find('.dda').addClass('open');
					 	break;
					case 'MultipleHomeDelivery':
					  	parent.find('.multiplehomedelivery').addClass('open');
					 	break;
					case 'TimeWindow':
					  	parent.find('.TimeWindow').addClass('open');
					 	break;
					case 'CertainDay':
					  	parent.find('.CertainDay').addClass('open');
					 	break;
				}
				parent.attr('specialservicestypes',$(this).val());
			});
		}

		if($('.multiplehomedelivery .InNonDeliveryCase').length){
			$('.SpecialServices .SpecialServicesTypesValues').each(function(){
				if($(this).val() == 'MultipleHomeDelivery'){
					var parent = $(this).closest('.SpecialServices');
					if(parent.find('.multiplehomedelivery .InNonDeliveryCase').val() == 'SendToAddress'){
						parent.find('.seconddeliveryaddress').addClass('open');
					}else{
						parent.find('.seconddeliveryaddress').removeClass('open');
					}
				}
			});
		}

	});


	$('body').on('click', '.SpecialServices .remove', function (event) {
		event.preventDefault();
		var SpecialService = $(this).attr('special_service');
		$('#' + SpecialService).remove();
		//Disable Special Services Used
		cepw_disable_Special_Services_Used();
	});


	$('.cepw_SpecialServices .add_more_special_service').click(function(e){
		e.preventDefault();
		var SpecialServices_count = parseInt($('input[name="SpecialServices_count"]').val()) + 1;
		var SpecialServices_order = $('input[name="SpecialServices_order"]').val();
		var SubProductId = $('input[name="SubProductId"]').val();
		var SpecialServices = JSON.stringify(cepw_get_Special_Services_Use());
		$.ajax({
			type:		'POST',
			url:		ajaxurl,
			data:		{
				action: 'cepw_add_more_SpecialService',
				SpecialServices_count: SpecialServices_count,
				SpecialServices_order: SpecialServices_order,
				SubProductId: SubProductId,
				SpecialServices_Use: SpecialServices
			},
			beforeSend: function(){
	            $('.cepw_SpecialServices .preloader').css('display','flex');
	        },
	        complete: function(){
	            $('.cepw_SpecialServices .preloader').css('display','none');
	        },
			success:function( data ) {
				console.log(data);
				if(data.response){
					$('.SpecialServicesList').append(data.response);
				}
				$('input[name="SpecialServices_count"]').val(SpecialServices_count);
			}
		});
	});


	$('.change_subproductId select[name="change_subproductId"]').change(function(){
		var SubProductId = $(this).val();
		if(SubProductId == "thirteen_multi" || SubProductId == "nineteen_multi"){
			$('.Quantity').removeClass('hide');
		}else{
			$('.Quantity').addClass('hide');
		}
	});
	

	$('.ctt_expresso_recolhas #doaction').click(function(e){
		e.preventDefault();

		var action = $('.ctt_expresso_recolhas #bulk-action-selector-top').val();
		if(action == 'NewOfferPickUp'){
		    $( "#cepw_recolhas_form" ).submit();
		}
		if(action == 'DeletePickUp'){
			var orders = [];
			$("input[name='orders[]']:checked").each(function () {
		    	var sThisVal = (this.checked ? $(this).attr("order_id") : "");
		    	orders.push(sThisVal);
		  	});
			$.ajax({
				type:		'POST',
				url:		ajaxurl,
				data:		{
					action: "cepw_RemoveOrderFromRecolhaList",
					orders: orders
				},
				success:function( data ) {
					location.reload();
				},
				error: function (request, status, error) {
			        console.err(request.responseText);
			    }
			});
		}
	});


	$('.cepw_create_single_order_recolha').click(function(e){
		e.preventDefault();
		var order_id = $(this).attr('order_id');
		var element = $('#post-'+order_id).find('.check_input').prop( "checked", true );
		$( "#cepw_recolhas_form" ).submit();
	});

	$('.cepw_create_recolha').submit(function(e){
		e.preventDefault();
		$.ajax({
			type:		'POST',
			url:		ajaxurl,
			data:		$(this).serialize(),
			beforeSend: function(){
	            $('.cepw_create_recolha .preloader').css('display','flex');
	        },
	        complete: function(){
	            $('.cepw_create_recolha .preloader').css('display','none');
	        },
			success:function( data ) {
				console.log(data);
				$('.ctt_expresso_recolhas .modal').addClass('show');
				if(data.status == "Failure"){
					$('.ctt_expresso_recolhas .modal').addClass('failure');
					$('.ctt_expresso_recolhas .modal').find('.resultfailure').show();
					$.each(data.ErrorList, function(index, value){
						var error = JSON.stringify(value,null,2);
						var html = '<p><strong>'+index + "</strong>: " + error +'</p>';
			            $('.ctt_expresso_recolhas .resultfailure').append(html);
			        });				
				}else{
					$('.ctt_expresso_recolhas .modal').addClass('success');
					$('.ctt_expresso_recolhas .modal').find('.resultsuccess').show();
					$('.ctt_expresso_recolhas .modal').find('#pickup_id').text(data.PickUpID);
					$('.ctt_expresso_recolhas .modal').find('#documents_list').html(data.files.toString());
				}
			}
		});
	});


	$('.ctt_expresso_recolhas .modal.failure').click(function(e){
		$('.ctt_expresso_recolhas .modal').removeClass('show');
	});

	$('.ctt_expresso_recolhas .modal.success').click(function(e){
		window.location.href = "/wp-admin/admin.php?page=cepw_recolhas";
	});
	
	
	
} );
	

