const d = document;

$producto = d.getElementById("card-producto");
$codigo = d.getElementById("inputProd");
// $codigo = d.querySelector("input");
// codigo = d.querySelector("input");
// let id_pro = URL;
console.log($codigo);
// console.log(id_pro);

function loadProducto($codigo) {
  // let id_codigo = $codigo;
  console.log($codigo);
  // let url = "./php/api-productos.php";
  fetch(`./php/api-producto.php?id_prod=$codigo`)
    .then((res) => (res.ok ? res.json() : Promise.reject(res)))
    .then((json) => {
      // <p>${rubro} </p>
      let $template = "";
      $template += `<div class="card__titulo">
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
      $producto.innerHTML = $template;
    })
    .catch((err) => {
      console.log(err);
      let message = err.statusText || "Ocurrio un error";
      $producto.nextElementSibling.innerHTML = `${err.status}:${message}`;
    });
}

loadProducto();
