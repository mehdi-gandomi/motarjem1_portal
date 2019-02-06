let uploadedFiles = [];

//validate inputs

function validationError(condition,selector) {
  if (condition == "show") {
    $(selector).append("<div class='validation-errors'>لطفا فیلد های مورد نیاز را کامل کنید</div>");
  } else if (condition == "hide") {
    el=document.querySelector(selector);
    el.removeChild(el.childNodes[el.childNodes.length-1])
  }
}

function printValidationHint(el, value, className, addClass) {
  if (addClass === undefined) {
    addClass = true;
  }
  if (typeof el != "object") {
    el = $(el);
  }

  el.html(value);

  if (addClass) {
    el.addClass(className);
  } else {
    el.removeClass(className);
  }
}

function validate_words(e) {
  let words = e.target.value;
  let hint = $(".words--hint");
  if (words == "") {
    e.target.classList.add("validation-failed");
    printValidationHint(
      hint,
      "این فیلد نباید خالی بماند",
      "validation-failed-hint"
    );
  } else if (words < 250) {
    e.target.classList.add("validation-failed");
    printValidationHint(
      hint,
      "تعداد کلمات نباید کمتر از 250 باشد!",
      "validation-failed-hint"
    );
  } else {
      console.log("fuck");
    e.target.classList.remove("validation-failed");
    validationError("hide",".step-1 .row");
    printValidationHint(hint, "", "validation-failed-hint", false);
    
  }
}
$("#words").on("keyup",validate_words);
function validateEmail(email) {
  var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}

function validate_inputs() {
      let words = $("#words");
      if (words.val() == "") {
        words.addClass("validation-failed");
        validationError("show",".step-1 .row");
      } else if (words.val() < 250) {
        words.addClass("validation-failed");
        validationError("show",".step-1 .row");
      }
      
      if (!$("input[name=delivery_type]:checked") || $("input[name=delivery_type]:checked").length==0) {
          console.log("fuck");
        printValidationHint(
          "#delivery-hint",
          "این فیلد الزامی می باشد",
          "validation-failed-hint"
        );
        // validationError("show",".delivery-selection-wrap");
      } else {
        // validationError("hide",".delivery-selection-wrap");
        printValidationHint(
          "#delivery-hint",
          "",
          "validation-failed-hint",
          false
        );
      }
}

function hasEmptyValue(el) {
  if (typeof el != "object") {
    el = $(el);
  }
  return el.val() == "";
}


$(".delivery-selection-wrap")
  .find("input[type=radio]")
  .each(function(index,item){
      item.addEventListener("change",function(e){
          validationError("hide",".step-2");
          printValidationHint("#delivery-hint", "", "validation-failed-hint", false);
      })
  })
  
$("#phone_number").on("blur", function(e) {
  phone = e.target.value;
  if (isNaN(parseInt(phone))) {
    printValidationHint(
      ".phone--hint",
      "شماره تلفن باید عدد باشد و 11 رقمی باشد",
      "validation-failed"
    );
    e.target.classList.add("validation-failed");
  } else if (phone.length < 11 || phone.length > 11) {
    printValidationHint(
      ".phone--hint",
      "شماره تلفن باید 11 رقمی باشد",
      "validation-failed"
    );
    e.target.classList.add("validation-failed");
  } else {
    e.target.classList.remove("validation-failed");
    printValidationHint(".phone--hint", "", "validation-failed", false);
  }
});

$("#fullname").on("blur", function(e) {
    fullname = e.target.value;
    if (fullname == "") {
      e.target.classList.add("validation-failed");
    } 
     else {
      e.target.classList.remove("validation-failed");
    }
});

$("#email").on("change", function(e) {
  email = e.target.value;
  if (validateEmail(email)) {
    printValidationHint(".email--hint", "", "validation-failed", false);
    e.target.classList.remove("validation-failed");
    validationError("hide",".step-3");
  } else {
    printValidationHint(
      ".email--hint",
      "ایمیل وارد شده معتبر نمی باشد",
      "validation-failed"
    );
    e.target.classList.add("validation-failed");
  }
});

$("#type").on("change", function(e) {
  let kind = e.target.value;
  if (kind == "specialist") {
    $(".field_of_study").addClass("show");
  } else {
    $(".field_of_study").addClass("show");
  }
});

$(".new-order-form").on("submit", function(e) {
    e.preventDefault();
    validate_inputs();
    let validationIsGood = false;
    if (hasEmptyValue("#fullname")) {
      $("#fullname").addClass("validation-failed");
      validationIsGood = false;
    } else {
      $("#fullname").removeClass("validation-failed");
      validationError("hide",".step-3");
      validationIsGood = true;
    }
    if (hasEmptyValue("#phone_number")) {
      $("#phone_number").addClass("validation-failed");
      validationIsGood = false;
    } else {
      $("#phone_number").removeClass("validation-failed");
      validationError("hide",".step-3");
      validationIsGood = true;
    }
    if (hasEmptyValue("#email")) {
      $("#email").addClass("validation-failed");
    } else {
      $("#email").removeClass("validation-failed");
      validationError("hide",".step-3");
      validationIsGood = true;
    }
  
    if (validationIsGood) {
      e.target.submit();
    } else {
      validationError("show",".step-3");
    }
});

$(document).ready(function(e) {
  /*
We want to preview images, so we need to register the Image Preview plugin
*/
  FilePond.registerPlugin(
    // encodes the file as base64 data
    FilePondPluginFileEncode,

    // validates the size of the file
    FilePondPluginFileValidateSize
  );

  // Select the file input and use create() to turn it into a pond
  FilePond.create(document.querySelector("#file"));
  FilePond.setOptions({
    // instantUpload: false,
    server: {
      process: {
        url: "/upload-order-file",
        onload: function(response) {
          uploadedFiles.push(response);
          $("#uploaded-files").val(uploadedFiles.join(","));
          return response.key;
        },
        onerror: function(response) {
          return response.data;
        }
      }
    }
  });
});
