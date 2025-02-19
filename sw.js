// imports
importScripts('assets/js/sw-utils.js');

const STATIC_CACHE      = 'static-v1.0.9';
const DYNAMIC_CACHE     = 'dynamic-v1.0.9';
const INMUTABLE_CACHE   = 'inmutable-v1.0.6';
const API_CACHE         = 'cache-data';

/* corazon de la aplicacion
que puede ser modificado
*/
const APP_SHELL = [
    '/',
    'index.php',
    'assets/images/_smarty/logo_dark.png',
    'assets/css/essentials.css',
    'assets/css/layout.css',
    'assets/css/thematics-restaurant.css',
    'assets/css/header-1.css',
    'assets/css/color_scheme/green.css',
    'assets/images/icons/favicon.png',
    'assets/js/scripts.js',
    'assets/js/app-sw.js',
    'assets/js/sw-utils.js',
    'assets/images/icons/launcher48x48.png',
    'assets/images/icons/launcher72x72.png',
    'assets/images/icons/launcher96x96.png',
    'assets/images/icons/launcher144x144.png',
    'assets/images/icons/launcher168x168.png',
    'assets/images/icons/launcher192x192.png',
    'assets/images/icons/launcher512x512.png'
];

const APP_SHELL_INMUTABLE = [
    'assets/plugins/bootstrap/js/bootstrap.min.js',
    'assets/plugins/jquery/jquery-3.3.1.min.js',
    'assets/plugins/form.validate/jquery.form.min.js',
    'assets/plugins/form.validate/jquery.validation.min.js',
    'assets/js/fileinput.min.js',
    'assets/fonts/fontawesome-webfont.woff2?v=4.7.0',
    'assets/fonts/glyphicons-halflings-regular.woff2',
    'https://fonts.gstatic.com/s/kaushanscript/v9/vm8vdRfvXFLG3OLnsO15WYS5DG74wNJVMJ8b.woff2',
    'https://fonts.gstatic.com/s/kaushanscript/v9/vm8vdRfvXFLG3OLnsO15WYS5DG72wNJVMJ8br5Y.woff2',
    'https://fonts.gstatic.com/s/opensans/v18/mem5YaGs126MiZpBA-UNirkOUuhpKKSTjw.woff2',
    'https://fonts.gstatic.com/s/opensans/v18/mem8YaGs126MiZpBA-UFVZ0bf8pkAg.woff2',
    'https://fonts.googleapis.com/css?family=Open+Sans:300,400,600%7CRaleway:300,400,500,600,700%7CLato:300,400,400italic,600,700',
    'https://fonts.googleapis.com/css?family=Kaushan+Script',
    'assets/plugins/bootstrap/css/bootstrap.min.css',
    'assets/plugins/slider.revolution.v5/css/pack.css',
    'assets/plugins/slider.revolution.v5/js/jquery.themepunch.tools.min.js',
    'assets/plugins/slider.revolution.v5/js/jquery.themepunch.revolution.min.js',
    'assets/plugins/smoothscroll.js'
    
];


self.addEventListener('install', e => {
    
    self.skipWaiting();
    
    const cacheStatic = caches.open(STATIC_CACHE)
        .then(cache => cache.addAll(APP_SHELL));

    const cacheInmutable = caches.open(INMUTABLE_CACHE)
        .then(cache => cache.addAll(APP_SHELL_INMUTABLE));

    e.waitUntil(Promise.all([cacheStatic, cacheInmutable]));


});


self.addEventListener('activate', e => {

    const respuesta = caches.keys().then(keys => {

        keys.forEach(key => {

            if (key !== STATIC_CACHE && key.includes('static')) {

                return caches.delete(key);

            }
            if (key !== DYNAMIC_CACHE && key.includes('dynamic')) {

                return caches.delete(key);

            }
        });
    });

    e.waitUntil(respuesta);
});


self.addEventListener('fetch', e => {
    let cacheName;
    if (e.request.url.includes('chrome-extension') 
        || e.request.url.includes('/enlinea')
        || e.request.method == 'POST') {
        return e;
    }
    const respuesta = caches.match(e.request)
        .then(resp => {

            if (resp) {

                if (!APP_SHELL_INMUTABLE.includes(e.request.url)) {

                    if (e.request.url.includes('/enlinea')) {
                        return e;    
                    }
                    return actualizaCache(STATIC_CACHE, e.request, resp);
                }

                return resp;

            } else {
                return fetch(e.request).then(newResp => {
                    
                    cacheName = e.request.url.includes('/enlinea') ? API_CACHE : DYNAMIC_CACHE;
                    return actualizaCache(cacheName, e.request, newResp);

                });
            }

        }).catch(err => {
            console.log('Sin conexión: ',err);
        });
    e.respondWith(respuesta);
});

self.addEventListener('controllerchange', () => {
    window.location.reload();
});