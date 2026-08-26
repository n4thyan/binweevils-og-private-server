<?php
include '../../essential/backbone.php';
include '../../essential/admin_guard.php';
enforceAdminAccess();

$page = 0;
$developmentProg = getNextPage($page);

$today = strtotime('today midnight') - 1;
$lasthr = time() - (1*60*60);

$totalWeevils = GetJoinedWeevils(-1);
$weevilsToday = GetJoinedWeevils($today);
$weevilslasthr = GetJoinedWeevils($lasthr);

$logpage = 0;
$adminLogs = getNextPageAdminLog($logpage);

?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <!-- Required meta tags -->
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
        <title>BWR Admin</title>
        <!-- plugins:css -->
        <link rel="stylesheet" href="assets/vendors/mdi/css/materialdesignicons.min.css">
        <link rel="stylesheet" href="assets/vendors/css/vendor.bundle.base.css">
        <!-- endinject -->
        <!-- Plugin css for this page -->
        <link rel="stylesheet" href="assets/vendors/jvectormap/jquery-jvectormap.css">
        <link rel="stylesheet" href="assets/vendors/flag-icon-css/css/flag-icon.min.css">
        <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.carousel.min.css">
        <link rel="stylesheet" href="assets/vendors/owl-carousel-2/owl.theme.default.min.css">
        <!-- End plugin css for this page -->
        <!-- inject:css -->
        <!-- endinject -->
        <!-- Layout styles -->
        <link rel="stylesheet" href="assets/css/style.css?nocache=<?php echo time(); ?>">
        <link rel="stylesheet" href="assets/css/custom.css?nocache=<?php echo time(); ?>">
        <!-- End layout styles -->
        <link rel="shortcut icon" href="assets/images/favicon.png" />

        <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
        <script src="//cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
    </head>
    <body>
        <div class="container-scroller">
            <!-- partial:partials/_sidebar.html -->
            <nav class="sidebar sidebar-offcanvas" id="sidebar">
                <div class="sidebar-brand-wrapper d-none d-lg-flex align-items-center justify-content-center fixed-top">
                    <a class="sidebar-brand brand-logo" href=""><img src="assets/images/logo.png?nocache=<?php echo time(); ?>" alt="logo" style="height:auto;" /></a>
                    <a class="sidebar-brand brand-logo-mini" href=""><img src="assets/images/logo-mini.svg" alt="logo" /></a>
                </div>
                <ul class="nav">
                    <li class="nav-item profile">
                        <div class="profile-desc">
                            <div class="profile-pic">
                                <div class="count-indicator">
                                    <!-- <span class="count bg-success"></span> -->
                                </div>
                                <div class="profile-name">
                                    <h5 class="mb-0 font-weight-normal">Welcome, <?php echo $_SESSION['admin'];?></h5>
                                </div>
                            </div>
                            <!-- <a href="#" id="profile-dropdown" data-toggle="dropdown"><i class="mdi mdi-dots-vertical"></i></a>
                            <div class="dropdown-menu dropdown-menu-right sidebar-dropdown preview-list" aria-labelledby="profile-dropdown">
                                <a href="#" class="dropdown-item preview-item">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-settings text-primary"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject ellipsis mb-1 text-small">Account settings</p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item preview-item">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-onepassword  text-info"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject ellipsis mb-1 text-small">Change Password</p>
                                    </div>
                                </a>
                                <div class="dropdown-divider"></div>
                                <a href="#" class="dropdown-item preview-item">
                                    <div class="preview-thumbnail">
                                        <div class="preview-icon bg-dark rounded-circle">
                                            <i class="mdi mdi-calendar-today text-success"></i>
                                        </div>
                                    </div>
                                    <div class="preview-item-content">
                                        <p class="preview-subject ellipsis mb-1 text-small">To-do list</p>
                                    </div>
                                </a>
                            </div>
                        </div>
                    </li>-->
                    <li class="nav-item nav-category">
                        <span class="nav-link">Navigation</span>
                    </li>
                    <li class="nav-item menu-items active">
                        <a class="nav-link" href="">
                        <span class="menu-icon">
                        <i class="mdi mdi-speedometer"></i>
                        </span>
                        <span class="menu-title">Dashboard</span>
                        </a>
                    </li>
                    <li class="nav-item menu-items">
                        <a class="nav-link" href="/474dnru4394jrfyre/panel/icons">
                        <span class="menu-icon">
                            <i class="mdi mdi-contacts"></i>
                        </span>
                        <span class="menu-title">Icons</span>
                        </a>
                    </li>
                    <li class="nav-item menu-items">
                        <a class="nav-link" data-toggle="collapse" href="#auth" aria-expanded="false" aria-controls="auth">
                            <span class="menu-icon">
                                <i class="mdi mdi-security"></i>
                            </span>
                            <span class="menu-title">Admin Tools</span>
                            <i class="menu-arrow"></i>
                        </a>
                        <div class="collapse" id="auth">
                            <ul class="nav flex-column sub-menu">
                                <li class="nav-item"> <a class="nav-link" id="adm-banning" style="cursor: pointer;">Banning</a></li>
                                <li class="nav-item"> <a class="nav-link" id="adm-warning" style="cursor: pointer;">Warning</a></li>
                                <li class="nav-item"> <a class="nav-link" id="adm-kicking" style="cursor: pointer;">Kicking</a></li>
                                <li class="nav-item"> <a class="nav-link" id="adm-rewarding" style="cursor: pointer;">Item Rewarding</a></li>
                                <li class="nav-item"> <a class="nav-link" id="adm-garden-rewarding" style="cursor: pointer;">Garden Rewarding</a></li>
                                <li class="nav-item"> <a class="nav-link" id="adm-muting" style="cursor: pointer;">Muting</a></li>
                            </ul>
                        </div>
                    </li>
                </ul>
            </nav>
            <!-- partial -->
            <div class="container-fluid page-body-wrapper">
                <!-- partial:partials/_navbar.html -->
                <nav class="navbar p-0 fixed-top d-flex flex-row">
                    <div class="navbar-brand-wrapper d-flex d-lg-none align-items-center justify-content-center">
                        <a class="navbar-brand brand-logo-mini" href=""><img src="assets/images/logo-mini.svg" alt="logo" /></a>
                    </div>
                    <div class="navbar-menu-wrapper flex-grow d-flex align-items-stretch">
                        <button class="navbar-toggler navbar-toggler align-self-center" type="button" data-toggle="minimize">
                        <!--<h5>hi</h5>
                        <span class="mdi mdi-menu"></span>
                        </button>
                        <ul class="navbar-nav w-100">
                            <li class="nav-item w-100">
                                <form class="nav-link mt-2 mt-md-0 d-none d-lg-flex search">
                                    <input type="text" class="form-control" placeholder="Search products">
                                </form>
                            </li>
                        </ul>
                        <ul class="navbar-nav navbar-nav-right">
                            <li class="nav-item dropdown d-none d-lg-block">
                                <a class="nav-link btn btn-success create-new-button" id="createbuttonDropdown" data-toggle="dropdown" aria-expanded="false" href="#">+ Create New Project</a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="createbuttonDropdown">
                                    <h6 class="p-3 mb-0">Projects</h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-file-outline text-primary"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject ellipsis mb-1">Software Development</p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-web text-info"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject ellipsis mb-1">UI Development</p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-layers text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject ellipsis mb-1">Software Testing</p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <p class="p-3 mb-0 text-center">See all projects</p>
                                </div>
                            </li>
                            <li class="nav-item nav-settings d-none d-lg-block">
                                <a class="nav-link" href="#">
                                <i class="mdi mdi-view-grid"></i>
                                </a>
                            </li>
                            <li class="nav-item dropdown border-left">
                                <a class="nav-link count-indicator dropdown-toggle" id="messageDropdown" href="#" data-toggle="dropdown" aria-expanded="false">
                                <i class="mdi mdi-email"></i>
                                <span class="count bg-success"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="messageDropdown">
                                    <h6 class="p-3 mb-0">Messages</h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <img src="assets/images/faces/face4.jpg" alt="image" class="rounded-circle profile-pic">
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject ellipsis mb-1">Mark send you a message</p>
                                            <p class="text-muted mb-0"> 1 Minutes ago </p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <img src="assets/images/faces/face2.jpg" alt="image" class="rounded-circle profile-pic">
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject ellipsis mb-1">Cregh send you a message</p>
                                            <p class="text-muted mb-0"> 15 Minutes ago </p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <img src="assets/images/faces/face3.jpg" alt="image" class="rounded-circle profile-pic">
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject ellipsis mb-1">Profile picture updated</p>
                                            <p class="text-muted mb-0"> 18 Minutes ago </p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <p class="p-3 mb-0 text-center">4 new messages</p>
                                </div>
                            </li>
                            <li class="nav-item dropdown border-left">
                                <a class="nav-link count-indicator dropdown-toggle" id="notificationDropdown" href="#" data-toggle="dropdown">
                                <i class="mdi mdi-bell"></i>
                                <span class="count bg-danger"></span>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="notificationDropdown">
                                    <h6 class="p-3 mb-0">Notifications</h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-calendar text-success"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject mb-1">Event today</p>
                                            <p class="text-muted ellipsis mb-0"> Just a reminder that you have an event today </p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-settings text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject mb-1">Settings</p>
                                            <p class="text-muted ellipsis mb-0"> Update dashboard </p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-link-variant text-warning"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject mb-1">Launch Admin</p>
                                            <p class="text-muted ellipsis mb-0"> New admin wow! </p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <p class="p-3 mb-0 text-center">See all notifications</p>
                                </div>
                            </li>
                            <li class="nav-item dropdown" disabled>
                                <a class="nav-link" id="profileDropdown" href="#" data-toggle="dropdown">
                                    <div class="navbar-profile">
                                        <p class="mb-0 d-none d-sm-block navbar-profile-name"><?php echo $_SESSION['admin'];?></p>
                                        <i class="mdi mdi-menu-down d-none d-sm-block"></i>
                                    </div>
                                </a>
                                <div class="dropdown-menu dropdown-menu-right navbar-dropdown preview-list" aria-labelledby="profileDropdown">
                                    <h6 class="p-3 mb-0">Profile</h6>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-settings text-success"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject mb-1">Settings</p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item preview-item">
                                        <div class="preview-thumbnail">
                                            <div class="preview-icon bg-dark rounded-circle">
                                                <i class="mdi mdi-logout text-danger"></i>
                                            </div>
                                        </div>
                                        <div class="preview-item-content">
                                            <p class="preview-subject mb-1">Log out</p>
                                        </div>
                                    </a>
                                    <div class="dropdown-divider"></div>
                                    <p class="p-3 mb-0 text-center">Advanced settings</p>
                                </div>
                            </li>
                        </ul>
                        -->
                        <button class="navbar-toggler navbar-toggler-right d-lg-none align-self-center" type="button" data-toggle="offcanvas">
                        <span class="mdi mdi-format-line-spacing"></span>
                        </button>
                    </div>
                </nav>
                <!-- partial -->
                <div class="main-panel">
                    <div class="content-wrapper">
                        <div class="row">
                            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="d-flex align-items-center align-self-start">
                                                    <h3 class="mb-0" id="onlineweevils"></h3>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="icon icon-box-success ">
                                                    <span class="mdi mdi-arrow-top-right icon-item"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 class="text-muted font-weight-normal">Online Weevils</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="d-flex align-items-center align-self-start">
                                                    <h3 class="mb-0"><?php echo $totalWeevils; ?></h3>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="icon icon-box-warning">
                                                    <span class="mdi mdi-arrow-top-right icon-item"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 class="text-muted font-weight-normal">Total weevils</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="d-flex align-items-center align-self-start">
                                                    <h3 class="mb-0"><?php echo $weevilsToday; ?></h3>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="icon icon-box-danger">
                                                    <span class="mdi mdi-arrow-top-right icon-item"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 class="text-muted font-weight-normal">New weevils today</h6>
                                    </div>
                                </div>
                            </div>
                            <div class="col-xl-3 col-sm-6 grid-margin stretch-card">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="row">
                                            <div class="col-9">
                                                <div class="d-flex align-items-center align-self-start">
                                                    <h3 class="mb-0"><?php echo $weevilslasthr; ?></h3>
                                                </div>
                                            </div>
                                            <div class="col-3">
                                                <div class="icon icon-box-info ">
                                                    <span class="mdi mdi-arrow-top-right icon-item"></span>
                                                </div>
                                            </div>
                                        </div>
                                        <h6 class="text-muted font-weight-normal">New weevils in the last hour</h6>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-12 grid-margin">
                                <div class="card">
                                    <div class="card-body">
                                        <div class="add-items d-flex">
                                            <input type="text" class="form-control todo-list-input new-task-name" placeholder="Name">
                                            <input type="text" class="form-control todo-list-input new-task-description" placeholder="Description">
                                            <input type="text" class="form-control todo-list-input new-task-icon" placeholder="Icon [Go to icons page to get names]">
                                            <button class="add btn btn-primary todo-list-add-btn">Add</button>
                                        </div>
                                        <div class="d-flex flex-row justify-content-between">
                                            <h4 class="card-title mb-1">Development</h4>
                                            <p class="text-muted mb-1">Status</p>
                                        </div>
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="preview-list development-items" name="development-items">
                                                    <?php echo $developmentProg; ?>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="btn-group" role="group" aria-label="Basic example">
                                            <button type="button" class="btn btn-outline-secondary" style="margin-top:8px;" name="prevpage" id="prevpage"><</button>
                                            <button type="button" class="btn btn-outline-secondary" style="margin-top:8px;" name="nextpage" id="nextpage">></button>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="row ">
                            <div class="col-12 grid-margin">
                                <div class="card">
                                    <div class="card-body">
                                        <h4 class="card-title">Recent Logs</h4>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <thead>
                                                    <tr>
                                                        <th> Admin Name </th>
                                                        <th> Weevil Name </th>
                                                        <th> LogId </th>
                                                        <th> Log Type </th>
                                                        <th> Log Date </th>
                                                        <th> ItemID </th>
                                                        <th> Reason </th>
                                                    </tr>
                                                </thead>
                                                <tbody id="logs-body">
                                                    <?php echo $adminLogs; ?>
                                                </tbody>
                                            </table>
                                            <div class="btn-group" role="group" aria-label="Basic example">
                                                <button type="button" class="btn btn-outline-secondary" style="margin-top:8px;" id="prevlogpage" disabled=""><</button>
                                                <button type="button" class="btn btn-outline-secondary" style="margin-top:8px;" id="nextlogpage">></button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- content-wrapper ends -->
                    <!-- partial:partials/_footer.html -->
                    <footer class="footer">
                        <div class="d-sm-flex justify-content-center justify-content-sm-between">
                            <span class="text-muted d-block text-center text-sm-left d-sm-inline-block">Copyright © bwrewritten.com 2021</span>
                        </div>
                    </footer>
                    <!-- partial -->
                </div>
                <!-- main-panel ends -->
            </div>
            <!-- page-body-wrapper ends -->
        </div>

        <script>
        </script>
        <!-- container-scroller -->
        <!-- plugins:js -->
        <script src="assets/vendors/js/vendor.bundle.base.js"></script>
        <!-- endinject -->
        <!-- Plugin js for this page -->
        <script src="assets/vendors/chart.js/Chart.min.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/vendors/progressbar.js/progressbar.min.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/vendors/jvectormap/jquery-jvectormap.min.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/vendors/jvectormap/jquery-jvectormap-world-mill-en.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/vendors/owl-carousel-2/owl.carousel.min.js?nocache=<?php echo time(); ?>"></script>
        <!-- End plugin js for this page -->
        <!-- inject:js -->
        <script src="assets/js/off-canvas.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/js/hoverable-collapse.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/js/misc.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/js/settings.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/js/todolist.js?nocache=<?php echo time(); ?>"></script>
        <script src="assets/js/admintools.js?nocache=<?php echo time(); ?>"></script>
        <script>
            // Moderator token issued at login (Option A). Used instead of a plaintext password
            // on the moderation socket channel. Falls back to empty if not set.
            window.adminToken = <?php echo json_encode(isset($_SESSION['adminToken']) ? $_SESSION['adminToken'] : ''); ?>;
            window.adminName = <?php echo json_encode(isset($_SESSION['admin']) ? $_SESSION['admin'] : ''); ?>;
        </script>
        <script src="assets/js/weevilcounter.js?nocache=<?php echo time(); ?>"></script>
        <script>
            var page = 0;
            var lastpage = 3476463;
            //todoListItem.append();
            if(page == 0){
                $('#prevpage').prop('disabled', true);
            }
            else{
                $('#prevpage').prop('disabled', false);
            }
            $('#nextpage').on("click", function(event) {
                page +=1;
                if(page == 0){
                    $('#prevpage').prop('disabled', true);
                }
                else{
                    $('#prevpage').prop('disabled', false);
                }
                var btn = $(this);
                $.get( "apis/getdevtasks.php", { pageindex: page } ).done(function( data ) {
                    var length = $(data).length;
                    var todoListItem = $('.development-items');
                    todoListItem.empty();
                    todoListItem.append(data);

                    if(length < 4){
                        btn.prop('disabled', true);
                        lastpage = page;
                    }
                    else{
                        $.get( "apis/getdevtasks.php", { pageindex: page+1 } ).done(function( data ) {
                            var length2 = $(data).length;
                            if(length2 == 0){
                                btn.prop('disabled', true);
                            }
                        });
                    }
                });
            });

            $('#prevpage').on("click", function(event) {
                page -=1;
                if(page == 0){
                    $('#prevpage').prop('disabled', true);
                }
                else{
                    $('#prevpage').prop('disabled', false);
                }

                if(page != lastpage){
                    $('#nextpage').prop('disabled', false);
                }
                var btn2 = $(this);
                $.get( "apis/getdevtasks.php", { pageindex: page } ).done(function( data ) {
                    var length = $(data).length;
                    var todoListItem = $('.development-items');
                    todoListItem.empty();
                    todoListItem.append(data);

                    if(length < 4){
                        btn2.prop('disabled', true);
                    }
                    else{
                        $.get( "apis/getdevtasks.php", { pageindex: page-1 } ).done(function( data ) {
                            var length2 = $(data).length;
                            if(length2 == 0){
                                btn2.prop('disabled', true);
                            }
                        });
                    }
                });
            });


            $.get("apis/getOnlineWeevils.php", { } ).done(function( data ) {
                data = JSON.parse(data.replace(/[^\x20-\x7E]/g, ""));
                console.log(data.weevils);
                $('#onlineweevils').html(data.weevils);
            });

            var logpage = 0;
            var loglastpage = 3476463;
            //todoListItem.append();
            if(logpage == 0){
                $('#prevlogpage').prop('disabled', true);
            }
            else{
                $('#prevlogpage').prop('disabled', false);
            }
            $('#nextlogpage').on("click", function(event) {
                logpage +=1;
                if(logpage == 0){
                    $('#prevlogpage').prop('disabled', true);
                }
                else{
                    $('#prevlogpage').prop('disabled', false);
                }
                var btn = $(this);
            });

        </script>
        <!-- endinject -->
        <!-- Custom js for this page -->
        <script src="assets/js/dashboard.js?nocache=<?php echo time(); ?>"></script>
        <!-- End custom js for this page -->
    </body>
</html>