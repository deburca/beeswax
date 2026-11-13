/**
 * @file
 * Customization of messages.
 */

((Drupal, once) => {
  /**
   * Adds a close button to the message.
   *
   * @param {HTMLElement} message
   *   The message element.
   */
  const closeMessage = (message) => {
    const closeBtn = message.querySelector("[data-message-close]");
    if (closeBtn) {
      closeBtn.addEventListener("click", () => {
        // Pick a neighbouring message to take focus before this one leaves.
        const neighbour = message.nextElementSibling || message.previousElementSibling;
        const nextButton = neighbour ? neighbour.querySelector("[data-message-close]") : null;

        let done = false;
        const hide = () => {
          if (done) {
            return;
          }
          done = true;
          message.classList.add("hidden");
          if (nextButton) {
            nextButton.focus();
          }
        };

        if (window.matchMedia("(prefers-reduced-motion: reduce)").matches) {
          hide();
          return;
        }

        // Fade out and slide up, then leave the layout. transitionend is not
        // guaranteed to fire (overridden CSS, element already invisible), so a
        // timer backs it up.
        message.classList.add("opacity-0", "-translate-y-2");
        message.addEventListener("transitionend", hide, { once: true });
        window.setTimeout(hide, 400);
      });
    }
  };

  /**
   * Get messages from context.
   *
   * @type {Drupal~behavior}
   *
   * @prop {Drupal~behaviorAttach} attach
   *   Attaches the close button behavior for messages.
   */
  Drupal.behaviors.beeswaxMessages = {
    attach(context) {
      once("beeswax-messages", "[data-message-item]", context).forEach(closeMessage);
    },
  };
})(Drupal, once);
