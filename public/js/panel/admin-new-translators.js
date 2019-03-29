//show full translator profile details
function showُTranslatorInfo(translatorId){
    $("#NotificationInfoModal").modal("hide");
    $.get("/admin/translator-info/all/json",{translator_id:translatorId},function(data,status){
        //i coded this with template literal but because of low browser support , i converted the code
        //for debugging you have to convert it to es6 with babel.io
        //unfortunately it converted persian to utf :( you have to convert it to text
        //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
        var output = "\n        <div class=\"row \">\n    <div class=\"col-lg-4 translator-avatar\">\n        <img src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\" alt=\"\">\n    </div>\n    <div class=\"col-lg-3\">\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0646\u0627\u0645 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.fname, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0646\u0627\u0645 \u062E\u0627\u0646\u0648\u0627\u062F\u06AF\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.lname, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062C\u0646\u0633\u06CC\u062A : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.sex === '1' ? 'مرد' : 'زن', "</span>\n        </p>\n    </div>\n    <div class=\"col-lg-5\">\n        <strong>\u0622\u062F\u0631\u0633</strong>\n        <div class=\"translator-address mt-2\">\n            ").concat(data.info.address, "\n        </div>\n    </div>\n</div>\n<hr>\n<div class=\"row mt-5\">\n    <div class=\"col-lg-4 translator-melicard\">\n        <img src=\"/public/uploads/translator/melicard/").concat(data.info.melicard_photo, "\" alt=\"\">\n    </div>\n    <div class=\"col-lg-4\">\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u06A9\u062F \u0645\u0644\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.meli_code, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0645\u062F\u0631\u06A9 \u062A\u062D\u0635\u06CC\u0644\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.degree, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0633\u0627\u0628\u0642\u0647 \u06A9\u0627\u0631 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.exp_years, " \u0633\u0627\u0644</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062A\u0627\u0631\u06CC\u062E \u062B\u0628\u062A \u0646\u0627\u0645 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.register_date_persian, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062A\u0631\u062C\u0645\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC \u0628\u0647 \u0641\u0627\u0631\u0633\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.en_to_fa == '0' ? 'خیر' : 'بله', "</span>\n        </p>\n    </div>\n    <div class=\"col-lg-4\">\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.phone, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0634\u0645\u0627\u0631\u0647 \u0647\u0645\u0631\u0627\u0647 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.cell_phone, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u0627\u06CC\u0645\u06CC\u0644 : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.email, "</span>\n        </p>\n        <p class=\"translator-info\">\n            <strong class=\"translator-info__title\">\u062A\u0631\u062C\u0645\u0647 \u0641\u0627\u0631\u0633\u06CC \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC : </strong>\n            <span class=\"translator-info__value\">").concat(data.info.fa_to_en == '0' ? 'خیر' : 'بله', "</span>\n        </p>\n\n    </div>\n</div>\n<hr style=\"border-width:2px\">\n<div class=\"row mt-5\">\n    <div class=\"col-12 mb-4\">\n        <h5 class=\"text-center\">\u0622\u0632\u0645\u0648\u0646 ").concat(data.info.study_field_title, "</h5>\n        <h6 class=\"text-center\">(").concat(data.info.language_id == '1' ? 'انگلیسی به فارسی' : 'فارسی به انگلیسی', ")</h6>\n    </div>\n    <div class=\"col-lg-6\"><h6>متن اصلی</h6>\n        <div class=\"test question ").concat(data.info.language_id == "1" ? 'ltr' : 'rtl', "\">\n            ").concat(data.info.question_text, "\n        </div>\n    </div>\n    <div class=\"col-lg-6\"><h6>ترجمه کاربر</h6>\n        <div class=\"test answer ").concat(data.info.language_id == "1" ? 'rtl' : 'ltr', "\">\n            ").concat(data.info.translated_text, "\n        </div>\n    </div>\n    <div class=\"col-12\">\n        <div class=\"more-info__actions d-flex justify-content-center\">\n            <button class=\"btn btn-success\" onclick=\"employTranslator('").concat(data.info.translator_id, "')\">\u0627\u0633\u062A\u062E\u062F\u0627\u0645 \u0645\u062A\u0631\u062C\u0645</button>\n            <button class=\"btn btn-danger\" onclick=\"denyTranslator('").concat(data.info.translator_id, "')\">\u0631\u062F \u0645\u062A\u0631\u062C\u0645</button>\n            <button class=\"btn btn-secondary\" data-dismiss=\"modal\" type=\"button\">\u0628\u0633\u062A\u0646</button>\n        </div>\n    </div>\n</div>\n        \n        ");
        $("#translatorInfoWrap").html(output);
        $("#translatorInfo").modal({backdrop: 'static', keyboard: false});
    });
    
}

//employment process for translator
function employTranslator(translatorId){

    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا واقعا می خواهید این مترجم را استخدام کنید ؟",
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
                url:"/admin/translator/employ",
                data:{
                    translator_id:translatorId,
                    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success:function(data,status){
                    if (data.status) {
                        $("#translatorInfo").modal("hide");
                        Swal.fire({
                            title: 'موفق !',
                            text: "مترجم با موفقیت استخدام شد !",
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
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در استخدام مترجم رخ داد !", 'error')
                    }
                }
            })             
        }else{

        }
      })



}

//deny employment request from translator
function denyTranslator(translatorId){



    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا واقعا می خواهید این مترجم را رد کنید ؟",
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
                url:"/admin/translator/deny",
                data:{
                    translator_id:translatorId,
                    token:"bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success:function(data,status){
                    if (data.status) {
                        $("#translatorInfo").modal("hide");
                        Swal.fire({
                            title: 'موفق !',
                            text: "مترجم رد شد !",
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
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در رد مترجم رخ داد !", 'error')
                    }
                }
            })   
        }else{

        }
      })

    
}

//handle lightbox
//show the translator avatar in large mode
// Get the modal
function handleImgClick(e){
    modal.style.display = "block";
    let image=e.currentTarget.src;
    modalImg.src=image;
    captionText.innerHTML = e.currentTarget.getAttribute("aria-label");
}
let modal = document.getElementById('avatarModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var images= document.querySelectorAll('.translatorAvatar');
var modalImg = document.getElementById("avatarPhoto");
var captionText = document.getElementById("caption");
images.forEach(function(img){
    img.addEventListener("click",handleImgClick);
})
// Get the <span> element that closes the modal
var span = document.getElementById("closeModal");
// When the user clicks on <span> (x), close the modal
span.addEventListener("click",function(e){
    modal.style.display = "none";
})
e