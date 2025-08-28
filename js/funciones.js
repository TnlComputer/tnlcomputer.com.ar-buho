const d = document;
const $selectPrimary = d.getElementById("select-primary");
const $productosSecundary = d.getElementById("productos-secundary");

function loadRubros() {
  fetch("./php/api-select.php")
    .then((res) => (res.ok ? res.json() : Promise.reject(res)))
    .then((json) => {
      console.log(json);
      let $options = `<option value="">Elige un Rubro</option>`;
      json.forEach(
        (el) =>
          ($options += `<option value = "${el.rubro}">${el.rubro}</option>`)
      );
      $selectPrimary.innerHTML = $options;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $selectPrimary.nextElementSibling.innerHTML = `${err.status}:${message}`;
    });
}

function loadProductos(rubro) {
  let url = "./php/api-productos.php";
  let jsonp = fetch(`./php/api-productos.php?rubro=${rubro}`)
    .then((res) => (res.ok ? res.json() : Promise.reject(res)))
    .then((jsonp) => {
      let $template = "";
      $template += `<div class="card__titulo">          
            <p>${rubro} </p>
          </div>`;
      jsonp.forEach(
        (ele) =>
          ($template += `
          <div class="card__items">          
          <figure>
              <img src="./assets/img/webp/productos/${ele.imagen}" alt="imagen del producto" width="319" height="213">
            </figure>
            <p>${ele.detalle} </p>
            <div class="card__stock-precio">
              <span class="card__stock"> STOCK:  ${ele.stock} </span>          
              <span class="card__precio">$ ${ele.preciogp} </span>          
            </div>
                        <div>
            <form action="">
            <input type="hidden" name="${ele.codigo}" />
            <input type="submit" value="COMPRAR" class="card__comprar" />
            </form>
            </div>

          </div>`)
      );
      $productosSecundary.innerHTML = $template;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $productosSecundary.nextElementSibling.innerHTML = `${err.status}:${message}`;
    });
}

d.addEventListener("DOMContentLoaded", loadRubros);
$selectPrimary.addEventListener("change", (e) => loadProductos(e.target.value));
