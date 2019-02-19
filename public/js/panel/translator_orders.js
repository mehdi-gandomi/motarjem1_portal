//this fucntion replaces all occurances in string with desired value
String.prototype.replaceAll = function(search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, "g"), replacement);
  };
  // this function substitutes placeholders with value given as object
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
      if (Array.isArray(obj[p]) && obj[p].length < 1) continue;
      if (obj.hasOwnProperty(p)) {
        str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
      }
    }
  
    return str.join("&").replaceAll("%2C", ",");
  }
  //this function converts querystring to js object to manipulate object
  function queryStringToObj(queryString) {
    if (queryString == "?" || queryString == "") return {};
    return JSON.parse(
      '{"' + queryString.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
      function(key, value) {
        return key === "" ? value : decodeURIComponent(value);
      }
    );
  }

$("input[type=checkbox]").on("change",function(e){
    let state=e.target.checked;
    let queryString="";
    let value=e.target.value;
    let page=getQueryString("page");
    let doneQsFromUrl=getQueryString("done");
    if(doneQsFromUrl && doneQsFromUrl.indexOf(value) < 0 ){
        console.log("fuck1");
        if(state){
            doneQsFromUrl+=","+value;
        }else{
            doneQsFromUrl=doneQsFromUrl.substring(doneQsFromUrl.indexOf(value),1);
            if(doneQsFromUrl.indexOf(",")) doneQsFromUrl= doneQsFromUrl.substr(0,doneQsFromUrl.length-1);
        }
    }else{
        if(state){
            doneQsFromUrl=value;
        }else{
            doneQsFromUrl=$("input[type=checkbox]:checked").val();
        }
    }
    if(page){
        queryString="?page="+page;
        if(doneQsFromUrl){
            queryString+="&done="+doneQsFromUrl
        }
    }else{
        if(doneQsFromUrl){
            queryString="?done="+doneQsFromUrl;
        }
    }
    window.history.pushState("filtered data","سفارشات شما",window.location.origin+window.location.pathname+queryString);
    getOrdersFromApi(window.location.origin+window.location.pathname,queryString);
})

function getOrdersFromApi(baseUrl,queryString){
    $.get(baseUrl+"/json"+queryString,function(data,status){
        renderOrders(data.orders);
        showPagination(data.orders_count,data.current_page,10,window.location.origin+window.location.pathname, queryString,3);
    })
}


function renderOrders(orders){
    console.log(orders);
    let output="";
    $(".OrdersTable thead").css("display","table-header-group");
    for(let index in orders){
        let translationLang=orders[index].translation_lang == "1"? "انگلیسی به فارسی": "فارسی به انگلیسی";
        let translationQuality=orders[index].translation_quality == "5" ? "نقره ای" : "طلایی";
        output+="<tr>";
            output+="<td data-label='شماره سفارش'>"+orders[index].order_id+"</td>";
            output+="<td data-label='تعداد صفحات'>"+Math.ceil(orders[index].word_numbers / 250)+"</td>";
            output+="<td data-label='زبان ترجمه'>"+translationLang+"</td>";
            output+="<td data-label='رشته'>"+orders[index].study_field+"</td>";
            output+="<td data-label='کیفیت ترجمه'>"+translationQuality+"</td>";
            output+="<td data-label='هزینه ترجمه'>"+orders[index].order_price+"</td>";
            output+="<td data-label='سهم شما'>"+Math.ceil((orders[index].order_price*70)/100)+"</td>";
            
            output+="<td data-label='جزییات' class='order-more-info'>";
            output+="<a onclick='showOrderInfo(\""+orders[index].order_id+"\")'><svg height='23px' viewBox='0 0 50 80' width='13px' xml:space='preserve'><polyline fill='none' points='45.63,75.8 0.375,38.087 45.63,0.375 ' stroke-linecap='round' stroke-linejoin='round' stroke-width='10' stroke='#a9a9a9'></polyline></svg></a>";
            output+="</td>";
        output+="</tr>";
    }

    if(output){
        $("h5.no-data").remove();
        $("#OrdersWrap").html(output);
    }else{
        $(".OrdersTable thead").css("display","none");
        $(".OrdersTable tbody").html("<h5 class='text-center mt-4'>اطلاعاتی دریافت نشد !</h5>");
    }
    
}
  
//this function shows pagination
function showPagination(count,current_page,offset,baseUrl, queryString,visibleNumbers) {
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
    startIndex= startIndex<=0 ? 1:startIndex;
    endIndex=current_page+ (visibleNumbers-1);
    }

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