<?php
/**
 * Created by PhpStorm.
 * User: galina.logofatu
 * Date: 7/6/2018
 * Time: 3:12 PM
 */

$posturi = ['PRO TV','Antena 1','TVR 1','Prima TV','Kanal D','National TV','B1 TV','TVR 2','6TV','Agro TV'];


?>
<script>
    function setCookie(cname, cvalue, exdays) {
        var d = new Date();
        d.setTime(d.getTime() + (exdays * 24 * 60 * 60 * 1000));
        var expires = "expires="+d.toUTCString();
        document.cookie = cname + "=" + cvalue + ";" + expires + ";path=/";
    }

    function getCookie(cname) {
        var name = cname + "=";
        var ca = document.cookie.split(';');
        for(var i = 0; i < ca.length; i++) {
            var c = ca[i];
            while (c.charAt(0) == ' ') {
                c = c.substring(1);
            }
            if (c.indexOf(name) == 0) {
                return c.substring(name.length, c.length);
            }
        }
        return "";
    }

    function isCookie(cname) {
        var cookie = getCookie(cname);
        if (cookie == "") {
            return false;
        } else {
            return true;
        }
    }
</script>
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
<div id="name">
    <label>Name: </label>
    <input type="text" name="user_name">
</div>
<div id="control" style="padding-top: 10px;">
    <button type="button"><label for="switch_on">Pornire TV</label></button>
    <button type="button"><label for="switch_off">Oprire TV</label></button>
    <input type="radio" name="on" value="1" id="switch_on" style="display: none">
    <input type="radio" name="on" value="0" id="switch_off" style="display: none" checked="checked">
</div>
<div id="channels" style="padding-top: 10px;">
    <?php for ($i = 0; $i < count($posturi); $i++){ ?>
        <button type="button"><label for="tv-post-<?php echo $i; ?>"><?php echo $posturi[$i]; ?></label></button>
        <input type="radio" name="tv_post" value="<?php echo $posturi[$i]; ?>" id="tv-post-<?php echo $i; ?>" style="display: none">
    <?php } ?>
</div>

<script>
    var events = [];
    // var data = {'events' : []};
    var data = {};
    var prev_ts = '';
    var nr_change = 0;
    var prev_tv_post = '';
    var current_tv_post = '';

    $(document).ready(function(){
        $('[name="on"]').trigger('change');
    });

    $('[name="on"]').on('change', function(){
        if($('[name="on"]:checked').val() == 0){
            $('label[for="switch_on"]').parent().attr('disabled',false);
            $('label[for="switch_off"]').parent().attr('disabled',true);
            $('#channels button').attr('disabled',true);
            $('label[for="switch_on"]').parent().show();
            $('label[for="switch_off"]').parent().hide();
        }
        if($('[name="on"]:checked').val() == 1){
            $('label[for="switch_on"]').parent().attr('disabled',true);
            $('label[for="switch_off"]').parent().attr('disabled',false);
            $('#channels button').attr('disabled',false);
            $('label[for="switch_on"]').parent().hide();
            $('label[for="switch_off"]').parent().show();
        }
    })

    $('[name="tv_post"]').on('change', function(){
        if ($('#channels button').attr('disabled') != "disabled"){
            if (nr_change == 0){
                prev_ts = $.now()/1000;
            }
            if (nr_change > 0){
                change();
            }
            nr_change++;
            current_tv_post = $('[name="tv_post"]:checked').val();
            prev_tv_post = current_tv_post;
        }
    })

    $('#channels button').on('click', function(){
        if ($('#channels button').attr('disabled') != "disabled"){
            $(this).find('label')[0].click();
            // $('[name="tv_post"]').trigger('change');
        }
    });

    // $('#control button').on('click', function(){
    //     $(this).find('label')[0].click();
    // });

    $('[name="user_name"]').on('focusout', function(){
        setCookie("user_name",$('[name="user_name"]').val(),1);
        $('[name="user_name"]').val('');
        check_name();
    });

    // $('[name="user_name"]').keypress(function(e) {
    //     if(e.which == 13) {
    //         setCookie("user_name",$('[name="user_name"]').val(),1);
    //         $('[name="user_name"]').val('');
    //         $('[name="user_name"]').attr('placeholder',getCookie('user_name'));
    //     }
    // });

    check_name();

    function check_name(){
        if (isCookie('user_name')){
            $('[name="user_name"]').attr('placeholder',getCookie('user_name'));
            $('[name="user_name"]').val(getCookie('user_name'));
            $('#control').show();
            $('#channels').show();
        }else{
            $('[name="user_name"]').attr('placeholder','');
            $('[name="user_name"]').val('');
            $('#control').hide();
            $('#channels').hide();
        }
    }

    $('label[for="switch_on"]').parent().on('click', function(){
        // switch_on_time = new Date();
        prev_ts = $.now()/1000;
        item = {}
        item ["name"] = 'switch_on';
        item ["ts"] = prev_ts;
        events.push(item);
    });

    $('label[for="switch_off"]').parent().on('click', function(){
        change();
        item = {}
        item ["name"] = 'switch_off';
        if (nr_change == 0) {
            item ["ts"]= $.now()/1000;
        } else {
            item ["ts"] = prev_ts;
        }

        // console.log($.now()/1000);
        // console.log(prev_ts);

        events.push(item);

        send_json();
    });

    function send_json(){
        // data.serialize();
        // console.log(data.toString);
        // try {
        //     var date = new Date($.ajax({'type': 'HEAD', 'url': '/'}).getResponseHeader('Date'));
        // }
        // catch(err) {
        //     var date = null;
        // }
        // data.serverTime = date;
        // data.clientIP = 'ceva';
        data.clientName = getCookie('user_name');
        // data.os = 'os';
        // data.browser = 'browser';
        // data.browser_ver = 'browser_ver';
        data.events = events;

        console.log(JSON.stringify(data));
        events = [];
        nr_change = 0;
        $.ajax({
            url: "save_data.php",
            method: "POST",
            data: data,
            dataType: "json",
            success: function(data) {
                return data;
            },
            error: function(xhr,status,error) {
                alert('something went wrong', status, error );
            }
        });
    }

    function change(){
        if (nr_change == 0){
            return;
        }
        var now = $.now()/1000;
        var time_spent = now - prev_ts;
        prev_ts = now;
        item = {}
        item ["name"]       = 'change';
        item ["tv_post"]    = prev_tv_post;
        item ["ts"]         = prev_ts;
        item ["time_spent"]  = time_spent;
        events.push(item);
    }



</script>



