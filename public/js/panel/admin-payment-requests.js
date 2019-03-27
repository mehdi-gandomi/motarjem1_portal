//global variable for date object
var paymentDateInstance;
//this fucntion replaces all occurances in string with desired value
String.prototype.replaceAll = function (search, replacement) {
    var target = this;
    return target.replace(new RegExp(search, "g"), replacement);
};
//this function gets querystring value by key from url or a querystring text
function getQueryString(name, url) {
    if (!url) url = window.location.href;
    name = name.replace(/[\[\]]/g, "\\$&");
    var regex = new RegExp("[?&]" + name + "(=([^&#]*)|&|#|$)"),
        results = regex.exec(url);
    if (!results) return null;
    if (!results[2]) return null;
    return decodeURIComponent(results[2].replace(/\+/g, " "));
}
//this function converts js object to querystring to use on urls
function serializeQSObject(obj) {
    var str = [];
    for (var p in obj) {
        if (obj[p] == "") continue;
        if (Array.isArray(obj[p]) && obj[p].length < 1) continue;
        if (obj.hasOwnProperty(p)) {
            str.push(encodeURIComponent(p) + "=" + encodeURIComponent(obj[p]));
        }
    }

    return str.join("&").replaceAll("%2C", ",");
}
//this function converts querystring to js object to manipulate object
function quertStringToObj(queryString) {
    if (queryString == "?" || queryString == "") return false;
    return JSON.parse(
        '{"' + queryString.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
        function (key, value) {
            return key === "" ? value : decodeURIComponent(value);
        }
    );
}

function substitute(str, data) {
    let output = str.replace(/%[^%]+%/g, function (match) {
        if (match in data) {
            return (data[match]);
        } else {
            return ("");
        }
    });
    return (output);
}

//this function shows pagination
function showPagination(count, current_page, offset, visibleNumbers, queryString, baseUrl) {
    baseUrl = baseUrl === undefined ? window.location.origin + window.location.pathname : baseUrl;
    let output = "";
    let fullUrl;
    if (queryString) {
        fullUrl = baseUrl + "?" + queryString + "&page=%page%";
    } else {
        fullUrl = baseUrl + "?page=%page%";
    }
    if (count > offset) {

        let lastPage = Math.ceil(count / offset);
        let endIndex, startIndex;
        output += current_page > 1 ? '<li class="page-item"><a class="page-link" href="' + baseUrl + '" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li><li class="page-item"><a class="page-link" href="' + substitute(fullUrl, {
            "%page%": current_page - 1
        }) + '" aria-label="Previous">قبلی</a></li>' : "";
        if ((current_page + (visibleNumbers - 1)) > lastPage) {
            endIndex = lastPage;
            startIndex = current_page - (visibleNumbers - (lastPage - current_page));
        } else {
            startIndex = current_page - (visibleNumbers - 1);
            endIndex = current_page + (visibleNumbers - 1);
        }
        startIndex = startIndex <= 0 ? 1 : startIndex;
        for (pageNumber = startIndex; pageNumber <= endIndex; pageNumber++) {
            output += pageNumber == current_page ? "<li class='page-item active'>" : "<li class='page-item'>";
            output += "<a class='page-link' href='" + substitute(fullUrl, {
                "%page%": pageNumber
            }) + "'>" + pageNumber + "</a>";
        }
        if (current_page != lastPage) {
            output += '<li class="page-item"><a class="page-link" href="' + substitute(fullUrl, {
                "%page%": current_page + 1
            }) + '" aria-label="Previous">بعدی</a></li>';
            output += '<li class="page-item"><a class="page-link" href="' + substitute(fullUrl, {
                "%page%": lastPage
            }) + '" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
    }
    $(".pagination").html(output);
}


//START this functions gets called when a checkbox state changes

$(".table-filter input[type=checkbox]").on("change", function (e) {
    let queryStringObj = {
        state: [],
        paid: []
    };
    $("input[name=state]").each(function () {
        if ($(this).is(":checked")) {
            queryStringObj["state"].push($(this).val());
        }
    });
    $("input[name=paid]").each(function () {
        if ($(this).is(":checked")) {
            queryStringObj["paid"].push($(this).val());
        }
    });
    if (queryStringObj["state"].length > 0) {
        queryStringObj["state"] = queryStringObj["state"].join(",");
    }
    if (queryStringObj["paid"].length > 0) {
        queryStringObj["paid"] = queryStringObj["paid"].join(",");
    }
    let page = getQueryString("page");
    if (page) {
        queryStringObj["page"] = page;
    }
    queryString = serializeQSObject(queryStringObj);
    applyFilters(queryString);
});

//END this functions gets called when a checkbox state changes

//this function gets data from server based on given filters
function applyFilters(queryString) {
    url = location.origin + location.pathname;
    url = queryString ? url + "?" + queryString : url;
    window.history.pushState("filtered messages", "درخواست های فیلتر شده", url);
    $.get("/admin/translator/payment-requests/json?" + queryString, function (res, status) {
        if (status) {
            showRequests(res.payment_requests);
            showPagination(res.count, res.current_page, 10, 3, queryString);
        }
    });
}

function showRequests(requests) {
    //i coded this with template literal but because of low browser support , i converted the code
    //for debugging you have to convert it to es6 with babel.io
    //unfortunately it converted persian to utf :( you have to convert it to text
    //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
    var output = "";
    var state = "";
    requests.forEach(function (request) {
        if (request.state == "-1") state = "درانتظار تایید";
        else if (request.state == "0") state = "رد شده";
        else state = "تایید شده";
        output += '<tr>\n      <td data-label="\u0631\u062F\u06CC\u0641">\n          '
            .concat(
                request.id,
                '\n      </td>\n      <td data-label="\u0646\u0627\u0645 \u0645\u062A\u0631\u062C\u0645">\n          <a aria-role="button" href="javascript:void(0)" onclick="showTranslatorInfo(\''
            )
            .concat(request.translator_id, "')\">")
            .concat(
                request.translator_fname + " " + request.translator_lname,
                '</a>\n      </td>\n      <td data-label="\u0645\u0628\u0644\u063A \u062F\u0631\u062E\u0648\u0627\u0633\u062A\u06CC">\n          '
            )
            .concat(
                parseInt(request.amount).toLocaleString("us"),
                '\n          \u062A\u0648\u0645\u0627\u0646\n      </td>\n      <td data-label="\u062A\u0627\u0631\u06CC\u062E \u062F\u0631\u062E\u0648\u0627\u0633\u062A">\n          '
            )
            .concat(
                request.request_date_persian,
                '\n      </td>\n      <td data-label="\u0648\u0636\u0639\u06CC\u062A \u062A\u0627\u06CC\u06CC\u062F">\n\t\t'
            )
            .concat(
                state,
                '\n      </td>\n      <td data-label="\u0648\u0636\u0639\u06CC\u062A \u067E\u0631\u062F\u0627\u062E\u062A">\n          '
            )
            .concat(
                request.is_paid == "1" ? "پرداخت شده"+"\n <a href='javascript:void(0)' onclick=\"showPaymentInfo('"+request.payment_log_id+"')\">جزییات</a>" : "پرداخت نشده",
                '\n      </td>\n\t  <td class="order-actions" data-label="\u0639\u0645\u0644\u06CC\u0627\u062A">'
            );
        if (request.state == "1")
            output += '\n  <button class="expand-button order-action is--primary is--large" onclick="showPaymentModal(\''.concat(
                request.id,
                '\')">\n                                            <span data-hover="\u062B\u0628\u062A \u0627\u0637\u0644\u0627\u0639\u0627\u062A \u067E\u0631\u062F\u0627\u062E\u062A">\n                                                <i class="icon-info"></i>\n                                            </span>\n                                        </button>\n'
            );
        if (request.state == "-1")
            output += '\n                                        <button class="expand-button order-action is--success is--medium" onclick="acceptRequest(\''
            .concat(
                request.id,
                '\')">\n                                            <span data-hover="\u0642\u0628\u0648\u0644 \u062F\u0631\u062E\u0648\u0627\u0633\u062A">\n                                                <i class="icon-check"></i>\n                                            </span>\n                                        </button>\n                                        <button class="expand-button order-action is--danger" onclick="denyRequest(\''
            )
            .concat(
                request.id,
                '\')">\n                                            <span data-hover="\u0631\u062F \u062F\u0631\u062E\u0648\u0627\u0633\u062A">\n                                                <i class="icon-close"></i>\n                                            </span>\n                                        </button>\n'
            );
        if (request.state == "0")
            output += '\n                                                                                                      <button class="expand-button order-action is--success is--medium" onclick="acceptRequest(\''.concat(
                request.id,
                '\')">\n                                            <span data-hover="\u0642\u0628\u0648\u0644 \u062F\u0631\u062E\u0648\u0627\u0633\u062A">\n                                                <i class="icon-check"></i>\n                                            </span>\n                                        </button>  \n'
            );
        output += "</td></tr>";
    });
    $("#paymentRequests").html(output);
}
// show translator's basic info
function showTranslatorInfo(translatorId) {
    $.get("/admin/translator/basic-info/json", {
        translator_id: translatorId
    }, function (data, status) {
        if (data.status) {
            //i coded this with template literal but because of low browser support , i converted the code
            //for debugging you have to convert it to es6 with babel.io
            //unfortunately it converted persian to utf :( you have to convert it to text
            //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
            var output = "\n  <div class=\"translator-info\">\n    <div class=\"translator-info__avatar\">\n        <img alt=\"\" src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\"></div>\n    <div class=\"translator-info__info\">\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0646\u0627\u0645 \u0645\u062A\u0631\u062C\u0645 :\u200C\n            </label>\n            <strong>").concat(data.info.fname + " " + data.info.lname, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0645\u062F\u0631\u06A9 \u062A\u062D\u0635\u06CC\u0644\u06CC</label>\n            <strong>").concat(data.info.degree, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0641\u0627\u0631\u0633\u06CC \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC</label>\n            <strong>").concat(data.info.fa_to_en == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC \u0628\u0647 \u0641\u0627\u0631\u0633\u06CC</label>\n            <strong>").concat(data.info.en_to_fa == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n            </label>\n            <strong>").concat(data.info.email, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A</label>\n            <strong>").concat(data.info.phone, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n            <strong>").concat(data.info.cell_phone, "</strong>\n        </div>\n    </div>\n</div>        \n");
            $("#translatorBasicInfoWrap").html(output);
            $("#translatorBasicInfo").modal("show");
            console.log(output);
        }
    })
}
//accept translator's payment request
function acceptRequest(requestId) {
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا میخواهید این درخواست را قبول کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText: 'نه'
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                type: "POST",
                url: "/admin/translator/payment-requests/accept",
                data: {
                    request_id: requestId,
                    token: "bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success: function (data, status) {
                    if (data.status) {
                        Swal.fire({
                            title: 'موفق !',
                            text: "اطلاعات با موفقیت ثبت شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function (result) {
                            if (result.value) {
                                applyFilters(window.location.search);
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در ثبت اطلاعات رخ داد !", 'error')
                    }
                }
            })
        } else {

        }
    })

}
//deny translator's payment request
function denyRequest(requestId) {
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "آیا میخواهید این درخواست را رد کنید ؟",
        type: 'info',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'بله',
        cancelButtonText: 'نه'
    }).then(function (result) {
        if (result.value) {
            $.ajax({
                type: "POST",
                url: "/admin/translator/payment-requests/deny",
                data: {
                    request_id: requestId,
                    token: "bad47df23cb7e6b3b8abf68cbba85d0f"
                },
                success: function (data, status) {
                    if (data.status) {
                        Swal.fire({
                            title: 'موفق !',
                            text: "اطلاعات با موفقیت ثبت شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function (result) {
                            if (result.value) {
                                applyFilters(window.location.search);
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در ثبت اطلاعات رخ داد !", 'error')
                    }
                }
            })
        } else {

        }
    })

}

function showPaymentModal(requestId) {
    $("#reqId").val(requestId);
    $("#paymentDetails").modal("show");
}
function showPaymentInfo(logId){
    console.log(logId);
}
function formatDates(){
    //formatting to save in db
    var state=paymentDateInstance.getState();
    var persianDate=state.selected.year+"/"+(state.selected.date < 10 ? "0"+state.selected.month:state.selected.month)+"/"+(state.selected.date < 10 ? "0"+state.selected.date:state.selected.date)+" "+(state.selected.hour < 10 ? "0"+state.selected.hour:state.selected.hour)+":"+(state.selected.minute < 10 ? "0"+state.selected.minute:state.selected.minute);
    var gregDate=state.selected.dateObject.ON.gDate;
    var dateEnglish=gregDate.getFullYear()+"/"+(gregDate.getMonth() < 10 ? "0"+gregDate.getMonth():gregDate.getMonth())+"/"+(gregDate.getDate() < 10 ? "0"+gregDate.getDate():gregDate.getDate())+" "+(gregDate.getHours() < 10 ? "0"+gregDate.getHours():gregDate.getHours())+":"+(gregDate.getMinutes() < 10 ? "0"+gregDate.getMinutes():gregDate.getMinutes());
    return{
        persianDate:persianDate,
        dateEnglish:dateEnglish
    }
}
$(document).ready(function (e) {
    paymentDateInstance= $('.normal-example').persianDatepicker({
        timePicker: {
            enabled: true
        }
    });
    $("#refId").on("blur input",function (e) {
        if ($(this).val() === ""){
            $(this).addClass("has-error");
            $(this).parent().find(".validation-error").eq(0).remove();
            $(this).parent().append("<p class='validation-error'>کد پیگیری الزامی است !</p>");
        } else{
            $(this).removeClass("has-error");
            $(this).parent().find(".validation-error").eq(0).remove();
        }
    });
    $("#paidPrice").on("blur input",function (e) {
        if ($(this).val() === ""){
            $(this).addClass("has-error");
            $(this).parent().find(".validation-error").eq(0).remove();
            $(this).parent().append("<p class='validation-error'>باید یک مبلغ وارد کنید!</p>");
        } else{
            $(this).removeClass("has-error");
            $(this).parent().find(".validation-error").eq(0).remove();
        }
    });
    $("#paymentModalForm").on("submit",function(e){
        e.preventDefault();
        var validationIsGood=true;
        var refId=$("#refId");
        var paidPrice=$("#paidPrice");
        if (refId.val() === ""){
            refId.addClass("has-error");
            refId.parent().find(".validation-error").eq(0).remove();
            refId.parent().append("<p class='validation-error'>کد پیگیری الزامی است !</p>");
            validationIsGood=false;
        }else{
            refId.removeClass("has-error");
            refId.parent().find(".validation-error").eq(0).remove();
        }

        if (paidPrice.val() === ""){
            paidPrice.addClass("has-error");
            paidPrice.parent().find(".validation-error").eq(0).remove();
            paidPrice.parent().append("<p class='validation-error'>باید یک مبلغ وارد کنید!</p>");
            validationIsGood=false;
        }else{
            paidPrice.removeClass("has-error");
            paidPrice.parent().find(".validation-error").eq(0).remove();
        }
        if (validationIsGood){
            var dates=formatDates();
            $.ajax({
                type:"POST",
                url:$(this).attr("action"),
                data:{
                    request_id:$("#reqId").val(),
                    refer_code:refId.val(),
                    amount:paidPrice.val(),
                    payment_date:dates.dateEnglish,
                    payment_date_persian:dates.persianDate
                },
                success:function (data) {
                    if (data.status) {
                        Swal.fire({
                            title: 'موفق !',
                            text: "اطلاعات با موفقیت ذخیره شد !",
                            type: 'success',
                            confirmButtonColor: '#3085d6',
                            cancelButtonColor: '#d33',
                            confirmButtonText: 'باشه'
                        }).then(function(result){
                            if (result.value) {
                                applyFilters(window.location.search);
                            }
                        })
                    } else {
                        console.log(data.message);
                        Swal.fire('خطا !', "خطایی در ذخیره اطلاعات رخ داد !", 'error');
                    }
                }
            });
        }

    });

})
