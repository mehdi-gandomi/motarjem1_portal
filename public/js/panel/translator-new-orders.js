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
function quertStringToObj(queryString) {
  if (queryString == "?" || queryString == "") return {};
  return JSON.parse(
    '{"' + queryString.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
    function(key, value) {
      return key === "" ? value : decodeURIComponent(value);
    }
  );
}

$("input[name=choice]").on("change", function(e) {
  const value=e.target.value;
  const qsObject=quertStringToObj(window.location.search.replace("?",""));
  qsObject['choice']=value;
  let pageTitle;
  if(value=="new"){
    pageTitle="سفارشات جدید";
  }
  else if(value=="requested"){
    pageTitle="سفارشات درخواستی";
  }
  else{
    pageTitle="سفارشات رد شده";
  }
  $(".table-title h4").html(pageTitle);
  const url=window.location.origin+window.location.pathname+"?"+serializeQSObject(qsObject);
  getOrders(window.location.origin+window.location.pathname,qsObject);
  window.history.pushState(value+" orders",pageTitle,url);
});

function getOrders(baseUrl,qsObject){
    $.get(baseUrl+"/json",qsObject,function(data,status){
        if(data.status){
            renderOrders(data.orders,data.translator_id,data.choice);
            showPagination(data.orders_count,parseInt(data.current_page),10,window.location.origin+window.location.pathname, serializeQSObject(qsObject),3);
        }
    })
}



function showOrderInfo(orderId){
  $.get("/translator/order/info/"+orderId,function(data,status){
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
      output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>شماره سفارش</div><div class='order-details__detail__value'>"+data.order_id+"</div></div>";
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
      output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>قیمت کل سفارش</div><div class='order-details__detail__value'>"+data.order_price+"</div></div>";
      output+="<div class='order-details__detail col-md-5'><div class='order-details__detail__label'>سهم شما از این سفارش</div><div class='order-details__detail__value'>"+Math.ceil((data.order_price*70)/100)+"</div></div>";
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
//accept order process
function acceptOrder(orderId,translatorId){
    
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "می خواهید این ترجمه را انجام بدهید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText:'نه'
      }).then((result) => {
        if (result.value) {
            $.ajax({
                type:"POST",
                url:"/translator/order/request",
                data:{
                    order_id:orderId,
                    translator_id:translatorId
                },
                success:function(data,status){
                    
                    if(data.status){
                        Swal.fire(
                            'موفق !',
                            'درخواست شما با موفقیت ثبت شد !',
                            'success'
                          )
                          const page=getQueryString("page") ? parseInt(getQueryString("page")) : 1;
                          $.get("/translator/new-orders/json",{page:page,offset:10},function(data,status){
                              if(data.status){
                                renderOrders(data.orders,translatorId,"new");
                                const queryString=getQueryString("choice") ? "choice="+getQueryString("choice") : "";
                                showPagination(data.orders_count,parseInt(data.current_page),10,window.location.origin+window.location.pathname,queryString,3);
                              }
                              
                          })
                    }else{
                        Swal.fire(
                            'موفق !',
                            'خطایی در ثبت اطلاعات رخ داد !',
                            'error'
                          )
                    }
                }
            })      
        }else{

        }
      })
    
}

function declineOrder(orderId,translatorId){
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا می خواهید این ترجمه را رد کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText:'نه'
      }).then((result) => {
        if (result.value) {
            $.ajax({
                type:"POST",
                url:"/translator/order/decline",
                data:{
                    order_id:orderId,
                    translator_id:translatorId
                },
                success:function(data,status){
                    if(data.status){
                        console.log("success");
                        Swal.fire(
                            'موفق !',
                            'درخواست شما با موفقیت انجام شد !',
                            'success'
                          )
                          const page=getQueryString("page") ? parseInt(getQueryString("page")) : 1;
                          $.get("/translator/new-orders/json",{page:page,offset:10},function(data,status){
                              if(data.status){
                                renderOrders(data.orders,translatorId,"new");
                                const queryString=getQueryString("choice") ? "choice="+getQueryString("choice") : "";
                                showPagination(data.orders_count,parseInt(data.current_page),10,window.location.origin+window.location.pathname,queryString,3);
                              }
                              
                          })
                    }else{
                        Swal.fire(
                            'موفق !',
                            'خطایی در ثبت اطلاعات رخ داد !',
                            'error'
                          )
                    }
                }
            })      
        }else{

        }
      })
    
}


function renderOrders(orders,translatorId,choice){
    console.log(orders);
    let output="";
    $(".newOrderTable thead").css("display","table-header-group");
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
            output+="<td data-label='عملیات' class='order-actions'>";
            if(choice=="new"){
                output+="<button onclick='showOrderInfo(\""+orders[index].order_id+"\")' class='expand-button order-action is--primary is--medium'><span data-hover='جزییات سفارش'><i class='icon-info'></i></span></button>";
                output+="<button onclick='acceptOrder(\""+orders[index].order_id+"\",\""+translatorId+"\")' class='expand-button order-action is--success is--large'><span data-hover='درخواست انجام سفارش'><i class='icon-check'></i></span></button>";
                output+="<button onclick='declineOrder(\""+orders[index].order_id+"\",\""+translatorId+"\")' class='expand-button order-action is--danger'><span data-hover='رد سفارش'><i class='icon-check'></i></span></button>";
            }else if (choice=="requested") {
                output+="<button onclick='showOrderInfo(\""+orders[index].order_id+"\")' class='btn btn-primary'><i class='icon-info' style='margin-left:0.5rem'></i><span>جزییات سفارش</span></button>";
            }else{
                output+="<button onclick='showOrderInfo(\""+orders[index].order_id+"\")' class='btn btn-primary'><i class='icon-info' style='margin-left:0.5rem'></i><span>جزییات سفارش</span></button>";
            }
            output+="</td>";
        output+="</tr>";
    }

    if(output){
        $("h5.no-data").remove();
        $("#newOrdersWrap").html(output);
    }else{
        $(".newOrderTable thead").css("display","none");
        $(".newOrderTable tbody").html("<h5 class='text-center mt-4'>اطلاعاتی دریافت نشد !</h5>");
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
  