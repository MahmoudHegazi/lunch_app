<?php
require_once("config.php");
require_once("functions.php");

// this function last of dynamic can insert data to any table with secuirty and way prefered for all




/* insert user
$names = array('name', 'email', 'company', 'firstdate', 'lastdate');
$values = array('Christopher', 'c.theuser@iasset.nl', 'thecompany', '2022-01-01 00:00:00', '2022-12-31 00:00:00');

echo insertInto('users', $names, $values);
*/

/* insert user meta
$names = array('topped_up', 'attended', 'fee', 'day_id', 'user_id');
$values = array('19.10', true, 4.85, 1, 1);
*/

/*
$names = array('date_standard', 'date_formated', 'activated', 'day', 'month', 'year');
$values = array('2022-01-01', '', 0, 1, 1, 2001);
echo insertInto('days', $names, $values);
*/


/* insert day
$names = array('topped_up', 'attended', 'fee', 'day_id', 'user_id');
$values = array('19.10', true, 4.85, 1, 1);
*/


/* ----- update ----  */
function easyUpdate($tablename, $col_to_updates, $col_values, $conditon, $value, $addon=''){
  global $conn;
  if (count($col_to_updates) == 0 || count($col_values) == 0){return false;}
  if (count($col_to_updates) != count($col_values)){return false;}

  $column_values_str = format_column_updates($col_to_updates, $col_values);
  $sql = 'UPDATE ' . $tablename . ' SET ' . $column_values_str . ' WHERE ' . $conditon  . '=' . $value . $addon;
  if (mysqli_query($conn, $sql)) {
    return true;
    // ===
  } else {
    return mysqli_error($conn);
  }
}

function easy_one_Select($tablename, $selector, $conditon, $value){
  global $conn;
  $sql = 'SELECT `'. $selector .'` FROM `'. test_input($tablename) .'` WHERE '. test_input($conditon) .'=' . test_input($value);
  if (mysqli_query($conn, $sql)) {
    return mysqli_query($conn, $sql);
    // ===
  } else {
    return mysqli_error($conn);
  }
}


// example call echo easyUpdate('user_meta', ['topped_up', 'attended'], ['1.01', 1], 'id', 4);

/* ----- insert ---- */

/* this small function do prepead insert for any table secured can control all your inserts and secuirty */
function easyInsert($tablename, $column_names, $column_values, $prepare_string=false){
  global $conn;
  $column_name_data = format_column_names($column_names);
  $names = $column_name_data[0];
  $values_marks = $column_name_data[1];
  $values = secure_array($column_values);
  if ($prepare_string == false){
    $prepare_string = format_prepeared_string($column_values);
  }
  try{
    $sql = 'INSERT INTO ' . $tablename . ' ' . $names .' VALUES ' . $values_marks .'';
    $stmt = $conn->prepare($sql);
    $stmt->bind_param($prepare_string, ...$values);
    $stmt->execute();

    if (!$conn->error){
      return $conn->insert_id;
    } else {
      return $conn->error;
    }
  } catch(Exception $e){
    return false;
  }

}


/*
$names = array('date_standard', 'date_formated', 'activated', 'day', 'month', 'year');
$values = array('2015-12-12', 'Wed 01 Jan 01 2155', true, '1', '1', '2006');
echo easyInsert('days', $names, $values);
*/
/*
$names = array('name', 'email', 'company', 'firstdate', 'lastdate');
$values = array('name2', 'c.mruse22r@net.nl', 'name123', '2022-01-01 00:00:00', '2022-12-31 00:00:00');
echo easyInsert('users', $names, $values);

$names = array('topped_up', 'attended', 'fee', 'day_id', 'user_id');
$values = array('29.10', true, '0.15', 2, 7);
echo easyInsert('user_meta', $names, $values);

$names = array('balance', 'user_id');
$values = array('59.10', 2);
echo easyInsert('credit_account', $names, $values);

$names = array('date_standard', 'date_formated', 'activated', 'day', 'month', 'year');

$values = array('2015-12-12', 'Wed 01 Jan 01 2155', false, 1, 1, 1996);
echo easyInsert('days', $names, $values);

*/

// update users balance
function update_users_fees($selecteddate){
  global $conn;
   $user_fees = "SELECT (SELECT spent FROM `credit_system` WHERE day_date='". $selecteddate ."') / (SELECT COUNT(id) FROM `user_meta` WHERE attended=1 AND day_date='". $selecteddate ."') AS 'day_fees'";
   $user_fees = $conn->query($user_fees);
   $user_fees = $user_fees->fetch_row();
   if (count($user_fees) > 0){
      $today_fees = number_format($user_fees[0], 2);
      $user_fee_update = "UPDATE `user_meta` SET fee = '-". $today_fees ."' WHERE attended=1 AND day_date='". $selecteddate ."'";
      $user_fee_update = $conn->query($user_fee_update);
   }
}
function deduct_allfees_frombalance(){
   global $conn;
   $update_balance_query = "UPDATE credit_account SET balance = balance -(SELECT SUM(fee) FROM user_meta WHERE user_id=2) WHERE user_id=2";
   return $conn->query($update_balance_query);
}

function update_usersbalance_ontime(){
  global $conn;
  $update_user_balance = "UPDATE credit_account SET balance = (SELECT SUM(user_meta.fee) FROM user_meta WHERE user_id=credit_account.user_id) + (SELECT SUM(user_meta.topped_up) FROM user_meta WHERE user_id=credit_account.user_id)";
  return $conn->query($update_user_balance);
}

function update_toppedup($new_toppedup, $meta_id){
  global $conn;
  $update_toppedup_query = "UPDATE user_meta SET topped_up = '".test_input($new_toppedup)."' WHERE id=".test_input($meta_id);
  return $conn->query($update_toppedup_query);
}

function update_attended($new_attended, $meta_id){
  global $conn;
  $update_attended_query = "UPDATE user_meta SET attended = '".test_input($new_attended)."' WHERE id=".test_input($meta_id);
  return $conn->query($update_attended_query);
}

function empty_fees($meta_id){
  global $conn;
  $empty_fees = "UPDATE user_meta SET fee = '0.00' WHERE id=".test_input($meta_id);
  return $conn->query($empty_fees);
}

function format_column_updates($column_names, $column_values){
    if (count($column_names) == 0 || count($column_values) == 0){return '';}
    if (count($column_names) != count($column_values)){return '';}
    $col_val_string = '';
    for ($i=0; $i<count($column_names); $i++){
      if ($i==count($column_names)-1){
        $col_val_string .= '`' . test_input($column_names[$i]) . '`' . '=' . test_input($column_values[$i]);
      } else {
        $col_val_string .= '`' . test_input($column_names[$i]) . '`' . '=' . test_input($column_values[$i]) . ', ';
      }
    }
    return $col_val_string;
}


function check_or_create_credit_day(){
  global $conn;
  $sql = "SELECT id FROM users";
  $all_users = $conn->query($sql);

  $sql1 = "SELECT id FROM credit_account";
  $all_credit_accounts = $conn->query($sql1);

  if ($all_users->num_rows > 0 && $all_credit_accounts->num_rows != $all_users->num_rows) {

    while($row = $all_users->fetch_assoc()) {
      $user_balance = '0.00';
      $selectuser = easy_one_Select('credit_account', 'id', 'user_id', $row['id']);
      if ($selectuser->num_rows == 0){
        //easyInsert('credit_account', array('balance', 'user_id'), array('0.00', $row['id']), $prepare_string=false);
        // SELECT (SELECT SUM(topped_up) FROM `user_meta` WHERE user_id=2) - (SELECT SUM(fee) FROM `user_meta` WHERE attended=1 AND user_id=2)
        //$current_balance = "SELECT (SELECT SUM(topped_up) FROM `user_meta` WHERE user_id=". $row['id'] .") - (SELECT SUM(fee) FROM `user_meta` WHERE attended=1 AND user_id=". $row['id'] .") AS 'result'";
        //$current_balance = $conn->query($current_balance);
        //$balnce = $current_balance->fetch_assoc();
        //if ($balnce['result']){
        //  $user_balance = $balnce['result'];
        //}

        easyInsert('credit_account', array('balance', 'user_id'), array($user_balance, $row['id']), $prepare_string=false);
      }
    }
  }
}

// dynamic update user balance when visit page, good for add new user too
function calc_user_balance(){
  global $conn;
  $sql = "SELECT id FROM users";
  $all_users = $conn->query($sql);

  $sql1 = "SELECT id FROM credit_account";
  $all_credit_accounts = $conn->query($sql1);

  if ($all_users->num_rows > 0 && $all_credit_accounts->num_rows == $all_users->num_rows) {

    while($row = $all_users->fetch_assoc()) {
      $user_balance = '0.00';
      $selectuser = easy_one_Select('credit_account', 'id', 'user_id', $row['id']);
      $get_user_account = easy_one_Select('credit_account', 'id', 'user_id', $row['id']);


      if ($get_user_account->num_rows > 0){
        $account_id = easy_one_Select('credit_account', 'id', 'user_id', $row['id'])->fetch_assoc()['id'];

        //easyInsert('credit_account', array('balance', 'user_id'), array('0.00', $row['id']), $prepare_string=false);
        // SELECT (SELECT SUM(topped_up) FROM `user_meta` WHERE user_id=2) - (SELECT SUM(fee) FROM `user_meta` WHERE attended=1 AND user_id=2)
        $current_balance = "SELECT (SELECT SUM(topped_up) FROM `user_meta` WHERE user_id=". $row['id'] .") - (SELECT SUM(fee) FROM `user_meta` WHERE attended=1 AND user_id=". $row['id'] .") AS 'result'";
        $current_balance = $conn->query($current_balance);
        $balnce = $current_balance->fetch_assoc();
        if ($balnce['result']){
          $user_balance = $balnce['result'];
        }

         easyUpdate('credit_account', array('balance'), array($user_balance), 'id', $account_id);

    }
  }
}
}

function add_query_parameters($url, $parms, $values){
  if (count($parms) != count($values)){return false;}
  $newurl = count(explode("?",$url)) > 1 ? $url . '&' : $url .'?';
  for ($i=0; $i<count($parms); $i++){
    $newurl .= $parms[$i] . '=' . $values[$i];
  }
}

function remove_allright_parameters($url, $querypara){
  if (strpos($url,"&". $querypara ."=")){
    return substr($url,0, strpos($url,"&". $querypara ."="));
  } else if (strpos($url,"?". $querypara ."=")){
    return substr($url,0, strpos($url,"?". $querypara ."="));
  } else {
    return $url;
  }
}

function get_total_attendance(){
  global $conn;
  $attendance_count = 0;
  $total_attendance = 'SELECT SUM(attended) FROM user_meta';
  $total_attendance = $conn->query($total_attendance);
  if ($total_attendance->num_rows > 0) {
    $attendance_count = $total_attendance->fetch_row()[0];
  }
  return number_format($attendance_count);
}
function get_total_spent(){
  global $conn;
  $spent_count = 0;
  $total_spent = 'SELECT SUM(spent) FROM credit_system';
  $total_spent = $conn->query($total_spent);
  if ($total_spent->num_rows > 0) {
    $spent_count = $total_spent->fetch_row()[0];
  }
  return number_format($spent_count, 2);
}

function getall_rowmetas_json($day_date, $order_type){
  global $conn;
  $data = array();
  $order_type = $order_type == '' ? ', users.id ASC': $order_type;
  $sql = "SELECT user_meta.fee, user_meta.day_date, user_meta.attended, users.id, users.name, credit_account.balance FROM user_meta INNER JOIN users ON user_meta.user_id = users.id JOIN credit_account ON user_meta.user_id = credit_account.user_id WHERE user_meta.day_date='". $day_date . "' ORDER BY user_meta.day_date" . $order_type;
  $rows_meta = $conn->query($sql);
  if ($rows_meta->num_rows > 0 ) {
    while($row = $rows_meta->fetch_assoc()) {
      $data_row = array('id'=> $row['id'], 'fee'=>$row['fee'], 'attended'=> $row['attended'], 'name'=> $row['name'], 'balance'=> $row['balance']);
      array_push($data, $data_row);
    }
    return $data;
  } else {
    return array();
  }

}



//echo remove_allright_parameters($url, 'picked_day');
//array('topped_up', 'attended', 'fee', 'day_id', 'user_id')
?>
