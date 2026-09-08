/**
 * @file
 * Upgrades HiveLog <select> elements to Tom Select so the option list can
 * be themed to match Beeswax — a native <select> popup is browser chrome
 * and cannot be styled. Covers the table-filter selects
 * (`.hivelog-filter-form`) and the entity add/edit form selects
 * (`.hivelog-entity-form` — Type / Material / Status …). Attached via
 * `libraries-extend` on `hivelog/filter_form` and `hivelog/forms` in
 * beeswax.info.yml, so it loads only where one of those forms renders.
 */
((Drupal, once) => {
  Drupal.behaviors.beeswaxHivelogSelect = {
    attach(context) {
      if (typeof TomSelect === 'undefined') {
        return;
      }
      const selector =
        '.hivelog-filter-form select, .hivelog-entity-form select';
      once('bw-tom-select', selector, context).forEach((el) => {
        // eslint-disable-next-line no-new
        new TomSelect(el, {
          // Short option lists — no search box, click to open.
          controlInput: null,
          allowEmptyOption: true,
          // Keep the current value selected on render.
          hidePlaceholder: false,
        });
      });
    },
  };
})(Drupal, once);
