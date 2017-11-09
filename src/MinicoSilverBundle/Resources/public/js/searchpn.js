$(function(){
   $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });
   var route1,
       route2,
       inputValue,
       productId,
       saleValue,
       withdrawValue,
       entriesValue,
       date,
       route1 = Routing.generate('sales_filter_search_by_pn'),
       route2 = Routing.generate('sales_save_sales_withdraws_entries');

    if( $('#ui-id-2').length )
    {
        // it exists
        $("#ui-id-2").click(function() {
            //var value = $(".custom-combobox-input:first").val();
            //getProductsForStorage(value);
            $( "form" ).submit();
        });

    } else {

        $( ".custom-combobox-input:first" ).change(function() {
            //var value = $(this).val();
            //getProductsForStorage(value);
            $( "form" ).submit();
        });

        $("#ui-id-1").click(function() {
            //var value = $(".custom-combobox-input:first").val();
            //getProductsForStorage(value);
            $( "form" ).submit();
        });
    }

   $("#click").click(function(){
        inputValue = $('#productId').val();
        getProductInfo(inputValue, route1);
    });

    $("#productId").keypress(function(e) {
        if(e.which == 13) {
            inputValue = $('#productId').val();
            getProductInfo(inputValue, route1);
        }
    });

    $("body").on('click',".save[save='saveInfoToDatabase']",function(){
        productId       = $(this).attr('idprodus');
        saleValue       = $(".sale[idprodus =  '"+productId+"']").val();
        withdrawValue   = $(".withdrawls[idprodus =  '"+productId+"']").val();
        entriesValue    = $(".entries[idprodus =  '"+productId+"']").val();
        date            = $("#datepicker").val();

        $.ajax({
            url: route2,
            type: 'POST',
            async: false,
            data: {
                'saleValue':        saleValue,
                'withdrawValue':    withdrawValue,
                'entriesValue':     entriesValue,
                'productId':        productId,
                'date':             date
            },
            success: function(msg2) {
                $("#result_2").html(msg2);
                getProductInfo(inputValue, route1);
            },
            error: function(msg2) {
                alert('eroare'+msg2.responseText);
            }
        });

    });
});

function getProductInfo(productId, route) {
    $.ajax({
        url: route,
        type: 'POST',
        async: false,
        data: { 'productId': productId },
        success: function(msg) {
            $("#result").html(msg);
        },
        error: function(msg) {
            alert('eroare'+msg.responseText);
        }
    });
};

function getProductsForStorage(storageName) {
    $.ajax({
        url: Routing.generate('sales_get_selling_items'),
        type: 'POST',
        async: false,
        data: { 'storageName': storageName },
        success: function(data) {
            $("body minico_silverbundle_sales_productId").empty();
            $.each( data.items, function( i, item ) {
                $( "body minico_silverbundle_sales_productId" ).append( '<option value="'+i+'">'+item+'</option>');
            });
            //$("#result").html(msg);
        },
        error: function(msg) {
            alert('eroare'+msg.responseText);
        }
    });
};