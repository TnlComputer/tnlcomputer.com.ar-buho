const $clientes = document.getElementById("list__clie");
const $formClie = document.getElementById("cli-formulario");
const datosClie = document.querySelector(".line__tabla");
const templateClie = document.getElementById("template__clientes").content;
const fragmentClie = document.createDocumentFragment();
const tablaGenerator = document.querySelector(".tablagen");
const addClie = document.querySelector(".form__add");
const editClie = document.querySelector(".form__edit");
// const delClie = document.querySelector(".form__del");
// const vPreClie = document.querySelector(".form__vPre");
// const vRepClie = document.querySelector(".form__vRep");
async function loadClientes() {
  await fetch("php/clientes.php")
    .then((respuestas) => respuestas.json())
    .then((resultado) => {
      // let $options = `<option value="">Elige un Cliente</option>`;
      resultado.forEach((el) => {
        templateClie.querySelector(".edit__form").dataset.editid =
          el.id_cliente;
        templateClie.querySelector(".vVeh__form").dataset.vVehid =
          el.id_cliente;
        templateClie.querySelector(".cuit__c").textContent = el.cuit;
        templateClie.querySelector(".cliente__c").textContent = el.empresa_nyp;
        templateClie.querySelector(".direccion__c").textContent = el.direccion;
        templateClie.querySelector(".localidad__c").textContent = el.localidad;
        templateClie.querySelector(".provincia__c").textContent = el.provincia;
        templateClie.querySelector(".cpost__c").textContent = el.cpost;
        templateClie.querySelector(".contacto__c").textContent = el.contacto;
        templateClie.querySelector(".telefono__c").textContent = el.telefono;
        templateClie.querySelector(".celular__c").textContent = el.celular;
        templateClie.querySelector(".mail__c").textContent = el.mail;
        const clone = templateClie.cloneNode(true);
        fragmentClie.appendChild(clone);
      });

      datosClie.appendChild(fragmentClie);
    });
}
loadClientes();

tablaGenerator.addEventListener("click", async (event) => {
  // console.log(event.target);

  if (event.target.closest(".add__form")) {
    //ejecutar funcion de agregar tarea
    alert("ADD Cliente");
  }

  if (event.target.closest(".edit__form")) {
    //ejecutar funcion de agregar tarea
    alert("modificacion Cliente");
  }

  if (event.target.closest(".del__form")) {
    //ejecutar funcion de agregar tarea
    alert("borrar Cliente");
  }

  if (event.target.closest(".vVeh__form")) {
    //ejecutar funcion de agregar tarea
    console.log(vVeh__form);
    alert("Ver Vehiculos del Cliente");
  }
  // if (event.target.closest(".vPre__form")) {
  //   //ejecutar funcion de agregar tarea
  //   alert("Ver Presupuestos del Cliente");
  // }
  // if (event.target.closest(".vRep__form")) {
  //   //ejecutar funcion de agregar tarea
  //   alert("Ver Reparaciones del Cliente");
  // }
});

//   $clientes.addEventListener("change", (e) => loadVehiculos(e.target.value));

//   // console.log("datos ===>", datos);
//   async function loadVehiculos(datos) {
//     const opciones = {
//       method: "POST",
//       body: datos,
//     };
//     resp = await fetch("php/vehiculos.php", opciones)
//       .then((resp) => resp.json())
//       .then((data) => {
//         let $options = "";
//         if (data === "error") {
//           $options += `<p class="error__vehiculo">El Cliente no tiene Vehiculos asociados</p>`;
//         } else {
//           data.forEach((ele) => {
//             $options += `<div class="cli-vehiculos-main">

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Patentes:</div>
//           <div class="cli-vehiculos-detalle">${ele.patente}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Marca:</div>
//           <div class="cli-vehiculos-detalle">${ele.marca}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Modelo:</div>
//           <div class="cli-vehiculos-detalle">${ele.modelo}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">KM:</div>
//           <div class="cli-vehiculos-detalle">${ele.km} km</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Tanque:</div>
//           <div class="cli-vehiculos-detalle">${ele.tanque}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Detalles: </div>
//           <div class="cli-vehiculos-detalle">${ele.detalle}</div>
//           </div>

//           </div>`;
//           });
//         }
//         $vehiculos.innerHTML = $options;
//       });
//   }
// }

// loadClientes();

// $clientes.addEventListener("change", (e) => loadVehiculos(e.target.value));

// // console.log("datos ===>", datos);
// async function loadVehiculos(datos) {
//   const opciones = {
//     method: "POST",
//     body: datos,
//   };
//   respuesta = await fetch("php/vehiculos.php", opciones)
//     .then((respuesta) => respuesta.json())
//     .then((data) => {
//       let $options = "";
//       if (data === "error") {
//         $options += `<p class="error__vehiculo">El Cliente no tiene Vehiculos asociados</p>`;
//       } else {
//         data.forEach((ele) => {
//           $options += `<div class="cli-vehiculos-main">

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Patentes:</div>
//           <div class="cli-vehiculos-detalle">${ele.patente}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Marca:</div>
//           <div class="cli-vehiculos-detalle">${ele.marca}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Modelo:</div>
//           <div class="cli-vehiculos-detalle">${ele.modelo}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">KM:</div>
//           <div class="cli-vehiculos-detalle">${ele.km} km</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Tanque:</div>
//           <div class="cli-vehiculos-detalle">${ele.tanque}</div>
//           </div>

//           <div class="cli-vehiculos-linea">
//           <div class="cli-vehiculos-titulo">Detalles: </div>
//           <div class="cli-vehiculos-detalle">${ele.detalle}</div>
//           </div>

//           </div>`;
//         });
//       }
//       $vehiculos.innerHTML = $options;
//     });
