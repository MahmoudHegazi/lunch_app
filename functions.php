<?php

function test_input($data) {
  $data = trim($data);
  $data = stripslashes($data);
  $data = htmlspecialchars($data);
  return $data;
}
function format_value($data) {

   /*&& (intval($data) * (10000*10000)) > (intval($data) * (10000*10000)) / 2*/
   if ($data === false or $data === true){
     return 'i';
   }
   try {
     $isfloat = count(explode(".",strval($data))) == 2;
     if (is_numeric($data) == true && $isfloat == false){

            return 'i';
      }
  } catch(Exception $e) {
    //catch exception
      echo 'Message1: ' .$e->getMessage();
    }
  return 's';

}

function format_prepeared_string($data_array){
  $prepare_string = '';
  for ($i=0; $i<count($data_array); $i++){
    $prepare_string .= format_value($data_array[$i]);
  }
  return $prepare_string;
}


function secure_array($data){
  $secure_result = array();
  for ($i=0; $i<count($data); $i++){

    array_push($secure_result, $data[$i]);
  }
  return $secure_result;
}

function format_column_names($column_names){
    if (count($column_names) == 0){return '()';}
    $columnnames = '(';
    $columnvaluesstr = '(';
    for ($i=0; $i<count($column_names); $i++){
      if ($i==0){
        $columnnames .= test_input($column_names[$i]);
        $columnvaluesstr .= '?';
      } else {
        $columnnames .= "," . test_input($column_names[$i]);
        $columnvaluesstr .= ', ?';
      }
    }
    $columnnames .= ')';
    $columnvaluesstr .= ')';
    return array($columnnames, $columnvaluesstr);
}


?>
