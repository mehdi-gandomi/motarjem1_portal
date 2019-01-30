//this fucntion replaces all occurances in string with desired value
String.prototype.replaceAll = function(search, replacement) {
  var target = this;
  return target.replace(new RegExp(search, "g"), replacement);
};
//this function gets querystring value by key from url or a querystring text
function getQueryString(name, url) {
  if (!url) url = window.location.href;
  name = name.replace(/[\[\]]/g, "\\$&");
  var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
    results = regex.exec(url);
  if (!results) return null;
  if (!results[2]) return "";
  return decodeURIComponent(results[2].replace(/\+/g, " "));
}
//this function converts js object to querystring to use on urls
function serializeQSObject(obj) {
  var str = [];
  for (var p in obj) {
    if (obj[p] == "") continue;
    if(Array.isArray(obj[p]) && obj[p].length<1) continue;
    if (obj.hasOwnProperty(p)) {
      str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
    }
  }

  return str.join("&").replaceAll("%2C", ",");
}
//this function converts querystring to js object to manipulate object
function quertStringToObj(queryString) {
  if(queryString=="?" || queryString == "") return false;
  return JSON.parse('{"' + queryString.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
    function(key, value) {
      return key === "" ? value : decodeURIComponent(value);
    }
  );
}

//this functions show messages based on given json
function showMessages(data) {
  let output = "";
  if (data.messages.length > 0) {
    data.messages.forEach(function(message) {
      let isRead=message.is_read == "0" ? "پاسخ داده نشده": "پاسخ داده شده";
      let isAnswered=message.is_answered == "0" ? "پاسخ داده نشده" : "پاسخ داده شده";
      output += "<tr>";
      output += "<td>" + message.msg_id + "</td>";
      output += "<td>" + message.subject + "</td>";
      output += "<td>" + isRead + "</td>";
      output += "<td>" + isAnswered + "</td>";
      output += "<td>" + message.create_date_persian + "</td>";
      output += "<td>" + message.update_date_persian + "</td>";
      output +=
        "<td class='order-more-info'><a href='/user/message/view/" +
        message.msg_id +
        '\'><svg width="13px" height="23px" viewBox="0 0 50 80" xml:space="preserve"><polyline fill="none" stroke="#a9a9a9" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" points="45.63,75.8 0.375,38.087 45.63,0.375 "/></svg></a></td>';
      output+="</tr>";
    });
  }
  document.getElementById("user-messages").innerHTML = output;
}

function createPaginationUrl(queryString,pageNumber){
  queryStringObj = quertStringToObj(queryString);
  queryStringObj["page"]=pageNumber;
  return "/user/messages?".serializeQSObject(queryStringObj);
}

//this function shows pagination for messages
function showPagination(data, queryString,visibleNumbers) {
  let output="";
  if(data.messages_count > 0){
    let lastPage=data.messages_count/10;
    let endIndex,startIndex;
    output+=data.current_page !=0 ? '<li class="page-item"><a class="page-link" href="/user/messages" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li><li class="page-item"><a class="page-link" href="/user/messages?page='+data.current_page-1+'" aria-label="Previous">قبلی</a></li>' : "";
    if((data.current_page+(visibleNumbers-1)) > lastPage){
      endIndex=lastPage;
      startIndex=data.current_page-(visibleNumbers-(lastPage-data.current_page));
    }else{
      startIndex=data.current_page - (visibleNumbers-1);
      startIndex= startIndex<=0 ? 1:startIndex;
      endIndex=data.current_page+ (visibleNumbers-1);
    }

    for(pageNumber=startIndex;pageNumber<=endIndex;pageNumber++){
      output+= pageNumber==data.current_page ? "<li class='page-item active'>":"<li class='page-item'>";
      output+="<a class='page-link' href='"+createPaginationUrl(queryString,pageNumber)+"'>"+pageNumber+"</a>";
    }
    if(data.current_page != lastPage){
      output+='<li class="page-item"><a class="page-link" href="/user/messages?page='+data.current_page+1+'" aria-label="Previous">بعدی</a></li>';
      output+='<li class="page-item"><a class="page-link" href="/user/messages?page='+lastPage+'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
    }
  }
  console.log(output);
  $(".pagination").html(output);
}

//this function gets data from server based on given filters
function applyFilters(queryString) {
  window.history.pushState(
    "filtered messages",
    "پیام های فیلتر شده",
    location.origin + location.pathname + "?" + queryString
  );
  $.get("/user/messages/json?" + queryString, function(res, status) {
    console.log(status);
    if (status) {
      showMessages(res);
      showPagination(res, queryString);
    }
  });
}

//START this functions gets called when a checkbox state changes
$("#read-messages").change(function(e) {
  let pageQs = getQueryString("page");
  let queryStringObject = {};
  if (pageQs) {
    queryStringObject["page"] = pageQs;
  }
  let isUnreadChecked = document.getElementById("unread-messages").checked;
  let isAnsweredChecked = document.getElementById("answered-messages").checked;
  let isUnansweredChecked = document.getElementById("unanswered-messages")
    .checked;
  queryStringObject["read"] = [];
  queryStringObject["answered"] = [];
  if (e.currentTarget.checked) {
    queryStringObject["read"].push("1");
  }
  if (isUnreadChecked) {
    queryStringObject["read"].push("0");
  }
  if (isAnsweredChecked) {
    queryStringObject["answered"].push("1");
  }
  if (isUnansweredChecked) {
    queryStringObject["answered"].push("0");
  }
  queryStringObject["read"] =queryStringObject["read"].join(",");
  queryStringObject["answered"] = queryStringObject["answered"].join(",");
  let queryString = serializeQSObject(queryStringObject);

  applyFilters(queryString);
});
$("#unread-messages").change(function(e) {
  let pageQs = getQueryString("page");
  let queryStringObject = {};
  if (pageQs) {
    queryStringObject["page"] = pageQs;
  }
  let isReadChecked = document.getElementById("read-messages").checked;
  let isAnsweredChecked = document.getElementById("answered-messages").checked;
  let isUnansweredChecked = document.getElementById("unanswered-messages")
    .checked;
  queryStringObject["read"] = [];
  queryStringObject["answered"] = [];
  if (e.currentTarget.checked) {
    queryStringObject["read"].push("0");
  }
  if (isReadChecked) {
    queryStringObject["read"].push("1");
  }
  if (isAnsweredChecked) {
    queryStringObject["answered"].push("1");
  }
  if (isUnansweredChecked) {
    queryStringObject["answered"].push("0");
  }
  queryStringObject["read"] =queryStringObject["read"].join(",");
  queryStringObject["answered"] = queryStringObject["answered"].join(",");
  let queryString = serializeQSObject(queryStringObject);
  applyFilters(queryString);
});

$("#answered-messages").change(function(e) {
  let pageQs = getQueryString("page");
  let queryStringObject = {};
  if (pageQs) {
    queryStringObject["page"] = pageQs;
  }
  let isReadChecked = document.getElementById("read-messages").checked;
  let isUnreadChecked = document.getElementById("unread-messages").checked;
  let isUnansweredChecked = document.getElementById("unanswered-messages")
    .checked;
  queryStringObject["read"] = [];
  queryStringObject["answered"] = [];
  if (e.currentTarget.checked) {
    queryStringObject["answered"].push("1");
  }
  if (isUnansweredChecked) {
    queryStringObject["answered"].push("0");
  }
  if (isReadChecked) {
    queryStringObject["read"].push("1");
  }
  if (isUnreadChecked) {
    queryStringObject["read"].push("0");
  }
  queryStringObject["read"] =queryStringObject["read"].join(",");
  queryStringObject["answered"] = queryStringObject["answered"].join(",");
  let queryString = serializeQSObject(queryStringObject);

  applyFilters(queryString);
});
$("#unanswered-messages").change(function(e) {
  let pageQs = getQueryString("page");
  let queryStringObject = {};
  if (pageQs) {
    queryStringObject["page"] = pageQs;
  }
  let isReadChecked = document.getElementById("read-messages").checked;
  let isUnreadChecked = document.getElementById("unread-messages").checked;
  let isAnsweredChecked = document.getElementById("answered-messages").checked;
  queryStringObject["read"] = [];
  queryStringObject["answered"] = [];
  if (e.currentTarget.checked) {
    queryStringObject["answered"].push("0");
  }
  if (isAnsweredChecked) {
    queryStringObject["answered"].push("1");
  }
  if (isReadChecked) {
    queryStringObject["read"].push("1");
  }
  if (isUnreadChecked) {
    queryStringObject["read"].push("0");
  }
  queryStringObject["read"] =queryStringObject["read"].join(",");
  queryStringObject["answered"] = queryStringObject["answered"].join(",");
  let queryString = serializeQSObject(queryStringObject);

  applyFilters(queryString);
});
//END this functions gets called when a checkbox state changes

//send message with ajax request
$("#send-message-btn").click(function(e){
  let subject=$("#subject").val();
  let body=$("#medium-editor").val();
  if(subject == ""){
    alert("باید حداقل یک عنوان وارد نمایید!");
    return;
  }
  if(body == ""){
    alert("باید متن پیام تان را وارد نمایید !");
    return;
  }

  $.ajax({
    type:"POST",
    url:$("#sendMessageForm").attr("action"),
    data:{
      subject:subject,
      body:body
    },
    success:function(data,status){
      if(status && data.status){
        $("#newMessageModal").modal("hide");
        Swal.fire(
          'موفقیت آمیز !',
          'پیام شما با موفقیت ارسال شد !',
          'success'
        )
      }
    }
  })
})