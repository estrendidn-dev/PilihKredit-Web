(() => {
  "use strict";

  // Constants
  const SCROLL_THRESHOLD = 100;
  const ANIMATION_DELAY = 150;
  const isNewsPage = window.location.pathname.endsWith("news.php");
  const isContentPage = window.location.pathname.endsWith("content.php");

  // DOM Elements
  const selectors = {
    body: document.querySelector("body"),
    header: document.querySelector("#header"),
    logo: document.querySelector(".logo img"),
    mobileNavToggle: document.querySelector(".mobile-nav-toggle"),
    mobileNavOverlay: document.querySelector(".mobile-nav-overlay"),
    preloader: document.querySelector("#preloader"),
    scrollTop: document.querySelector(".scroll-top"),
    navmenuLinks: document.querySelectorAll(".navmenu a"),
    faqTabs: document.querySelectorAll('[data-bs-toggle="tab"]'),
    cardItems: document.querySelectorAll(".card-item"),
    faqItems: document.querySelectorAll(".faq-item h3, .faq-item .faq-toggle"),
  };

  /**
   * Header & Navigation Functions
   */
  const headerHandlers = {
    toggleScrolled: () => {
      if (isNewsPage || isContentPage) {
        selectors.body.classList.add("scrolled");
        return;
      }
      if (
        !selectors.header.classList.contains("scroll-up-sticky") &&
        !selectors.header.classList.contains("sticky-top") &&
        !selectors.header.classList.contains("fixed-top")
      )
        return;

      window.scrollY > SCROLL_THRESHOLD
        ? selectors.body.classList.add("scrolled")
        : selectors.body.classList.remove("scrolled");
    },

    toggleScrolledLogo: () => {
      if (isNewsPage || isContentPage) return;
      const scrollLogo = selectors.logo.getAttribute("data-scroll-logo");
      const originalLogo = "assets/img/logo_copy.png";

      if (window.scrollY > SCROLL_THRESHOLD) {
        if (selectors.logo.src.includes(originalLogo)) {
          selectors.logo.src = scrollLogo;
        }
      } else {
        if (selectors.logo.src.includes("logo_filled.png")) {
          selectors.logo.src = originalLogo;
        }
      }
    },

    mobileNavToggle: () => {
      selectors.body.classList.toggle("mobile-nav-active");
      selectors.mobileNavToggle.classList.toggle("bi-list");
      selectors.mobileNavToggle.classList.toggle("bi-x");

      // Toggle body scroll
      document.body.style.overflow = selectors.body.classList.contains(
        "mobile-nav-active"
      )
        ? "hidden"
        : "";
    },

    navmenuScrollspy: () => {
      selectors.navmenuLinks.forEach((link) => {
        if (!link.hash) return;
        const section = document.querySelector(link.hash);
        if (!section) return;

        const position = window.scrollY + 200;
        if (
          position >= section.offsetTop &&
          position <= section.offsetTop + section.offsetHeight
        ) {
          document
            .querySelectorAll(".navmenu a.active")
            .forEach((activeLink) => activeLink.classList.remove("active"));
          link.classList.add("active");
        } else {
          link.classList.remove("active");
        }
      });
    },
  };

  /**
   * FAQ Section Handlers
   */
  const faqHandlers = {
    initializeTabs: () => {
      const firstTab = document.querySelector("#faq-pengajuan");
      const firstTabButton = document.querySelector(
        '[data-bs-target="#faq-pengajuan"]'
      );

      if (firstTab && firstTabButton) {
        const bsTab = new bootstrap.Tab(firstTabButton);
        bsTab.show();

        firstTab.classList.add("show", "active");
        firstTabButton.classList.add("active");

        const firstFaqItem = firstTab.querySelector(".faq-item");
        if (firstFaqItem) {
          firstFaqItem.classList.add("faq-active");
        }
      }

      document.querySelectorAll('[data-bs-toggle="tab"]').forEach((tabEl) => {
        tabEl.addEventListener("shown.bs.tab", (event) => {
          const targetPane = document.querySelector(
            event.target.dataset.bsTarget
          );
          if (!targetPane) return;

          targetPane.querySelectorAll(".faq-item").forEach((item) => {
            item.classList.remove("faq-active");
          });

          const firstItem = targetPane.querySelector(".faq-item");
          if (firstItem) {
            firstItem.classList.add("faq-active");
          }
        });
      });
    },

    initializeItems: () => {
      document.querySelectorAll(".faq-item").forEach((item) => {
        const header = item.querySelector("h3");
        const toggle = item.querySelector(".faq-toggle");

        const clickHandler = () => {
          const container = item.closest(".faq-container");
          container
            .querySelectorAll(".faq-item.faq-active")
            .forEach((activeItem) => {
              if (activeItem !== item) {
                activeItem.classList.remove("faq-active");
              }
            });
          item.classList.toggle("faq-active");
        };

        if (header) header.addEventListener("click", clickHandler);
        if (toggle) toggle.addEventListener("click", clickHandler);
      });
    },
  };

  /**
   * Card Section Handlers
   */
  const cardHandlers = {
    initialize: () => {
      const cards = document.querySelectorAll(".card-item");
      const firstCard = document.querySelector('.card-item[data-tab="first"]');
      const firstTab = document.querySelector("#first");

      if (firstCard && firstTab) {
        firstCard.classList.add("active");
        firstTab.classList.add("active", "show");

        const images = firstTab.querySelectorAll(".phone-screenshot");
        images.forEach((img) => {
          img.style.opacity = "1";
          img.style.transform = "scale(0.7)";
        });
      }

      cards.forEach((card) => {
        card.addEventListener("click", function () {
          cards.forEach((c) => c.classList.remove("active"));
          this.classList.add("active");

          const tabId = this.getAttribute("data-tab");
          const targetTab = document.querySelector(`#${tabId}`);

          if (targetTab) {
            document
              .querySelectorAll("#stepsContent .tab-pane")
              .forEach((pane) => {
                pane.classList.remove("show", "active");
              });

            targetTab.classList.add("active", "show");

            const images = targetTab.querySelectorAll(".phone-screenshot");
            images.forEach((img, idx) => {
              img.style.opacity = "0";
              img.style.transform = "scale(0.7) translateY(0)";

              setTimeout(() => {
                img.style.opacity = "1";
                img.style.transform = "scale(0.7) translateY(0)";
              }, idx * ANIMATION_DELAY);
            });
          }
        });
      });
    },
  };

  /**
   * Language Switcher
   */
  async function fetchLanguageData(lang) {
    const response = await fetch(`languages/${lang}.json`);
    return response.json();
  }

  function setLanguagePreference(lang) {
    localStorage.setItem("language", lang);
  }

  function updateContent(langData) {
    document.querySelectorAll("[data-i18n]").forEach((element) => {
      const key = element.getAttribute("data-i18n");
      element.textContent = langData[key];
    });
  }

  function setActiveLanguageFlag(lang) {
    document.querySelectorAll(".flag-icon").forEach((flag) => {
      flag.classList.remove("active");
    });
    if (lang === "id") {
      document.querySelector(".flag-icon.indonesia")?.classList.add("active");
    } else if (lang === "en") {
      document.querySelector(".flag-icon.english")?.classList.add("active");
    }
  }

  async function changeLanguage(lang) {
    setLanguagePreference(lang);
    setActiveLanguageFlag(lang);
    const langData = await fetchLanguageData(lang);
    updateContent(langData);
  }

  /**
   * Swiper Configurations
   */
  const swiperConfigs = {
    partners: {
      loop: true,
      speed: 4000,
      autoplay: {
        delay: 0,
        disableOnInteraction: false,
      },
      slidesPerView: 5,
      spaceBetween: 30,
      freeMode: true,
      freeModeMomentum: false,
      grabCursor: true,
      breakpoints: {
        320: { slidesPerView: 2, spaceBetween: 20 },
        576: { slidesPerView: 3, spaceBetween: 20 },
        768: { slidesPerView: 4, spaceBetween: 30 },
        992: { slidesPerView: 5, spaceBetween: 30 },
      },
      pagination: false,
    },
  };

  /**
   * Initialize components
   */
  const initComponents = () => {
    // AOS initialization
    AOS.init({
      duration: 600,
      easing: "ease-in-out",
      once: true,
      mirror: false,
    });

    // Swiper initialization
    document.querySelectorAll(".init-swiper").forEach((element) => {
      let config = {};
      if (element.closest("#partners")) {
        config = swiperConfigs.partners;
      } else {
        const configScript = element.querySelector(".swiper-config");
        if (configScript) {
          config = JSON.parse(configScript.innerHTML.trim());
        }
      }
      new Swiper(element, config);
    });

    // GLightbox initialization
    GLightbox({
      selector: ".glightbox",
    });
  };

  /**
   * Event Listeners
   */
  const attachEventListeners = () => {
    // Scroll events
    document.addEventListener("scroll", () => {
      headerHandlers.toggleScrolled();
      headerHandlers.toggleScrolledLogo();
      headerHandlers.navmenuScrollspy();
    });

    // Mobile nav events
    selectors.mobileNavToggle?.addEventListener(
      "click",
      headerHandlers.mobileNavToggle
    );

    // Mobile nav overlay click
    selectors.mobileNavOverlay?.addEventListener("click", () => {
      selectors.body.classList.remove("mobile-nav-active");
      selectors.mobileNavToggle.classList.remove("bi-x");
      selectors.mobileNavToggle.classList.add("bi-list");
      document.body.style.overflow = "";
    });

    // Close mobile nav when a nav link is clicked
    selectors.navmenuLinks.forEach((link) => {
      link.addEventListener("click", () => {
        if (selectors.body.classList.contains("mobile-nav-active")) {
          selectors.body.classList.remove("mobile-nav-active");
          selectors.mobileNavToggle.classList.remove("bi-x");
          selectors.mobileNavToggle.classList.add("bi-list");
          document.body.style.overflow = "";
        }
      });
    });

    // Scroll top button
    selectors.scrollTop?.addEventListener("click", (e) => {
      e.preventDefault();
      window.scrollTo({ top: 0, behavior: "smooth" });
    });

    // Language selector
    document.querySelectorAll(".flag-icon").forEach((flag) => {
      flag.addEventListener("click", function () {
        const lang = this.classList.contains("indonesia") ? "id" : "en";
        changeLanguage(lang);
      });
    });
  };

  /**
   * Initialize all
   */
  document.addEventListener("DOMContentLoaded", async () => {
    initComponents();
    attachEventListeners();
    faqHandlers.initializeTabs();
    faqHandlers.initializeItems();
    cardHandlers.initialize();

    // Language initialization
    const userPreferredLanguage = localStorage.getItem("language") || "id";
    setActiveLanguageFlag(userPreferredLanguage);
    const langData = await fetchLanguageData(userPreferredLanguage);
    updateContent(langData);
  });

  /**
   * Window load handlers
   */
  window.addEventListener("load", () => {
    if (selectors.preloader) {
      selectors.preloader.remove();
    }
    headerHandlers.toggleScrolled();
    headerHandlers.toggleScrolledLogo();
    headerHandlers.navmenuScrollspy();
  });
})();
