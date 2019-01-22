
document.addEventListener('DOMContentLoaded', function (e) {




    FilePond.registerPlugin(

        // encodes the file as base64 data
        FilePondPluginFileEncode,

        // validates the size of the file
        FilePondPluginFileValidateSize,

    );

    // Select the file input and use create() to turn it into a pond
    let userPhoto=FilePond.create(
        document.querySelector('#user_photo')
    );
    userPhoto.setOptions({
        // instantUpload: false,
        server: {
            url: '/',
            process: {
                url: 'upload-employee-photo',
                onload: function (response) {
                    select("#user_photo_uploaded").value=response;
                    return response.data;
                },
                onerror: function (response) {
                    console.log(response);
                    return response.data;
                }
            }
        }
    });


    let meliCard=FilePond.create(
        document.querySelector('#meli_card_photo')
    );
    meliCard.setOptions({
        // instantUpload: false,
        server: {
            url: '/',
            process: {
                url: 'upload-employee-melicard',
                onload: function (response) {
                    select("#melicard_photo_uploaded").value=response;
                    return response.key;
                },
                onerror: function (response) {
                    console.log(response);
                    return response.data;
                }
            }
        }
    });

});

addListener("#reload-captcha","click",function(e){
    e.preventDefault();
    
    fetch("/new-captcha")
        .then(function(res){
            return res.json();
        })
        .then(function(res){
            console.log(res);
            select("#captcha").setAttribute("src",res.captcha);
        });
});