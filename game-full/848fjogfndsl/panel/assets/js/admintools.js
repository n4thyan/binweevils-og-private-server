(function($) {
    'use strict';
    $(function() {
        /* Admin Banning */
        $('#sidebar').on("click", '#adm-banning', function(event) {
            Swal.fire({
                html: `
                <div class="grid-margin stretch-card" style="width:600px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Banning</h4>
                            <div class="forms-sample">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">@</span>
                                        </div>
                                        <input type="text" class="form-control" id="banned-weevil" placeholder="Weevil Name" aria-label="Weevil Name" aria-describedby="basic-addon1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="ban-length" placeholder="Length [Days, 'MAX' = Permanent]">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="ban-reason" placeholder="Reason">
                                </div>
                                <button class="btn btn-primary mr-2" id="confirm-ban">Confirm</button>
                                <button class="btn btn-dark" id="close-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>`,
                imageAlt: "AppleFN",
                customClass: 'swal-wide',
                showConfirmButton: false
            });
            //$(".swal2-popup").css('background', '#101010');
            $(".swal2-popup").css('background', 'linear-gradient(45deg, transparent,transparent)');
            $(".swal2-content").css('color','#fff');
            $('.swal2-title').css('color', '#3e53b9');
            $('.swal2-image').css('border-radius', '5px');

            $('#close-cancel').on("click", function(event) {
                Swal.close();
            });

            $('#confirm-ban').on("click", function(event) {
                var weevil = $('#banned-weevil').val();
                var length = $('#ban-length').val();
                var reason = $('#ban-reason').val();

                if(weevil == "" || length == "" || reason == "")
                alert('Please fill out all fields.');
                else {
                    $.get("apis/banUser.php", { adminName: window.adminName, adminPassword: window.adminToken, weevilName: weevil, duration: (length == "MAX" ? 999 : length), banReason: reason }).done(function(data) {
                        var lengthh = $(data).length;

                        if(lengthh == 0) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Successfully banned ' + weevil,
                                showConfirmButton: false,
                                timer: 1500
                              });
                        }
                        else alert('something went wrong.');
                    });
                }
            });
        });
        /* Admin Banning */

        /* Admin Warning */
        $('#sidebar').on("click", '#adm-warning', function(event) {
            Swal.fire({
                html: `
                <div class="grid-margin stretch-card" style="width:600px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Warning</h4>
                            <div class="forms-sample">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">@</span>
                                        </div>
                                        <input type="text" class="form-control" id="warned-weevil" placeholder="Weevil Name" aria-label="Weevil Name" aria-describedby="basic-addon1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="warning-message" placeholder="Message">
                                </div>
                                <button class="btn btn-primary mr-2" id="confirm-warning">Confirm</button>
                                <button class="btn btn-dark" id="close-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>`,
                imageAlt: "AppleFN",
                customClass: 'swal-wide',
                showConfirmButton: false
            });
            //$(".swal2-popup").css('background', '#101010');
            $(".swal2-popup").css('background', 'linear-gradient(45deg, transparent,transparent)');
            $(".swal2-content").css('color','#fff');
            $('.swal2-title').css('color', '#3e53b9');
            $('.swal2-image').css('border-radius', '5px');

            $('#close-cancel').on("click", function(event) {
                Swal.close();
            });

            $('#confirm-warning').on("click", function(event) {
                var weevil = $('#warned-weevil').val();
                var message = $('#warning-message').val();

                if(weevil == "" || message == "")
                alert('Please fill out all fields.');
                else {
                    $.get("apis/warnUser.php", { adminName: window.adminName, adminPassword: window.adminToken, weevilName: weevil, modMsg: message }).done(function(data) {
                        var length = $(data).length;

                        if(length == 0) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Successfully warned ' + weevil,
                                showConfirmButton: false,
                                timer: 1500
                              });
                        }
                        else alert('something went wrong.');
                    });
                }
            });
        });
        /* Admin Warning */

        /* Admin Kicking */
        $('#sidebar').on("click", '#adm-kicking', function(event) {
            Swal.fire({
                html: `
                <div class="grid-margin stretch-card" style="width:600px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Kicking</h4>
                            <div class="forms-sample">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">@</span>
                                        </div>
                                        <input type="text" class="form-control" id="kicked-weevil" placeholder="Weevil Name" aria-label="Weevil Name" aria-describedby="basic-addon1">
                                    </div>
                                </div>
                                <button class="btn btn-primary mr-2" id="confirm-kick">Confirm</button>
                                <button class="btn btn-dark" id="close-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>`,
                imageAlt: "AppleFN",
                customClass: 'swal-wide',
                showConfirmButton: false
            });
            //$(".swal2-popup").css('background', '#101010');
            $(".swal2-popup").css('background', 'linear-gradient(45deg, transparent,transparent)');
            $(".swal2-content").css('color','#fff');
            $('.swal2-title').css('color', '#3e53b9');
            $('.swal2-image').css('border-radius', '5px');

            $('#close-cancel').on("click", function(event) {
                Swal.close();
            });

            $('#confirm-kick').on("click", function(event) {
                var weevil = $('#kicked-weevil').val();

                if(weevil == "")
                alert("Please fill out all fields.");
                else {
                    $.get("apis/kickUser.php", { adminName: window.adminName, adminPassword: window.adminToken, weevilName: weevil }).done(function(data) {
                        var length = $(data).length;

                        if(length == 0) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Successfully kicked ' + weevil,
                                showConfirmButton: false,
                                timer: 1500
                              });
                        }
                        else alert('something went wrong.');
                    });
                }
            });
        });
        /* Admin Kicking */

        /* Admin Rewarding */
        $('#sidebar').on("click", '#adm-rewarding', function(event) {
            Swal.fire({
                html: `
                <div class="grid-margin stretch-card" style="width:600px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Item Rewarding</h4>
                            <div class="forms-sample">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">@</span>
                                        </div>
                                        <input type="text" class="form-control" id="rewarded-weevil" placeholder="Weevil Name" aria-label="Weevil Name" aria-describedby="basic-addon1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="rewarded-item" placeholder="Item Name">
                                </div>
                                <button class="btn btn-primary mr-2" id="confirm-reward">Confirm</button>
                                <button class="btn btn-dark" id="close-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>`,
                imageAlt: "AppleFN",
                customClass: 'swal-wide',
                showConfirmButton: false
            });
            //$(".swal2-popup").css('background', '#101010');
            $(".swal2-popup").css('background', 'linear-gradient(45deg, transparent,transparent)');
            $(".swal2-content").css('color','#fff');
            $('.swal2-title').css('color', '#3e53b9');
            $('.swal2-image').css('border-radius', '5px');

            $('#close-cancel').on("click", function(event) {
                Swal.close();
            });

            $('#confirm-reward').on("click", function(event) {
                var weevil = $('#rewarded-weevil').val();
                var item = $('#rewarded-item').val();
                
                if(weevil == "" || item == "") alert('Please fill out all fields.');
                else {
                    $.get("apis/rewardNestItem.php", { adminName: window.adminName, adminPassword: window.adminToken, weevilName: weevil, itemName: item }).done(function(data) {
                        //var length = $(data).length;

                        if(data.toString() == "1") {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Successfully gave ' + weevil + ' a nest item',
                                showConfirmButton: false,
                                timer: 1500
                              });
                        }
                        else alert('something went wrong.');
                    });
                }
            });
        });
        /* Admin Rewarding */
               /* Admin Garden Rewarding */
               $('#sidebar').on("click", '#adm-garden-rewarding', function(event) {
                Swal.fire({
                    html: `
                    <div class="grid-margin stretch-card" style="width:600px;">
                        <div class="card">
                            <div class="card-body">
                                <h4 class="card-title">Garden Rewarding</h4>
                                <div class="forms-sample">
                                    <div class="form-group">
                                        <div class="input-group">
                                            <div class="input-group-prepend">
                                                <span class="input-group-text">@</span>
                                            </div>
                                            <input type="text" class="form-control" id="rewarded-weevil" placeholder="Weevil Name" aria-label="Weevil Name" aria-describedby="basic-addon1">
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <input type="text" class="form-control" id="rewarded-item" placeholder="Garden Item Name">
                                    </div>
                                    <button class="btn btn-primary mr-2" id="confirm-reward">Confirm</button>
                                    <button class="btn btn-dark" id="close-cancel">Cancel</button>
                                </div>
                            </div>
                        </div>
                    </div>`,
                    imageAlt: "AppleFN",
                    customClass: 'swal-wide',
                    showConfirmButton: false
                });
                //$(".swal2-popup").css('background', '#101010');
                $(".swal2-popup").css('background', 'linear-gradient(45deg, transparent,transparent)');
                $(".swal2-content").css('color','#fff');
                $('.swal2-title').css('color', '#3e53b9');
                $('.swal2-image').css('border-radius', '5px');
    
                $('#close-cancel').on("click", function(event) {
                    Swal.close();
                });
    
                $('#confirm-reward').on("click", function(event) {
                    var weevil = $('#rewarded-weevil').val();
                    var item = $('#rewarded-item').val();
                    
                    if(weevil == "" || item == "") alert('Please fill out all fields.');
                    else {
                        $.get("apis/rewardGardenItem.php", { adminName: window.adminName, adminPassword: window.adminToken, weevilName: weevil, itemName: item }).done(function(data) {
                            //var length = $(data).length;
    
                            if(data.toString() == "1") {
                                Swal.fire({
                                    position: 'top-end',
                                    icon: 'success',
                                    title: 'Successfully gave ' + weevil + ' a nest item',
                                    showConfirmButton: false,
                                    timer: 1500
                                  });
                            }
                            else alert('something went wrong.');
                        });
                    }
                });
            });
            /* Admin Rewarding */
        /* Admin Chat Muting */
        $('#sidebar').on("click", '#adm-muting', function(event) {
            Swal.fire({
                html: `
                <div class="grid-margin stretch-card" style="width:600px;">
                    <div class="card">
                        <div class="card-body">
                            <h4 class="card-title">Muting</h4>
                            <div class="forms-sample">
                                <div class="form-group">
                                    <div class="input-group">
                                        <div class="input-group-prepend">
                                            <span class="input-group-text">@</span>
                                        </div>
                                        <input type="text" class="form-control" id="muted-weevil" placeholder="Weevil Name" aria-label="Weevil Name" aria-describedby="basic-addon1">
                                    </div>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="mute-duration" placeholder="Duration [MINUTES, 'MAX' = Permanent]">
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="mute-reason" placeholder="Reason">
                                </div>
                                <button class="btn btn-primary mr-2" id="confirm-muting">Confirm</button>
                                <button class="btn btn-dark" id="close-cancel">Cancel</button>
                            </div>
                        </div>
                    </div>
                </div>`,
                imageAlt: "AppleFN",
                customClass: 'swal-wide',
                showConfirmButton: false
            });
            //$(".swal2-popup").css('background', '#101010');
            $(".swal2-popup").css('background', 'linear-gradient(45deg, transparent,transparent)');
            $(".swal2-content").css('color','#fff');
            $('.swal2-title').css('color', '#3e53b9');
            $('.swal2-image').css('border-radius', '5px');

            $('#close-cancel').on("click", function(event) {
                Swal.close();
            });

            $('#confirm-muting').on("click", function(event) {
                var weevil = $('#muted-weevil').val();
                var durations = $('#mute-duration').val();
                var reason = $('#mute-reason').val();
                if(weevil == "" || durations == "" || reason == "") alert('Please fill out all fields.');
                else {
                    $.get("apis/chatBanUser.php", { adminName: window.adminName, adminPassword: window.adminToken, weevilName: weevil, duration: (durations == "MAX" ? "999999" : durations), reasonForMute: reason }).done(function(data) {
                        var length = $(data).length;

                        if(length == 0) {
                            Swal.fire({
                                position: 'top-end',
                                icon: 'success',
                                title: 'Successfully muted ' + weevil,
                                showConfirmButton: false,
                                timer: 1500
                              });
                        }
                        else alert('something went wrong.');
                    });
                }
            });
        });
        /* Admin Chat Muting */

    });
})(jQuery);