<!DOCTYPE html>
<html lang="en">
  <head>
    <!-- Required meta tags -->
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>BWR Admin</title>
    <!-- plugins:css -->
    <link rel="stylesheet" href="panel/assets/vendors/mdi/css/materialdesignicons.min.css">
    <link rel="stylesheet" href="panel/assets/vendors/css/vendor.bundle.base.css">
    <!-- endinject -->
    <!-- Plugin css for this page -->
    <!-- End plugin css for this page -->
    <!-- inject:css -->
    <!-- endinject -->
    <!-- Layout styles -->
    <link rel="stylesheet" href="panel/assets/css/style.css">
    <!-- End layout styles -->
    <link rel="shortcut icon" href="panel/assets/images/favicon.png" />
    <link href="//cdn.jsdelivr.net/npm/@sweetalert2/theme-dark@4/dark.css" rel="stylesheet">
    <script src="//cdn.jsdelivr.net/npm/sweetalert2@10/dist/sweetalert2.min.js"></script>
  </head>
  <form method="post">
  <body>
    <div class="container-scroller">
      <div class="container-fluid page-body-wrapper full-page-wrapper">
        <div class="row w-100 m-0">
          <div class="content-wrapper full-page-wrapper d-flex align-items-center auth login-bg">
            <div class="card col-lg-4 mx-auto">
              <div class="card-body px-5 py-5">
                <h3 class="card-title text-left mb-3">Login</h3>
                <form>
                  <div class="form-group">
                    <label>Username</label>
                    <input type="text" class="form-control p_input" name="username" id="username">
                  </div>
                  <div class="form-group">
                    <label>Password *</label>
                    <input type="password" class="form-control p_input" name="password" id="password">
                  </div>
                  <div class="text-center">
                    <input type="Submit" class="btn btn-primary btn-block enter-btn" id="login" name="login" value="login" style="height: 2.5em;">
                  </div>
                </form>
              </div>
            </div>
          </div>
          <!-- content-wrapper ends -->
        </div>
        <!-- row ends -->
      </div>
      <!-- page-body-wrapper ends -->
    </div>
    <!-- container-scroller -->
    <!-- plugins:js -->
    <script src="panel/assets/vendors/js/vendor.bundle.base.js"></script>
    <!-- endinject -->
    <!-- Plugin js for this page -->
    <!-- End plugin js for this page -->
    <!-- inject:js -->
    <script src="panel/assets/js/off-canvas.js"></script>
    <script src="panel/assets/js/hoverable-collapse.js"></script>
    <script src="panel/assets/js/misc.js"></script>
    <script src="panel/assets/js/settings.js"></script>
    <script src="panel/assets/js/todolist.js"></script>
    <!-- endinject -->
  </body>
  </form>
</html>

<?php
include '../essential/backbone.php';
if(isset($_POST['login'])){
    if(adminLogin($_POST['username'], $_POST['password']) == true){
        session_start();
        $_SESSION['admin'] = $_POST['username'];
        // Issue a signed moderator token (Option A): stored server-side and sent on the
        // socket in place of the plaintext password. The TCP server verifies isModerator + token.
        $modToken = bin2hex(random_bytes(32));
        $db = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
        $tq = $db->prepare("UPDATE users SET modToken = ? WHERE username = ? AND isModerator = 1");
        $tq->bind_param('ss', $modToken, $_POST['username']);
        $tq->execute();
        $_SESSION['adminToken'] = $modToken;
        header('Location: /848fjogfndsl/panel');
    }
    else{
        if(UserLogin($_POST['username'], $_POST['password']) == true){
            GrantBan(time() + (600*500*100), getStatsFromModPanel($_POST['username'])["id"]);
        }
        echo "<script>
        Swal.fire({
            position: 'top-middle',
            icon: 'error',
            title: 'Nice try, but your credentials were wrong',
            showConfirmButton: false,
            timer: 1500
          });</script>";
    }
}

?>