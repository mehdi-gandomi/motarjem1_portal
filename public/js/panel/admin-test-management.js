//START this functions gets called when a checkbox state changes

$(".table-filter input[type=checkbox]").on("change", function(e) {
    let queryStringObj = { language: []};
    $("input[name=language]").each(function() {
        if ($(this).is(":checked")) {
            queryStringObj["language"].push($(this).val());
        }
    });
    if (queryStringObj["language"].length > 0) {
        queryStringObj["language"] = queryStringObj["language"].join(",");
    }
    let page = getQueryString("page");
    if (page) {
        queryStringObj["page"] = page;
    }
    queryString = serializeQSObject(queryStringObj);
    applyFilters(queryString);
});

//END this functions gets called when a checkbox state changes
function showTests(tests) {
    let output="";
    tests.forEach(function (test) {
        output+=`
             <tr>
                <td data-label="شماره آزمون">
                    ${test.id}
                </td>
                <td data-label="زبان ترجمه">
                    ${test.language_id == "1" ? "انگلیسی به فارسی":"فارسی به انگلیسی"}
                </td>
                <td data-label="رشته دانشگاهی">
                    ${test.study_field}
                </td>
                <td data-label="عملیات" class="order-actions">
                    <button onclick="showُTestInfo('${test.id}')" class="expand-button order-action is--primary is--medium">
                        <span data-hover="اطلاعات بیشتر">
                            <i class="icon-info"></i>
                        </span>
                    </button>
                    <button onclick="editTest('${test.id}')" class="expand-button order-action is--success is--medium">
                        <span data-hover="ویرایش آزمون">
                            <i class="icon-check"></i>
                        </span>
                    </button>
                    <button onclick="deleteTest('${test.id}')" class="expand-button order-action is--danger">
                        <span data-hover="حذف آزمون">
                            <i class="icon-close"></i>
                        </span>
                    </button>

                </td>

            </tr>
        `;
    });
    $("#testsWrap").html(output);
}

//this function gets data from server based on given filters
function applyFilters(queryString) {
    let url = location.origin + location.pathname;
    url = queryString ? url + "?" + queryString : url;
    window.history.pushState("filtered tests", "آزمون های فیلتر شده", url);
    $.get(window.location.origin+window.location.pathname+"/json?" + queryString, function(res, status) {
        if (status) {
            showTests(res.tests);
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

$(document).ready(function (e) {
   $("#newTestForm").on("submit",function (e) {
       e.preventDefault();
      let text=$("#testText");
      if (text.val() == ""){
          alert("باید حتما یک متن وارد کنید !");
      }else{
          $.ajax({
              url:$(this).attr("action"),
              type:"POST",
              data:$(this).serialize(),
              success:function (data,status) {
                  if (data.status){
                      $("#newTestModal").modal("hide");
                      Swal.fire({
                          title: 'موفق !',
                          text: "آزمون با موفقیت ذخیره شد !",
                          type: 'success',
                          confirmButtonColor: '#3085d6',
                          cancelButtonColor: '#d33',
                          confirmButtonText: 'باشه'
                      }).then(function(result){
                          if (result.value) {
                              window.location.reload();
                          }
                      })
                  } else{
                      console.log(data.message);
                      Swal.fire(
                          'خطا !',
                          'خطایی در ذخیره آزمون رخ داد !',
                          'error'
                      )
                  }
              }
          })
      }
   });
});

function showُTestInfo(testId) {
    $.get("/admin/test/info",{test_id:testId},function (data,status) {
        if (data.status){
            let output="<div class='order-details row'>";
            output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>شماره آزمون</div><div class='order-details__detail__value'>"+data.info.id+"</div></div>";
            output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>زبان ترجمه</div><div class='order-details__detail__value'>"+(data.info.language_id == "1" ? "انگلیسی به فارسی":"فارسی به انگلیسی")+"</div></div>";
            output+="<div class='order-details__detail col-md-4'><div class='order-details__detail__label'>رشته دانشگاهی</div><div class='order-details__detail__value'>"+data.info.study_field+"</div></div>";
            output+="<div class='order-details__detail col-12'><div class='order-details__detail__label'>متن آزمون</div><div class='order-details__detail__value "+(data.info.language_id == "1" ? "ltr":"rtl")+"'>"+data.info.text+"</div></div>";
            output+="</div>";
            $("#testInfoWrap").html(output);
            $("#testInfoModal").modal("show");
        }
    })
}