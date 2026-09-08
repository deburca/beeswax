/**
 * @file
 * Upgrades the HiveLog table-filter <select> elements to Tom Select so the
 * option list can be themed to match Beeswax (a native <select> popup is
 * browser chrome and cannot be styled). Scoped to `.hivelog-filter-form`
 * via `libraries-extend` on `hivelog/filter_form` in beeswax.info.yml.
 */
((Drupal, once) => {
  Drupal.behaviors.beeswaxHivelogFilterSelect = {
    attach(context) {
      if (typeof TomSelect === 'undefined') {
        return;
      }
      once('bw-tom-select', '.hivelog-filter-form select', context).forEach(
        (el) => {
          // eslint-disable-next-line no-new
          new TomSelect(el, {
            // Short filter lists — no search box, click to open.
            controlInput: null,
            allowEmptyOption: true,
            // Keep the current filter value selected on render.
            hidePlaceholder: false,
          });
        },
      );
    },
  };
})(Drupal, once);
