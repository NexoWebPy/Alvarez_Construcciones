document.addEventListener("DOMContentLoaded", () => {

  /* =========================================================
     CONFIGURACIÓN
  ========================================================= */

  const WHATSAPP_NUMBER = "595981704655";
  const reduceMotion =
    window.matchMedia("(prefers-reduced-motion: reduce)").matches;


  /* =========================================================
     HEADER SCROLL
  ========================================================= */

  const header = document.querySelector(".header");

  if (header) {
    const updateHeaderState = () => {
      header.classList.toggle("scrolled", window.scrollY > 80);
    };

    updateHeaderState();
    window.addEventListener("scroll", updateHeaderState, { passive: true });
  }


  /* =========================================================
     MENÚ HAMBURGUESA
  ========================================================= */

  const hamburger = document.getElementById("hamburger");
  const navLinks = document.getElementById("nav-links");

  if (hamburger && navLinks) {

    hamburger.addEventListener("click", () => {
      const isOpen = navLinks.classList.toggle("active");
      hamburger.classList.toggle("active", isOpen);
      hamburger.setAttribute("aria-expanded", String(isOpen));
    });

    navLinks.querySelectorAll("a").forEach(link => {
      link.addEventListener("click", () => {
        navLinks.classList.remove("active");
        hamburger.classList.remove("active");
        hamburger.setAttribute("aria-expanded", "false");
      });
    });

    document.addEventListener("click", event => {
      if (
        navLinks.classList.contains("active") &&
        !navLinks.contains(event.target) &&
        !hamburger.contains(event.target)
      ) {
        navLinks.classList.remove("active");
        hamburger.classList.remove("active");
        hamburger.setAttribute("aria-expanded", "false");
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
      disable: () => window.matchMedia("(prefers-reduced-motion: reduce)").matches
    });
  }


  /* =========================================================
     CONTADORES
  ========================================================= */

  const statsSection = document.getElementById("estadisticas");
  let countersAnimated = false;

  function runCounters() {
    const statNumbers = document.querySelectorAll(".stat-number");

    statNumbers.forEach(number => {
      const target = parseInt(number.getAttribute("data-target"), 10);

      if (isNaN(target)) return;

      let current = 0;
      const duration = 1800;
      const startTime = performance.now();

      function animateCounter(currentTime) {
        const elapsed = currentTime - startTime;
        const progress = Math.min(elapsed / duration, 1);
        const ease = 1 - Math.pow(1 - progress, 3);

        current = Math.floor(target * ease);
        number.textContent = current;

        if (progress < 1) {
          requestAnimationFrame(animateCounter);
        } else {
          number.textContent = target;
        }
      }

      requestAnimationFrame(animateCounter);
    });
  }

  if (statsSection && "IntersectionObserver" in window) {
    const statsObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        if (entry.isIntersecting && !countersAnimated) {
          runCounters();
          countersAnimated = true;
          statsObserver.unobserve(entry.target);
        }
      });
    }, { threshold: 0.35 });

    statsObserver.observe(statsSection);
  } else if (statsSection) {
    runCounters();
  }


  /* =========================================================
     FILTROS DE GALERÍA
  ========================================================= */

  const filterButtons = document.querySelectorAll(".filter-btn");
  const projectCards = document.querySelectorAll(".project-card");

  function applyFilter(filter) {
    projectCards.forEach(card => {
      const categories = (card.getAttribute("data-category") || "")
        .toLowerCase()
        .split(/\s+/);

      const show = filter === "all" || categories.includes(filter.toLowerCase());

      if (show) {
        card.classList.remove("hidden");
        card.removeAttribute("aria-hidden");
      } else {
        card.classList.add("hidden");
        card.setAttribute("aria-hidden", "true");
      }
    });
  }

  filterButtons.forEach(button => {
    button.addEventListener("click", () => {
      const filter = button.getAttribute("data-filter") || "all";

      filterButtons.forEach(btn => {
        btn.classList.remove("active");
        btn.setAttribute("aria-selected", "false");
      });

      button.classList.add("active");
      button.setAttribute("aria-selected", "true");

      applyFilter(filter);
    });
  });


  /* =========================================================
     LIGHTBOX
  ========================================================= */

  const lightbox = document.getElementById("projectLightbox");
  const lightboxImage = document.getElementById("lightboxImage");
  const lightboxTitle = document.getElementById("lightboxTitle");
  const lightboxLocation = document.getElementById("lightboxLocation");
  const lightboxDescription = document.getElementById("lightboxDescription");
  const lightboxWhatsapp = document.getElementById("lightboxWhatsapp");
  const lightboxClose = document.getElementById("lightboxClose");
  const lightboxPrev = document.getElementById("lightboxPrev");
  const lightboxNext = document.getElementById("lightboxNext");
  const lightboxCounter = document.getElementById("lightboxCounter");

  let currentProject = 0;

  function getVisibleProjects() {
    return Array.from(document.querySelectorAll(".project-card")).filter(card => !card.classList.contains("hidden"));
  }

  function updateCounter() {
    if (!lightboxCounter) return;

    const projects = getVisibleProjects();

    if (!projects.length) {
      lightboxCounter.textContent = "";
      return;
    }

    lightboxCounter.textContent = `${currentProject + 1} / ${projects.length}`;
  }

  function showProject(index) {
    const projects = getVisibleProjects();

    if (!projects.length) return;

    if (index < 0) index = projects.length - 1;
    if (index >= projects.length) index = 0;

    currentProject = index;
    const card = projects[currentProject];
    const button = card.querySelector(".view-project");

    if (!button) return;

    const image = button.getAttribute("data-image") || "";
    const title = button.getAttribute("data-title") || "Proyecto";
    const location = button.getAttribute("data-location") || "";
    const description = button.getAttribute("data-description") || "";
    const whatsappMessage = button.getAttribute("data-whatsapp") || "Hola, me interesa conocer más sobre sus proyectos.";

    if (lightboxImage) {
      lightboxImage.src = image;
      lightboxImage.alt = title;
    }

    if (lightboxTitle) lightboxTitle.textContent = title;
    if (lightboxLocation) lightboxLocation.textContent = location ? `📍 ${location}` : "";
    if (lightboxDescription) lightboxDescription.textContent = description;

    if (lightboxWhatsapp) {
      lightboxWhatsapp.href = `https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(whatsappMessage)}`;
    }

    updateCounter();
  }

  function openProject(button) {
    if (!lightbox) return;

    const card = button.closest(".project-card");
    if (!card) return;

    const projects = getVisibleProjects();
    const position = projects.indexOf(card);
    currentProject = position >= 0 ? position : 0;

    showProject(currentProject);

    lightbox.classList.add("active");
    lightbox.setAttribute("aria-hidden", "false");
    document.body.classList.add("lightbox-open");
    document.body.style.overflow = "hidden";

    setTimeout(() => {
      if (lightboxClose) lightboxClose.focus();
    }, 100);
  }

  function closeProject() {
    if (!lightbox) return;

    lightbox.classList.remove("active");
    lightbox.setAttribute("aria-hidden", "true");
    document.body.classList.remove("lightbox-open");
    document.body.style.overflow = "";

    setTimeout(() => {
      if (lightboxImage) lightboxImage.removeAttribute("src");
    }, 300);
  }

  document.querySelectorAll(".view-project").forEach(button => {
    button.addEventListener("click", event => {
      event.preventDefault();
      event.stopPropagation();
      openProject(button);
    });
  });

  if (lightboxClose) lightboxClose.addEventListener("click", closeProject);

  if (lightbox) {
    lightbox.addEventListener("click", event => {
      if (event.target === lightbox) closeProject();
    });
  }

  if (lightboxPrev) {
    lightboxPrev.addEventListener("click", event => {
      event.preventDefault();
      event.stopPropagation();
      showProject(currentProject - 1);
    });
  }

  if (lightboxNext) {
    lightboxNext.addEventListener("click", event => {
      event.preventDefault();
      event.stopPropagation();
      showProject(currentProject + 1);
    });
  }

  document.addEventListener("keydown", event => {
    if (!lightbox || !lightbox.classList.contains("active")) return;

    switch (event.key) {
      case "Escape":
        closeProject();
        break;
      case "ArrowLeft":
        if (lightboxPrev) showProject(currentProject - 1);
        break;
      case "ArrowRight":
        if (lightboxNext) showProject(currentProject + 1);
        break;
    }
  });


  /* =========================================================
     VIDEOS DE GALERÍA
  ========================================================= */

  const galleryVideos = document.querySelectorAll(".project-image video");

  if (galleryVideos.length && !reduceMotion && "IntersectionObserver" in window) {
    const videoObserver = new IntersectionObserver(entries => {
      entries.forEach(entry => {
        const video = entry.target;

        if (entry.isIntersecting) {
          video.play().catch(() => {});
        } else {
          video.pause();
        }
      });
    }, { threshold: 0.5 });

    galleryVideos.forEach(video => videoObserver.observe(video));
  }


  /* =========================================================
     FORMULARIO DE COTIZACIÓN
  ========================================================= */

  const quoteForm = document.getElementById("cotizacionForm") || document.getElementById("presupuestoForm");

  if (quoteForm) {
    quoteForm.addEventListener("submit", event => {
      event.preventDefault();

      const nombre = document.getElementById("nombre")?.value.trim() || "";
      const telefono = document.getElementById("telefono")?.value.trim() || "";
      const ciudad = document.getElementById("ciudad")?.value.trim() || "";
      const proyecto = document.getElementById("proyecto")?.value.trim() || "";
      const mensaje = document.getElementById("mensaje")?.value.trim() || "";

      const formFields = {
        nombre: document.getElementById("nombre")?.value.trim() || "",
        tipoProyecto: document.getElementById("tipoProyecto")?.value || "",
        ciudad: document.getElementById("ciudad")?.value.trim() || "",
        tamano: document.getElementById("tamano")?.value.trim() || "",
        presupuesto: document.getElementById("presupuesto")?.value.trim() || "",
        fechaInicio: document.getElementById("fechaInicio")?.value.trim() || "",
        extras: document.getElementById("extras")?.value.trim() || ""
      };

      if (quoteForm.id === "cotizacionForm") {
        if (!formFields.nombre || !formFields.tipoProyecto || !formFields.ciudad) {
          alert("Por favor completá los campos obligatorios.");
          return;
        }

        const details = [
          ["Tipo de proyecto", formFields.tipoProyecto],
          ["Ubicación", formFields.ciudad],
          ["Tamaño aproximado", formFields.tamano],
          ["Presupuesto estimado", formFields.presupuesto],
          ["Inicio estimado", formFields.fechaInicio],
          ["Detalles", formFields.extras]
        ].filter(([, value]) => value);

        const message = [
          "Hola AC Alvarez Construcciones, quiero consultar por un proyecto.",
          "",
          `Mi nombre es ${formFields.nombre}.`,
          ...details.map(([label, value]) => `${label}: ${value}`)
        ].join("\n");

        window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(message)}`, "_blank", "noopener,noreferrer");
        return;
      }

      if (!nombre || !telefono || !ciudad || !proyecto) {
        alert("Por favor completá todos los campos obligatorios.");
        return;
      }

      const texto = `Hola AC Alvarez Construcciones 👋\n\nQuiero solicitar un presupuesto.\n\n👤 Nombre: ${nombre}\n\n📱 Teléfono:\n${telefono}\n\n📍 Ciudad:\n${ciudad}\n\n🏗️ Proyecto:\n${proyecto}\n\n📝 Detalles:\n${mensaje || "No especificado"}\n\nQuedo atento/a a su respuesta. ¡Muchas gracias!`;

      window.open(`https://wa.me/${WHATSAPP_NUMBER}?text=${encodeURIComponent(texto)}`, "_blank", "noopener,noreferrer");
    });
  }


  /* =========================================================
     CARRUSEL DE TESTIMONIOS
  ========================================================= */

  const carouselTrack = document.querySelector(".carousel-track");
  const carouselSlides = document.querySelectorAll(".carousel-slide");

  if (carouselTrack && carouselSlides.length > 0) {
    let currentSlide = 0;
    let autoSlide = null;
    const totalSlides = carouselSlides.length;

    function getSlideWidth() {
      return carouselSlides[0].getBoundingClientRect().width;
    }

    function slideTo(index) {
      if (index < 0) {
        currentSlide = totalSlides - 1;
      } else if (index >= totalSlides) {
        currentSlide = 0;
      } else {
        currentSlide = index;
      }

      carouselTrack.scrollTo({
        left: getSlideWidth() * currentSlide,
        behavior: reduceMotion ? "auto" : "smooth"
      });
    }

    function startAutoSlide() {
      if (reduceMotion || totalSlides <= 1) return;

      stopAutoSlide();
      autoSlide = setInterval(() => slideTo(currentSlide + 1), 5000);
    }

    function stopAutoSlide() {
      if (autoSlide) {
        clearInterval(autoSlide);
        autoSlide = null;
      }
    }

    carouselTrack.addEventListener("mouseenter", stopAutoSlide);
    carouselTrack.addEventListener("mouseleave", startAutoSlide);
    carouselTrack.addEventListener("touchstart", stopAutoSlide, { passive: true });
    carouselTrack.addEventListener("touchend", () => setTimeout(startAutoSlide, 1000), { passive: true });

    startAutoSlide();

    window.addEventListener("resize", () => {
      carouselTrack.scrollTo({
        left: getSlideWidth() * currentSlide,
        behavior: "auto"
      });
    });
  }


  /* =========================================================
     SMOOTH SCROLL
  ========================================================= */

  document.querySelectorAll('a[href^="#"]').forEach(link => {
    link.addEventListener("click", event => {
      const targetId = link.getAttribute("href");

      if (!targetId || targetId === "#") return;

      const target = document.querySelector(targetId);
      if (!target) return;

      event.preventDefault();
      target.scrollIntoView({
        behavior: reduceMotion ? "auto" : "smooth",
        block: "start"
      });
    });
  });


  /* =========================================================
     BOTÓN WHATSAPP
  ========================================================= */

  document.querySelectorAll('a[href*="wa.me"]').forEach(button => {
    button.setAttribute("target", "_blank");
    button.setAttribute("rel", "noopener noreferrer");
  });


  /* =========================================================
     AÑO AUTOMÁTICO DEL FOOTER
  ========================================================= */

  const currentYear = document.querySelector("#currentYear");
  if (currentYear) {
    currentYear.textContent = new Date().getFullYear();
  }


  /* =========================================================
     FAQ - PREGUNTAS FRECUENTES
  ========================================================= */

  const faqItems = document.querySelectorAll(".faq-item");

  if (faqItems.length) {
    faqItems.forEach(item => {
      const question = item.querySelector(".faq-question");
      const answer = item.querySelector(".faq-answer");
      if (!question) return;

      const setOpenState = (isOpen) => {
        item.classList.toggle("active", isOpen);
        question.setAttribute("aria-expanded", String(isOpen));

        if (answer) {
          answer.style.gridTemplateRows = isOpen ? "1fr" : "0fr";
          answer.style.maxHeight = isOpen ? `${answer.scrollHeight + 40}px` : "0px";
          answer.style.opacity = isOpen ? "1" : "0";
          answer.style.visibility = isOpen ? "visible" : "hidden";
        }
      };

      setOpenState(false);

      question.addEventListener("click", () => {
        const isOpen = item.classList.contains("active");

        faqItems.forEach(otherItem => {
          otherItem.classList.remove("active");
          const otherButton = otherItem.querySelector(".faq-question");
          const otherAnswer = otherItem.querySelector(".faq-answer");

          if (otherButton) otherButton.setAttribute("aria-expanded", "false");
          if (otherAnswer) {
            otherAnswer.style.gridTemplateRows = "0fr";
            otherAnswer.style.maxHeight = "0px";
            otherAnswer.style.opacity = "0";
            otherAnswer.style.visibility = "hidden";
          }
        });

        setOpenState(!isOpen);
      });
    });
  }


  console.log("AC Alvarez Construcciones — Web cargada correctamente.");
});