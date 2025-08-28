const d = document;
const cardRubros = d.getElementById("card__rubros");
const Productos = d.getElementById("productos");
const template = d.getElementById("template__Productos").content;
const fragment = d.createDocumentFragment();
const btnComprar = d.getElementById("formCompra");
const btnCarrito = d.getElementById("head__carrito");
const nroCarro = d.querySelector(".nroCarrito");

let familia = localStorage.getItem("familia");
let leoCarrito = localStorage.getItem("proCar");
let rubro = "";
let codPtos = [];
cardRubros.addEventListener("click", (e) => {
  if (e.target.matches("button")) {
    e.preventDefault();
    // console.log(e.target.value);
    localStorage.setItem("familia", e.target.value);
    loadProductos(e.target.value);
    e.stopPropagation();
  }
});

if (localStorage.getItem("familia") === null) {
  rubro = "PC ARMADA";
  localStorage.setItem("familia", rubro);
} else {
  rubro = familia;
}

const loadProductos = async (rubro) => {
  try {
    const res = await fetch(`./php/api-productos.php?rubro=${rubro}`);
    const data = await res.json();
    pintarProductos(data);
  } catch (err) {
    let message = err.statusText || "Ocurrio un error";
  }
};
loadProductos(rubro);

const pintarProductos = (data) => {
  Productos.innerHTML = "";
  data.forEach((ele) => {
    template
      .querySelector("img")
      .setAttribute(
        "src",
        `./assets/img/webp/productos/` + ele.codigo + `.webp`
      );
    template.querySelector("p").textContent = ele.detalle;
    template.querySelector(".card__stock").textContent = "STOCK: " + ele.stock;
    template.querySelector(".card__precio").textContent = "$ " + ele.preciogp;
    template.querySelector("input").dataset.id = ele.id_prod;
    const clone = template.cloneNode(true);
    fragment.appendChild(clone);
  });

  Productos.appendChild(fragment);
};

if (localStorage.getItem("proCar") === null) {
  codPtos = [];
  // console.log("paso-1");
} else {
  codPtos = leoCarrito.split(",");
  // console.log("paso-2");
  contador();
}

Productos.addEventListener("click", (event) => {
  if (event.target.matches("input")) {
    event.preventDefault();
    codPtos.push(event.target.dataset.id);
    // codPtos.push([]id_prod: event.target.dataset.id, cant_prod: 1]);
    localStorage.setItem("proCar", codPtos);
    // localStorage.setItem("prueba", prueba);
    event.stopPropagation();
    contador();
  }
});
btnCarrito.addEventListener("click", (event) => {
  // if (event.target.matches("input")) {
  event.preventDefault();
  let leoCarrito = localStorage.getItem("proCar");
  if (localStorage.getItem("proCar") === null) {
    codPtos = [];
    alert("El Carrito esta vacio");
  } else {
    codPtos = leoCarrito.split(",");
    location.href = "./carrito.html";
  }
});

function contador() {
  nroProd = codPtos.length;
  // console.log("nroProd", nroProd);
  nroCarro.innerHTML = nroProd;
  return nroProd;
}
