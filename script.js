document.addEventListener("DOMContentLoaded", () => {

  /* =========================================================
     CONFIGURACIÓN
  ========================================================= */

  const WHATSAPP_NUMBER = "595981704655";


  /* =========================================================
     MENÚ HAMBURGUESA
  ========================================================= */

  const hamburger = document.getElementById("hamburger");
  const navLinks = document.getElementById("nav-links");

  if (hamburger && navLinks) {

    hamburger.addEventListener("click", () => {

      const isOpen = navLinks.classList.toggle("active");

      hamburger.classList.toggle("active", isOpen);

      hamburger.setAttribute(
        "aria-expanded",
        String(isOpen)
      );

    });


    navLinks.querySelectorAll("a").forEach(link => {

      link.addEventListener("click", () => {

        navLinks.classList.remove("active");
        hamburger.classList.remove("active");

        hamburger.setAttribute(
          "aria-expanded",
          "false"
        );

      });

    });


    // Cerrar menú al hacer clic fuera

    document.addEventListener("click", event => {

      if (
        navLinks.classList.contains("active") &&
        !navLinks.contains(event.target) &&
        !hamburger.contains(event.target)
      ) {

        navLinks.classList.remove("active");
        hamburger.classList.remove("active");

        hamburger.setAttribute(
          "aria-expanded",
          "false"
        );

      }

    });

  }


  /* =========================================================
     AOS
  ========================================================= */

  if (typeof AOS !== "undefined") {

    AOS.init({

      duration: 900,

      once: true,

      offset: 80,

      easing: "ease-out-cubic",

      disable: () =>
        window.matchMedia(
          "(prefers-reduced-motion: reduce)"
        ).matches

    });

  }


  /* =========================================================
     CONTADORES
  ========================================================= */

  const statsSection =
    document.getElementById("estadisticas");

  let countersAnimated = false;


  function runCounters() {

    const statNumbers =
      document.querySelectorAll(".stat-number");


    statNumbers.forEach(number => {

      const target =
        parseInt(
          number.getAttribute("data-target"),
          10
        );


      if (isNaN(target)) return;


      let current = 0;

      const duration = 1800;

      const startTime = performance.now();


      function animateCounter(currentTime) {

        const elapsed =
          currentTime - startTime;

        const progress =
          Math.min(
            elapsed / duration,
            1
          );


        // Ease-out

        const ease =
          1 - Math.pow(
            1 - progress,
            3
          );


        current =
          Math.floor(
            target * ease
          );


        number.textContent =
          current;


        if (progress < 1) {

          requestAnimationFrame(
            animateCounter
          );

        } else {

          number.textContent =
            target;

        }

      }


      requestAnimationFrame(
        animateCounter
      );

    });

  }


  if (
    statsSection &&
    "IntersectionObserver" in window
  ) {

    const statsObserver =
      new IntersectionObserver(
        entries => {

          entries.forEach(entry => {

            if (
              entry.isIntersecting &&
              !countersAnimated
            ) {

              runCounters();

              countersAnimated = true;

              statsObserver.unobserve(
                entry.target
              );

            }

          });

        },
        {
          threshold: 0.35
        }
      );


    statsObserver.observe(statsSection);

  } else if (statsSection) {

    runCounters();

  }


  /* =========================================================
     FILTROS DE GALERÍA
  ========================================================= */

  const filterButtons =
    document.querySelectorAll(
      ".filter-btn"
    );

  const projectCards =
    document.querySelectorAll(
      ".project-card"
    );


  function applyFilter(filter) {

    projectCards.forEach(card => {

      const categories =
        (
          card.getAttribute(
            "data-category"
          ) || ""
        )
        .toLowerCase()
        .split(/\s+/);


      const show =
        filter === "all" ||
        categories.includes(
          filter.toLowerCase()
        );


      if (show) {

        card.classList.remove(
          "hidden"
        );

        card.removeAttribute(
          "aria-hidden"
        );

      } else {

        card.classList.add(
          "hidden"
        );

        card.setAttribute(
          "aria-hidden",
          "true"
        );

      }

    });

  }


  filterButtons.forEach(button => {

    button.addEventListener(
      "click",
      () => {

        const filter =
          button.getAttribute(
            "data-filter"
          ) || "all";


        filterButtons.forEach(btn => {

          btn.classList.remove(
            "active"
          );

          btn.setAttribute(
            "aria-selected",
            "false"
          );

        });


        button.classList.add(
          "active"
        );

        button.setAttribute(
          "aria-selected",
          "true"
        );


        applyFilter(filter);

      }
    );

  });


  /* =========================================================
     LIGHTBOX
  ========================================================= */

  const lightbox =
    document.getElementById(
      "projectLightbox"
    );

  const lightboxImage =
    document.getElementById(
      "lightboxImage"
    );

  const lightboxTitle =
    document.getElementById(
      "lightboxTitle"
    );

  const lightboxLocation =
    document.getElementById(
      "lightboxLocation"
    );

  const lightboxDescription =
    document.getElementById(
      "lightboxDescription"
    );

  const lightboxWhatsapp =
    document.getElementById(
      "lightboxWhatsapp"
    );

  const lightboxClose =
    document.getElementById(
      "lightboxClose"
    );

  const lightboxPrev =
    document.getElementById(
      "lightboxPrev"
    );

  const lightboxNext =
    document.getElementById(
      "lightboxNext"
    );

  const lightboxCounter =
    document.getElementById(
      "lightboxCounter"
    );


  let currentProject = 0;


  /* =========================================================
     PROYECTOS VISIBLES
  ========================================================= */

  function getVisibleProjects() {

    return Array.from(
      document.querySelectorAll(
        ".project-card"
      )
    ).filter(card => {

      return !card.classList.contains(
        "hidden"
      );

    });

  }


  /* =========================================================
     ACTUALIZAR CONTADOR
  ========================================================= */

  function updateCounter() {

    if (!lightboxCounter) return;


    const projects =
      getVisibleProjects();


    if (!projects.length) {

      lightboxCounter.textContent = "";

      return;

    }


    lightboxCounter.textContent =
      `${currentProject + 1} / ${projects.length}`;

  }


  /* =========================================================
     MOSTRAR PROYECTO
  ========================================================= */

  function showProject(index) {

    const projects =
      getVisibleProjects();


    if (!projects.length) return;


    if (index < 0) {

      index =
        projects.length - 1;

    }


    if (index >= projects.length) {

      index = 0;

    }


    currentProject = index;


    const card =
      projects[currentProject];


    const button =
      card.querySelector(
        ".view-project"
      );


    if (!button) return;


    const image =
      button.getAttribute(
        "data-image"
      ) || "";


    const title =
      button.getAttribute(
        "data-title"
      ) || "Proyecto";


    const location =
      button.getAttribute(
        "data-location"
      ) || "";


    const description =
      button.getAttribute(
        "data-description"
      ) || "";


    const whatsappMessage =
      button.getAttribute(
        "data-whatsapp"
      ) ||
      "Hola, me interesa conocer más sobre sus proyectos.";


    /* Imagen */

    if (lightboxImage) {

      lightboxImage.src = image;

      lightboxImage.alt =
        title;

    }


    /* Título */

    if (lightboxTitle) {

      lightboxTitle.textContent =
        title;

    }


    /* Ubicación */

    if (lightboxLocation) {

      lightboxLocation.textContent =
        location
          ? `📍 ${location}`
          : "";

    }


    /* Descripción */

    if (lightboxDescription) {

      lightboxDescription.textContent =
        description;

    }


    /* WhatsApp */

    if (lightboxWhatsapp) {

      const whatsappURL =
        `https://wa.me/${WHATSAPP_NUMBER}?text=` +
        encodeURIComponent(
          whatsappMessage
        );


      lightboxWhatsapp.href =
        whatsappURL;

    }


    updateCounter();

  }


  /* =========================================================
     ABRIR LIGHTBOX
  ========================================================= */

  function openProject(button) {

    if (!lightbox) return;


    const card =
      button.closest(
        ".project-card"
      );


    if (!card) return;


    const projects =
      getVisibleProjects();


    const position =
      projects.indexOf(card);


    currentProject =
      position >= 0
        ? position
        : 0;


    showProject(
      currentProject
    );


    lightbox.classList.add(
      "active"
    );


    lightbox.setAttribute(
      "aria-hidden",
      "false"
    );


    document.body.classList.add(
      "lightbox-open"
    );


    document.body.style.overflow =
      "hidden";


    // Enfocar botón cerrar

    setTimeout(() => {

      if (lightboxClose) {

        lightboxClose.focus();

      }

    }, 100);

  }


  /* =========================================================
     CERRAR LIGHTBOX
  ========================================================= */

  function closeProject() {

    if (!lightbox) return;


    lightbox.classList.remove(
      "active"
    );


    lightbox.setAttribute(
      "aria-hidden",
      "true"
    );


    document.body.classList.remove(
      "lightbox-open"
    );


    document.body.style.overflow =
      "";


    setTimeout(() => {

      if (lightboxImage) {

        lightboxImage.removeAttribute(
          "src"
        );

      }

    }, 300);

  }


  /* =========================================================
     BOTONES VER PROYECTO
  ========================================================= */

  document
    .querySelectorAll(
      ".view-project"
    )
    .forEach(button => {

      button.addEventListener(
        "click",
        event => {

          event.preventDefault();

          event.stopPropagation();

          openProject(button);

        }
      );

    });


  /* =========================================================
     BOTÓN CERRAR
  ========================================================= */

  if (lightboxClose) {

    lightboxClose.addEventListener(
      "click",
      closeProject
    );

  }


  /* =========================================================
     CLIC EN FONDO
  ========================================================= */

  if (lightbox) {

    lightbox.addEventListener(
      "click",
      event => {

        if (
          event.target === lightbox
        ) {

          closeProject();

        }

      }
    );

  }


  /* =========================================================
     ANTERIOR
  ========================================================= */

  if (lightboxPrev) {

    lightboxPrev.addEventListener(
      "click",
      event => {

        event.preventDefault();

        event.stopPropagation();


        showProject(
          currentProject - 1
        );

      }
    );

  }


  /* =========================================================
     SIGUIENTE
  ========================================================= */

  if (lightboxNext) {

    lightboxNext.addEventListener(
      "click",
      event => {

        event.preventDefault();

        event.stopPropagation();


        showProject(
          currentProject + 1
        );

      }
    );

  }


  /* =========================================================
     TECLADO
  ========================================================= */

  document.addEventListener(
    "keydown",
    event => {

      if (
        !lightbox ||
        !lightbox.classList.contains(
          "active"
        )
      ) {

        return;

      }


      switch (event.key) {

        case "Escape":

          closeProject();

          break;


        case "ArrowLeft":

          if (lightboxPrev) {

            showProject(
              currentProject - 1
            );

          }

          break;


        case "ArrowRight":

          if (lightboxNext) {

            showProject(
              currentProject + 1
            );

          }

          break;

      }

    }
  );


  /* =========================================================
     VIDEOS DE GALERÍA
  ========================================================= */

  const galleryVideos =
    document.querySelectorAll(
      ".project-image video"
    );


  const reduceMotion =
    window.matchMedia(
      "(prefers-reduced-motion: reduce)"
    ).matches;


  if (
    galleryVideos.length &&
    !reduceMotion &&
    "IntersectionObserver" in window
  ) {

    const videoObserver =
      new IntersectionObserver(
        entries => {

          entries.forEach(entry => {

            const video =
              entry.target;


            if (
              entry.isIntersecting
            ) {

              video
                .play()
                .catch(() => {});

            } else {

              video.pause();

            }

          });

        },
        {
          threshold: 0.5
        }
      );


    galleryVideos.forEach(video => {

      videoObserver.observe(
        video
      );

    });

  }


  /* =========================================================
     FORMULARIO DE PRESUPUESTO
  ========================================================= */

  const presupuestoForm =
    document.getElementById(
      "presupuestoForm"
    );


  if (presupuestoForm) {

    presupuestoForm.addEventListener(
      "submit",
      event => {

        event.preventDefault();


        const nombre =
          document.getElementById(
            "nombre"
          )?.value.trim() || "";


        const telefono =
          document.getElementById(
            "telefono"
          )?.value.trim() || "";


        const ciudad =
          document.getElementById(
            "ciudad"
          )?.value.trim() || "";


        const proyecto =
          document.getElementById(
            "proyecto"
          )?.value.trim() || "";


        const mensaje =
          document.getElementById(
            "mensaje"
          )?.value.trim() || "";


        /* Validación */

        if (
          !nombre ||
          !telefono ||
          !ciudad ||
          !proyecto
        ) {

          alert(
            "Por favor completá todos los campos obligatorios."
          );

          return;

        }


        /* Crear mensaje */

        const texto =
`Hola AC Alvarez Construcciones 👋

Quiero solicitar un presupuesto.

👤 Nombre: ${nombre}

📱 Teléfono:
${telefono}

📍 Ciudad:
${ciudad}

🏗️ Proyecto:
${proyecto}

📝 Detalles:
${mensaje || "No especificado"}

Quedo atento/a a su respuesta. ¡Muchas gracias!`;


        const url =
          `https://wa.me/${WHATSAPP_NUMBER}?text=` +
          encodeURIComponent(
            texto
          );


        /* Abrir WhatsApp */

        window.open(
          url,
          "_blank",
          "noopener,noreferrer"
        );

      }
    );

  }


  /* =========================================================
     CARRUSEL DE TESTIMONIOS
  ========================================================= */

  const carouselTrack =
    document.querySelector(
      ".carousel-track"
    );


  const carouselSlides =
    document.querySelectorAll(
      ".carousel-slide"
    );


  if (
    carouselTrack &&
    carouselSlides.length > 0
  ) {

    let currentSlide = 0;

    let autoSlide = null;

    const totalSlides =
      carouselSlides.length;


    function getSlideWidth() {

      return carouselSlides[0]
        .getBoundingClientRect()
        .width;

    }


    function slideTo(index) {

      if (index < 0) {

        currentSlide =
          totalSlides - 1;

      } else if (
        index >= totalSlides
      ) {

        currentSlide = 0;

      } else {

        currentSlide = index;

      }


      carouselTrack.scrollTo({

        left:
          getSlideWidth() *
          currentSlide,

        behavior:
          reduceMotion
            ? "auto"
            : "smooth"

      });

    }


    function startAutoSlide() {

      if (
        reduceMotion ||
        totalSlides <= 1
      ) {

        return;

      }


      stopAutoSlide();


      autoSlide =
        setInterval(() => {

          slideTo(
            currentSlide + 1
          );

        }, 5000);

    }


    function stopAutoSlide() {

      if (autoSlide) {

        clearInterval(
          autoSlide
        );

        autoSlide = null;

      }

    }


    /* Pausar con mouse */

    carouselTrack.addEventListener(
      "mouseenter",
      stopAutoSlide
    );


    carouselTrack.addEventListener(
      "mouseleave",
      startAutoSlide
    );


    /* Pausar al tocar */

    carouselTrack.addEventListener(
      "touchstart",
      stopAutoSlide,
      {
        passive: true
      }
    );


    carouselTrack.addEventListener(
      "touchend",
      () => {

        setTimeout(
          startAutoSlide,
          1000
        );

      },
      {
        passive: true
      }
    );


    /* Iniciar */

    startAutoSlide();


    /* Ajustar al cambiar tamaño */

    window.addEventListener(
      "resize",
      () => {

        carouselTrack.scrollTo({

          left:
            getSlideWidth() *
            currentSlide,

          behavior: "auto"

        });

      }
    );

  }


  /* =========================================================
     SMOOTH SCROLL
  ========================================================= */

  document
    .querySelectorAll(
      'a[href^="#"]'
    )
    .forEach(link => {

      link.addEventListener(
        "click",
        event => {

          const targetId =
            link.getAttribute(
              "href"
            );


          if (
            !targetId ||
            targetId === "#"
          ) {

            return;

          }


          const target =
            document.querySelector(
              targetId
            );


          if (!target) return;


          event.preventDefault();


          target.scrollIntoView({

            behavior:
              reduceMotion
                ? "auto"
                : "smooth",

            block: "start"

          });

        }
      );

    });


  /* =========================================================
     BOTÓN WHATSAPP
  ========================================================= */

  const whatsappButtons =
    document.querySelectorAll(
      'a[href*="wa.me"]'
    );


  whatsappButtons.forEach(button => {

    button.setAttribute(
      "target",
      "_blank"
    );


    button.setAttribute(
      "rel",
      "noopener noreferrer"
    );

  });


  /* =========================================================
     AÑO AUTOMÁTICO DEL FOOTER
  ========================================================= */

  const currentYear =
    document.querySelector(
      "#currentYear"
    );


  if (currentYear) {

    currentYear.textContent =
      new Date().getFullYear();

  }


  /* =========================================================
     LOG DE INICIO
  ========================================================= */

  console.log(
    "AC Alvarez Construcciones — Web cargada correctamente."
  );

});window.addEventListener("scroll", () => {
    const header = document.querySelector(".header");

    if(window.scrollY > 80){
        header.classList.add("scrolled");
    }else{
        header.classList.remove("scrolled");
    }
});/* =========================================================
   FAQ - PREGUNTAS FRECUENTES
========================================================= */

document.addEventListener("DOMContentLoaded", function () {

    const faqItems = document.querySelectorAll(".faq-item");

    if (!faqItems.length) {
        console.warn("No se encontraron elementos .faq-item");
        return;
    }

    faqItems.forEach(function (item) {

        const question = item.querySelector(".faq-question");

        if (!question) return;

        question.addEventListener("click", function () {

            const isOpen = item.classList.contains("active");

            // Cerrar todas las preguntas
            faqItems.forEach(function (otherItem) {

                otherItem.classList.remove("active");

                const otherButton =
                    otherItem.querySelector(".faq-question");

                if (otherButton) {
                    otherButton.setAttribute(
                        "aria-expanded",
                        "false"
                    );
                }

            });

            // Abrir la seleccionada
            if (!isOpen) {

                item.classList.add("active");

                question.setAttribute(
                    "aria-expanded",
                    "true"
                );

            }

        });

    });

});