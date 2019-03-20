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
    if (!results[2]) return null;
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  }
  //this function converts js object to querystring to use on urls
  function serializeQSObject(obj) {
    var str = [];
    for (var p in obj) {
      if (obj[p] == "") continue;
      if (Array.isArray(obj[p]) && obj[p].length < 1) continue;
      if (obj.hasOwnProperty(p)) {
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
      }
    }
  
    return str.join("&").replaceAll("%2C", ",");
  }
  //this function converts querystring to js object to manipulate object
  function quertStringToObj(queryString) {
    if (queryString == "?" || queryString == "") return false;
    return JSON.parse(
      '{"' + queryString.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
      function(key, value) {
        return key === "" ? value : decodeURIComponent(value);
      }
    );
  }
  function substitute(str, data) {
    let output = str.replace(/%[^%]+%/g, function(match) {
        if (match in data) {
            return(data[match]);
        } else {
            return("");
        }
    });
    return(output);
  }
  
  
  //this functions show messages based on given json
  function showTickets(data) {
    let output = "";
    // if (data.messages.length > 0) {
    data.tickets.forEach(function(ticket) {
      let state;
      if(ticket.state=="read"){
        state="خوانده شده"
      }
      else if(ticket.state=="unread"){
        state="خوانده نشده"
      }
      else if(ticket.state=="waiting"){
        state="در انتظار پاسخ";
      }else{
        state="پاسخ داده شده";
      }
      output += "<tr>";
      output += "<td data-label='شماره تیکت'>#" + ticket.ticket_number + "</td>";
      output += "<td data-label='عنوان تیکت'>" + ticket.subject + "</td>";
      output += "<td data-label='وضعیت'>" + state + "</td>";
      output += "<td data-label='تاریخ ایجاد تیکت'>" + ticket.create_date_persian + "</td>";
      output += "<td data-label='آخرین تاریخ بروزرسانی'>" + ticket.update_date_persian + "</td>";
      output +=
        "<td class='order-more-info'><a href='/admin/ticket/view/" +
        ticket.ticket_number +
        '\'><svg width="13px" height="23px" viewBox="0 0 50 80" xml:space="preserve"><polyline fill="none" stroke="#a9a9a9" stroke-width="10" stroke-linecap="round" stroke-linejoin="round" points="45.63,75.8 0.375,38.087 45.63,0.375 "/></svg></a></td>';
      output += "</tr>";
    });
    // }
    document.getElementById("user-messages").innerHTML = output;
  }
  
  
  //this function shows pagination
  function showPagination(count,current_page,offset,visibleNumbers,queryString,baseUrl) {
    baseUrl=baseUrl===undefined ? window.location.origin+window.location.pathname : baseUrl;
    let output="";
    let fullUrl;
    if(queryString){
        fullUrl=baseUrl+"?"+queryString+"&page=%page%";
    }else{
        fullUrl=baseUrl+"?page=%page%";
    }
    if(count > offset){
      
        let lastPage=Math.ceil(count/offset);
        let endIndex,startIndex;
        output+=current_page > 1 ? '<li class="page-item"><a class="page-link" href="'+baseUrl+'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li><li class="page-item"><a class="page-link" href="'+substitute(fullUrl,{"%page%":current_page-1})+'" aria-label="Previous">قبلی</a></li>' : "";
        if((current_page+(visibleNumbers-1)) > lastPage){    
          endIndex=lastPage;
          startIndex=current_page-(visibleNumbers-(lastPage-current_page));
        }else{
        startIndex=current_page - (visibleNumbers-1);
        endIndex=current_page+ (visibleNumbers-1);
        }
        startIndex= startIndex<=0 ? 1:startIndex;
        for(pageNumber=startIndex;pageNumber<=endIndex;pageNumber++){
        output+= pageNumber==current_page ? "<li class='page-item active'>":"<li class='page-item'>";
        output+="<a class='page-link' href='"+substitute(fullUrl,{"%page%":pageNumber})+"'>"+pageNumber+"</a>";
        }
        if(current_page != lastPage){
        output+='<li class="page-item"><a class="page-link" href="'+substitute(fullUrl,{"%page%":current_page+1})+'" aria-label="Previous">بعدی</a></li>';
        output+='<li class="page-item"><a class="page-link" href="'+substitute(fullUrl,{"%page%":lastPage})+'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
    }
    $(".pagination").html(output);
    }
  
  //this function gets data from server based on given filters
  function applyFilters(queryString) {
    url = location.origin + location.pathname;
    url = queryString ? url + "?" + queryString : url;
    window.history.pushState("filtered messages", "پیام های فیلتر شده", url);
    $.get(window.location.pathname+"/json?" + queryString, function(res, status) {
      if (status) {
        showTickets(res);
        showPagination(res.tickets_count,res.current_page,10,3,queryString);
      }
    });
  }
  
  //START this functions gets called when a checkbox state changes
  $(".table-filter input[type=checkbox]").on("change", function(e) {
    let queryStringObj = { state: [] };
    $("input[name=state]").each(function() {
      if ($(this).is(":checked")) {
        queryStringObj["state"].push($(this).val());
      }
    });
    if (queryStringObj["state"].length > 0) {
      queryStringObj["state"] = queryStringObj["state"].join(",");
    }
    let page = getQueryString("page");
    if (page) {
      queryStringObj["page"] = page;
    }
    queryString = serializeQSObject(queryStringObj);
    applyFilters(queryString);
  });
  
  //END this functions gets called when a checkbox state changes