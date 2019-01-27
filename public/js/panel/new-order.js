let uploadedFiles = [];

//validate inputs

function validationError(condition) {
  let activeSection = $(".section.is-active");
  if (condition == "show") {
    activeSection
    element = createEl("div", ["validation-errors"]);
    element.innerHTML = "لطفا فیلد های مورد نیاز را کامل کنید";
    activeSection.appendChild(element);
  } else if (condition == "hide") {
    element = select(".validation-errors");
    if (element) {
      activeSection.removeChild(element);
    }
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
  console.log(words);
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
    e.target.classList.remove("validation-failed");
    printValidationHint(hint, "", "validation-failed-hint", false);
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
      let words = $("#words");
      if (words.value == "") {
        words.addClass("validation-failed");
        validationError("show");
        return false;
      } else if (words.value < 250) {
        words.addClass("validation-failed");
        validationError("show");
        return false;
      }
      return true;
      break;
    case 1:
      if (!$("input[name=delivery_type]:checked")) {
        printValidationHint(
          "#delivery-hint",
          "این فیلد الزامی می باشد",
          "validation-failed-hint"
        );
        validationError("show");
        return false;
      } else {
        validationError("hide");
        printValidationHint(
          "#delivery-hint",
          "",
          "validation-failed-hint",
          false
        );
        return true;
      }
      break;
  }
}

function hasEmptyValue(el) {
  if (typeof el != "object") {
    el = $(el);
  }
  return el.val() == "";
}

$("#words").on("keyup",validate_words);
$("#words").on("keyup", validate_words);
$(".delivery-selection-wrap")
  .find("input[type=radio]")
  .on("change", function(e) {
    validationError("hide");
    printValidationHint("#delivery-hint", "", "validation-failed-hint", false);
  });
$("#phone_number").on("blur", function(e) {
  phone = e.target.value;
  if (isNaN(parseInt(phone))) {
    printValidationHint(
      ".phone--hint",
      "شماره تلفن باید عدد باشد و 11 رقمی باشد",
      "validation-failed"
    );
  } else if (phone.length < 11 || phone.length > 11) {
    printValidationHint(
      ".phone--hint",
      "شماره تلفن باید 11 رقمی باشد",
      "validation-failed"
    );
  } else {
    printValidationHint(".phone--hint", "", "validation-failed", false);
  }
});

$("#email").on("change", function(e) {
  email = e.target.value;
  if (validateEmail(email)) {
    printValidationHint(".email--hint", "", "validation-failed", false);
  } else {
    printValidationHint(
      ".email--hint",
      "ایمیل وارد شده معتبر نمی باشد",
      "validation-failed"
    );
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
    let validationIsGood = false;
    if (hasEmptyValue("#fullname")) {
      $("#fullname").addClass("validation-failed");
      validationIsGood = false;
    } else {
      $("#fullname").addClass("validation-failed");
      validationError("hide");
      validationIsGood = true;
    }
    if (hasEmptyValue("#phone_number")) {
      $("#phone_number").addClass("validation-failed");
      validationIsGood = false;
    } else {
      $("#phone_number").removeClass("validation-failed");
      validationError("hide");
      validationIsGood = true;
    }
    if (hasEmptyValue("#email")) {
      $("#email").addClass("validation-failed");
    } else {
      $("#email").removeClass("validation-failed");
      validationError("hide");
      validationIsGood = true;
    }
  
    if (validationIsGood) {
      e.target.submit();
    } else {
      validationError("show");
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
      url: "/",
      process: {
        url: "upload-order-file",
        onload: function(response) {
          uploadedFiles.push(response);
          select("#uploaded-files").value = uploadedFiles.join(",");
          return response.key;
        },
        onerror: function(response) {
          return response.data;
        }
      }
    }
  });
});
