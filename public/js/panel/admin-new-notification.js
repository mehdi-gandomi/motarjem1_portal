let uploadedFiles = [];
let editor = new MediumEditor('#medium-editor');
FilePond.registerPlugin(
    // encodes the file as base64 data
    FilePondPluginFileEncode,
    // validates the size of the file
    FilePondPluginFileValidateSize
);
// Select the file input and use create() to turn it into a pond
FilePond.create(document.querySelector("#files"));
FilePond.setOptions({
    // instantUpload: false,
    server: {
        process: {
            url: "/admin/notifications/upload-attachment",
            onload: function(response) {
                uploadedFiles.push(response);
                $("#attachments").val(uploadedFiles.join(","));
                return response.key;
            },
            onerror: function(response) {
                return response.data;
            }
        }
    }
});
$("#type").on("change",function (e) {
    $("#recipients").parent().toggleClass("d-none");
});
//validates inputs that is passed to this function
function validateInputs(){
    let validationIsGood=true;
    for (let key in arguments){
        if (arguments[key].val()===""){
            arguments[key].parent().append("<p class='validation-error'>این فیلد نباید خالی باشد !</p>");
            arguments[key].addClass("not-valid");
            validationIsGood=false;
        }else{
            arguments[key].removeClass("not-valid");
            arguments[key].parent().find(".validation-error").eq(0).remove();
            validationIsGood=true;
        }
    }
    return validationIsGood;
}
$("#newNotificationForm").on("submit",function (e) {
    e.preventDefault();
    let title=$("#title");
    let body=$("#medium-editor");
    let validationIsGood=validateInputs(title,body);
    if (validationIsGood){
        $.ajax({
            type: "POST",
            url: $(this).attr("action"),
            data: $(this).serialize(),
            success: function (data, status) {
                if (data.status){
                    Swal.fire({
                        title: 'موفق !',
                        text:"اطلاعیه با موفقیت ازسال شد !",
                        type: 'success',
                        confirmButtonColor: '#3085d6',
                        showCancelButton: true,
                        cancelButtonColor: '#d33',
                        confirmButtonText: 'بازگشت به صفحه اطلاعیه ها',
                        cancelButtonText: 'باشه'
                    }).then(function(result){
                        if (result.value) {
                            window.location.href=window.location.origin+"/admin/notifications";
                        }
                    })
                }else {
                    console.log(data.message);
                    return Swal.fire(
                        'خطا !',
                        "خطایی در ارسال اطلاعیه رخ داد !",
                        'error'
                    );
                }

            }
        });
    }
});

$("#recipients").select2({
    placeholder:"لطفا یک کاربر را انتخاب کنید...",
    dir:"rtl",
    width:"100%"
});