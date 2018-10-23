jQuery( document ).on('click' , '[name="coolrunner_search_droppoints"]', function() {
    jQuery('#map_wrapper').css("visibility","visible");
    if( jQuery('[name="coolrunner_zip_code_search"]').val().length >= 4){
        var post_code = jQuery('[name="coolrunner_zip_code_search"]').val();
        var used_postcode;

        if(jQuery('[name="ship_to_different_address"]').is(':checked')){
            var used_postcode = jQuery("[name='shipping_postcode']").val();
            var street = jQuery('[name="shipping_address_1"]').val();
        } else {
            var used_postcode = jQuery("[name='billing_postcode']").val();
            var street = jQuery('[name="billing_address_1"]').val();
        }

        var  city = jQuery('[name="billing_city"]').val();

        jQuery.ajax({
            url : post_search.ajax_url,
            type : 'post',
            data : {
                action : 'post_search',
                post_code : post_code,
                street : street,  
                city : city
                      },
            success : function( response ) {
                jQuery.getScript(post_search.map_url);                
                jQuery('#coolrunner-dao-droppoint-wrapper').show();
                jQuery('#coolrunner-dao-droppoint-wrapper').html( response );
                
            },
            fail: function(){
                jQuery('#coolrunner-dao-droppoint-wrapper').html("Noget gik galt, kontakt webshop ejeren")
            }
        });
    }
});

/*
jQuery( document ).on('click' , '[name="droppoint-address"]', function() {
    
        var droppoint_id=jQuery(this).attr("id");
 //       alert(droppoint_id);

        jQuery.ajax({
            url : post_search.ajax_url,
            type : 'post',
            data : {
                action : 'post_droppoint',
                droppoint_id : droppoint_id
                  },
            success : function( response ) {
                jQuery('#coolrunner-dao-opening-container').show();
                jQuery('#coolrunner-dao-opening-container').html( response );
            },
            fail: function(){
                jQuery('#coolrunner-dao-opening-container').html("Noget gik galt, kontakt webshop ejeren")
            }
        });
   
});
*/

jQuery( document ).ready(function() {
    showHideDroppointSelector();

    jQuery(document).on('change', '[name="shipping_method[0]"]', function(){
        showHideDroppointSelector();
        
    });

    jQuery(document).on('click', '.shipping_method', function(){
        showHideDroppointSelector();
        jQuery('#coolrunner_zip_code_search').val('');
        jQuery('#coolrunner-search-results').hide();
    });

    jQuery("#coolrunner_search_droppoints").fancybox({
        clickOutside  : "disable", // prevents closing when clicking INSIDE fancybox 
    
       });

    
})

function showHideDroppointSelector() {
     var selectedOption = jQuery('[name="shipping_method[0]"]:checked').val();

     if(selectedOption.indexOf('coolrunner') >= 0 && selectedOption.indexOf('droppoint') >= 0) {
          jQuery('[name="coolrunner_select_shop"]').show();
     }else {
        jQuery('[name="coolrunner_select_shop"]').hide();     

     }
}


