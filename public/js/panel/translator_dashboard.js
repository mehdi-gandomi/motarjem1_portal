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
function showOrderInfo(order_id){
    console.log(order_id);
}