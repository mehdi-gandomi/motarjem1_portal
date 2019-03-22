function showOrdererInfo(ordererId){
    $.get("/admin/order/orderer-info/"+ordererId,function(data,status){
        if(data.status){
            $("#InfoLabel").text("اطلاعات مشتری");
            //i coded this with template literal but because of low browser support , i converted the code
            //for debugging you have to convert it to es6 with babel.io
            //unfortunately it converted persian to utf :( you have to convert it to text
            //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
            var output="\n<div class=\"user-info\">\n   <div class=\"user-info__avatar\">\n      <img src=\"/public/uploads/avatars/user/".concat(data.info.avatar, "\" alt=\"").concat(data.info.fname + ' ' + data.info.lname, "\" id=\"user-avatar\" >\n   </div>\n   <div class=\"user-info__info\">\n      <div class=\"user-info__info__item\">\n         <label for=\"\">\u0646\u0627\u0645 \u06A9\u0627\u0631\u0628\u0631 :\u200C\n         </label>\n         <strong>").concat(data.info.fname + ' ' + data.info.lname, "</strong>\n      </div>\n      <div class=\"user-info__info__item\">\n         <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n         </label>\n         <strong>").concat(data.info.email, "</strong>\n      </div>\n      <div class=\"user-info__info__item\">\n         <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n         <strong>").concat(data.info.phone, "</strong>\n      </div>\n   </div>\n</div>\n</div>\n");
            $("#infoWrap").html(output);
            $("#infoModal").modal("show");
        }else{
            console.error(data.message);
        }
    });
}
function showTranslatorInfo(translatorId){
    $.get("/admin/order/translator-info/"+translatorId,function(data,status){
        if(data.status){
            $("#InfoLabel").text("اطلاعات مترجم");
            //i coded this with template literal but because of low browser support , i converted the code
            //for debugging you have to convert it to es6 with babel.io
            //unfortunately it converted persian to utf :( you have to convert it to text
            //if you have problem , you can contact me via coderguy1999@gmail.com or @coder_guy in social media
            var output = "\n<div class=\"translator-info\" style='border:0'>\n   <div class=\"translator-info__avatar\">\n      <img alt=\"\" id=\"translator-avatar\" src=\"/public/uploads/avatars/translator/".concat(data.info.avatar, "\">\n   </div>\n   <div class=\"translator-info__info\">\n      <div class=\"translator-info__info__item\">\n         <label for=\"\">\u0646\u0627\u0645 \u0645\u062A\u0631\u062C\u0645 :\u200C\n         </label>\n         <strong>").concat(data.info.fname + ' ' + data.info.lname, "</strong>\n      </div>\n      <div class=\"translator-info__info__item\">\n         <label for=\"\">\u0627\u06CC\u0645\u06CC\u0644 :\n         </label>\n         <strong>").concat(data.info.email, "</strong>\n      </div>\n\t  <div class=\"translator-info__info__item\">\n         <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u062A\u0644\u0641\u0646 \u062B\u0627\u0628\u062A</label>\n         <strong>").concat(data.info.phone, "</strong>\n      </div>\n      <div class=\"translator-info__info__item\">\n         <label for=\"\">\u0634\u0645\u0627\u0631\u0647 \u0645\u0648\u0628\u0627\u06CC\u0644</label>\n         <strong>").concat(data.info.cell_phone, "</strong>\n      </div>\n   </div>\n</div>\n</div>\n");
            $("#infoWrap").html(output);
            $("#infoModal").modal("show");
        }else{
            console.error(data.message);
        }
    });
    
}
