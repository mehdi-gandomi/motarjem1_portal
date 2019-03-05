function getQueryString(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
      results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return "";
    return decodeURIComponent(results[2].replace(/\+/g, " "));
  }
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
        output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>قیمت کل سفارش</div><div class='order-details__detail__value'>"+parseInt(data.order_price).toLocaleString("us")+"</div></div>";
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
  $("#requestCheckoutForm").on("submit",function(e){
    e.preventDefault();
    if($("#amount").val()=="") {
        return Swal.fire(
            'خطا !',
            "فیلد مبلغ نباید خالی باشد !",
            'error'
          );
    }
    $.ajax({
        type:"POST",
        url:$(this).attr("action"),
        data:$(this).serialize(),
        success:function(data,status){
            if (!status) {
                Swal.fire(
                    'خطا !',
                    "خطایی رخ داد لطفا صفحه را رفرش کنید !",
                    'error'
                  );
            }
            if(status && data.status){
                Swal.fire(
                    'موفق !',
                    'درخواست شما با موفقیت ارسال شد !',
                    'success'
                  );
                  queryString={};
                  page=getQueryString("checkout_request_page");
                  if(page) queryString['page']=page;
                  $.get("/translator/account-report/checkout-requests/json",queryString,function(data,status){
                    renderCheckoutRequests(data.requests);
                    showPagination(data.count,data.current_page,10,window.location.origin+window.location.pathname, "",3,".checkout-requests-wrap .pagination","checkout_request_");
                  })
                  $("#requestCheckoutModal").modal("hide");
                  $("#amount").val("");
                  
            }else{
                Swal.fire(
                    'خطا !',
                    data.message,
                    'error'
                  );
            }
            
        }
    });
    
  })
  $("#amount").on("input",function(e){
    let amount=$(this).val();
    amount=amount.replace(/\,/g,"");
    amount=parseInt(amount);
    if(isNaN(amount)) {
        $(this).val("");
        return;
    }
    $(this).val(amount.toLocaleString("us"));
  })
  function renderCheckoutRequests(requests){
    let output="";
    requests.forEach(function(request){
        output+="<tr>";
            output+="<td data-label='ردیف'>"+request.id+"</td>";
            output+="<td data-label='مبلغ درخواستی'>"+request.amount+"</td>";
            output+="<td data-label='تاریخ درخواست'>"+request.request_date_persian+"</td>";
        output+="<tr>";
    });
    $("#user-checkout-requests").html(output);
  }

  //this function shows pagination
function showPagination(count,current_page,offset,baseUrl, queryString,visibleNumbers,el,prefix) {
    let output="";
    let fullUrl;
    if(el===undefined) el=".pagination";
    if(prefix === undefined) prefix="";
    if(queryString){
        fullUrl=baseUrl+"?"+queryString+"&"+prefix+"page=%page%";
    }else{
        fullUrl=baseUrl+"?"+prefix+"page=%page%";
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
    $(el).html(output);
}
  