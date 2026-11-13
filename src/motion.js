import { animate, inView } from "motion";

// Animation transforms for each type: [from, to].
const animationTransforms = {
  fade_up: ["translateY(100px)", "translateY(0px)"],
  fade_down: ["translateY(-100px)", "translateY(0px)"],
  fade_left: ["translateX(100px)", "translateX(0px)"],
  fade_right: ["translateX(-100px)", "translateX(0px)"],
};

/**
 * Reveals an element: fades and slides it in from its animation type.
 *
 * The element is hidden by CSS (src/main.css, `[data-animation]`) only while
 * scripting is on and the visitor has not asked for reduced motion, so a
 * failure anywhere in here leaves it visible (and a CSS timer reveals it if
 * this script never runs at all). `data-animated` lifts the hidden state.
 */
function reveal(element) {
  element.setAttribute("data-animated", "");

  if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
    return;
  }

  const animationType = element.dataset.animation || "fade_up";
  const transform = animationTransforms[animationType] || animationTransforms.fade_up;

  // Delay and duration come from data attributes in milliseconds.
  const delay = (parseFloat(element.dataset.delay) || 0) / 1000;
  const duration = (parseFloat(element.dataset.duration) || 300) / 1000;

  animate(element, { opacity: [0, 1], transform }, { delay, duration, ease: "easeOut" }).then(() => {
    // Leave no inline opacity/transform behind: `data-animated` already
    // un-hides the element, and a stray transform makes a stacking context.
    // Next frame, because motion commits its final styles after this resolves.
    requestAnimationFrame(() => {
      element.style.removeProperty("opacity");
      element.style.removeProperty("transform");
    });
  });
}

Drupal.behaviors.beeswaxMotion = {
  attach(context) {
    once("bw-motion", "[data-animation]", context).forEach((element) => {
      // Start as soon as any part of the element is in view (a group taller
      // than the screen can never be wholly in view), a little before it
      // reaches the bottom edge. One observer per element, dropped after the
      // first reveal so scrolling back does not replay it.
      const stop = inView(
        element,
        () => {
          stop();
          reveal(element);
        },
        { amount: "some", margin: "0px 0px -10% 0px" },
      );
    });
  },
};
