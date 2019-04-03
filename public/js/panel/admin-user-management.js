//handle lightbox
//show the translator avatar in large mode
// Get the modal
function handleImgClick(e){
    modal.style.display = "block";
    console.log(e);
    let image=e.currentTarget.src;
    modalImg.src=image;
    captionText.innerHTML = e.currentTarget.getAttribute("alt");
}
let modal = document.getElementById('lightboxModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var modalImg = document.getElementById("lightboxPhoto");
var captionText = document.getElementById("lightboxCaption");
$(document).on("click",".lightbox-enabled",handleImgClick);
// Get the <span> element that closes the modal
var span = document.getElementById("closeModal");
// When the user clicks on <span> (x), close the modal
span.addEventListener("click",function(e){
    modal.style.display = "none";
});
$(document).ready(function () {
    //START this functions gets called when a checkbox state changes

    $(".table-filter input[type=checkbox]").on("change", function(e) {
        let queryStringObj = { translator_active_state: [], user_active_state: [],employ_state:[] };
        $(".table-filter input[type=checkbox]").each(function() {
            if ($(this).is(":checked")) {
                queryStringObj[$(this).attr("name")].push($(this).val());
            }
        });
        if (queryStringObj.translator_active_state.length > 0) {
            queryStringObj.translator_active_state = queryStringObj.translator_active_state.join(",");
        }else{
            delete queryStringObj.translator_active_state;
        }
        if (queryStringObj.user_active_state.length > 0) {
            queryStringObj.user_active_state = queryStringObj.user_active_state.join(",");
        }else{
            delete queryStringObj.user_active_state;
        }
        if (queryStringObj.employ_state.length > 0) {
            queryStringObj.employ_state = queryStringObj.employ_state.join(",");
        }else{
            delete queryStringObj.employ_state;
        }
        let cPage = getQueryString("c_page");
        if (cPage) {
            queryStringObj["c_page"] = cPage;
        }
        let tPage = getQueryString("t_page");
        if (tPage) {
            queryStringObj["t_page"] = tPage;
        }
        let queryString = serializeQSObject(queryStringObj);
        applyFilters(queryString);
    });

    //END this functions gets called when a checkbox state changes

});


//this function shows pagination
function showPagination(count,current_page,offset,baseUrl, queryString,visibleNumbers,el,prefix,noHref) {
    let output="";
    let fullUrl;
    if(el===undefined) el=".pagination";
    if(prefix === undefined || prefix === false) prefix="";
    noHref=noHref===undefined ? false:noHref;
    if(queryString){
        fullUrl=baseUrl+"?"+queryString+"&"+prefix+"page=%page%";
    }else{
        fullUrl=baseUrl+"?"+prefix+"page=%page%";
    }
    if(count > offset){
        let lastPage=Math.ceil(count/offset);
        let endIndex,startIndex;
        if (current_page > 1){
            output+= noHref ? '<li class="page-item"><a class="page-link" data-page="1" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li><li class="page-item"><a class="page-link" data-page="'+(current_page-1)+'" aria-label="Previous">قبلی</a></li>':'<li class="page-item"><a class="page-link" href="'+baseUrl+'" aria-label="Previous"><span aria-hidden="true">&laquo;</span></a></li><li class="page-item"><a class="page-link" href="'+substitute(fullUrl,{"%page%":current_page-1})+'" aria-label="Previous">قبلی</a></li>';
        }
        if((current_page+(visibleNumbers-1)) > lastPage){
            endIndex=lastPage;
            startIndex=current_page-(visibleNumbers-(lastPage-current_page));
        }else{

            startIndex=current_page - (visibleNumbers-1);
            endIndex=current_page+ (visibleNumbers-1);
        }
        startIndex= startIndex<=0 ? 1:startIndex;
        for(pageNumber=startIndex;pageNumber<=endIndex;pageNumber++){
            output+= pageNumber==current_page ? "<li class='page-item active'>":"<li class='page-item'>";
            output+=noHref ? "<a class='page-link' data-page='"+pageNumber+"'>"+pageNumber+"</a>":"<a class='page-link' href='"+substitute(fullUrl,{"%page%":pageNumber})+"'>"+pageNumber+"</a>";
        }
        if(current_page != lastPage){
            output+=noHref ? '<li class="page-item"><a class="page-link" data-page="'+(current_page+1)+'" aria-label="Previous">بعدی</a></li>':'<li class="page-item"><a class="page-link" href="'+substitute(fullUrl,{"%page%":current_page+1})+'" aria-label="Previous">بعدی</a></li>';
            output+=noHref ? '<li class="page-item"><a class="page-link" data-page="'+lastPage+'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>':'<li class="page-item"><a class="page-link" href="'+substitute(fullUrl,{"%page%":lastPage})+'" aria-label="Next"><span aria-hidden="true">&raquo;</span></a></li>';
        }
    }
    $(el).html(output);
}

function showTranslators(translators) {
    let output="";
    translators.forEach(function (user) {
        output+=`
           <tr>
                <td data-label="تصویر کاربر">
                    <img class="avatar-in-table lightbox-enabled" src="/public/uploads/avatars/user/${user.avatar}" alt="">
                </td>
                <td data-label="نام کاربر">
                    ${user.fname + " " + user.lname}
                </td>
                <td data-label="نام کاربری">
                    ${user.username}
                </td>
                <td data-label="وضعیت حساب کاربری">
                    ${ user.is_active=="1" ? "فعال":"غیر فعال" }
                </td>
                <td data-label="وضعیت استخدام">
                     ${ user.is_employed=="1" ? "استخدام شده":"استخدام نشده" }
                 </td>
                <td data-label="تاریخ ثبت نام">
                    ${ user.register_date_persian }
                </td>
                <td data-label="عملیات" class="order-actions">
                    <button onclick="showTranslatorInfo('${ user.user_id  }')" class="expand-button order-action is--primary is--medium">
                        <span data-hover="جزییات بیشتر">
                            <i class="icon-info"></i>
                        </span>
                    </button>
                    <button onclick="deactivateTranslatorAccount('${ user.user_id  }')" class="expand-button order-action is--default is--large">
                        <span data-hover="غیر فعال کردن حساب">
                            <i class="icon-lock"></i>
                        </span>
                    </button>
                    <button onclick="deleteTranslator('${ user.user_id  }')" class="expand-button order-action is--danger">
                        <span data-hover="حذف کاربر">
                            <i class="icon-user-unfollow"></i>
                        </span>
                    </button>

                </td>
                </td>
            </tr>
        `;
    })
    $("#translatorsWrap").html(output);
}

function showUsers(users) {
    let output="";
    users.forEach(function (user) {
        output+=`
            <tr>
                <td data-label="تصویر کاربر">
                    <img class="avatar-in-table lightbox-enabled" src="/public/uploads/avatars/user/${user.avatar}" alt="">
                </td>
                <td data-label="نام کاربر">
                    ${user.fname + " " + user.lname}
                </td>
                <td data-label="نام کاربری">
                    ${user.username}
                </td>
                <td data-label="وضعیت حساب کاربری">
                    ${ user.is_active=="1" ? "فعال":"غیر فعال" }
                </td>
                <td data-label="تاریخ ثبت نام">
                    ${ user.register_date_persian }
                </td>
                <td data-label="عملیات" class="order-actions">
                    <button onclick="showUserInfo('${ user.user_id  }')" class="expand-button order-action is--primary is--medium">
                        <span data-hover="جزییات بیشتر">
                            <i class="icon-info"></i>
                        </span>
                    </button>
                    <button onclick="deactivateUserAccount('${ user.user_id  }')" class="expand-button order-action is--default is--large">
                        <span data-hover="غیر فعال کردن حساب">
                            <i class="icon-lock"></i>
                        </span>
                    </button>
                    <button onclick="deleteUser('${ user.user_id  }')" class="expand-button order-action is--danger">
                        <span data-hover="حذف کاربر">
                            <i class="icon-user-unfollow"></i>
                        </span>
                    </button>

                </td>
            </tr>
        `;
    })
    $("#usersWrap").html(output);
}

//this function gets data from server based on given filters
function applyFilters(queryString) {
    let url = location.origin + location.pathname;
    url = queryString ? url + "?" + queryString : url;
    window.history.pushState("filtered users", "کاربران فیلتر شده", url);
    $.get(window.location.origin+window.location.pathname+"/json?" + queryString, function(res, status) {
        if (status) {
            showTranslators(res.translators);
            showUsers(res.customers);
            showPagination(parseInt(res.translators_count),parseInt(res.translator_current_page),10,window.location.origin+window.location.pathname, window.location.search,3,".translator-pagination","t_");
            showPagination(parseInt(res.customerss_count),parseInt(res.customer_current_page),10,window.location.origin+window.location.pathname, window.location.search,3,".customer-pagination","c_");
        }
    });
}
//this fucntion replaces all occurances in string with desired value
String.prototype.replaceAll = function(search, replacement) {
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
        function(key, value) {
            return key === "" ? value : decodeURIComponent(value);
        }
    );
}
function substitute(str, data) {
    let output = str.replace(/%[^%]+%/g, function(match) {
        if (match in data) {
            return(data[match]);
        } else {
            return("");
        }
    });
    return(output);
}

function deactivateUser(userId) {
    Swal.fire({
        title: 'آیا مطمینید ؟',
        text: "می خواهید این ترجمه را انجام بدهید ؟",
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
                url:"/translator/order/request",
                data:{
                    order_number:orderNumber,
                    translator_id:translatorId
                },
                success:function(data,status){

                    if(data.status){
                        Swal.fire(
                            'موفق !',
                            'درخواست شما با موفقیت ثبت شد !',
                            'success'
                        )
                        $.get("/translator/new-orders/json",{page:1,offset:3},function(data,status){
                            if(data.status){
                                renderOrders(data.orders,translatorId,"#newOrdersWrap");
                            }
                        })
                    }else{
                        Swal.fire(
                            'موفق !',
                            'خطایی در ثبت اطلاعات رخ داد !',
                            'error'
                        )
                    }
                }
            })
        }else{

        }
    })
}