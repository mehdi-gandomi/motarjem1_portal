function sendAjax(file) {
    let formData = new FormData();
    formData.append("file", file, file.name);
    $.ajax({
        type: "POST",
        url:"/user/edit-profile/upload-avatar",
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000,
        success:function(data,status){
            console.log(data);
            if(status){
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


//handle lightbox
// Get the modal
var modal = document.getElementById('melicardModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var img = document.querySelector('.imagePreview');
var modalImg = document.getElementById("melicardPhoto");
var captionText = document.getElementById("caption");
img.addEventListener("click",function(e){
    modal.style.display = "block";
    let image=e.currentTarget.style.backgroundImage;
    image=image.substr(5,image.length-7);
    modalImg.src=image;
    captionText.innerHTML = e.currentTarget.getAttribute("aria-label");
})
// Get the <span> element that closes the modal
var span = document.getElementById("closeModal");
// When the user clicks on <span> (x), close the modal
span.addEventListener("click",function(e){
    modal.style.display = "none";
})

