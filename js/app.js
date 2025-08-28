const btn = document.querySelector(".menu-btn");
const menu = document.querySelector(".sidemenu");
// const body = document.querySelector(".body");

// const btn__close = document.querySelector('.btn-close');
// const btn__menu = document.querySelector('.btn-menu');

  btn.addEventListener("click", (e) => {
  menu.classList.toggle("menu-expanded");
  menu.classList.toggle("menu-collapsed");

  
});


// body.classList.toggle("body-collapsed");
  // body.classList.toggle("body-expanded");
  // // btn__menu.classList.toggle("btn-close");
  // btn__close.classList.toggle("btn-close");

  //  document.querySelector("body").classList.toggle("body-expanded");