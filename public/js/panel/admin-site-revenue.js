// this function substitutes placeholders with value given as object
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
let svgLoader = "  <svg width='45' fill='#fff' version='1.1' id='L9' xmlns='http://www.w3.org/2000/svg' xmlns:xlink='http://www.w3.org/1999/xlink'x='0px' y='0px' viewBox='0 0 100 100' enable-background='new 0 0 0 0' xml:space='preserve'><path fill=' #fff' d='M73,50c0-12.7-10.3-23-23-23S27,37.3,27,50 M30.9,50c0-10.5,8.5-19.1,19.1-19.1S69.1,39.5,69.1,50'><animateTransform attributeName='transform' attributeType='XML' type='rotate' dur='1s' from='0 50 50'to='360 50 50' repeatCount='indefinite' /></path></svg>";
let infoCards={
  total_revenue:"#totalRevenue",
  admin_revenue:"#adminRevenue",
  masoud_revenue:"#masoudRevenue",
  translators_revenue:"#translatorsRevenue",
  pending_orders:"#pendingOrders",
  completed_orders:"#completedOrders",
  payment_requests:"#paymentRequests",
  payment_requests_sum:"#paymentRequestsSum",
};
let filterFormData;
$(document).ready(function () {
   var fromDate=$("#fromDate").persianDatepicker({
       initialValue: false,
       timePicker: {
           enabled: true
       },
       altField: '#fromDateAlt',
       altFormat: 'YYYY/MM/DD hh:mm',
   });
   // fromDate.setDate(new persianDate().subtract('month', 1).valueOf());
    $("#toDate").persianDatepicker({
        timePicker: {
            enabled: true
        },
        altField: '#toDateAlt',
        altFormat: 'YYYY/MM/DD hh:mm',
    });

    //handle filter form submission
    $("#infoFilterForm").on("submit",function (e) {
        e.preventDefault();
        addLoaderToCards();
        filterFormData=$(this).serialize();
        setTimeout(()=>{
            $.ajax({
                type: "POST",
                url: $(this).attr("action"),
                data: filterFormData,
                success: function (data, status) {
                    for (let key in infoCards){
                        $(infoCards[key]).html(data.info[key]);
                    }
                    showOrders(data.info.filtered_orders);
                    showPagination(data.info.filtered_orders_count,data.info.current_page,10,"/admin/site-revenue", "",3,".pagination",false,true);
                }
            });
        },800);
    });
    $(document).on("click",".orders_pagination .page-link",function (e) {
        $.ajax({
            type: "POST",
            url: "/admin/site-revenue/filter?page="+$(this).data("page"),
            data: filterFormData,
            success: function (data, status) {
                console.log(data);
                showOrders(data.info.filtered_orders);
                showPagination(parseInt(data.info.filtered_orders_count),parseInt(data.info.current_page),10,"/admin/site-revenue", "",3,".orders_pagination",false,true);
            }
        });
    })
    $(document).on("click",".requests_pagination .page-link",function (e) {
        $.ajax({
            type: "POST",
            url: "/admin/site-revenue/filter?request_page="+$(this).data("page"),
            data: filterFormData,
            success: function (data, status) {
                console.log(data);
                showRequests(data.info.filtered_payment_requests);
                showPagination(parseInt(data.info.payment_requests),parseInt(data.info.requests_current_page),10,"/admin/site-revenue", "",3,".requests_pagination",false,true);
            }
        });
    })
});

function addLoaderToCards() {
    for (let key in infoCards){
        $(infoCards[key]).html(svgLoader);
    }
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
            var output = "\n  <div class=\"translator-info no-border\">\n    <div class=\"translator-info__avatar\">\n        <img alt=\"\" src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\"></div>\n    <div class=\"translator-info__info\">\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0646\u0627\u0645 \u0645\u062A\u0631\u062C\u0645 :\u200C\n            </label>\n            <strong>").concat(data.info.fname + " " + data.info.lname, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0645\u062F\u0631\u06A9 \u062A\u062D\u0635\u06CC\u0644\u06CC</label>\n            <strong>").concat(data.info.degree, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0641\u0627\u0631\u0633\u06CC \u0628\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC</label>\n            <strong>").concat(data.info.fa_to_en == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u062A\u0631\u062C\u0645\u0647 \u0627\u0646\u06AF\u0644\u06CC\u0633\u06CC \u0628\u0647 \u0641\u0627\u0631\u0633\u06CC</label>\n            <strong>").concat(data.info.en_to_fa == "1" ? "بله" : "خیر", "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n            </label>\n            <strong>").concat(data.info.email, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A</label>\n            <strong>").concat(data.info.phone, "</strong>\n        </div>\n        <div class=\"translator-info__info__item\">\n            <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n            <strong>").concat(data.info.cell_phone, "</strong>\n        </div>\n    </div>\n</div>        \n");
            $("#translatorBasicInfoWrap").html(output);
            $("#translatorBasicInfo").modal("show");
            console.log(output);
        }
    })
}
function showOrdererInfo(ordererId){
    $.get("/admin/order/orderer-info/"+ordererId,function(data,status){
        if(data.status){
            $("#InfoLabel").text("اطلاعات مشتری");
            //i coded this with template literal but because of low browser support , i converted the code
            //for debugging you have to convert it to es6 with babel.io
            //unfortunately it converted persian to utf :( you have to convert it to text
            //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
            var output="\n<div class=\"user-info\">\n   <div class=\"user-info__avatar\">\n      <img src=\"/public/uploads/avatars/user/".concat(data.info.avatar, "\" alt=\"").concat(data.info.fname + ' ' + data.info.lname, "\" id=\"user-avatar\" >\n   </div>\n   <div class=\"user-info__info\">\n      <div class=\"user-info__info__item\">\n         <label for=\"\">\u0646\u0627\u0645 \u06A9\u0627\u0631\u0628\u0631 :\u200C\n         </label>\n         <strong>").concat(data.info.fname + ' ' + data.info.lname, "</strong>\n      </div>\n      <div class=\"user-info__info__item\">\n         <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n         </label>\n         <strong>").concat(data.info.email, "</strong>\n      </div>\n      <div class=\"user-info__info__item\">\n         <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n         <strong>").concat(data.info.phone, "</strong>\n      </div>\n   </div>\n</div>\n</div>\n");
            $("#ordererInfoWrap").html(output);
            $("#ordererInfo").modal("show");
        }else{
            console.error(data.message);
        }
    });
}

function showOrders(orders) {
    let output="";
    orders.forEach(function (order) {
        output+=`
            <tr>
               <td data-label="شماره سفارش">
                  ${order.order_number}
               </td>
               <td data-label="سفارش دهنده">
                  <a aria-role="button" href="javascript:void(0)" onclick="showOrdererInfo('${order.orderer_id}')" >${order.orderer_fname + " " + order.orderer_lname}</a>
               </td>
               <td data-label="نام مترجم">
                  <a aria-role="button" href="javascript:void(0)" onclick="showTranslatorInfo('${order.translator_id}')">${order.translator_fname + " " + order.translator_lname}</a>
               </td>
               <td data-label="هزینه ترجمه">
                  ${ parseInt(order.order_price).toLocaleString("us") } تومان
               </td>
               <td data-label="سهم مترجم">
                  ${ parseInt(Math.ceil((order.order_price*70)/100)).toLocaleString("us")} تومان
               </td>
               <td data-label="سهم شما">
                  ${ parseInt(Math.ceil((order.order_price*15)/100)).toLocaleString("us")} تومان
               </td>
               <td data-label="سهم مسعود">
                  ${ parseInt(Math.ceil((order.order_price*15)/100)).toLocaleString("us")} تومان
               </td>
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
    $("#filteredOrders").html(output);
}
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
function showRequests(requests) {
    let output="";
    let state="";
    requests.forEach(function (request) {
        if (request.state == "-1" ) state="درانتظار تایید";
        else if (request.state == "0") state="رد شده";
        else if (request.state == "1") state="تایید شده";
        output+=`
            <tr>
               <td data-label="ردیف">
                  ${request.id}
               </td>
               <td data-label="نام مترجم">
                  <a aria-role="button" href="javascript:void(0)" onclick="showTranslatorInfo('${request.translator_id}')">${request.translator_fname + " " + request.translator_lname}</a>
               </td>
               <td data-label="مبلغ درخواستی">
                  ${parseInt(request.amount).toLocaleString("us")}
                  تومان
               </td>
               <td data-label="تاریخ درخواست">
                  ${request.request_date_persian}
               </td>
               <td data-label="وضعیت تایید">
                ${state}
               </td>
               <td data-label="وضعیت پرداخت">
                  ${request.is_paid == "1" ? "پرداخت شده":"پرداخت نشده"}
               </td>
               <td class="order-more-info" data-label="جزییات">
                  <a href="/admin/translator/payment-requests">
                     <svg height="23px" viewBox="0 0 50 80" width="13px" xml:space="preserve">
                        <polyline fill="none" points="45.63,75.8 0.375,38.087 45.63,0.375 " stroke-linecap="round" stroke-linejoin="round" stroke-width="10" stroke="#a9a9a9"></polyline>
                     </svg>
                  </a>
               </td>
            </tr>
        `;
    })
    $("#paymentRequestsWrap").html(output);
}