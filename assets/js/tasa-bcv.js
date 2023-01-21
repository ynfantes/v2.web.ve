fetch('http://bcv.pronet21.net/')
.then((response) => response.json())
.then((data) => {
    const element = document.getElementById('tasa_del_dia');
    const element2 = document.getElementById('tasa_del_dia2');
    element.innerHTML = data.usd;
    element2.innerHTML = data.usd;
    console.log(data);
});