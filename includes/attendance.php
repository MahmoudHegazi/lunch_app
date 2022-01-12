<?php


require_once('../crud.php');
// attendance update

$redirect_url = $_SERVER['HTTP_REFERER'];


if ($_SERVER['REQUEST_METHOD'] == 'POST'){
  global $conn;
  $response  = array('code'=> 0, "message"=>"", "status"=>false);
  $data = json_decode(file_get_contents('php://input'), true);
  if (
      isset($data['attended']) &&
      isset($data['meta_id']) &&
      isset($data['meta_day'])
      )
    {
      $redirect_url = remove_allright_parameters($redirect_url, 'danger');
      $attended = test_input($data['attended']);
      $meta_id = test_input($data['meta_id']);
      $selecteddate = test_input($data['meta_day']);

       // 1- add attended true to the row
       update_attended($attended, $meta_id);
       // calc the new fees depend on attended numbers
       update_users_fees($selecteddate);
       if (empty($attended)){
         // empty the fees in case attended == false
         empty_fees($meta_id);
       }
       // update users balance depend on fees and topped_up
       update_usersbalance_ontime();
       $js_attended_checkbox = $attended == 1 ? 'checked' : '';
       $ordertype = !isset($_SESSION['order_by']) && empty($_SESSION['order_by']) ? ', users.id ASC' : $_SESSION['order_by'];
       $data = array('code'=> 200, 'row_id'=>$meta_id, 'target_checked_status'=>$js_attended_checkbox, 'data'=> getall_rowmetas_json($selecteddate, $ordertype), 'message'=>'User-ID: {'. $meta_id .'}, attended successfully.');
       header('Content-Type: application/json; charset=utf-8');
       echo json_encode($data);
       die();

    } else {
      header('Content-Type: application/json; charset=utf-8');
      $data = array('code'=> 422, 'Recored Not updated invalid data sent restart the page');
      echo json_encode($data);
    }
} else {
  header('Content-Type: application/json; charset=utf-8');
  $data = array('code'=> 400, 'Recored Not updated unexpted request');
  echo json_encode($data);
}

?>
