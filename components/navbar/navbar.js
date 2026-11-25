import { ComponentType, ComponentInstance } from "../../lib/component.js";
import { measureScrollbarAndObserve } from "../../lib/measureScrollbar.js";

class Navbar extends ComponentInstance {
  #savedAsOpen = false;
  #desktopQuery;
  #inertElements = [];

  init() {
    this.closeButton = this.el.querySelector(".navbar--hide-menu");
    this.menuButton = this.el.querySelector(".navbar--hamburger");
    this.menu = this.el.querySelector(".navbar--menu");

    measureScrollbarAndObserve(this.el.querySelector(".navbar--dropdown-menu"));

    this.menuButton.addEventListener("click", () => {
      this.isOpen = true;
    });

    this.closeButton.addEventListener("click", () => {
      this.isOpen = false;
    });

    // The overlay only exists below the desktop breakpoint: if the window
    // grows while it is open, close it so the page is not left inert.
    this.#desktopQuery = window.matchMedia("(min-width: 48rem)");
    this.#desktopQuery.addEventListener("change", (e) => {
      if (e.matches && this.isOpen) {
        this.isOpen = false;
      }
    });

    // Keep up with scroll amount for mobile menu positioning.
    const scrollHandler = this.measureScrollTop.bind(this);
    const desktopMQ = this.#desktopQuery;

    // Attach on page load only if less than desktop width AND navbar is visible.
    if (!desktopMQ.matches && this.el.getBoundingClientRect().bottom > 0) {
      scrollHandler();
      window.addEventListener("scroll", scrollHandler);
    }

    // Respond to window width changes, also checking scroll position.
    desktopMQ.addEventListener("change", (e) => {
      if (!e.matches && this.el.getBoundingClientRect().bottom > 0) {
        scrollHandler();
        window.addEventListener("scroll", scrollHandler);
      } else {
        window.removeEventListener("scroll", scrollHandler);
      }
    });

    // Respond to scroll position changes, also checking window width.
    const intersectionObserver = new IntersectionObserver((entries) => {
      for (const entry of entries) {
        if (entry.isIntersecting && !desktopMQ.matches) {
          scrollHandler();
          window.addEventListener("scroll", scrollHandler);
        } else {
          window.removeEventListener("scroll", scrollHandler);
        }
      }
    });

    intersectionObserver.observe(this.el);
  }

  set isOpen(value) {
    const open = !!value;
    if (open === this.#savedAsOpen) {
      return;
    }
    this.#savedAsOpen = open;

    if (open) {
      this.menu.classList.add("navbar--menu--open");
      document.documentElement.classList.add("navbar-modal-open");
      this.menuButton.setAttribute("aria-expanded", "true");
      this.#makeRestInert();
      // Escape closes the overlay, as it does any dialog.
      document.addEventListener("keydown", this.#onKeydown);
      this.menu.querySelector("a, button").focus();
    } else {
      this.menu.classList.remove("navbar--menu--open");
      document.documentElement.classList.remove("navbar-modal-open");
      this.menuButton.setAttribute("aria-expanded", "false");
      document.removeEventListener("keydown", this.#onKeydown);
      this.#restoreInert();
      // Return focus to the control that opened the overlay. The button is
      // not visible on desktop, where focus is left alone.
      if (!this.#desktopQuery.matches) {
        this.menuButton.focus();
      }
    }
  }

  #onKeydown = (event) => {
    if (event.key === "Escape") {
      this.isOpen = false;
    }
  };

  // Make everything except the overlay inert while it is open, so Tab and
  // screen readers cannot reach the page behind it. Walks from the menu up to
  // <body> and marks every sibling on the way.
  #makeRestInert() {
    this.#inertElements = [];
    for (let node = this.menu; node && node !== document.body; node = node.parentElement) {
      for (const sibling of node.parentElement.children) {
        if (sibling !== node && !sibling.inert) {
          sibling.inert = true;
          this.#inertElements.push(sibling);
        }
      }
    }
  }

  #restoreInert() {
    this.#inertElements.forEach((element) => {
      element.inert = false;
    });
    this.#inertElements = [];
  }

  get isOpen() {
    return this.#savedAsOpen;
  }

  measureScrollTop() {
    document.documentElement.style.setProperty("--navbar-scroll-top", `${window.scrollY}px`);
  }
}

window.navbar = new ComponentType(Navbar, "navbar", ".navbar");
