function uploadFile(file,url,callback){
    let formData = new FormData();
    formData.append("file", file, file.name);
    $.ajax({
        type: "POST",
        url:url,
        async: true,
        data: formData,
        cache: false,
        contentType: false,
        processData: false,
        timeout: 60000,
        success:callback
    })
}

function readURL(input,previewElement) {
    if (input.files && input.files[0]) {
        var reader = new FileReader();
        reader.onload = function (e) {
            $(previewElement).css("background-image", "url(" + e.target.result + ")");
            $(previewElement).hide();
            $(previewElement).fadeIn(650);
        };
        reader.readAsDataURL(input.files[0]);

    }
}
$("#avatarUpload").change(function () {
    readURL(this,"#avatarPreview");
    uploadFile(this.files[0],"/translator/edit-profile/upload-avatar",function(data,status){
        console.log(data);
        if(status){
            $("#translator-avatar").val(data.filename);
        }
    });
});
$("#melicardUpload").change(function(){
    readURL(this,".melicardPreview");
    uploadFile(this.files[0],"/translator/edit-profile/melicard-photo/upload",function(data,status){
        console.log(data);
        if(status){
            $("#melicard_photo").val(data.filename);
        }
    });
})

//function to validate input based on their pattern
function validateInputs(index,element){
    element.addEventListener("blur",function(e){
        let pattern2=$(this).attr("pattern");
        let value=$(this).val();
        if(value==""){
            $(this).parent().append("<p class='validation-error'>این فیلد نباید خالی باشد !</p>");
        }else if(){

        }

    })
}

// function setCustomValidationMessages(index,element){
// element.setCustomValidity(element.getAttribute("data-pattern-error"));
// }

//listener for validation
$("#editProfileDataForm").find(".validate-it").each(validateInputs);

// listener for form submit
$("#editProfileDataForm").on("submit",function(e){
    e.preventDefault();

})


//handle lightbox
// Get the modal
var modal = document.getElementById('melicardModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var img = document.querySelector('.melicardPreview');
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

