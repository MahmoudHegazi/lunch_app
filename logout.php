<?php
session_start();

if(isset($_SESSION['loggedin']) || isset($_SESSION['username'])) {
  session_unset();
  session_destroy();
  header("Location: login.php?message=You have successfully logged out");
} else {
  header("Location: login.php?message=Please Log in");
}

?>
