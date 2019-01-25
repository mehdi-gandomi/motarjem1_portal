function showOrders(data){
    let output="";
    if(data.orders.length>0){
        data.orders.forEach(function(order){
            let pageNumber=order.word_numbers/250;
            let delivery_type="";
            let translation_type="";
            let translation_quality="";
            let accepted="";
            if (order.delivery_type == 1){
                delivery_type = "معمولی";
            }else if(order.delivery_type == 2){
                delivery_type = "نیمه فوری";
            }else if(order.delivery_type == 3){
                delivery_type = "فوری";
            }
            
            if(order.translation_type==1){
                translation_type="فارسی به انگلیسی";
            }else{
                translation_type="انگلیسی به فارسی";
            }

            if(order.translation_quality==5){
                translation_quality="نقره ای";
            }else if(order.translation_quality==10){
                translation_quality="طلایی";
            }

            if(order.accepted==0){
                accepted="تایید نشده";
            }else{
                accepted="تایید شده";
            }
            output+="<tr>";
            output+="<td>"+order.order_id+"</td>";
            output+="<td>"+pageNumber+"</td>";
            output+="<td>"+translation_type+"</td>";
            output+="<td>"+translation_quality+"</td>";
            output+="<td>"+delivery_type+"</td>";
            output+="<td>"+order.order_price+"</td>";
            output+="<td>"+accepted+"</td>";
            
            output+="<td><a style='cursor:pointer;color:#20a8d8' onclick='showTranslatorInfo(\""+order.translator_id+"\")'>"+order.translator_fname+" "+order.translator_lname+"</a></td>";
            output+="<td><a href='/user/order/view/"+order.order_id+"' class='btn btn-primary'>مشاهده سفارش</a></td>";
            
        })
    }
    document.getElementById("user-orders").innerHTML=output;
}

$("#pending-orders").change(function (e) {
    let newLocation = "";
    let currentLocation = window.location.href;
    let jsonUrl="/user/orders/json";
    if (e.currentTarget.checked) {
        newLocation = currentLocation.replace(/(\?|&)pending=true/, "");
        if (currentLocation.indexOf("?") === -1) {
            newLocation = currentLocation += "?pending=true";
        } else {
            newLocation = currentLocation += "&pending=true";
        }
        if (document.getElementById("completed-orders").checked){
            jsonUrl+="?pending=true&completed=true";
        }else{
            jsonUrl+="?pending=true";
        }
    } else {
        newLocation = currentLocation.replace(/(\?|&)pending=true/, "");
        if (document.getElementById("completed-orders").checked){
            jsonUrl+="?completed=true";
            newLocation = currentLocation.replace(/(\?|&)completed=true/, "");
            newLocation+="?completed=true";
        }
        
        
    }
   
    $.get(jsonUrl,function(res,status){
        if(status){
            showOrders(res);
        }
    })
    window.history.pushState("pending orders", "سفارشات درحال انجام", newLocation);
});
$("#completed-orders").change(function (e) {
    let newLocation = "";
    let currentLocation = window.location.href;
    let jsonUrl="/user/orders/json";
    if (e.currentTarget.checked) {
        newLocation = currentLocation.replace(/(\?|&)completed=true/, "");
        if (currentLocation.indexOf("?") === -1) {
            
            newLocation = currentLocation += "?completed=true";
        } else {
            newLocation = currentLocation += "&completed=true";
        }
        
        if (document.getElementById("pending-orders").checked){
            jsonUrl+="?pending=true&completed=true";
        }else{
            jsonUrl+="?completed=true";
        }
        console.log(jsonUrl);
        $.get(jsonUrl,function(res,status){
            if(status){
                
                showOrders(res);
            }
        })
    } else {
        newLocation = currentLocation.replace(/(\?|&)completed=true/, "");
        
        if (document.getElementById("pending-orders").checked){
            jsonUrl+="?pending=true";
            newLocation = currentLocation.replace(/(\?|&)pending=true/, "");
            newLocation+="?pending=true";
        }
        
        
    }

    window.history.pushState("completed orders", "سفارشات تکمیل شده", newLocation);
});

