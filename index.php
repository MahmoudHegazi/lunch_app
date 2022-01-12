<?php
session_start();
$loggedin = isset($_SESSION['loggedin']) && $_SESSION['loggedin'] == True ? True : False;

if (!$loggedin){
  header("Location: login.php?message=Please Log in to access lunch app");
}
require('crud.php');

/*
$names = array('topped_up', 'attended', 'fee', 'user_id');
$values = array('29.10', true, '0.15', 7);
easyInsert('user_meta', $names, $values);
*/
$pickedday = "";
$credit_spent_id = 0;
$getspenttoday = 0;
$todaydate = '1970-01-01';

$order_by = '';




/* handle new day checks  */
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
  /* handle order by  */


    if (isset($_GET['picked_day']) && !empty($_GET['picked_day']) && strlen($_GET['picked_day']) == 10){
      $pickedday = test_input($_GET['picked_day']);
      check_or_create_credit_day();
      if (strlen($pickedday) == 10){
        update_users_fees($pickedday);
        update_usersbalance_ontime();
        // this make the app database friendly and dynamic
        // it will add the day meta recored when date picked and only if day not exist
      $sql = "SELECT id FROM user_meta WHERE day_date = '". test_input($pickedday) . "' LIMIT 1";
      $sql1 = "SELECT id, spent, day_date FROM credit_system WHERE day_date = '". test_input($pickedday) . "' LIMIT 1";
      $result = $conn->query($sql);
      $result_credit = $conn->query($sql1);
      $data = $result->fetch_row();
      $system_day_credit = $result_credit->fetch_row();

      $exist = $result->num_rows > 0 ? true : false;
      $exist1 = $result_credit->num_rows > 0 ? true : false;
      # this add control for app later update this will update fee automatic in meta
      # this Important variable can control app easy
      $intial_spent_today = '0.00';
      if ($exist1 == false){
        $sql = "SELECT id FROM users ORDER BY id";
        $result = $conn->query($sql);
        if ($result->num_rows > 0) {
          $names1 = array('spent', 'day_date');
          $values1 = array($intial_spent_today, $pickedday);
          // get system day data for render
          $credit_spent_id = easyInsert('credit_system', $names1, $values1);
          $getspenttoday = $intial_spent_today;
          $todaydate = $pickedday;
          //easy_one_Select('credit_system', 'spent', 'id', $_GET['credit_spent_id']);
        }
      } else {
        // get id direct if insrted before in database
        $credit_spent_id = $system_day_credit[0];
        $getspenttoday = $system_day_credit[1];
        $todaydate = $system_day_credit[2];
      }


      if ($exist == false){
        $sql = "SELECT id FROM users ORDER BY id";
        $result = $conn->query($sql);

        $totaladded = 0;
        if ($result->num_rows > 0) {
          // output data of each row
          while($row = $result->fetch_assoc()) {
            $names = array('topped_up', 'attended', 'fee', 'day_date', 'user_id');
            $values = array('0.00', false, ($intial_spent_today / $result->num_rows), $pickedday, $row["id"]);
            $new_meta = easyInsert('user_meta', $names, $values);
            $totaladded += 1;
          }
        }
        }
      }
    }
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
  /* mange order add, remove edit dynamic easy from here  */
if (isset($_POST['order']) && !empty($_POST['order'])){

     // for better UX i control the order in session so it work smoth with second function
     // and give 2 order ASC, and DES
     if (!isset($_SESSION['id']) || empty($_SESSION['id'])) {
          $_SESSION['id'] = 'ASC';
     }
     if (!isset($_SESSION['name']) || empty($_SESSION['name'])){
          $_SESSION['name'] = 'ASC';
     }
     if (!isset($_SESSION['attended']) || empty($_SESSION['attended'])){
          $_SESSION['attended'] = 'ASC';
     }
     if (!isset($_SESSION['topped_up']) || empty($_SESSION['topped_up'])){
         $_SESSION['topped_up'] = 'ASC';
     }
     if (!isset($_SESSION['balance']) || empty($_SESSION['balance'])){
         $_SESSION['balance'] = 'ASC';
     }


  if (
      $_POST['order']=='id' || $_POST['order'] == 'attended' ||
      $_POST['order']=='name' || $_POST['order'] == 'topped_up' ||
      $_POST['order'] == 'balance'
     ){
    if ( $_POST['order'] == 'name' ){
      $_SESSION['order_by'] = ', users.name ASC';
    } else if ( $_POST['order'] == 'attended' ) {
      $_SESSION['order_by'] = ', user_meta.attended DESC';
    } else if ( $_POST['order'] == 'topped_up' ) {
      $_SESSION['order_by'] = ', user_meta.topped_up DESC';
    } else if ( $_POST['order'] == 'balance' ) {
      $_SESSION['order_by'] = ', credit_account.balance ASC';
    } else {
      $_SESSION['order_by'] = ', users.id ASC';
    }

  }
}
}

$order_by = isset($_SESSION['order_by']) && !empty($_SESSION['order_by']) ? $_SESSION['order_by'] : ', users.id ASC';

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



  <div class="container mt-5">



  <div class="container mt-3">
    <div id="accordion">
      <div class="card">
        <div class="card-header">
          <a class="btn text-center" data-bs-toggle="collapse" href="#collapseOne" style="width:100%;">
            Total
          </a>
        </div>
        <div id="collapseOne" class="collapse hide" data-bs-parent="#accordion">
          <div class="card-body text-justify" style="display:flex;justify-content:flex-start;align">
             <span class="p-1"><span>Total Spent : <?php echo get_total_spent(); ?>$</span> <span id="total_spent_id"></span></span>
             <span class="p-1 spentclass" style="margin-left: auto;"><span>Total Attended : </span>
             <span id="total_attended_id"><?php echo get_total_attendance(); ?></span></span>
          </div>


             <div class="container">




    <table class="table table-active table-striped table-hover p-3 text-center total_table">
      <thead class="table-dark">
        <tr>
          <th class="p-2">Name</th>
          <th class="p-2">Attendance times</th>
          <th class="p-2">Total Fees</th>
          <th class="p-2">Topped Up Total</th>
        </tr>
      </thead>
      <tbody>
        <?php
          $total_rows_query = "SELECT SUM(attended) AS 'attendance', SUM(fee) AS 'fees', SUM(topped_up) AS 'toppedups', users.name FROM user_meta JOIN users ON user_meta.user_id = users.id GROUP BY user_id";
          $total_rows = $conn->query($total_rows_query);
          if ($total_rows->num_rows > 0) {
            while($row = $total_rows->fetch_assoc()) {
              $attendance = $row['attendance'];
              $fees = number_format($row['fees'], 2);
              $toppedups = number_format($row['toppedups'], 2);
              $uname = $row['name'];
        ?>

        <tr>
          <td><?php echo $uname; ?></td>
          <td><?php echo $attendance; ?></td>
          <td><?php echo $fees; ?></td>
          <td><?php echo $toppedups; ?></td>
        </tr>
        <?php             }
                  }
        ?>
      </tbody>
    </table>
  </div>

          </div>
        </div>
      </div>

    </div>
  </div>
  <div class="container mt-3 table_container">

    <div class="card bg-light text-black spent_card">
      <div class="card-body">
        <div class="date_picker_title p-2"><h4 class="text-white text-center bg-success date_badge p-2">Date Picker</h4></div>
        <div class="d-flex p-2">
          <div class="p-2 text-center flex-fill"><button id="previous_day" class="previous_day_class btn btn-outline-dark" style="width: 100%;font-size:1.3em;font-weight:bold;">Previous Day</button></div>
          <div class="text-black bg-dark p-3 date_badge_cont">
            <span class="badge bg-danger p-2 date_badge" style="font-size: 18px; font-weight:bold;">
              <form id="picka_date" action="./" method="GET">
                <input type="date" required name="picked_day" class="form-control form-control-date" id="the_daydate" value="<?php echo $pickedday != ''? $pickedday : ''; ?>" title="pick a date!">
                <input type="submit" id="submit_daypicker" style="display:none;">
              </form>
            </span>
          </div>
          <div class="p-2 text-center flex-fill"><button id="next_day" class="next_day_class btn btn-outline-success" style="width: 100%;font-size:1.3em;font-weight:bold;">Next Day</button></div>
        </div>



      </div>
    </div>

    <div class="card bg-dark text-white spent_card">
      <div id="js_flash" class="alert alert-dismissible text-center p-2" style="display:none;">
        <button type="button" class="btn-close p-2" data-bs-dismiss="alert"></button>
        <strong id="flash_message"></strong>
      </div>

      <div class="card-body text-center" style='font-size:1.1em;font-weight:bold;'>Spent Today : <span>$</span>
        <span class="spent_today"><?php echo number_format($getspenttoday, 2); ?></span>

    <span style="display:inline-block;float: right;"><button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#spentTodayModal" id="open_spent_edit">Edit</button></span>
      </div>
    </div>
    <table class="table table-active table-striped table-hover attendance_table p-3">
      <thead class="table-dark">

        <tr>
          <th class="p-2"><span class="p-1">ID</span>
              <input class="form-radio-input order_input" data-submit="id_order" value="id" type="radio" name="orderinput" <?php echo preg_match("/id/i", $order_by) ? 'checked' : '';  ?>>
          </th>
          <th class="p-2"><span class="p-1">Name</span>
              <input class="form-radio-input order_input" data-submit="id_name" value="name" type="radio" name="orderinput" <?php echo preg_match("/name/i", $order_by) ? 'checked' : '';  ?>>
          </th>
          <th class="p-2"><span class="p-1">Attended?</span>
             <input class="form-radio-input order_input" data-submit="attend_order" value="attended" type="radio" name="orderinput" <?php echo preg_match("/attended/i", $order_by) ? 'checked' : ''; ?>>
          </th>
          <th class="p-2"><span class="p-1">Topped up</span>
              <input class="form-radio-input order_input" data-submit="toppedup_order" value="topped_up" type="radio" name="orderinput" <?php echo preg_match("/topped_up/i", $order_by) ? 'checked' : ''; ?>>
          </th>
          <th class="p-2"><span class="p-1">Today Fees</span></th>
          <th class="p-2"><span class="p-1">Balance</span>
              <input class="form-radio-input order_input" data-submit="balance_order" value="balance" type="radio" name="orderinput" <?php echo preg_match("/balance/i", $order_by) ? 'checked' : ''; ?>>
          </th>
        </tr>



      </thead>
      <tbody>
      <?php
        //SELECT credit_system.spent, credit_system.day_date,user_meta.fee, user_meta.attended, user_meta.day_date AS 'meta_day', user_meta.user_id, users.id, users.email, credit_account.balance FROM `credit_system` JOIN user_meta ON credit_system.day_date = user_meta.day_date JOIN users ON user_meta.user_id = users.id JOIN credit_account ON user_meta.user_id = credit_account.user_id ORDER BY user_meta.day_date
        //$user_datasql = "SELECT credit_system.spent, credit_system.day_date,user_meta.fee, user_meta.id AS 'meta_id', user_meta.topped_up, user_meta.attended, user_meta.day_date AS 'meta_day', user_meta.user_id, users.id, users.email, credit_account.balance FROM `credit_system` JOIN user_meta ON credit_system.day_date = user_meta.day_date JOIN users ON user_meta.user_id = users.id JOIN credit_account ON user_meta.user_id = credit_account.user_id ORDER BY user_meta.day_date, users.id";
        $user_datasql = "SELECT credit_system.day_date,user_meta.fee, user_meta.id AS 'meta_id', user_meta.topped_up, user_meta.attended, user_meta.day_date AS 'meta_day',";
        $user_datasql .= "user_meta.user_id, users.id, users.name, credit_account.balance FROM `credit_system` INNER JOIN user_meta ON credit_system.day_date = user_meta.day_date INNER JOIN users ON user_meta.user_id = users.id JOIN credit_account ON user_meta.user_id = credit_account.user_id  WHERE credit_system.day_date='". $todaydate  ."' ORDER BY user_meta.day_date" . $order_by;
        /* Main query return the meta table id,name,fee,topped_up, balance*/
        $user_data = $conn->query($user_datasql);
        if ($user_data->num_rows > 0) {
          while($row = $user_data->fetch_assoc()) {
            $user_name = $row['name'];
            $meta_id = $row['meta_id'];
            $userid = $row['user_id'];
            $attended = $row['attended'] == True ? 'checked' : '';
            $topped_uptoday = number_format($row['topped_up'] , 2);
            $fee_today = number_format($row['fee'], 2);
            $balance = number_format($row['balance'], 2); ?>
        <tr>
          <td><?php echo $userid; ?></td>
          <td><?php echo $user_name; ?></td>
          <td>
            <div  class="form-check text-center">
              <form name="update_attended_<?php echo $meta_id ?>" action="./includes/attendance.php" method="POST">
                <input class="attend_checkbox" type="checkbox" data-id="<?php echo $meta_id ?>" id="check_<?php echo $meta_id ?>" <?php echo $attended; ?> >
              </form>
            </div>
          </td>
          <td>
            <div style="position:relative;">
              <span>$</span><span class="toped_price"><?php echo $topped_uptoday; ?></span>
              <span class="edit_row"><button type="button" data-id="<?php echo $meta_id ?>" data-value="<?php echo $topped_uptoday; ?>" class="fa fa-edit bg-light text-dark p-1 toppedup_update" data-bs-toggle="modal" data-bs-target="#toppedupModal"></button></span>
           </div>
          </td>
          <td>
              <div style="position:relative;">
                <span>$</span><span class="fee"><?php echo $fee_today; ?></span>
                <!-- <span class="edit_fee"><button type="button" data-id="" data-value="" class="fa fa-edit bg-light text-dark p-1" data-bs-toggle="modal" data-bs-target="#feeModal"></button></span> -->
              </div>
          </td>
          <td><span>$</span><span class="balance"><?php echo $balance; ?></span></td>
        </tr>
      <?php     }
       } ?>

      </tbody>
    </table>
  </div>



  <div class="container mt-3">
    <div id="users">
      <div class="card">
        <div class="card-header">
          <a class="btn text-center" data-bs-toggle="collapse" href="#userOne" style="width:100%;">
            Users Control
          </a>
        </div>
        <div id="userOne" class="collapse hide" data-bs-parent="#users">


    <div class="container text-center">

    <div class="d-grid">
      <button class="btn btn-primary btn-block mt-2 mb-1" data-bs-toggle="modal" data-bs-target="#userModal" >Add New User</button>
    </div>
    <table class="table table-active table-striped table-hover p-3 text-center total_table">
      <thead class="table-dark">
        <tr>
          <th class="p-2">ID</th>
          <th class="p-2">Name</th>
          <th class="p-2">Email</th>
          <th class="p-2">Company</th>
          <th class="p-2">Balance</th>
        </tr>
      </thead>
      <tbody>
        <?php
          //SELECT credit_system.spent, credit_system.day_date,user_meta.fee, user_meta.attended, user_meta.day_date AS 'meta_day', user_meta.user_id, users.id, users.email, credit_account.balance FROM `credit_system` JOIN user_meta ON credit_system.day_date = user_meta.day_date JOIN users ON user_meta.user_id = users.id JOIN credit_account ON user_meta.user_id = credit_account.user_id ORDER BY user_meta.day_date
          //$user_datasql = "SELECT credit_system.spent, credit_system.day_date,user_meta.fee, user_meta.id AS 'meta_id', user_meta.topped_up, user_meta.attended, user_meta.day_date AS 'meta_day', user_meta.user_id, users.id, users.email, credit_account.balance FROM `credit_system` JOIN user_meta ON credit_system.day_date = user_meta.day_date JOIN users ON user_meta.user_id = users.id JOIN credit_account ON user_meta.user_id = credit_account.user_id ORDER BY user_meta.day_date, users.id";
          $user_datasql = "SELECT users.id, users.name, users.email, users.company, credit_account.balance FROM users JOIN credit_account ON users.id=credit_account.user_id ORDER BY users.id";

          $user_data = $conn->query($user_datasql);
          if ($user_data->num_rows > 0) {
            while($row = $user_data->fetch_assoc()) {
              $uid = $row['id'];
              $uname = $row['name'];
              $umail = $row['email'];
              $ucompany = $row['company'];
              $balance = $row['balance'];
        ?>
        <tr>
          <td><?php echo $uid ?></td>
          <td><?php echo $uname ?></td>
          <td><?php echo $umail ?></td>
          <td><?php echo $ucompany ?></td>
          <td><?php echo $balance ?></td>
        </tr>
      <?php
           }
              }
      ?>
      </tbody>
    </table>
  </div>

          </div>
        </div>
      </div>

    </div>


  <!-- The Modales -->
  <div class="modal fade" id="myModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Modal Heading</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
               <form id="meta_update" action="./" method="GET">
                <label for="topped_up" class="mb-2">spent today</label>
                <input type="text" class="form-control" name="topped_up"
                 placeholder="0.00" required>
                <input class="form-control btn btn-primary" name="meta_update"  type="submit">
              </form>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>


  <div class="modal fade" id="userModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Add New User</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
               <form id="create_user" action="./includes/users.php" method="POST">
               <label for="name" class="mb-2">User Name:</label>
               <input type="text" class="form-control" name="name" placeholder="Enter Name" required>


               <label for="email" class="mb-2">User Email:</label>
               <input type="text" class="form-control" name="email" placeholder="Enter Email" required>

               <label for="email" class="mb-2">User Company:</label>
               <input type="text" class="form-control" name="company" placeholder="Enter Company" required>

                <input class="form-control btn btn-primary" name="create_user"  type="submit">
              </form>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>


  <div class="modal fade" id="toppedupModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4 class="modal-title">Topp Up</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">
               <form id="topped_update" action="./includes/topped_up.php" method="POST">
                 <label for="topped_up" class="mb-2">Topp Up Amount: </label>
                 <input type="hidden" class="form-control" id="toppedup_metaid" name="meta_id" value="" style="display:none;" required>
                 <input type="text" class="form-control" id="toppedup_value" name="topped_up" placeholder="0.00"
                  required>
                <input class="form-control btn btn-primary" type="submit">
              </form>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Close</button>
        </div>

      </div>
    </div>
  </div>


  <div class="modal fade" id="spentTodayModal">
    <div class="modal-dialog">
      <div class="modal-content">

        <!-- Modal Header -->
        <div class="modal-header">
          <h4>update the spent today value:</h4>
          <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
        </div>

        <!-- Modal body -->
        <div class="modal-body">

               <form id="spent_update" action="includes/spent.php" method="POST">
                <label for="spent_today" class="mb-2">spent today</label>
                <input type="hidden" class="form-control" id="credit_spent_id" value="<?php echo $credit_spent_id; ?>" name="credit_spent_id" style="display:none;">


                <input type="text" class="form-control" id="spent_today" name="spent_today"
                 placeholder="0.00"
                 title="Enter Price example 10.00" required>
                 <input type="hidden" class="form-control" id="spent_today_value" name="spent_today_value"
                  placeholder="0.00" required style="display:none;">
                <input id="spent_today_input" type="hidden" value="" style="display:none;">
                <input  class="form-control btn btn-primary" type="submit">
              </form>
        </div>

        <!-- Modal footer -->
        <div class="modal-footer">
          <button type="button" class="btn btn-danger" data-bs-dismiss="modal">Cancel</button>
        </div>

      </div>
    </div>
  </div>

<form action="./index.php" method="POST" style="display:none;">
  <input type="hidden" id="order_select" name="order">
  <input type="submit" id="order_submit">
</form>

<script src="assets/js/app.js" type="text/javascript"></script>
<script src="assets/js/appjquery.js" type="text/javascript"></script>



</body>
</html>
