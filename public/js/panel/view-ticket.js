let editor = new MediumEditor('#medium-editor', {
    elementsContainer: document.getElementById('replyModal') // use your modal element here
});
// send message with ajax request
$("#replyMessageForm").on("submit", function (e) {
    e.preventDefault();
    let body = $("#medium-editor").val();
    if (body == "") {
        alert("باید متن پیام تان را وارد نمایید !");
        return;
    }
    $.ajax({
        type: "POST",
        url: $(this).attr("action"),
        data: $(this).serialize(),
        success: function (data, status) {
            if (status && data.status) {
                $("#replyModal").modal("hide");
                Swal.fire('موفقیت آمیز !', 'پیام شما با موفقیت ارسال شد !', 'success')
                $(".answer-card .card-body").toggleClass("show");
                $.get(window.location.href + "/json", function (data) {
                    if (data.status) {
                        renderTickets(data.tickets);
                        $("#updateDate").html(data.date);
                        $("#state").html("در انتظار پاسخ");
                    }
                })
            }
        }
    })
});
$(".answer-card .card-header").on("click", function (e) {
    $(".answer-card .card-body").toggleClass("show");
})
function renderTickets(tickets) {
    let output = "<div class='card'><div class='card-body'><h5 class='text-center'>برای پیام قبلی شما هنوز پاسخی ارسال نشده است !</h5></div></div>";
    tickets.forEach(function (ticket) {
        output += ticket.sender_id == "0" ? "<div class='card card-default ticket is--answer'><div class='card-header'>" : "<div class='card card-default ticket is--message'><div class='card-header bg-primary'>";
        output += "<div class='card-header__title'><i class='icon-user'></i>";
        output += ticket.sender_id == "0" ? "<p>ادمین سایت</p>" : "<p>" + $(".profile-avatar__text p").text() + "(کاربر)</p>";
        output += "</div><div class='card-header__date'>" + ticket.sent_date_persian + "</div></div>";
        output += "<div class='card-body'><div class='msg-body'>" + ticket.body + "</div></div></div>";
    });
    $(".chat-section").html(output);
}