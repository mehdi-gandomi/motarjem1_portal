function showNotificationInfo(notifId){
    $.get("/translator/notification/info.json",function(data,status){
        renderNotification(data);
    })
}

function renderNotification(notification){
    let output="<div class='order-details row'>";
    let importance;
    if(notification.notif_type=="1"){
        importance="خیلی مهم";
    }else if(notification.notif_type=="2"){
        importance="مهم";
    }else if(notification.notif_type=="3"){
        importance="معمولی";
    }
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>ردیف</div><div class='order-details__detail__value'>"+notification.notif_id+"</div></div>";
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>عنوان اطلاعیه</div><div class='order-details__detail__value'>"+notification.title+"</div></div>";
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>تاریخ ارسال اطلاعیه</div><div class='order-details__detail__value'>"+notification.sent_date_persian+"</div></div>";
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>درجه اهمیت</div><div class='order-details__detail__value'>"+importance+"</div></div>";
    output+="<div class='order-details__detail col-md-3'><div class='order-details__detail__label'>متن اطلاعیه</div><div class='order-details__detail__value'>"+notification.body+"</div></div>";
    if(notification.attach_files){
        let files=notification.attach_files.split(",");
        let filesHtml="";
        files.forEach(function(file){
            filesHtml+="<a style='display:block' href='/public/uploads/notifications/"+file+"' download='"+file+"'>"+file+"</a>"
        })
        output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>فایل (های) پیوست</div><div class='order-details__detail__value'>"+filesHtml+"</div></div>";
    }
    output+="</div>";
    $("#NotificationInfoWrap").html(output);
    $("#NotificationInfoModal").modal("show");
}
