//this fucntion replaces all occurances in string with desired value
String.prototype.replaceAll = function(search, replacement) {
  var target = this;
  return target.replace(new RegExp(search, "g"), replacement);
};
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
  if (queryString == "?" || queryString == "") return {};
  return JSON.parse(
    '{"' + queryString.replace(/&/g, '","').replace(/=/g, '":"') + '"}',
    function(key, value) {
      return key === "" ? value : decodeURIComponent(value);
    }
  );
}

$("input[name=choice]").on("change", function(e) {
  const value=e.target.value;
  const qsObject=quertStringToObj(window.location.search.replace("?",""));
  qsObject['choice']=value;
  let pageTitle;
  if(value=="new"){
    pageTitle="سفارشات جدید";
  }
  else if(value=="requested"){
    pageTitle="سفارشات درخواستی";
  }
  else{
    pageTitle="سفارشات رد شده";
  }
  const url=window.location.origin+window.location.pathname+"?"+serializeQSObject(qsObject);
  getOrders(window.location.origin+window.location.pathname,qsObject);
  window.history.pushState(value+" orders",pageTitle,url);
});

function getOrders(baseUrl,qsObject){
    $.get(baseUrl+"/json",qsObject,function(data,status){
        if(data.status){
            renderOrders(data.orders,data.translator_id,data.choice);
        }
    })
}


function renderOrders(orders,translatorId,choice){
    console.log(orders);
    let output="";
    $(".newOrderTable thead").css("display","table-header-group");
    for(let index in orders){
        let translationLang=orders[index].translation_lang == "1"? "انگلیسی به فارسی": "فارسی به انگلیسی";
        let translationQuality=orders[index].translation_quality == "5" ? "نقره ای" : "طلایی";
        output+="<tr>";
            output+="<td data-label='شماره سفارش'>"+orders[index].order_id+"</td>";
            output+="<td data-label='تعداد صفحات'>"+Math.ceil(orders[index].word_numbers / 250)+"</td>";
            output+="<td data-label='زبان ترجمه'>"+translationLang+"</td>";
            output+="<td data-label='رشته'>"+orders[index].study_field+"</td>";
            output+="<td data-label='کیفیت ترجمه'>"+translationQuality+"</td>";
            output+="<td data-label='هزینه ترجمه'>"+orders[index].order_price+"</td>";
            output+="<td data-label='سهم شما'>"+Math.ceil((orders[index].order_price*70)/100)+"</td>";
            output+="<td data-label='عملیات' class='order-actions'>";
            if(choice=="new"){
                output+="<button onclick='showOrderInfo(\""+orders[index].order_id+"\")' class='expand-button order-action is--primary is--medium'><span data-hover='جزییات سفارش'><i class='icon-info'></i></span></button>";
                output+="<button onclick='acceptOrder(\""+orders[index].order_id+"\",\""+translatorId+"\")' class='expand-button order-action is--success is--large'><span data-hover='درخواست انجام سفارش'><i class='icon-check'></i></span></button>";
                output+="<button onclick='declineOrder(\""+orders[index].order_id+"\",\""+translatorId+"\")' class='expand-button order-action is--danger'><span data-hover='رد سفارش'><i class='icon-check'></i></span></button>";
            }else if (choice=="requested") {
                output+="<button onclick='showOrderInfo(\""+orders[index].order_id+"\")' class='expand-button order-action is--primary is--medium'><span data-hover='جزییات سفارش'><i class='icon-info'></i></span></button>";
            }else{
                output+="<button onclick='showOrderInfo(\""+orders[index].order_id+"\")' class='expand-button order-action is--primary is--medium'><span data-hover='جزییات سفارش'><i class='icon-info'></i></span></button>";
            }
            output+="</td>";
        output+="</tr>";
    }

    if(output){
        $("h5.no-data").remove();
        $("#newOrdersWrap").html(output);
    }else{
        $(".newOrderTable thead").css("display","none");
        $(".newOrderTable tbody").html("<h5 class='text-center mt-4'>اطلاعاتی دریافت نشد !</h5>");
    }
    
}