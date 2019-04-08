
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
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>قیمت کل سفارش</div><div class='order-details__detail__value'>"+parseInt(data.order_price).toLocaleString("us")+"</div></div>";
        output+="<div class='order-details__detail col-md-5'><div class='order-details__detail__label'>سهم شما از این سفارش</div><div class='order-details__detail__value'>"+Math.ceil((data.order_price*15)/100).toLocaleString("us")+"</div></div>";
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
