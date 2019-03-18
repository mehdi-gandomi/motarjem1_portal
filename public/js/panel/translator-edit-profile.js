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
function addValidationListeners(index,element){
    element.addEventListener("blur",function(e){
        let value=$(this).val();
        if(value==""){
            $(this).addClass("not-valid");
            $(this).parent().append("<p class='validation-error'>این فیلد نباید خالی باشد !</p>");
        }else{
            let isValid=e.target.checkValidity();
            if(!isValid){
                $(this).addClass("not-valid");
                $(this).parent().append("<p class='validation-error'>"+$(this).attr("data-pattern-error")+"</p>");
            }else{
                $(this).removeClass("not-valid");
                $(this).parent().find(".validation-error").eq(0).remove();
            }
        }

    })
}

// function setCustomValidationMessages(index,element){
// element.setCustomValidity(element.getAttribute("data-pattern-error"));
// }
//listener for validation
$("#editProfileDataForm").find(".validate-it").each(addValidationListeners);

// listener for form submit
$("#editProfileDataForm").on("submit",function(e){
    e.preventDefault();
    let validationIsGood=true;
    $("#editProfileDataForm").find(".validate-it").each(function(index,element){    
        if(element.value==""){
            element.classList.add("not-valid");
            element.parentElement.innerHTML+="<p class='validation-error'>این فیلد نباید خالی باشد !</p>";
            validationIsGood=false;
        }else{
            let isValid=e.target.checkValidity();
            if(!isValid){
                element.classList.add("not-valid");
                element.parentElement.innerHTML+="<p class='validation-error'>"+element.dataset['pattern-error']+"</p>";
                validationIsGood=false;
            }else{
                element.classList.remove("not-valid");
                let parent=element.parentElement;
                let child=parent.querySelector(".validation-error");
                if(child) parent.removeChild(child);
            }
        }
    });
    if(validationIsGood){
        $.ajax({
            type: "POST",
            url: e.target.getAttribute("action"),
            data:$(this).serialize(),
            success: function (data) {
                if (data.status) {
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
                } else {
                    Swal.fire('خطا !', data.message, 'error')
                }
            }
        });
    }
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

