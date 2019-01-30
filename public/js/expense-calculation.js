let svgLoader = "  <svg version='1.1' id='L9' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'x='0px' y='0px' viewBox='0 0 100 100' enable-background='new 0 0 0 0' xml:space='preserve'><path fill=' #8D8F8E' d='M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50'><animateTransform attributeName='transform' attributeType='XML' type='rotate' dur='1s' from='0 50 50'to='360 50 50' repeatCount='indefinite' /></path></svg>";



function calculatePrice(translate_language, type, delivery_type, wordsNumber) {
    let goldBasePrice = 0;
    let silverBasePrice = 0;
    let goldFinalPrice = 0;
    let silverFinalPrice = 0;
    let coefficient = 1;
    let baseDuration = 1;
    let page_number = Math.round(wordsNumber / 250);
    if (page_number < 1)
        page_number = 1;


    if (translate_language == "en_to_fa") {
        if (type == "common") {
            goldBasePrice = 32;
            silverBasePrice = 20;

        } else if (type == "specialist") {
            goldBasePrice = 44;
            silverBasePrice = 40;
        }

    } else if (translate_language == "fa_to_en") {


        if (type == "common") {
            goldBasePrice = 40;
            silverBasePrice = 32;

        } else if (type == "specialist") {
            goldBasePrice = 60;
            silverBasePrice = 52;
        }

    }
    goldFinalPrice = wordsNumber * goldBasePrice;
    silverFinalPrice = wordsNumber * silverBasePrice;

    if (delivery_type == "normal") { coefficient = 1; baseDuration = 5; }

    else if (delivery_type == "half_an_instant") { coefficient = 1.2; baseDuration = 6; }

    else if (delivery_type == "instantaneous") { coefficient = 1.5; baseDuration = 8; }
    goldFinalPrice = goldFinalPrice * coefficient;
    silverFinalPrice = silverFinalPrice * coefficient;
    var durend = page_number / baseDuration;

    durend = Math.ceil(durend)

    return {
        "goldPrice": goldFinalPrice,
        "silverPrice": silverFinalPrice,
        "duration": durend,
        "pageNumber": page_number
    }

}


addListener("#type", "change", function (e) {
    let kind = e.target.value;
    if (kind == "specialist") {
        select(".field_of_study").classList.add("show");
    } else {
        select(".field_of_study").classList.remove("show");
    }
})

addListener("#calc", "click", function (e) {
    e.preventDefault();
    const words = select("#words");
    let wordsNumber = parseInt(words.value);
    const wordsAlert = select(".words--hint");
    const goldQualityResult = select(".calculate-result__gold .result");
    const silverQualityResult = select(".calculate-result__silver .result");
    const orderButton = select("#order");

    const calcInfo = select(".calculate-result__info");
    if (wordsNumber == 0 || isNaN(wordsNumber)) {
        alert("لطفا فیلدهای مورد نیاز را پر کنید");
        return;
    }

    if (wordsNumber < 200) {
        words.classList.add("input-error");
        wordsAlert.innerHTML = "تعداد کلمات نباید کمتر از 200 باشد!";
        wordsAlert.style.color="red";
        return;
    }
    words.classList.remove("input-error");
    wordsAlert.innerHTML = "هر صفحه استاندارد، 250 کلمه است";
    wordsAlert.style.color="#000";
    goldQualityResult.innerHTML = svgLoader;
    silverQualityResult.innerHTML = svgLoader;
    select(".calculate-result").classList.add("show");
    const type = select("#type").value;
    const translate_language = select('#language').value;
    const delivery_type = select('#delivery_type').value;
    ////////
    result = calculatePrice(translate_language, type, delivery_type, wordsNumber);

    calcInfo.innerHTML = "تعداد صفحات : " + result.pageNumber;
    setTimeout(function () {
        silverQualityResult.innerHTML = result.silverPrice + ' تومان در مدت ' + result.duration + " روز";
        goldQualityResult.innerHTML = result.goldPrice + ' تومان در مدت ' + result.duration + " روز";
        orderButton.classList.remove("d-none");
    }, 1000);



});


// function checkOrderForm() {
//     var wordsNumber = $('#wordsNumber').val();
//     if (wordsNumber == "") {
//         $('#mess').html('<h5 class="alert alert-danger">فرم خالی نماند</h5>');
//         $("html, body").animate({ scrollTop: 0 }, 500);
//         return false;
//     }
// }
