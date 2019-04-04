//handle lightbox
//show the translator avatar in large mode
// Get the modal
function handleImgClick(e){
    modal.style.display = "block";
    let image=e.currentTarget.src;
    modalImg.src=image;
    captionText.innerHTML = e.currentTarget.getAttribute("alt");
}
let modal = document.getElementById('lightboxModal');

// Get the image and insert it inside the modal - use its "alt" text as a caption
var images= document.querySelectorAll('.lightbox-enabled');
var modalImg = document.getElementById("lightboxPhoto");
var captionText = document.getElementById("lightboxCaption");
images.forEach(function(img){
    img.addEventListener("click",handleImgClick);
})
// Get the <span> element that closes the modal
var span = document.getElementById("closeModal");
// When the user clicks on <span> (x), close the modal
span.addEventListener("click",function(e){
    modal.style.display = "none";
})


$(document).ready(function () {
    //START this functions gets called when a checkbox state changes
    $(".table-filter input[type=checkbox]").on("change", function(e) {
        let queryStringObj = { done: []};
        $(".table-filter input[type=checkbox]").each(function() {
            if ($(this).is(":checked")) {
                queryStringObj[$(this).attr("name")].push($(this).val());
            }
        });
        if (queryStringObj.done.length > 0) {
            queryStringObj.done = queryStringObj.done.join(",");
        }else{
            delete queryStringObj.done;
        }
        let page = getQueryString("page");
        if (page) {
            queryStringObj["page"] = page;
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


function showOrders(orders) {
    let output="";
    orders.forEach(function (order) {
       output+=`
            <tr>
                <td data-label="شماره سفارش">
                    ${order.order_number}</td>
                <td data-label="تعداد صفحات">
                    ${Math.ceil((order.word_numbers / 250))}</td>
                <td data-label="زبان ترجمه">
                    ${order.translation_lang == "1"? "انگلیسی به فارسی": "فارسی به انگلیسی"}
                </td>
                <td data-label="رشته">
                    ${order.study_field}
                </td>
                <td data-label="کیفیت ترجمه">
                    ${ order.translation_quality == "5" ? "نقره ای" : "طلایی" }
                </td>
                <td data-label="هزینه ترجمه">
                    ${parseFloat(order.order_price).toLocaleString("us")}
                    تومان</td>
                <td data-label="سهم شما">
                    ${ Math.ceil(((order.order_price*15)/100)).toLocaleString("us")}
                    تومان</td>
                <td class="order-more-info" data-label="جزییات">
                    <a href="/admin/order/view/${order.order_number}">
                        <svg height="23px" viewBox="0 0 50 80" width="13px" xml:space="preserve">
                    <polyline fill="none" points="45.63,75.8 0.375,38.087 45.63,0.375 " stroke-linecap="round" stroke-linejoin="round" stroke-width="10" stroke="#a9a9a9"></polyline>
                </svg>
                    </a>
                </td>
            </tr>
       `;
    });
    $("#userOrders").html(output);
}

//this function gets data from server based on given filters
function applyFilters(queryString) {
    let url = location.origin + location.pathname;
    url = queryString ? url + "?" + queryString : url;
    window.history.pushState("filtered orders", "سفارشات فیلتر شده", url);
    $.get(window.location.origin+window.location.pathname+"/json?" + queryString, function(res, status) {
        if (status) {
            showOrders(res.orders);
            showPagination(parseInt(res.count),parseInt(res.current_page),10,window.location.origin+window.location.pathname, window.location.search,3);
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