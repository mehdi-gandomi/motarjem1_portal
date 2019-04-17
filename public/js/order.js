let steps = selectAll('.steps li');
let sections = selectAll(".form-wrapper .section");
let activeStep = 0;
let uploadedFiles = [];
function toggleStep(direction) {
  if (direction === undefined) {
    direction = "next";
  }

  if (direction == "next") {
    activeStep++;
    activeStep = activeStep === steps.length ? 0 : activeStep;
  } else if (direction == "prev") {
    activeStep--;
    activeStep = activeStep === -1 ? 0 : activeStep;
  }

  if (activeStep == 1) {
    select(".steps-container").classList.add("h-1000");
  } else {
    select(".steps-container").classList.remove("h-1000");
  }

  removeClassFromElements(sections, "is-active");
  sections[activeStep].classList.add("is-active");
  removeClassFromElements(steps, "is-active");
  steps[activeStep].classList.add("is-active");

}


//validate inputs

function validationError(condition){
  let activeSection=document.querySelector(".section.is-active");
  if(condition=="show"){
    element=createEl("div",["validation-errors"]);
    element.innerHTML="لطفا فیلد های مورد نیاز را کامل کنید";
    activeSection.appendChild(element);
  }else if(condition=="hide"){
    element=select(".validation-errors");
    if(element){
      activeSection.removeChild(element);
    }
  }
}

function printValidationHint(el, value, className,addClass){
  if(addClass===undefined){
    addClass=true;
  }
  if (typeof el != "object") {
    el = select(el);
  }


    el.innerHTML=value;


  if(addClass){
    el.classList.add(className);
  }else{
    el.classList.remove(className);
  }
}

function validate_words(e) {
  let words = e.target.value;
  console.log(words);
  let hint = select(".words--hint");
  if(words < 0 || words==0){
    e.target.value="";
  }
  if(words==""){
    e.target.classList.add("validation-failed");
    printValidationHint(hint,"این فیلد نباید خالی بماند","validation-failed-hint");
  }
  else if (words < 250) {
    e.target.classList.add("validation-failed");
    printValidationHint(hint,"تعداد کلمات نباید کمتر از 250 باشد!","validation-failed-hint");
  }
  else {
    e.target.classList.remove("validation-failed");
    printValidationHint(hint,"","validation-failed-hint",false);
    validationError("hide");
  }
}

function validateEmail(email) {
  var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}

function validate_inputs() {
  switch (activeStep) {
    case 0:
        let words = select("#words");
        if (words.value == "") {
          words.classList.add("validation-failed");
          validationError("show");
          return false;
        }
        else if (words.value < 250) {
          words.classList.add("validation-failed");
          validationError("show");
          return false;
        }
        return true;
      break;
      case 1:
          var isValid=true;
          if(!select("input[name=delivery_type]:checked")){
            printValidationHint("#delivery-hint","این فیلد الزامی می باشد","validation-failed-hint");
            validationError("show");
            isValid= false;
          }else{
            validationError("hide");
            printValidationHint("#delivery-hint","","validation-failed-hint",false);
            isValid=true;
          }
          if (uploadedFiles.length < 1){
            // validationError("show");
            var element=createEl("div",["validation-errors","mt-2"]);
            element.innerHTML=" حتما باید یک فایل آپلود کنید !";
            select(".filepond--root").parentElement.appendChild(element);
            isValid=false;
          } else{
            // validationError("hide");
            isValid=true;
          }
          return isValid;
      break;
  }
}

function hasEmptyValue(el){
  if (typeof el != "object") {
    el = select(el);
  }
  return el.value=="";
}

addListener("#words","keyup",validate_words);
addListener("input[type=radio]","change",function(e){
  validationError("hide");
  printValidationHint("#delivery-hint","","validation-failed-hint",false);
},true);
addListener("#phone_number","blur",function(e){
  phone=e.target.value;
  if(isNaN(parseInt(phone))){
    printValidationHint(".phone--hint","شماره تلفن باید عدد باشد و 11 رقمی باشد","validation-failed");
  }else if (phone.length<11 || phone.length>11){
    printValidationHint(".phone--hint","شماره تلفن باید 11 رقمی باشد","validation-failed");
  }else{
    printValidationHint(".phone--hint","","validation-failed",false);
  }
});

addListener("#email","change",function(e){
  email=e.target.value;
  if(validateEmail(email)){
    printValidationHint(".email--hint","","validation-failed",false);
  }else{
    printValidationHint(".email--hint","ایمیل وارد شده معتبر نمی باشد","validation-failed");
  }
});

addListener(".next-step", "click", function (e) {
  if (validate_inputs()) {
    toggleStep("next");
  }
}, true);
addListener(".prev-step", "click", function (e) {
  toggleStep("prev");
}, true);


addListener("#type", "change", function (e) {
  let kind = e.target.value;
  if (kind == "2") {
    select(".field_of_study").classList.add("show");
  } else {
    select(".field_of_study").classList.remove("show");
  }
})

function validate_discount_code(e) {
  const discountHint=select("#discountHint");
  if (e.target.value == ""){
    discountHint.classList.remove("validation-failed");
    discountHint.innerHTML="جهت دریافت کد تخفیف <a href=\"http://www.t.me/motarjem_one\">پیام</a> دهید";
  } else{
    axios.get('order/discount-code/validate', {
      params: {
        coupon_code: e.target.value
      }
    })
    .then(function (response) {
      if (response.data.valid){
        if (response.data.info.is_percent_based){
          discountHint.classList.remove("validation-failed");
          discountHint.innerHTML="کد تخفیف تایید شد ! "+response.data.info.discount_percent+"% تخفیف";
          return true;
        }
        discountHint.classList.remove("validation-failed");
        discountHint.innerHTML="کد تخفیف تایید شد ! "+parseInt(response.data.info.discount_price).toLocaleString("us")+ "تومان";
        return true;
      }else{
        discountHint.innerHTML="کد تخفیف وارد شده یافت نشد !";
        discountHint.classList.add("validation-failed");
        return false;
      }
    })
    .catch(function (error) {
      console.log(error);
    })
  }
}

addListener("#discount_code","blur",validate_discount_code);

addListener(".order-form","submit",function(e){
  e.preventDefault();
  var validationIsGood=false;
  if(hasEmptyValue("#fullname")){
    select("#fullname").classList.add("validation-failed");
    validationIsGood=false;
  }else{
    select("#fullname").classList.remove("validation-failed");
    validationError("hide");
    validationIsGood=true;
  }
  if(hasEmptyValue("#phone_number")){
    select("#phone_number").classList.add("validation-failed");
    validationIsGood=false;
  }
  else{
    select("#phone_number").classList.remove("validation-failed");
    validationError("hide");
    validationIsGood=true;
  }
  if(hasEmptyValue("#email")){
    select("#email").classList.add("validation-failed");
    
  }
  else{
    select("#email").classList.remove("validation-failed");
    validationError("hide");
    validationIsGood=true;
  }
  if (uploadedFiles.length < 1){
    toggleStep("prev");
    // validationError("show");
    body.scrollTop=550;
    validationIsGood=false;
  }else{
    validationError("hide");
    validationIsGood=true;
  }

  if(validationIsGood){
    e.target.submit();
  }else{
    validationError("show");
  }
});

document.addEventListener('DOMContentLoaded', function (e) {

  /*
We want to preview images, so we need to register the Image Preview plugin
*/
  FilePond.registerPlugin(

    // encodes the file as base64 data
    FilePondPluginFileEncode,

    // validates the size of the file
    FilePondPluginFileValidateSize,

  );

  // Select the file input and use create() to turn it into a pond
  FilePond.create(
    document.querySelector('#file')
  );
  FilePond.setOptions({
    // instantUpload: false,
    server: {
      url: '/',
      process: {
        url: 'upload-order-file',
        onload: function (response) {
          uploadedFiles.push(response);
          select("#uploaded-files").value = uploadedFiles.join(",");
          var child = select(".filepond--root").parentElement.querySelector(".validation-errors");
          if (child){
            select(".filepond--root").parentElement.removeChild(child);
          }
          return response.key;
        },
        onerror: function (response) {
          return response.data;
        }
      }
    }
  });
});