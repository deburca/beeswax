import { ComponentType, ComponentInstance } from "../../lib/component.js";
import currentlyInCanvasEditor from "../../lib/currentlyInCanvasEditor.js";

class Accordion extends ComponentInstance {
  // An internal private property to keep up with the current state of the
  // accordion. The hash makes it so you can't get or set this property outside
  // of this file.
  #savedAsOpen;
  #resizeObserver;

  static openClass = "accordion--open";
  static animateClass = "accordion--animate";

  init() {
    if (currentlyInCanvasEditor()) {
      // In Canvas editor, show content by removing collapsed state classes
      const content = this.el.querySelector(".accordion--content");
      content.classList.remove("h-0", "py-0", "overflow-hidden");
      content.classList.add("h-auto", "py-4", "overflow-visible");
      return;
    }

    // Save our togglable elements for easy reference.
    this.button = this.el.querySelector(".accordion--title");
    this.contentContainer = this.el.querySelector(".accordion--content");

    // With the `set isOpen()` below, merely setting this property does all the
    // stuff necessary to open or close the accordion.
    this.isOpen = this.el.dataset.openByDefault === "true";

    // Figure out what height the content will be when open so we can smoothly
    // animate to it with CSS.
    this.measureNaturalHeight();

    // Remeasure the height when the width changes (debounced). The observer is
    // disconnected in remove(), so a re-rendered accordion leaves nothing behind.
    let timeout = 0;
    let lastWidth = this.el.getBoundingClientRect().width;

    this.#resizeObserver = new ResizeObserver(() => {
      const width = this.el.getBoundingClientRect().width;
      if (width === lastWidth) {
        return;
      }
      lastWidth = width;
      this.el.classList.add("accordion--resizing");
      window.clearTimeout(timeout);
      timeout = window.setTimeout(() => {
        this.measureNaturalHeight();
        this.el.classList.remove("accordion--resizing");
      }, 350);
    });
    this.#resizeObserver.observe(this.el);

    // Make the button work.
    this.button.addEventListener("click", () => {
      // Toggle the accordion.
      this.isOpen = !this.isOpen;
    });

    this.el.classList.add("accordion--js");
    void this.el.offsetHeight;
    this.el.classList.add(Accordion.animateClass);
  }

  // This setter makes it so the accordion can be opened and closed just by
  // doing `this.isOpen = true` or `this.isOpen = false` rather than calling a
  // method. The advantage is that (for example) if you have a boolean variable
  // `shouldOpen`, you can just do `this.isOpen = shouldOpen` rather than all
  // this:
  //
  // ```js
  // if(shouldOpen) {
  //   this.open();
  // } else {
  //   this.close();
  // }
  // ```
  set isOpen(val) {
    if (val) {
      // First do all the DOM manipulation needed to actually open the
      // accordion.
      this.el.classList.add(Accordion.openClass);
      this.contentContainer.inert = false;
      this.button.setAttribute("aria-expanded", "true");

      // Then stash the current state in a simple private property with no
      // getters or setters involved.
      this.#savedAsOpen = true;

      // Dispatch an event that any accordion container ancestors can use to
      // close other accordions.
      this.el.dispatchEvent(new Event("accordionopen", { bubbles: true }));
    } else {
      // DOM manipulation.
      this.el.classList.remove(Accordion.openClass);
      // A closed panel is only clipped by CSS: `inert` also takes it out of
      // the tab order and the accessibility tree, and keeps any focusable
      // content added later out of both.
      this.contentContainer.inert = true;
      this.button.setAttribute("aria-expanded", "false");
      // Stash current state.
      this.#savedAsOpen = false;
    }
  }

  // Get the simple boolean we saved in the setter.
  get isOpen() {
    return this.#savedAsOpen;
  }

  // Measure how tall the content should be when open so we can smoothly animate
  // to it using CSS. Only classes change here, not the open state, so assistive
  // technology sees no `aria-expanded` flicker and ancestors hear nothing.
  measureNaturalHeight() {
    const hadAnimate = this.el.classList.contains(Accordion.animateClass);
    const wasOpen = this.el.classList.contains(Accordion.openClass);
    const wasResizing = this.el.classList.contains("accordion--resizing");

    // Turn off animations and lay the content out at its natural height.
    this.el.classList.remove(Accordion.animateClass);
    this.el.classList.add(Accordion.openClass, "accordion--resizing");

    // Make the natural height available to CSS as a custom property.
    const height = this.contentContainer.getBoundingClientRect().height;
    this.el.style.setProperty("--natural-height", `${height}px`);

    // Put everything back the way it was.
    if (!wasOpen) {
      this.el.classList.remove(Accordion.openClass);
    }
    if (!wasResizing) {
      this.el.classList.remove("accordion--resizing");
    }
    void this.el.offsetHeight;
    if (hadAnimate) {
      this.el.classList.add(Accordion.animateClass);
    }
  }

  // Called by ComponentType when the accordion is removed from the page.
  remove() {
    this.#resizeObserver?.disconnect();
  }
}

new ComponentType(Accordion, "accordion", ".accordion");
