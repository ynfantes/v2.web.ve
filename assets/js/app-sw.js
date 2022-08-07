var url = window.location.href;
var swLocation = '/sw.js';

if (navigator.serviceWorker) {
    if (url.includes('localhost')) {
        
        swLocation  = '/v2.web.ve/sw.js';
    }
    navigator.serviceWorker.register(swLocation);
}