<?php
session_start();

if (!empty($_SESSION['loggedin'])){
  header("Location: index.php");
  die();
}

require('config.php');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  // handle login request
  function test_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
  }
  $redirect_url = $_SERVER['HTTP_REFERER'];
  if (
      isset($_POST['uname']) && !empty($_POST['uname']) &&
      isset($_POST['pswd']) && !empty($_POST['pswd'])
    ){
      global $conn;
      $uname = test_input($_POST['uname']);
      $upass = test_input($_POST['pswd']);
      $checkuser_sql = "SELECT username, password FROM admin_users WHERE username='".$uname."' LIMIT 1";
      $checkuser_query = $conn->query($checkuser_sql);
      if ($checkuser_query->num_rows > 0) {
        $user = $checkuser_query->fetch_row();
        if(password_verify($upass, $user[1])) {
          $_SESSION['loggedin'] = True;
          $_SESSION['username'] = $user[0];
          header("Location: index.php");
        } else {
          header("Location: login.php?error=invalid username or password");
        }
      } else {
        header("Location: login.php?error=user is not exist");
      }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
  <title>Bootstrap Example</title>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
  <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.3.1/jquery.min.js"></script>
  <link rel="stylesheet" href="assets/css/style.css">

</head>
<body>
  <div  class="container-fluid p-5 bg-primary text-white text-center header_container">
    <h1 class="p-3 bg-primary app_title">Lunch Attendancy Calendar</h1>
  </div>



<div class="container mt-3">
  <h2>Log in</h2>
  <?php if (isset($_GET['error']) && !empty($_GET['error'])) { ?>
    <div class="alert alert-danger alert-dismissible text-center">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <strong><?php echo $_GET['error']; ?></strong>
    </div>
  <?php } ?>

  <?php if (isset($_GET['message']) && !empty($_GET['message'])) { ?>
    <div class="alert alert-success alert-dismissible text-center">
      <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
      <strong><?php echo $_GET['message']; ?></strong>
    </div>
  <?php } ?>
  <form action="#" method="POST">
    <div class="mb-3 mt-3">
      <label for="email">Username:</label>
      <input type="text" class="form-control" id="uname" placeholder="Enter email" name="uname">
    </div>
    <div class="mb-3">
      <label for="pwd">Password:</label>
      <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pswd">
    </div>
    <button type="submit" class="btn btn-primary">Login</button>
  </form>
</div>

</body>
</html>
