const d = document,
  w = window,
  $cardRubros = d.querySelector(".card__rubros"),
  // $links = d.getElementById("rubro"),
  $productos = d.getElementById("productos");

function loadRubros() {
  fetch("./php/api-select.php")
    .then((res) => (res.ok ? res.json() : Promise.reject(res)))
    .then((json) => {
      let $template = ``;
      json.forEach(
        (el) =>
          ($template += `
          <div class="boton__nav-pccomp">
          <button type="submit" value="${el.rubro}"  aria-label="Rubro ${el.rubro}">
          ${el.rubro}
          </button>
          </div>
      `)
      );
      $cardRubros.innerHTML = $template;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $cardRubros.innerHTML = `${err.status}:${message}`;
    });
}

function loadProductos(rubro) {
  // let url = "./php/api-productos.php";
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
      $productos.innerHTML = $template;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $productos.nextElementSibling.innerHTML = `${err.status}:${message}`;
    });
}

// d.addEventListener("DOMContentLoaded", loadRubros);
loadRubros();
// $cardNav.addEventListener("change", (e) => loadProductos(e.target.value));

d.addEventListener("click", (e) => {
  if (e.target.matches("button")) {
    e.preventDefault();
    // console.log(e.target.value);
    loadProductos(e.target.value);
  }
});

function loadPC(rubro) {
  fetch("./php/api-pc.php")
    .then((res) => (res.ok ? res.json() : Promise.reject(res)))
    .then((json) => {
      let $template = "";
      $template += `<div class="card__titulo">          
            <p>${rubro} </p>
          </div>`;
      json.forEach(
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
      $productos.innerHTML = $template;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $productos.innerHTML = `${err.status}:${message}`;
    });
}
loadPC("PC ARMADA");

// function scrollTopButton(btn) {
//   const $scrollBtn = d.querySelector(btn);

//   w.addEventListener("scroll", (e) => {
//     let scrollTop = w.pageYOffset || d.documentElement.scrollTop;

//     if (scrollTop > 600) {
//       $scrollBtn.classList.remove("hidden");
//     } else {
//       $scrollBtn.classList.add("hidden");
//     }
//   });
// }
