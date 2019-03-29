// show translator's basic info
function showTranslatorBasicInfo(translatorId){
    console.log(translatorId);
    $.get("/admin/translator/basic-info/json",{translator_id:translatorId},function(data,status){
        if(data.status){
            //i coded this with template literal but because of low browser support , i converted the code
            //for debugging you have to convert it to es6 with babel.io
            //unfortunately it converted persian to utf :( you have to convert it to text
            //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
            var output = "\n  <div class=\"translator-info no-border\">\n    <div class=\"translator-info__avatar\">\n        <img alt=\"\" src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\"></div>\n    <div class=\"translator-info__info\">\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0646\u0627\u0645 \u0645\u062A\u0631\u062C\u0645 :\u200C\n            </label>\n            <strong>").concat(data.info.fname + " " + data.info.lname, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0645\u062F\u0631\u06A9 \u062A\u062D\u0635\u06CC\u0644\u06CC</label>\n            <strong>").concat(data.info.degree, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0641\u0627\u0631\u0633\u06CC \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC</label>\n            <strong>").concat(data.info.fa_to_en == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC \u0628\u0647 \u0641\u0627\u0631\u0633\u06CC</label>\n            <strong>").concat(data.info.en_to_fa == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n            </label>\n            <strong>").concat(data.info.email, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A</label>\n            <strong>").concat(data.info.phone, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n            <strong>").concat(data.info.cell_phone, "</strong>\n        </div>\n    </div>\n</div>        \n");
            $("#translatorBasicInfoWrap").html(output);
            $("#translatorBasicInfo").modal("show");
        }
    })
}

function showNotificationRecipients(notifId) {
    console.log(notifId);
}

function renderNotification(notification){
    let output="<div class='order-details row'>";
    let importance;
    if(notification.importance=="1"){
        importance="خیلی مهم";
    }else if(notification.importance=="2"){
        importance="مهم";
    }else if(notification.importance=="3"){
        importance="معمولی";
    }
    output+="<div class='order-details__detail col-md-2'><div class='order-details__detail__label'>ردیف</div><div class='order-details__detail__value'>"+notification.notif_id+"</div></div>";
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>عنوان اطلاعیه</div><div class='order-details__detail__value'>"+notification.title+"</div></div>";
    output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>تاریخ ارسال اطلاعیه</div><div class='order-details__detail__value'>"+notification.sent_date_persian+"</div></div>";
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>درجه اهمیت</div><div class='order-details__detail__value'>"+importance+"</div></div>";
    output+="<div class='order-details__detail col-md-12'><div class='order-details__detail__label'>متن اطلاعیه</div><div class='order-details__detail__value'>"+notification.body+"</div></div>";
    if(notification.attach_files){
        let files=notification.attach_files.split(",");
        let filesHtml="";
        files.forEach(function(file){
            filesHtml+="<a style='display:block' href='/public/uploads/notifications/"+file+"' download='"+file+"'>"+file+"</a>"
        })
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>فایل (های) پیوست</div><div class='order-details__detail__value'>"+filesHtml+"</div></div>";
    }
    if (notification.notif_type == "1"){
        output+="<div class='order-details__detail col-md-12'><div class='order-details__detail__label'>دریافت کننده (ها)</div><div class='order-details__detail__value'>";
        notification.translator_names.forEach(function (name,index) {
            output+="<a href=\"javascript:void(0)\" onclick=\"showTranslatorBasicInfo('"+notification.translator_ids[index]+"')\">"+name+"</a> &nbsp;";
        });
        output+="</div></div>";
    }
    output+="</div>";
    $("#NotificationInfoWrap").html(output);
    $("#NotificationInfoModal").modal("show");
}

function showNotificationInfo(notifId,isPrivate){
    isPrivate=isPrivate===undefined ? false:isPrivate;
    if (isPrivate){
        $.get("/admin/notification/private/info",{notif_id:notifId},function (data) {
            if (data.status){
                renderNotification(data.info);
            } else{
                console.log(data.message);
            }
        })
    } else{
        $.get("/admin/notification/public/info",{notif_id:notifId},function (data) {
            if (data.status){
                renderNotification(data.info);
            } else{
                console.log(data.message);
            }
        })
    }
}

function deleteNotification(notifId) {
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا می خواهید این اطلاعیه را حذف کنید ؟",
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
                url:"/admin/notification/delete",
                data:{
                    notif_id:notifId,
                },
                success:function(data,status){
                    if(data.status){
                        Swal.fire({
                            title: 'موفق !',
                            text: "اطلاعیه با موفقیت حذف شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function(result){
                            if (result.value) {
                                window.location.reload();
                            }
                        })
                    }else{
                        console.log(data.message);
                        Swal.fire(
                            'خطا !',
                            'خطایی در حذف اطلاعیه رخ داد !',
                            'error'
                        )
                    }
                }
            })
        }else{

        }
    })


}