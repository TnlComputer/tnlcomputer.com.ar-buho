const d = document,
  $oferHome = d.getElementById("ofertas-home");
// comprar = d.querySelector("form");
// comprar = d.getElementById("compraProd");
const ls = localStorage;
let leoCarrito = ls.getItem("proCar");

function loadOfertas() {
  fetch("./php/api-ofertas.php")
    .then((res) => (res.ok ? res.json() : Promise.reject(res)))
    .then((json) => {
      // console.log(json);
      let $template = "";
      // let $template;
      json.forEach(
        (ele) =>
          // ($template += `<a href="${ele.codigo}">
          ($template += `
          <div class="card__box">
            <figure>
              <img src="./assets/img/webp/productos/${ele.imagen}" alt="imagen del producto" width="319" height="213">
            </figure>
            <div class="card__items">
              <p>${ele.detalle} </p>
            </div>
            <div class="card__stock-precio">
              <span class="card__stock"> STOCK:  ${ele.stock} </span>          
              <span class="card__precio">$ ${ele.preciogp} </span>          
            </div>
            <div>
              <button value="${ele.codigo}" class="card__comprar" data-id="${ele.id_prod}" >COMPRAR</button>
            </div>
          </div>
            `)
      );
      // <form action="producto.html">
      // <input type="hidden" value="${ele.codigo}" />
      // </form>
      // <figcaption class="card__figcaption">
      // </figcaption>
      $oferHome.innerHTML = $template;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $oferHome.nextElementSibling.innerHTML = `${err.status}:${message}`;
    });
}

if (ls.getItem("proCar") === null) {
  var codPtos = [];

  // console.log("paso-1");
} else {
  var codPtos = leoCarrito.split(",");
  // console.log("paso-2");
}

d.addEventListener("click", (e) => {
  if (e.target.matches("button")) {
    e.preventDefault();
    codPtos.push(e.target.dataset.id);
    ls.setItem("proCar", codPtos);
    alert("Proximamente...");
    e.stopPropagation();
    // console.log(e.target.value);
    // loadProductos(e.target.value);
  }
});

// d.addEventListener("DOMContentLoaded", loadOfertas);
loadOfertas();
