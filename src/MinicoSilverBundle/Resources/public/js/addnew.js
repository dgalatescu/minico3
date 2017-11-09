/**
 * Created by dan.galatescu on 1/1/14.
 */


$(function(){
    $( "#datepicker" ).datepicker({ dateFormat: 'yy-mm-dd' });

    var entryPrice,
        supplierCode;

    $("body").on('change',"#entryPrice",function(){;
       entryPrice = $(this).val();
       supplierCode = $("#supplierId option:selected").attr("code");
       addProductCode(entryPrice, supplierCode)
    });

    $("body").on('change',"#supplierId",function(){
        entryPrice = $("#entryPrice").val();
        supplierCode = $("#supplierId option:selected").attr("code");
        addProductCode(entryPrice, supplierCode)
    });

    $("body").on('change',"#margin",function(){
        updateSalePrice();
    });

    $("body").on('change',"#margin",function(){
        entryPrice = $("#entryPrice").val();
        supplierCode = $("#supplierId option:selected").attr("code");
        addProductCode(entryPrice, supplierCode)
    })

    $("body").on('click',"#save",function(){
     var productCode    = $("#productCode").val(),
         salePrice      = $("#salePrice").val(),
         supplierId     = $("#supplierId option:selected").val(),
         entryPrice     = $("#entryPrice").val(),
         description    = $("#description").val(),
         categoryId     = $("#categoryId option:selected").val(),
         quantity       = $("#quantity").val(),
         date           = $("#datepicker").val(),
         route          = Routing.generate('save_add_new_entries');

        $.ajax({
            url: route,
            type: 'POST',
            async: false,
            data: {
                'productCode'   :        productCode,
                'salePrice'     :        salePrice,
                'supplierId'    :        supplierId,
                'entryPrice'    :        entryPrice,
                'description'   :        description,
                'categoryId'    :        categoryId,
                'quantity'      :        quantity,
                'date'          :        date
            },
            success: function(msg2) {
                $("#result").html('');
                $("#result").html(msg2);
//                getProductInfo(inputValue, route1);
            },
            error: function(msg2) {
                alert('eroare'+msg2.responseText);
            }
        });

    });
});

function updateSalePrice(){
    $("#salePrice").val('');
    var entryPrice = $("#entryPrice").val();
    var margin      = $("#margin option:selected").val();

    if (typeof entryPrice !== "undefined" && entryPrice !== '' && typeof margin !== "") {
        var salePrice   = parseInt(entryPrice)*((parseInt(margin) + 100)/100);
        $("#salePrice").val(salePrice);
    }
}

function addProductCode(entryPrice, supplierCode) {
    $("#productCode").val('');
    updateSalePrice();
    var mounthId = $("#datepicker").val().substring(5,7);
    var mounthCode = getMounthCode(mounthId);

    if (typeof entryPrice !== "undefined" && typeof supplierCode !== "undefined" && entryPrice !== '' && mounthCode !== 'NU') {
        $("#productCode").val(supplierCode+mounthCode+entryPrice)
    }
}

function getMounthCode(mounthId){
    switch(mounthId)
    {
        case '01':
            return 'I'
            break;
        case '02':
            return 'F'
            break;
        case '03':
            return 'M'
            break;
        case '04':
            return 'I'
            break;
        case '05':
            return 'I'
            break;
        case '06':
            return 'I'
            break;
        case '07':
            return 'I'
            break;
        case '08':
            return 'I'
            break;
        case '09':
            return 'I'
            break;
        case '10':
            return 'I'
            break;
        case '11':
            return 'I'
            break;
        case '12':
            return 'I'
            break;
        default:
            return 'NU'
    }
}
