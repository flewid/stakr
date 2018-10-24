/**
 * Created by Nurbek on 12/24/14.
 */


$( document ).ready(function() {
    $('#signButton').click(function(){
        window.location.href = regUrl;
    });
    $('#resetButton').click(function(){
        window.location.href = resetUrl;
    });

    $('.datepicker').datepicker({
        format: 'dd-mm-yyyy'
        //format: 'yyyy-mm-dd'
    }).on('changeDate', function(ev) {
        $('#metalstack-metalsymbol').change();
    });;

    $('body').on('change', '[name="MetalStack[roll]"]:radio', function(event){
        var val = $('[name="MetalStack[roll]"]:radio:checked').val();
        if(val==1)
        {
            $('.field-metalstack-roll_id').hide();
            $('#metalstack-roll_id').val('');
        }
        else
        {
            $('.field-metalstack-roll_id').show();
        }
    });
    $('[name="MetalStack[roll]"]:radio').change();


    $('body').on('click', '.down', function(event){
        var $down=$(this);
        var id=$down.data('id');
        var $tr=$down.parents('tr');
        var $loading=$down.next();

        if($('.kidsFrom-'+id).length)
            $('.kidsFrom-'+id).show(1000);
        else
            $.ajax({
                    url:baseUrl+'/metal-stack/down',
                    data:{id:id},
                    beforeSend:function()
                    {
                        $loading.show();
                    },
                    success:function(data)
                    {
                        $loading.hide();
                        $tr.after(data);
                        $('.kidsFrom-'+id).show(500);
                    }
            });
        $down.replaceWith('<a data-id="'+id+'" data-pjax="0" title="Up" href="javascript:void(0);" class="up  btn btn-primary btn-xs">Hide roll</a>');
        return false;
    });

    $('body').on('click', '.up', function(event){
        $('.kidsFrom-'+$(this).data('id')).hide(500);

        var id = $(this).data('id');
        $(this).replaceWith('<a data-id="'+id+'" data-pjax="0" title="Down" href="javascript:void(0);" class="down  btn btn-primary btn-xs">View roll</a>');
        return false;
    });

});



var beforeunload=false;
$(window).bind('beforeunload', function(){
    if(beforeunload)
        return 'Are you sure you have downloaded the stacks ?';
});

$('body').on('click', '.downloadStacks', function(event){
    //jQuery('#customModal').modal({"show":true});
    $('#customModal').modal('show');
    return false;
});

$('body').on('click', '.downloadDeleteLink', function(event){

    bootbox.confirm("Are you sure to delete the stacks ?", function(result) {

        beforeunload = true;
        $.ajax({
            url: baseUrl+ '/metal-stack/download',
            data:{download:0},
            beforeSend:function(){
                $('#loading').show();
            },
            success:function()
            {
                $.ajax({
                    url: baseUrl+ '/metal-stack/truncate',
                    success:function()
                    {
                        beforeunload = false;
                        $('#loading').hide();
                        window.location.href = baseUrl  + '/metal-stack/readfile';
                        setTimeout(function(){
                            window.location.href = baseUrl  + '/metal-stack';
                        }, 3000);

                    }
                });
            }
        });
    });
    return false;
});



$(document).on('pjax:success', function(event, data, status, xhr, options) {
    var $title = $('title', $(data).context);
    $('section .page-header h2').html($title.html());

    var $bread=$('section header .right-wrapper', $(data));
    $('section header .right-wrapper').replaceWith($bread);
});

$('body').on('change', '.offline_modeUpdate', function(event){
    $.ajax({
            url: baseUrl+ '/metal-stack/update-mode',
            data:{checked:$(this).prop('checked') ? 1:0},
            beforeSend:function(){
                $('#loading').show();
            },
            success:function(data)
            {
                $.notify("Offline mode saved", "success");
                //$("#offline_mode_label").notify("Offline mode saved", "success");
                $('#loading').hide();
            }
    });
});

$('body').on('click', '[name="Form[trade_radio]"]', function(event){
    if($('[name="Form[trade_radio]"]:checked').val()=='stack')
    {
        $('.field-form-trade_id').show();
        $('.field-form-trade_text').hide();
    }
    if($('[name="Form[trade_radio]"]:checked').val()=='other')
    {
        $('.field-form-trade_id').hide();
        $('.field-form-trade_text').show();
    }

});

$('body').on('click', '.refreshStacks', function(event){
    $.ajax({
            url:baseUrl + '/metal-stack/list',
            type:'post',
            data:{trade_id:$('[name="Form[stack_id]"]').val()},
            success:function(data)
            {
                $('#form-trade_id').html(data);
                $('#form-trade_id').selectpicker('refresh');
            }
    });
});
$('body').on('click', '.tradeButton', function(event){
    $('#form-stack_id').val($(this).data('id'));
    $('.refreshStacks').click();
    $('#tradeModal').modal('show');
    return false;
});


$('body').on('click', '.soldButton', function(event){
    $('#soldform-stack_id').val($(this).data('id'));
    var action = baseUrl + '/metal-stack/sold?id='+$(this).data('id');
    $('#soldModal form').attr('action', action);
    $('#soldForm').data('yiiActiveForm').settings.validationUrl=action;
    $('#soldModal').modal('show');
    return false;
});







$('#metalstack-metalquantity, #metalstack-metalpurchaseprice, #metalstack-metalshippingcost').keyup(function(){
    var quantity = parseFloat($('#metalstack-metalquantity').val()) || 0;
    var price = parseFloat($('#metalstack-metalpurchaseprice').val()) || 0;
    var shipping = parseFloat($('#metalstack-metalshippingcost').val()) || 0;
    $('#metalstack-metaltotalpaid').val( (quantity * price) + shipping || 0)
});
$('#metalstack-weight, #metalstack-metalquantity').keyup(function(){
    var quantity = parseFloat($('#metalstack-metalquantity').val()) || 0;
    var weight = parseFloat($('#metalstack-weight').val()) || 0;
    var spotprice = parseFloat($('#metalstack-spotprice').val()) || 0;
    $('#metalstack-totalspotprice').val( (quantity * weight * spotprice) || 0)
});

$('#metalstack-numismatic_value, #metalstack-metalquantity').keyup(function(){
    var quantity = parseFloat($('#metalstack-metalquantity').val()) || 0;
    var numismatic_value = parseFloat($('#metalstack-numismatic_value').val()) || 0;
    $('#metalstack-total_numismatic_value').val( (quantity * numismatic_value) || 0)
});


$('body').on('change', '#metalstack-metalsymbol', function(event){

    if($(this).val())
    {
        //var text = $(\"#metalstack-metalsymbol option:selected\").text();
        $.ajax({
            url:baseUrl + '/history/list',
            data:{metal_id:$(this).val(), date:$('#metalstack-metalpurchasedate').val()},
            success:function(data)
            {
                $('#metalstack-spotprice').val(data.metalValue);
                $('.currentPriceSpan').html(data.metalValue);
                $('#metalstack-weight').keyup();
                if(data.nearDate)
                    $.notify("The spot price was taken from date "+data.nearDate, "warning");
            }
        });
    }
    else
    {
        $('#metalstack-spotprice').val('');
        $('.currentPriceSpan').html(0);
    }
    $('#metalstack-weight').keyup();
});