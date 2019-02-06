function getQueryString(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, '\\$&');
  var regex = new RegExp('[?&]' + name + '(=([^&#]*)|&|#|$)'),
      results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return '';
  return decodeURIComponent(results[2].replace(/\+/g, ' '));
}
function showOrders(data) {
  let output = "";
  if (data.orders.length > 0) {
    data.orders.forEach(function(order) {
      let pageNumber = order.word_numbers / 250;
      let delivery_type = "";
      let translation_type = "";
      let translation_quality = "";
      let accepted = "";
      if (order.delivery_type == 1) {
        delivery_type = "معمولی";
      } else if (order.delivery_type == 2) {
        delivery_type = "نیمه فوری";
      } else if (order.delivery_type == 3) {
        delivery_type = "فوری";
      }

      if (order.translation_type == 1) {
        translation_type = "فارسی به انگلیسی";
      } else {
        translation_type = "انگلیسی به فارسی";
      }

      if (order.translation_quality == 5) {
        translation_quality = "نقره ای";
      } else if (order.translation_quality == 10) {
        translation_quality = "طلایی";
      }

      if (order.accepted == 0) {
        accepted = "تایید نشده";
      } else {
        accepted = "تایید شده";
      }
      output += "<tr>";
      output += "<td>" + order.order_id + "</td>";
      output += "<td>" + pageNumber + "</td>";
      output += "<td>" + translation_type + "</td>";
      output += "<td>" + translation_quality + "</td>";
      output += "<td>" + delivery_type + "</td>";
      output += "<td>" + order.order_price + "</td>";
      output += "<td>" + accepted + "</td>";

      output +=
        "<td><a style='cursor:pointer;color:#20a8d8' onclick='showTranslatorInfo(\"" +
        order.translator_id +
        "\")'>" +
        order.translator_fname +
        " " +
        order.translator_lname +
        "</a></td>";
      output +=
        "<td><a href='/user/order/view/" +
        order.order_id +
        "' class='btn btn-primary'>مشاهده سفارش</a></td>";
    });
  }
  document.getElementById("user-orders").innerHTML = output;
}

$("#pending-orders").change(function(e) {

  let newQs="";
  let pageQs=getQueryString("page");
  if(pageQs){
    newQs+="?page="+pageQs;
  }
  let isCompletedChecked = document.getElementById("completed-orders").checked;
  if (e.currentTarget.checked) {
    if(newQs!=""){
      newQs+="&pending=true";
    }else{
      newQs+="?pending=true";
    }
    if (isCompletedChecked) {
      newQs += "&completed=true";
    }
  } else {
    if (isCompletedChecked) {
      if(newQs!=""){
        newQs += "&completed=true";
      }else{
        newQs += "?completed=true";
        
      } 
      
    }
  }
  $.get("/user/orders/json"+newQs, function(res, status) {
    if (status) {
      showOrders(res);
      // showPagination(res);
    }
  });
  window.history.pushState(
    "pending orders",
    "سفارشات درحال انجام",
    location.origin+location.pathname+newQs
  );
});
$("#completed-orders").change(function(e) {
  let newQs="";
  let pageQs=getQueryString("page");
  if(pageQs){
    newQs+="?page="+pageQs;
  }
  
  let isPendingChecked = document.getElementById("pending-orders").checked;
  
  if (e.currentTarget.checked) {
    if(newQs!=""){
      newQs+="&completed=true";
    }else{
      newQs+="?completed=true";
    }
    if (isPendingChecked) {
      newQs += "&pending=true";
      
    }
  } else {
    if (isPendingChecked) {
      if(newQs!=""){
        newQs += "&pending=true";
      }else{
        newQs += "?pending=true";
        
      } 
      
    }
  }
  
  
  $.get("/user/orders/json"+newQs, function(res, status) {
    if (status) {
      showOrders(res);
      // showPagination(res);
    }
  });
  window.history.pushState(
    "completed orders",
    "سفارشات تکمیل شده",
    location.origin+location.pathname+newQs
  );
});

//show translator info in a modal
function showTranslatorInfo(translatorId) {
    console.log(translatorId);
    $("#translatorInfo").modal("show");
    $.get("/user/translator/getinfo/" + translatorId, function (res) {

        $("#translator-avatar").attr("src", "/public/uploads/avatars/user/" + res.avatar);
        $("#translator-name").text(res.fname + " " + res.lname);
        $("#translator-email").text(res.email);
        $("#translator-phone").text(res.cell_phone);

    });
}