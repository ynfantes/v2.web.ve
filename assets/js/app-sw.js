var url = window.location.href;
var swLocation = '/sw.js';

// if (navigator.serviceWorker) {
    
//     navigator.serviceWorker.register(swLocation);
//     navigator.serviceWorker.ready.then(() => {
//         console.log('Service Worker is ready');
//     });
// }
if (navigator.serviceWorker) {
    if (url.includes('local')) {
        
         swLocation  = '/v2.web.ve/sw.js';
    }
    navigator.serviceWorker.register(swLocation).then(registration => {
        console.log('Service Worker registrado:', registration);

        // Escuchar cambios en el estado del Service Worker
        if (registration.waiting) {
            console.log('Nuevo Service Worker en espera.');
            registration.waiting.postMessage('skipWaiting');
        }

        navigator.serviceWorker.addEventListener('controllerchange', () => {
            console.log('Nuevo Service Worker activado.');
            // Recargar la página para aplicar los cambios
            window.location.reload();
        });
    });
}