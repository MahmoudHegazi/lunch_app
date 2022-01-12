window.addEventListener('DOMContentLoaded', (event) => {

let oldvalue, newvalue;
// function to get ontime old date value if no date return current date
const getCurrentDate = function(calc='none'){
  let selectedDate = $("#picka_date input[name='picked_day']") && $("#picka_date input[name='picked_day']").val();
  if (selectedDate){
    oldvalue = new Date($("#picka_date input[name='picked_day']").val());
  } else if (selectedDate == false && calc == 'add'){

    oldvalue = new Date();
    oldvalue = oldvalue.setDate(new Date().getDate() + -1);
  } else if (selectedDate == false && calc == 'substract'){
    oldvalue = new Date();
    oldvalue = oldvalue.setDate(oldvalue.getDate() + 1);
  } else {
    oldvalue = new Date();
  }
  return new Date(oldvalue);
};

function formatDate (formattedDate){
  if (formattedDate){
    formattedDate = new Date(formattedDate);
    let day = formattedDate.getDate();
    let month= formattedDate.getMonth() + 1;
    let year=  formattedDate.getFullYear();
    month = (month < 10) ? "0"+ month : month;
    day = (day < 10) ? "0"+ day : day;
    date = `${year}-${month}-${day}`;
    return date
  } else {
    return formattedDate;
  }
}


$(function() {
  $('#open_spent_edit').on('click', function() {
    $("#spent_today_input").val($("#the_daydate").val());
  });
  $('#spent_today').on('input', function() {
    $("#spent_today_value").val(Number($("#spent_today").val()).toFixed(2));
  });
});

$(function() {
  const urlSearchParams = new URLSearchParams(window.location.search);
  const params = Object.fromEntries(urlSearchParams.entries());
  const isselected_day = params.hasOwnProperty('picked_day');
  const orderValue = params.hasOwnProperty('order');
  if (!isselected_day){
    $("#the_daydate").val(formatDate(getCurrentDate()));
    $("#picka_date input[type='submit']").click();
  }
});


$(function() { //shorthand document.ready function
  $('#picka_date').on('change', function(e) { //use on if jQuery 1.7+
    e.preventDefault();  //prevent form from submitting
    $("#picka_date input[type='submit']").click();
  });

  /* get value of selected day or today if not selected
     update form date_input value after add 1 to the date and submit
  */



  $('#next_day').on('click', function() {
    $('#next_day').css('display', 'none');
    $('#previous_day').css('display', 'none');
    oldvalue = getCurrentDate('add');
    newvalue = new Date()
    newvalue = newvalue.setDate(oldvalue.getDate() + 1);
    newvalue = formatDate(newvalue);
    $("#the_daydate").val(newvalue);
    $("#picka_date input[type='submit']").click();

  });
  $('.toppedup_update').on('click', function(event) {
    $('#toppedup_metaid').val($(event.target).attr("data-id"));
    $('#toppedup_value').val($(event.target).attr("data-value"));
  });

  $('#previous_day').on('click', function() {
    $('#previous_day').css('display', 'none');
    $('#next_day').css('display', 'none');
    oldvalue = getCurrentDate('substract');
    newvalue = new Date()
    newvalue = newvalue.setDate(oldvalue.getDate() - 1);
    newvalue = formatDate(newvalue);
    $("#the_daydate").val(newvalue);
    $("#picka_date input[type='submit']").click();

  });


  /* get value of selected day or today if not selected
     update form date_input value after substract 1 from the date and submit
  */
  // Variable to hold request
  var request;


  let allattendcheckbox = $(".attend_checkbox");
  allattendcheckbox.each(function () {
    let elm = $(this);
    $(elm).on('change', async function(event) {
      event.preventDefault();
      if ($(`input[data-check='${$(elm).attr('id')}']`)){
        let checked = event.target.checked ? 1 : 0;

        const data = {attended: checked, meta_id: elm.attr("data-id"), meta_day: $('#the_daydate').val()}

        const rows_selectors = {attendes_rows: $('.attend_checkbox'), fee_rows: $('.fee'), balance_rows: $('.balance')};
        const inputs = $('.attend_checkbox');
        sendAJAX_request('includes/attendance.php', $('.attend_checkbox'), data, rows_selectors);


      } else {
        console.log("attend meta id not found on this row php releated error");
      }
    });
  });

  let allorders = $(".order_input");

  allorders.each(function () {
    let order_input = $(this);
    $(order_input).on('change', function(event) {
      $("#order_select").val($(order_input).val());
      $("#order_submit").click();
    });
  });




  function updateTextRows(rows, balance_values=[], fees_values=[]){
    const list1_size = rows.balance_rows.length;
    const list2_size = rows.fee_rows.length
    const list3_size = balance_values.length;
    const list4_size = fees_values.length;
    // chain all must same with less conditions
    // Important (make sure all row cells client and server equal) (PHP render related error)
    if (list1_size != list2_size && list2_size != list3_size && list3_size != list4_size){
      alert("Unexcepted Error Rows Count not equal contact support");
    }
    $(rows.balance_rows).each(function(index,element){
      $(rows.balance_rows).text(balance_values[index]);
      $(rows.fee_rows[index]).text(fees_values[index]);
    });
}
  // Bind to the submit event of our form

  async function sendAJAX_request(url='includes/attendance.php', inputs=[], data={}, rows){
    $(inputs).prop("disabled", true);
    const res = await postData(url, data);
    $(inputs).prop("disabled", false);

    if (res.code ==  200){
      let balance_values=[];
      let fees_values=[];
      res.data.forEach( (item, index)=>{


        rows.balance_rows[index].innerText = item.balance;
        rows.fee_rows[index].innerText = item.fee;
        $('#js_flash').css("display", "block");
        $('#js_flash').addClass("alert-success");
        let currnt_row = rows.balance_rows[index].parentElement.parentElement;
        $(".attendance_table tr").each(function(){
          let row = $(this);
          if (row[0].classList.contains("active_item")){
            $(currnt_row)[0].classList.remove("active_item");
          };

        });
        if (index == res.row_id-1){
          $(currnt_row).addClass("active_item");
        }
        $('#flash_message').text(res.message);
      });
    } else {
      $('#js_flash').css("display", "block");
      $('#js_flash').addClass("alert-danger");
      $('#flash_message').text(res.message);
    }
  }

  async function postData (url = '', data = {}) {

  	const response = await fetch(url, {
  		method: 'POST',
  		credentials: 'same-origin',
  		headers: {
  			'Content-Type': 'application/json',
  		},
  		// Body data type must match "Content-Type" header
  		body: JSON.stringify(data),
  	});
  	try {
  		const newData = await response.json();
  		return newData;
  	} catch (error) {
  		console.log("error", error);
  	}

  };

});
});
