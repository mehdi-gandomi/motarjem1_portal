function sendAjax(file) {
    let formData = new FormData();
    formData.append("file", file, file.name);
    $.ajax({
        type: "POST",
        url:"/admin/edit-profile/upload-avatar",
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000,
        success:function(data,status){
            console.log(data);
            if(data.status){
                $("#user-avatar-input").val(data.filename);
            }
        }
    })
}
function readURL(input) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $("#imagePreview").css("background-image", "url(" + e.target.result + ")");
            $("#imagePreview").hide();
            $("#imagePreview").fadeIn(650);
        };
        reader.readAsDataURL(input.files[0]);

    }
}
$("#imageUpload").change(function () {
    readURL(this);
    sendAjax(this.files[0]);
});

$(document).ready(function (e) {
   $("#editProfileForm").on("submit",function (e) {
       e.preventDefault();
       $.ajax({
           type: "POST",
           url:$(this).attr("action"),
           data: $(this).serialize(),
           success:function(data,status){
               if(data.status){
                   Swal.fire({
                       title: 'موفق !',
                       text: data.message,
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
                   Swal.fire(
                       'خطا !',
                       data.message,
                       'error'
                   );
               }
           }
       })
   })
});