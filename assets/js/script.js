'use strict';



/**
 * add event on element
 */

const addEventOnelem = function (elem, type, callback) {
  if (elem.length > 1) {
    for (let i = 0; i < elem.length; i++) {
      elem[i].addEventListener(type, callback);
    }
  } else {
    elem.addEventListener(type, callback);
  }
}



/**
 * toggle navbar
 */

const navbar = document.querySelector("[data-navbar]");
const navbarLinks = document.querySelectorAll("[data-nav-link]");
const navToggler = document.querySelector("[data-nav-toggler]");

const toggleNavbar = function () {
  navbar.classList.toggle("active");
  navToggler.classList.toggle("active");
}

addEventOnelem(navToggler, 'click', toggleNavbar);

const closeNavbar = function () {
  navbar.classList.remove("active");
  navToggler.classList.remove("active");
}

addEventOnelem(navbarLinks, "click", closeNavbar);



/**
 * header active on scroll down to 100px
 */

const header = document.querySelector("[data-header]");

const activeHeader = function () {
  if (window.scrollY > 100) {
    header.classList.add("active");
  } else {
    header.classList.remove("active");
  }
}

addEventOnelem(window, "scroll", activeHeader);



/**
 * filter tab
 */

const tabCard = document.querySelectorAll("[data-tab-card]");

let lastTabCard = tabCard[0];

const navigateTab = function () {
  lastTabCard.classList.remove("active");
  this.classList.add("active");
  lastTabCard = this;
}

addEventOnelem(tabCard, "click", navigateTab);

/**
 * login
 */


// Controle do Dropdown do Usuário na Home (Simplificado para Hover)
document.addEventListener("DOMContentLoaded", function() {
  const dropdown = document.querySelector(".dropdown-content");

  if(dropdown) {
    // Garante que o link funcione instantaneamente ao clicar
    dropdown.querySelectorAll("a").forEach(link => {
      link.addEventListener("click", function(e) {
        // e.stopPropagation(); // Opcional: evita que o clique passe para o documento
        window.location.href = this.href;
      });
    });

    // Fecha se houver algum clique perdido fora
    document.addEventListener("click", function() {
      dropdown.style.display = "none";
    });
  }
});
