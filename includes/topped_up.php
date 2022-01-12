<?php
require_once('../crud.php');
// topped_up update

$redirect_url = $_SERVER['HTTP_REFERER'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (
      isset($_POST['topped_up']) && !empty($_POST['topped_up']) &&
      isset($_POST['meta_id']) && !empty($_POST['meta_id'])
    ){

    $redirect_url = remove_allright_parameters($redirect_url, 'danger');

    $meta_id = test_input($_POST['meta_id']);
    $new_toppedup = test_input($_POST['topped_up']);
    if (is_numeric($new_toppedup) && $new_toppedup > 0){

      // update topped_up value
      update_toppedup($new_toppedup, $meta_id);
      update_usersbalance_ontime();

      header("Location: " . $redirect_url);
    } else {
      $redirect_url = add_query_parameters($redirect_url, ('danger'), ('recored can not updated Invalid Topped up value'));
      header("Location: " . $redirect_url);
    }
  } else {
    header("Location: " . $redirect_url);
  }
} else {
  header("Location: " . $redirect_url);
}
?>
