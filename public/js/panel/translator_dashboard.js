let editor = new MediumEditor("#medium-editor", {
    elementsContainer: document.getElementById("newMessageModal") // use your modal element here
});
//send message with ajax request
$("#send-message-btn").click(function (e) {

    let subject = $("#subject").val();
    let body = $("#medium-editor").val();
    if (subject == "") {
        alert("باید حداقل یک عنوان وارد نمایید!");
        return;
    }
    if (body == "") {
        alert("باید متن پیام تان را وارد نمایید !");
        return;
    }

    $.ajax({
        type: "POST",
        url: $("#sendMessageForm").attr("action"),
        data: {
            subject: subject,
            body: body
        },
        success: function (data, status) {
            if (status && data.status) {
                $("#newMessageModal").modal("hide");
                Swal.fire("موفقیت آمیز !", "پیام شما با موفقیت ارسال شد !", "success");
            }
        }
    });
});
$("#testFilterForm").submit(function (e) {
    e.preventDefault();
    let language = $("#language").val();
    let fieldOfStudy = $("#study_field").val();
    $.get("/translator/test/filter", {
        "language": language,
        "study_field": fieldOfStudy
    }, function (data, status) {
        $(".test-section").html("");
        $(".test-logs").html("");
        if (data.status) {
            if (language == '1') {
                $(".test-section").addClass("ltr");
            } else {
                $(".test-section").removeClass("ltr");
            }
            let output = "<div class='col-12'><h4 class='text-center mt-4 mb-4'>" + data.title + "</h4></div>" + "<div class='col-md-6'>";
            output += "<div class='question'>" + data.text + "</div></div>";
            output += "<div class='col-md-6 answer'><form id='testForm' method='post' action='/translator/test/send'><input type='hidden' id='test_id' value='" + data.test_id + "' /><div class='form-group'><textarea class='form-control' cols='10' rows='20' id='answer' name='answer'></textarea></div><div class='form-group'><input type='submit' value='ارسال پاسخ' class='btn btn-success' /></div></form></div>";
            $(".test-section").html(output);
        } else {
            $(".test-logs").html("<p style='color: #ff3c3c;font-size: 1rem;'>نتیجه ای یافت نشد !</p>");
        }
    })
})
$(document).on("submit", "#testForm", function (e) {
    e.preventDefault();
    $.ajax({
        type: "POST",
        url: e.target.getAttribute("action"),
        data: {
            translated_text: $("#answer").val(),
            test_id: $("#test_id").val()
        },
        success: function (data) {
            if (data.status) {
                $(".test-section").html("");
                Swal.fire('موفق !', 'پاسخ شما با موفقیت ارسال شد !', 'success')
            } else {
                Swal.fire('خطا !', data.error, 'error')
            }
        }
    })
})
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
function acceptOrder(orderNumber,translatorId){
    
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
                    order_number:orderNumber,
                    translator_id:translatorId
                },
                success:function(data,status){
                    
                    if(data.status){
                        Swal.fire(
                            'موفق !',
                            'درخواست شما با موفقیت ثبت شد !',
                            'success'
                          )
                          $.get("/translator/new-orders/json",{page:1,offset:3},function(data,status){
                                if(data.status){
                                    renderOrders(data.orders,translatorId,"#newOrdersWrap");
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

function declineOrder(orderNumber,translatorId){
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
                    order_number:orderNumber,
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
                          $.get("/translator/new-orders/json",{page:1,offset:3},function(data,status){
                              if(data.status){
                                renderOrders(data.orders,translatorId,"#newOrdersWrap");
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

function renderOrders(orders,translatorId,el){
    console.log(orders);
    let output="";
    $(".newOrderTable thead").css("display","table-header-group");
    $(".newOrderTable tbody").css("display","table-row-group");
    for(let index in orders){
        let translationLang=orders[index].translation_lang == "1"? "انگلیسی به فارسی": "فارسی به انگلیسی";
        let translationQuality=orders[index].translation_quality == "5" ? "نقره ای" : "طلایی";
        output+="<tr>";
            output+="<td data-label='شماره سفارش'>"+orders[index].order_number+"</td>";
            output+="<td data-label='تعداد صفحات'>"+Math.ceil(orders[index].word_numbers / 250)+"</td>";
            output+="<td data-label='زبان ترجمه'>"+translationLang+"</td>";
            output+="<td data-label='رشته'>"+orders[index].study_field+"</td>";
            output+="<td data-label='کیفیت ترجمه'>"+translationQuality+"</td>";
            output+="<td data-label='هزینه ترجمه'>"+orders[index].order_price+"</td>";
            output+="<td data-label='سهم شما'>"+Math.ceil((orders[index].order_price*70)/100)+"</td>";
            output+="<td data-label='عملیات' class='order-actions'><button onclick='showOrderInfo(\""+orders[index].order_number+"\")' class='expand-button order-action is--primary is--medium'><span data-hover='جزییات سفارش'><i class='icon-info'></i></span></button>";
            output+="<button onclick='acceptOrder(\""+orders[index].order_number+"\",\""+translatorId+"\")' class='expand-button order-action is--success is--large'><span data-hover='درخواست انجام سفارش'><i class='icon-check'></i></span></button>";
            output+="<button onclick='declineOrder(\""+orders[index].order_number+"\",\""+translatorId+"\")' class='expand-button order-action is--danger'><span data-hover='رد سفارش'><i class='icon-check'></i></span></button>";
            output+="</td>";
        output+="</tr>";
    }

    if(output){
        $(el).html(output);
    }else{
        $(".newOrderTable thead").css("display","none");
        $(".newOrderTable tbody").css("display","none");
        $(".newOrderTable").html("<h5 class='text-center'>پروژه جدیدی یافت نشد !</h5>");
    }
    
}

//request checkout functions
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