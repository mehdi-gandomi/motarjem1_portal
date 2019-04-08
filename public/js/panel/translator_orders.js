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

function showOrderInfo(orderNumber){
    $.get("/translator/order/info/"+orderNumber,function(data,status){
        let output="<div class='order-details row'>";
        let translationLang=data.translation_lang=="1" ? "انگلیسی به فارسی":"فارسی به انگلیسی";
        let translationQuality=data.translation_quality == "5" ? "نقره ای":"طلایی";
        let translationKind=data.translation_kind == '1' ? "عمومی":"تخصصی";
        let deliveryType;
        if(data.delivery_type=="1"){
            deliveryType="معمولی";
        }else if(data.delivery_type=="2"){
            deliveryType="نیمه فوری";
        }else{
            deliveryType="فوری";
        }
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>شماره سفارش</div><div class='order-details__detail__value'>"+data.order_number+"</div></div>";
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>تعداد صفحات</div><div class='order-details__detail__value'>"+Math.ceil(data.word_numbers/250)+"</div></div>";
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>زبان ترجمه</div><div class='order-details__detail__value'>"+translationLang+"</div></div>";
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>کیفیت ترجمه</div><div class='order-details__detail__value'>"+translationQuality+"</div></div>";
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>نوع ترجمه</div><div class='order-details__detail__value'>"+translationKind+"</div></div>";
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>رشته دانشگاهی</div><div class='order-details__detail__value'>"+data.study_field+"</div></div>";
        output+="<div class='order-details__detail col-md-5'><div class='order-details__detail__label'>زمان تحویل</div><div class='order-details__detail__value'>"+deliveryType+"</div></div>";
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>زمان تخمینی برحسب روز</div><div class='order-details__detail__value'>"+data.delivery_days+"</div></div>";
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>نام سفارش دهنده</div><div class='order-details__detail__value'>"+data.orderer_fname+" "+data.orderer_lname+"</div></div>";
        output+="<div class='order-details__detail col-md-5'><div class='order-details__detail__label'>ایمیل سفارش دهنده</div><div class='order-details__detail__value'>"+data.email+"</div></div>";
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>تاریخ ثبت سفارش</div><div class='order-details__detail__value'>"+data.order_date_persian+"</div></div>";
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>کد تخفیف</div><div class='order-details__detail__value'>"+(data.discount_code ? data.discount_code:"استفاده نشده")+"</div></div>";
        if (data.discount_code){
            output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>قیمت سفارش بدون تخفیف</div><div class='order-details__detail__value'>"+parseInt(data.price_without_discount).toLocaleString("us")+"</div></div>";
        }
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>قیمت کل سفارش</div><div class='order-details__detail__value'>"+parseInt(data.order_price).toLocaleString("us")+"</div></div>";
        output+="<div class='order-details__detail col-md-5'><div class='order-details__detail__label'>سهم شما از این سفارش</div><div class='order-details__detail__value'>"+Math.ceil((data.order_price*70)/100).toLocaleString("us")+"</div></div>";
        if(data.description){
            output+="<div class='order-details__detail col-md-8'><div class='order-details__detail__label'>توضیحات</div><div class='order-details__detail__value'>"+data.description+"</div></div>";
        }
        if(data.order_files){
            let files=data.order_files.split(",");
            let filesHtml="";
            files.forEach(function(file){
                filesHtml+="<a style='display:block' href='/public/uploads/order/"+file+"' download='"+file+"'>"+file+"</a>"
            })
            output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>فایل ها</div><div class='order-details__detail__value'>"+filesHtml+"</div></div>";
        }
        output+="</div>";
        $("#orderDetailsWrap").html(output);
        $("#orderDetailsModal").modal("show");
    });
    // Swal.fire({
    //     title: '<strong>HTML <u>example</u></strong>',
    //     type: 'info',
    //     html:
    //       'You can use <b>bold text</b>, ' +
    //       '<a href="//github.com">links</a> ' +
    //       'and other HTML tags',
    //     showCloseButton: true,
    //     showCancelButton: true,
    //     focusConfirm: false,
    //     confirmButtonText:
    //       '<i class="fa fa-thumbs-up"></i> Great!',
    //     confirmButtonAriaLabel: 'Thumbs up, great!',
    //     cancelButtonText:
    //       '<i class="fa fa-thumbs-down"></i>',
    //     cancelButtonAriaLabel: 'Thumbs down',
    //   })
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
            output+="<td data-label='هزینه ترجمه'>"+parseInt(orders[index].order_price).toLocaleString("us")+"</td>";
            output+="<td data-label='سهم شما'>"+Math.ceil((orders[index].order_price*70)/100).toLocaleString("us")+"</td>";
            
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