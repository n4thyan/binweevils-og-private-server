<?php
error_reporting(0);
include('../../essential/backbone.php');
// Stub: check-invite-status. The SWF polls this during onboarding; the legacy invite
// system is inactive on this private server, so report "no pending invite" cleanly.
if(isset($_POST)) {
    echo 'responseCode=1&hasInvite=0';
}
else echo 'responseCode=999';
?>
