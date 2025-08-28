const Productos = document.getElementById("productos__carrito");
const ConProd = document.querySelector(".content__carrito");
const template = document.getElementById("template__Productos").content;
const fragment = document.createDocumentFragment();
const totalCarrito = document.querySelector(".carrito__imp-total");
// const sumo = document.querySelector(".sumo");
// const resto = document.querySelector(".resto");
// const borro = document.querySelecto(".borro");

// const ls = localStorage;
// const leoCarrito = localStorage.getItem("proCar");

const loadCarrito = async (leoCarrito) => {
  try {
    // const cant = 1;
    // const id = leoCarrito[0];

    // const prueba = [
    //   {
    //     id_prod: id,
    //     cant: cant,
    //   },
    // ];

    // console.log(prueba);
    // console.log("llego 1");
    const idProductos = localStorage.getItem("proCar");
    const data = JSON.stringify(idProductos);
    const opciones = {
      method: "POST",
      body: data,
    };
    const res = await fetch("php/api-carrito.php", opciones);
    const datos = await res.json();
    // console.log(datos);
    // console.log("llego 2");

    pintarCarrito(datos);
  } catch (err) {
    let message = err.statusText || "Ocurrio un error";
  }
};
loadCarrito();

// function pintarCarrito(datosC) {
const pintarCarrito = (datosC) => {
  Productos.innerHTML = "";
  // console.log("llego 3");

  let $total = 0;
  let $totalT = 0;
  // let $totalTV = 0;
  // let $totalT1 = 0;
  let $precio = 0;
  // let $precioT = 0;
  let $precio100 = 0;
  let i = 0;
  $totalT = parseFloat($total);
  datosC.forEach((ele) => {
    $precio = ele.preciogp.replace(",", ".");
    $precio100 = $precio * 100;
    $total = $total + $precio100;
    $totalT = $total / 100;
    i++;
    console.log("I", i);
    // const arregle = Array[{ i, id, cantidad }];
    // console.log(arregle[i]);
    // console.log(arregle);
    template
      .querySelector("img")
      .setAttribute(
        "src",
        `./assets/img/webp/productos/` + ele.codigo + `.webp`
      );
    template.querySelector(".carrito__detalle").textContent = ele.detalle;
    template.querySelector(".carrito__cantidad").textContent = 1;
    // template.querySelector(".sumo).textContent =      dataset.ele;
    // template.querySelector(".resto").dataset.pos = ele;

    template.querySelector(".carrito__importeD").textContent = "$ " + $precio;
    // template.querySelector("input").dataset.id = ele.id_prod;
    // template.querySelector(".borro").dataset.pos = ele;

    const clone = template.cloneNode(true);
    fragment.appendChild(clone);
    // template.querySelector(".carrito__imp-total").textContent = "$ " + $total;

    //  let $prodApi[i] = array["id_prod" : ele.id_prod, "cantidad": cantidad];
    // const prueba = [
    //   {
    //     id_prod: ele.id_prod,
    //     cantidad: cantitad,
    //   },
    // ];
  });

  Productos.appendChild(fragment);
  totalCarrito.innerHTML = "$ " + $totalT.toFixed(2);
};

Productos.addEventListener("click", async (eSumo) => {
  if (eSumo.target.matches(".sumo")) {
    eSumo.preventDefault();
    console.log("Click en Suma");
    //rutina para sumar
  }
});
Productos.addEventListener("click", async (eResto) => {
  if (eResto.target.matches(".resto")) {
    eResto.preventDefault();
    console.log("Click en resto");
    //rutina para restar y verificar que si la cantidad es cero eliminar del carrito
  }
});
Productos.addEventListener("click", async (eBorro) => {
  if (eBorro.target.matches(".borro")) {
    eBorro.preventDefault();
    console.log("Click en borro");
    //rutina para borrarcontinuar__comprando
  }
});
ConProd.addEventListener("click", async (eVolver) => {
  if (eVolver.target.matches(".continuar__comprando")) {
    eVolver.preventDefault();
    console.log("Click continuar comprando");
    // console.log(locatio.href);
    location.href = "./hardware.html";
    //rutina para borrarcontinuar__comprando
  }
});
