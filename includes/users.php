<?php
require_once('../crud.php');
// add users

$redirect_url = $_SERVER['HTTP_REFERER'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (
      isset($_POST['name']) && !empty($_POST['name']) &&
      isset($_POST['email']) && !empty($_POST['email']) &&
      isset($_POST['company']) && !empty($_POST['company'])
    ){
      $name = test_input($_POST['name']);
      $email = test_input($_POST['email']);
      $company = test_input($_POST['company']);
      easyInsert('users', array('name', 'email', 'company'), array($name, $email, $company), $prepare_string=false);
      header("Location: " . $redirect_url);
    } else {
      die();
    }
} else {
  header("Location: " . $redirect_url);
}

?>
