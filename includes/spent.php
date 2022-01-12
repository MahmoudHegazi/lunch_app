<?php
require_once('../crud.php');
// spent_update

$redirect_url = $_SERVER['HTTP_REFERER'];


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  if (
      isset($_POST['spent_today_value']) && !empty($_POST['spent_today_value']) &&
      isset($_POST['credit_spent_id']) && !empty($_POST['credit_spent_id'])
    ){

    $debit_id = $_POST['spent_today_value'];
    $spent_row = easy_one_Select('credit_system', 'day_date', 'id', test_input($_POST['credit_spent_id']));

    $selecteddate = $spent_row->fetch_assoc()['day_date'];



    $new_spent_value = floatval(round(test_input($_POST['spent_today_value']),2));
    if ($new_spent_value < 0){ return 'Sorry Spent can only be in postive numbers'; }

    $system_cid = test_input($_POST['credit_spent_id']);
    easyUpdate('credit_system', ['spent'], [$new_spent_value], 'id', $system_cid);
    update_users_fees($selecteddate);
    deduct_allfees_frombalance();
    update_usersbalance_ontime();


    header("Location: " . $redirect_url);

    /*
    $get_price = $_GET['spent_today_value'];
    $get_pickedday = $_GET['spent_today_value'];
    $sql = "SELECT id FROM user_meta WHERE day_date = '". test_input($get_pickedday) . "'";
    $result = $conn->query($sql);
    $data = $result->fetch_assoc();
    $exist = $result->num_rows > 0 ? true : $get_pickedday;
    echo $exist;
    */
  }
}

?>
