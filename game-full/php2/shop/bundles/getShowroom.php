<?php
error_reporting(0);
include('../../../essential/backbone.php');
// Stub: shop/bundles/getShowroom. Returns an empty showroom bundle list so the
// Bin Mart showroom renders without a 404.
if(isset($_POST) || isset($_GET)) {
    echo 'responseCode=1&bundles=';
}
else echo 'responseCode=999';
?>
