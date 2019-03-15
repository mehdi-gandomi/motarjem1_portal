function showُTranslatorInfo(translatorId){    
    $.get("/admin/translator-info/all/json",{translator_id:translatorId},function(data,status){
        `
        <div class="row ">
    <div class="col-lg-4 translator-avatar">
        <img src="/public/uploads/avatars/translator/${data.info.avatar}" alt="">
    </div>
    <div class="col-lg-3">
        <p class="translator-info">
            <strong class="translator-info__title">نام : </strong>
            <span class="translator-info__value">${data.info.fname}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">نام خانوادگی : </strong>
            <span class="translator-info__value">${data.info.lname}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">جنسیت : </strong>
            <span class="translator-info__value">${data.info.sex==='1' ? 'مرد':'زن'}</span>
        </p>
    </div>
    <div class="col-lg-5">
        <strong>آدرس</strong>
        <div class="translator-address mt-2">
            ${data.info.address}
        </div>
    </div>
</div>
<hr>
<div class="row mt-5">
    <div class="col-lg-4 translator-melicard">
        <img src="/public/uploads/translator/melicard/${data.info.melicard_photo}" alt="">
    </div>
    <div class="col-lg-4">
        <p class="translator-info">
            <strong class="translator-info__title">کد ملی : </strong>
            <span class="translator-info__value">${data.info.meli_code}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">مدرک تحصیلی : </strong>
            <span class="translator-info__value">${data.info.degree}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">سابقه کار : </strong>
            <span class="translator-info__value">${data.info.exp_years} سال</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">تاریخ ثبت نام : </strong>
            <span class="translator-info__value">${data.info.register_date_persian}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">ترجمه انگلیسی به فارسی : </strong>
            <span class="translator-info__value">${data.info.en_to_fa == '0' ? 'خیر':'بله'}</span>
        </p>
    </div>
    <div class="col-lg-4">
        <p class="translator-info">
            <strong class="translator-info__title">شماره تلفن ثابت : </strong>
            <span class="translator-info__value">${data.info.phone}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">شماره همراه : </strong>
            <span class="translator-info__value">${data.info.cell_phone}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">ایمیل : </strong>
            <span class="translator-info__value">${data.info.email}</span>
        </p>
        <p class="translator-info">
            <strong class="translator-info__title">ترجمه فارسی به انگلیسی : </strong>
            <span class="translator-info__value">${data.info.fa_to_en == '0' ? 'خیر':'بله'}</span>
        </p>

    </div>
</div>
<hr style="border-width:2px">
<div class="row mt-5">
    <div class="col-12 mb-4">
        <h5 class="text-center">آزمون ${data.info.study_field_title}</h5>
        <h6 class="text-center">(${data.info.language == '1' ? 'انگلیسی به فارسی':'فارسی به انگلیسی'})</h6>
    </div>
    <div class="col-lg-6">
        <div class="test question">
            ${data.info.question_text}
        </div>
    </div>
    <div class="col-lg-6">
        <div class="test answer">
            ${data.info.translated_text}
        </div>
    </div>
    <div class="col-12">
        <div class="more-info__actions d-flex justify-content-center">
            <button class="btn btn-success" onclick="employTranslator('${data.info.translator_id}')">استخدام مترجم</button>
            <button class="btn btn-danger" onclick="denyTranslator('${data.info.translator_id}')">رد مترجم</button>
            <button class="btn btn-secondary" data-dismiss="modal" type="button">بستن</button>
        </div>
    </div>
</div>
        
        `
    });
    // $("#translatorInfo").modal({backdrop: 'static', keyboard: false});
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
