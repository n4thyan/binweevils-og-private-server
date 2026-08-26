(function($) {
    'use strict';
    $(function() {
        setInterval(function(){
            $.get("apis/getOnlineWeevils.php", { } ).done(function( data ) {
                data = JSON.parse(data.replace(/[^\x20-\x7E]/g, ""));
                console.log(data.weevils);
                $('#onlineweevils').html(data.weevils);
            });
        }, 10*1000); 
    });
})(jQuery);