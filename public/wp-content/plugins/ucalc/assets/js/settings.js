var XD = function () {
    var interval_id,
        last_hash,
        cache_bust = 1,
        attached_callback,
        window = this;
    return {
        postMessage: function (message, target_url, target) {
            if (!target_url) {
                return;
            }
            target = target || parent;
            if (window['postMessage']) {
                target['postMessage'](message, target_url.replace(/([^:]+:\/\/[^\/]+).*/, '$1'));
            } else if (target_url) {
                target.location = target_url.replace(/#.*$/, '') + '#' + (+new Date) + (cache_bust++) + '&' + message;
            }
        }
    };
}();

function crossdomainCallback(event) {
    event.data.ucalc && event.data.hash && setHash(event.data.hash);
}

if (window.addEventListener) {
    window.addEventListener("message", crossdomainCallback, false);
} else {
    window.attachEvent("onmessage", crossdomainCallback);
}

function getHash() {
    var lang = document.documentElement.lang.substr(0, 2);
    window.open('https://ucalc.pro/integration-code?lng='+lang, 'getHash', "width=550, height=550, top=" + ((screen.height - 550) / 2) + ',left=' + ((screen.width - 550) / 2));
}
function setHash(hash) {
    document.getElementById("ucalc_hash_wp").value = hash;
    document.forms.setoptions.submit.click();
}
function delHash() {
    document.getElementById("ucalc_hash_wp").value = "";
    document.forms.setoptions.submit.click();
}/**
 * Created by alex on 02.03.17.
 */
