
function getRequest(url, callback, isJson) {
    if (isJson === undefined) {
        isJson = true;
    }
    var xmlhttp;
    if (window.XMLHttpRequest) {
        xmlhttp = new XMLHttpRequest();
    } else {
        // code for older browsers
        xmlhttp = new ActiveXObject('Microsoft.XMLHTTP');
    }
    xmlhttp.onreadystatechange = function () {
        if (this.readyState == 4 && this.status == 200) {
            if (isJson) {
                response = JSON.parse(this.responseText);
            }
            callback(response);
        }
    };
    xmlhttp.open('GET', url, true);
    xmlhttp.send();
}
function handleHiringSection() {
    // animate hire translator section

    if (newOffset >= 1000) {
        selectAll('.animation-wrap__item').forEach(function (el) {
            el.classList.add('animate');
        })

    }
}

function handleTelegramAd() {
    if (newOffset >= 820) {
        select('.telegram-ad__image').classList.add('animate');
        select('.telegram-ad__title').classList.add('animate');
        select('.telegram-ad__subtitle').classList.add('animate');
        select('.telegram-ad__content').classList.add('animate');
        selectAll('.telegram-ad__action__button').forEach(function (el) {
            el.classList.add('animate');
        })
    }
}


const homeHandler = function (e) {
    newOffset = e.currentTarget.scrollTop;
    handleHiringSection();
    handleTelegramAd();
};
body.addEventListener('scroll', homeHandler, false);
