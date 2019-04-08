//show full translator profile details
function showُTranslatorInfo(translatorId){    
    $.get("/admin/translator-info/all/json",{translator_id:translatorId},function(data,status){
        //i coded this with template literal but because of low browser support , i converted the code
        //for debugging you have to convert it to es6 with babel.io
        //unfortunately it converted persian to utf :( you have to convert it to text
        //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
        var output = "\n        <div class=\"row \">\n    <div class=\"col-lg-4 translator-avatar\">\n        <img src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\" alt=\"\">\n    </div>\n    <div class=\"col-lg-3\">\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0646\u0627\u0645 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.fname, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0646\u0627\u0645 \u062E\u0627\u0646\u0648\u0627\u062F\u06AF\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.lname, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062C\u0646\u0633\u06CC\u062A : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.sex === '1' ? 'مرد' : 'زن', "</span>\n        </p>\n    </div>\n    <div class=\"col-lg-5\">\n        <strong>\u0622\u062F\u0631\u0633</strong>\n        <div class=\"translator-address mt-2\">\n            ").concat(data.info.address, "\n        </div>\n    </div>\n</div>\n<hr>\n<div class=\"row mt-5\">\n    <div class=\"col-lg-4 translator-melicard\">\n        <img src=\"/public/uploads/translator/melicard/").concat(data.info.melicard_photo, "\" alt=\"\">\n    </div>\n    <div class=\"col-lg-4\">\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u06A9\u062F \u0645\u0644\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.meli_code, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0645\u062F\u0631\u06A9 \u062A\u062D\u0635\u06CC\u0644\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.degree, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0633\u0627\u0628\u0642\u0647 \u06A9\u0627\u0631 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.exp_years, " \u0633\u0627\u0644</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062A\u0627\u0631\u06CC\u062E \u062B\u0628\u062A \u0646\u0627\u0645 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.register_date_persian, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062A\u0631\u062C\u0645\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC \u0628\u0647 \u0641\u0627\u0631\u0633\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.en_to_fa == '0' ? 'خیر' : 'بله', "</span>\n        </p>\n    </div>\n    <div class=\"col-lg-4\">\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.phone, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0634\u0645\u0627\u0631\u0647 \u0647\u0645\u0631\u0627\u0647 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.cell_phone, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0627\u06CC\u0645\u06CC\u0644 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.email, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062A\u0631\u062C\u0645\u0647 \u0641\u0627\u0631\u0633\u06CC \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.fa_to_en == '0' ? 'خیر' : 'بله', "</span>\n        </p>\n\n    </div>\n</div>\n<hr style=\"border-width:2px\">\n<div class=\"row mt-5\">\n    <div class=\"col-12 mb-4\">\n        <h5 class=\"text-center\">\u0622\u0632\u0645\u0648\u0646 ").concat(data.info.study_field_title, "</h5>\n        <h6 class=\"text-center\">(").concat(data.info.language_id == '1' ? 'انگلیسی به فارسی' : 'فارسی به انگلیسی', ")</h6>\n    </div>\n    <div class=\"col-lg-6\"><h6>متن اصلی</h6>\n        <div class=\"test question ").concat(data.info.language_id == "1" ? 'ltr' : 'rtl', "\">\n            ").concat(data.info.question_text, "\n        </div>\n    </div>\n    <div class=\"col-lg-6\"><h6>ترجمه کاربر</h6>\n        <div class=\"test answer ").concat(data.info.language_id == "1" ? 'rtl' : 'ltr', "\">\n            ").concat(data.info.translated_text, "\n        </div>\n    </div>\n    <div class=\"col-12\">\n        <div class=\"more-info__actions d-flex justify-content-center\">\n            <button class=\"btn btn-success\" onclick=\"employTranslator('").concat(data.info.translator_id, "')\">\u0627\u0633\u062A\u062E\u062F\u0627\u0645 \u0645\u062A\u0631\u062C\u0645</button>\n            <button class=\"btn btn-danger\" onclick=\"denyTranslator('").concat(data.info.translator_id, "')\">\u0631\u062F \u0645\u062A\u0631\u062C\u0645</button>\n            <button class=\"btn btn-secondary\" data-dismiss=\"modal\" type=\"button\">\u0628\u0633\u062A\u0646</button>\n        </div>\n    </div>\n</div>\n        \n        ");
        $("#translatorInfoWrap").html(output);
        $("#translatorInfo").modal({backdrop: 'static', keyboard: false});
    });
    
}

// show translator's basic info
function showTranslatorBasicInfo(translatorId){
    console.log(translatorId);
    $.get("/admin/translator/basic-info/json",{translator_id:translatorId},function(data,status){
        if(data.status){
             //i coded this with template literal but because of low browser support , i converted the code
            //for debugging you have to convert it to es6 with babel.io
            //unfortunately it converted persian to utf :( you have to convert it to text
            //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
            var output = "\n  <div class=\"translator-info\">\n    <div class=\"translator-info__avatar\">\n        <img alt=\"\" src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\"></div>\n    <div class=\"translator-info__info\">\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0646\u0627\u0645 \u0645\u062A\u0631\u062C\u0645 :\u200C\n            </label>\n            <strong>").concat(data.info.fname + " " + data.info.lname, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0645\u062F\u0631\u06A9 \u062A\u062D\u0635\u06CC\u0644\u06CC</label>\n            <strong>").concat(data.info.degree, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0641\u0627\u0631\u0633\u06CC \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC</label>\n            <strong>").concat(data.info.fa_to_en == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC \u0628\u0647 \u0641\u0627\u0631\u0633\u06CC</label>\n            <strong>").concat(data.info.en_to_fa == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n            </label>\n            <strong>").concat(data.info.email, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A</label>\n            <strong>").concat(data.info.phone, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n            <strong>").concat(data.info.cell_phone, "</strong>\n        </div>\n    </div>\n</div>        \n");
            $("#translatorBasicInfoWrap").html(output);
            $("#translatorBasicInfo").modal("show");
        }
    })
}
//render ticket's info on modal
function renderTicketInfo(info){
    var state = "";
    switch (info.state) {
    case "read":
        state = "خوانده شده";
        break;
    case "unread":
        state = "خوانده نشده";
        break;
    case "waiting":
        state = "درانتظار پاسخ";
        break;
    case "answered":
        state = "پاسخ داده شده";
        break;
    }
    //i coded this with template literal but because of low browser support , i converted the code
    //for debugging you have to convert it to es6 with babel.io
    //unfortunately it converted persian to utf :( you have to convert it to text
    return "<div class='col-lg-4'><ul class=\"list-group no-pad ticket-details\">\n   <li class=\"list-group-item\">\n      <div class=\"list-group-item__title\">\u0634\u0645\u0627\u0631\u0647 \u062A\u06CC\u06A9\u062A:</div>\n      <div class=\"list-group-item__value\">#".concat(info.ticket_number, "</div>\n   </li>\n   <li class=\"list-group-item\">\n      <div class=\"list-group-item__title\">\u0639\u0646\u0648\u0627\u0646 \u062A\u06CC\u06A9\u062A:</div>\n      <div class=\"list-group-item__value\">\n\t\t").concat(info.subject, "\n      </div>\n   </li>\n   <li class=\"list-group-item\">\n      <div class=\"list-group-item__title\">\u0648\u0636\u0639\u06CC\u062A:</div>\n      <div class=\"list-group-item__value\" id=\"state\">\n         ").concat(state, "\n      </div>\n   </li>\n  <li class=\"list-group-item\">\n      <div class=\"list-group-item__title\">کاربر ارسال کننده:</div>\n      <div class=\"list-group-item__value\" >"+info.creator_fname+" "+info.creator_lname+"</div></li> <li class=\"list-group-item\">\n      <div class=\"list-group-item__title\">\u062A\u0627\u0631\u06CC\u062E \u0627\u06CC\u062C\u0627\u062F \u062A\u06CC\u06A9\u062A:</div>\n      <div class=\"list-group-item__value\">\n         ").concat(info.create_date_persian, "\n      </div>\n   </li>\n   <li class=\"list-group-item\">\n      <div class=\"list-group-item__title\">\u0622\u062E\u0631\u06CC\u0646 \u0628\u0631\u0648\u0632\u0631\u0633\u0627\u0646\u06CC:</div>\n      <div class=\"list-group-item__value\" id=\"updateDate\">\n         ").concat(info.update_date_persian, "\n      </div>\n   </li>\n<li class='list-group-item'><a class='btn btn-primary' href='/admin/ticket/view/"+info.ticket_number+"'>اطلاعات بیشتر</a></li></ul></div>");
}
//render all ticket messages in modal
function renderTicketMessages(messages,fullname,ticketNumber,parentTicketId){ 
    var output = "<div class='col-lg-8'>";
    //i coded this with template literal but because of low browser support , i converted the code
    //for debugging you have to convert it to es6 with babel.io
    //unfortunately it converted persian to utf :( you have to convert it to text
    output += "\n<form action=\"/admin/ticket/reply\" id=\"replyMessageForm\" method=\"POST\">\n<input type=\"hidden\" name=\"ticket_number\" value=\"".concat(ticketNumber, "\">\n <input type=\"hidden\" name=\"parent_ticket_id\" value='"+parentTicketId+"' > \n  <div class=\"form-group\">\n      <label for=\"\">متن پاسخ</label>\n      <textarea cols=\"20\" class='form-control' name=\"body\" rows=\"7\"></textarea>\n  </div>\n  <input class=\"btn btn-primary\" type=\"submit\" value=\"ارسال پاسخ\">\n</form>\n");
    messages.forEach(function (message) {
        if (message.sender_id == "0") {
            output += "\n            <div class=\"card ticket  is--answer mt-4\">\n               <div class=\"card-header\">\n                  <div class=\"card-header__title\">\n                     <i class=\"icon-user\"></i>\n                     <p>\n                      \u0627\u062F\u0645\u06CC\u0646\n                     </p>\n                  </div>\n            ";
        } else {
            output += "\n          <div class=\"card ticket  is--message mt-4\">\n             <div class=\"card-header bg-primary\">\n                <div class=\"card-header__title\">\n                   <i class=\"icon-user\"></i>\n                   <p>\n                      ".concat(fullname, "\n                   </p>\n          </div>");
        }
        output += "\n      <div class=\"card-header__date\">\n   \t\t".concat(message.sent_date_persian, "\n\t</div>\n</div>\n<div class=\"card-body\">\n  ").concat("\n   <div class=\"msg-body\">\n      ").concat(message.body, "\n   </div>\n</div>\n</div>\n");
    });
    output+="</div>";
    return output;
}
//showing ticket information by ticket number
function showTicketInfo(ticketNumber,userType){
    $.get("/admin/ticket-details/json",{ticket_number:ticketNumber,user_type:userType},function(data,status){
        if(data.status){
            let output="<div class='row'>";            
            output+=renderTicketMessages(data.messages,data.info.creator_fname+" "+data.info.creator_lname,ticketNumber,data.last_ticket_id);
            output+=renderTicketInfo(data.info);
            output+="</div>";
            $("#ticketDetailsWrap").html(output);
            // new MediumEditor('#medium-editor', {
            //     elementsContainer: document.getElementById('ticketDetailsWrap') // use your modal element here
            // });
            $("#ticketDetailsModal").modal("show");
        }
    });
}

//employment process for translator
function employTranslator(translatorId){

    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا واقعا می خواهید این مترجم را استخدام کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText:'نه'
      }).then(function(result) {
        if (result.value) {
            $.ajax({
                type:"POST",
                url:"/admin/translator/employ",
                data:{
                    translator_id:translatorId,
                    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success:function(data,status){
                    if (data.status) {
                        $("#translatorInfo").modal("hide");
                        Swal.fire({
                            title: 'موفق !',
                            text: "مترجم با موفقیت استخدام شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function(result){
                            if (result.value) {
                            window.location.reload();
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در استخدام مترجم رخ داد !", 'error')
                    }
                }
            })             
        }else{

        }
      })



}

//deny employment request from translator
function denyTranslator(translatorId){



    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا واقعا می خواهید این مترجم را رد کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText:'نه'
      }).then(function(result) {
        if (result.value) {
            $.ajax({
                type:"POST",
                url:"/admin/translator/deny",
                data:{
                    translator_id:translatorId,
                    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success:function(data,status){
                    if (data.status) {
                        $("#translatorInfo").modal("hide");
                        Swal.fire({
                            title: 'موفق !',
                            text: "مترجم رد شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function(result){
                            if (result.value) {
                            window.location.reload();
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در رد مترجم رخ داد !", 'error')
                    }
                }
            })   
        }else{

        }
      })

    
}


//accept translator's request to do the order
function acceptRequest(requestId,translatorId){
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا میخواهید این درخواست را قبول کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText:'نه'
      }).then(function(result) {
        if (result.value) {
            $.ajax({
                type:"POST",
                url:"/admin/translator-order-request/accept",
                data:{
                    request_id:requestId,
                    translator_id:translatorId,
                    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success:function(data,status){
                    if (data.status) {
                        Swal.fire({
                            title: 'موفق !',
                            text: "اطلاعات با موفقیت ثبت شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function(result){
                            if (result.value) {
                            window.location.reload();
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در ثبت اطلاعات رخ داد !", 'error')
                    }
                }
            })             
        }else{

        }
      })

}


//deny translator's request to do the order
function denyRequest(requestId){
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا میخواهید این درخواست را رد کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText:'نه'
      }).then(function(result) {
        if (result.value) {
            $.ajax({
                type:"POST",
                url:"/admin/translator-order-request/deny",
                data:{
                    request_id:requestId,
                    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success:function(data,status){
                    if (data.status) {
                        Swal.fire({
                            title: 'موفق !',
                            text: "اطلاعات با موفقیت ثبت شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function(result){
                            if (result.value) {
                                window.location.reload();
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در ثبت اطلاعات رخ داد !", 'error')
                    }
                }
            })             
        }else{

        }
      })
}
//show order info in modal
function showOrderInfo(orderNumber){
    $.get("/admin/order/info/json/"+orderNumber,function(data,status){
        console.log(data);
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
}

//add listener for ticket reply form 
$(document).on("submit","#replyMessageForm",function(e){
    e.preventDefault();
    console.log($(this).serialize());
    $.ajax({
        type:"POST",
        url:"/admin/ticket/reply",
        data:$(this).serialize(),
        success:function(data,status){
            if (data.status) {
                $("#ticketDetailsModal").modal("hide");
                Swal.fire({
                    title: 'موفق !',
                    text: "پاسخ شما با موفقیت ارسال شد !",
                    type: 'success',
                    confirmButtonColor: '#3085d6',
                    cancelButtonColor: '#d33',
                    confirmButtonText: 'باشه'
                })
            } else {
                Swal.fire('خطا !',data.message, 'error')
            }
        }
    })   
})


//handle lightbox
//show the translator avatar in large mode
// Get the modal
function handleImgClick(e){
    modal.style.display = "block";
    let image=e.currentTarget.src;
    modalImg.src=image;
    captionText.innerHTML = e.currentTarget.getAttribute("aria-label");
}
let modal = document.getElementById('avatarModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var images= document.querySelectorAll('.translatorAvatar');
var modalImg = document.getElementById("avatarPhoto");
var captionText = document.getElementById("caption");
images.forEach(function(img){
    img.addEventListener("click",handleImgClick);
})
// Get the <span> element that closes the modal
var span = document.getElementById("closeModal");
// When the user clicks on <span> (x), close the modal
span.addEventListener("click",function(e){
    modal.style.display = "none";
})
