# Vendored libraries

## Tom Select

- Version: 2.4.3 (`tom-select.complete.min.js`, `tom-select.min.css`), Apache-2.0 (`tom-select/LICENSE`).
- Source: the `tom-select` package on npm (`dist/js/tom-select.complete.min.js`, `dist/css/tom-select.min.css`) or its GitHub releases, <https://github.com/orchidjs/tom-select>.
- Used by: the `hivelog-select` library (`beeswax.libraries.yml`), attached through `libraries-extend` in `beeswax.info.yml`, with `lib/hivelog-select.js` and `src/tom-select.css` on top.
- To upgrade: replace the two files from the new release, keep the version header comment, update the version here and in the comment in `beeswax.libraries.yml`, then check a HiveLog filter form and an entity add/edit form in light and dark.
