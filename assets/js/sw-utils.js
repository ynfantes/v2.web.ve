
// guarda en el cache 
function actualizaCache(cacheName, req, res) {

    if (res.ok && req.method ==='GET') {

        return caches.open(cacheName).then( cache => {

            cache.put( req, res.clone() );
            return res.clone();

        });
        
    } else {
        return res;
    }
}

function manejoLlamadasAlBack(cacheName, req) {

    return fetch(req).then( res => {

        if (res.ok) {
            actualizaCache(cacheName, req, res.clone());
            return res.clone();
        } else {
            return caches.match( req );
        }
    }).catch( err => {

        return caches.match( req );

    });

}