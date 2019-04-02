let uploadedFiles = [];
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
                        text:"اطلاعیه با موفقیت ویرایش شد !",
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
                        "خطایی در ویرایش اطلاعیه رخ داد !",
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

$(document).ready(function (e) {
    let editor = new MediumEditor('#medium-editor');
    FilePond.registerPlugin(
        // encodes the file as base64 data
        FilePondPluginFileEncode,
        // validates the size of the file
        FilePondPluginFileValidateSize
    );
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
            },
            revert:{
                url:"/admin/notifications/upload-attachment",
                onload:function (response) {
                    console.log(response);
                    uploadedFiles=uploadedFiles.filter(function (file) {
                        return file != response;
                    });
                    $("#attachments").val(uploadedFiles.join(","));
                },
                onerror:function (res) {
                    console.log(res);
                }
            }
        }
    });
    let uploadedFiles=$("#attachments").val()=="" ? []:$("#attachments").val().split(",");
       if (uploadedFiles.length>0){
           initialFiles=[];
           uploadedFiles.forEach(function (file) {
               initialFiles.push({
                   source: "/public/uploads/attachments/"+file,
                   // set type to limbo to tell FilePond this is a temp file
                   options: {
                       type: 'limbo'
                   }
               })
           });
           // Select the file input and use create() to turn it into a pond
           FilePond.create(document.querySelector("#files"),{
               files: initialFiles
           });
       }else{
           FilePond.create(document.querySelector("#files"));
       }


});