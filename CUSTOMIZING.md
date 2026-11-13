## Customizing Beeswax

**Don't subtheme Beeswax!** It does not provide backwards compatibility. This allows us to rapidly innovate, iterate, and improve it. If you create a sub-theme of Beeswax, it is likely to break in the future.

### Fonts & colors

To change the fonts or colors, copy the `src/theme.css` and `src/fonts.css` files to the web root, so that `theme.css` and `fonts.css` are sitting next to `index.php`. Commit them to your Git repository, and clear Drupal's cache. You can customize them however you like; changes you make to it will be reflected immediately on your site.

Two things to know about the copy:

- **Font paths.** `fonts.css` refers to the font files with relative URLs (`../fonts/...`), which only resolve from `src/`. In the copy, change each `../fonts/` to the site-absolute path of the theme's `fonts/` directory (for example `/themes/custom/beeswax/fonts/`), or point the rules at your own font files. Drupal serves the copy as it is and does not rewrite URLs.
- **Preloading and caches.** While no `fonts.css` sits in the web root, the theme preloads Fraunces and IBM Plex Sans (latin). Once you copy it, the preload is skipped, because your set may differ. Adding or removing either copy is picked up after a cache clear.

### Light, dark, or the visitor's device

Appearance > Beeswax > **Color scheme** is _Light_, _Dark_ or _Match the device_. The dark palette is the `.dark` block in
`src/theme.css`; it is the same palette the Vinculum iOS app uses (`Palette.swift` there copies these `--bw-*` tokens), so
change a colour here first, then there. _Match the device_ sets the `dark` class on `<html>` from a small script (`lib/scheme-auto.js`, attached from
`templates/layout/html.html.twig`) before anything paints, and again when the device's setting changes; with JavaScript off
the page stays light. If the site's admin theme is Gin, set its _Dark mode_ to _Auto_ too so its login and admin pages agree.

### HiveLog status colours

Beeswax maps HiveLog's `--hivelog-*` surface and status tokens from its own `--bw-*` palette (`body.hivelog-page` in
`src/hivelog.css`), including the `ok` pair (`--hivelog-ok`, `--hivelog-ok-tint`) used for positive states such as an
inspection's "Clear". Those two tokens first appeared in the HiveLog release that added the `ok` stat-tile sub-line
(HiveLog task 0256); on an older HiveLog they are simply unused. `--bw-ok` is a yellower green than the pine accent so a
status stays distinct from the brand colour; it keeps at least 4.5:1 text contrast in both modes.

### Strict Content-Security-Policy

The scheme switch for _Match the device_ is a file (`lib/scheme-auto.js`, library `beeswax/scheme-auto`), not inline script, so a policy that forbids inline script does not break it. The theme's templates add no inline script (`<style>` and inline `style` attributes are a separate directive, `style-src`).

### Compiled CSS

`src/hivelog.css` is minified into `build/hivelog.min.css` by `npm run build`; edit the source, not the build. Only `theme.css` and `fonts.css` are served as written, because those are the ones meant to be copied and hot-edited.

### Where HiveLog.css fights something else

For whoever retires these as HiveLog changes: the two `!important`s in `src/hivelog.css` are on `body.path-user` table headers (`position: static` over Gin's sticky header, and `display: none` for Gin's sticky copy `gin--sticky-table-header`); both go once the admin pages no longer carry Gin's markup. The `body.hivelog-page`, `body.path-user` and `:is(...)` chains exist to outrank the HiveLog module's own `component`-group CSS and Tailwind's utilities, since the skin sits in the `theme` group after them.

### Honeycomb background

HiveLog pages, the account pages and the sign-in cards get a faint honeycomb behind the top of the page, the same one the
Vinculum app draws (`HoneycombBackground.swift` there): pointy-top hexagon outlines in the accent colour at 10% opacity,
fading to nothing downwards (600px, or 380px on the sign-in cards). It is a pseudo-element on the body (`src/hivelog.css`),
so it needs no markup, takes no clicks, scrolls away with the page and is hidden when printing. Tune it in `src/theme.css`:
`--bw-honeycomb-opacity` (`0` turns it off), `--bw-honeycomb-cell` (the hexagon radius, 38px), `--bw-honeycomb-height` and
`--bw-honeycomb-height-short`. The colour is `--bw-accent`, so it follows the colour scheme and your own palette. The
geometry (cell 38, outlines drawn at 0.96 of the cell, 10%) is shared with the app: change it there too.

### Footer small print

Under the footer region the theme adds one line: the versions running ("HiveLog 3.4.0 · Beeswax 1.3.0", so a bug report can
name them) and, if you set them under Appearance > Beeswax, a **Contact** link (an email address or a web address) and a
**Get the Vinculum app** link (a web address). Leave a setting empty for no link. The settings are `footer_contact` and
`footer_app_url` in `beeswax.settings`; the markup is in `templates/layout/page.html.twig` and the style is
`.beeswax-footer-meta` in `src/hivelog.css`.

### Account pages

Log in, register, reset password, the profile page and edit / cancel account are ordinary pages in this theme (`body.path-user`
in `src/hivelog.css`): the same header, footer, field boxes and buttons as the HiveLog pages. Drupal draws the edit and
cancel pages in the _admin_ theme unless the route's `_admin_route` option is cleared, and the Gin Login module draws the
log-in pages in Gin; a site that wants them here must undo both (for example `hvg_landing`'s route subscriber in the
`cms2` project, and uninstalling Gin Login).

### Advanced customizations

If you want to make deeper customizations (e.g., to components or JavaScript), you will need to convert Beeswax to a custom theme with the same machine name. You can do this by running the following at the command line, from the Drupal project root (assuming `web` is the web root):

```shell
mkdir -p web/themes/custom
cp -R web/themes/contrib/beeswax web/themes/custom/beeswax
git add web/themes/custom/beeswax
composer remove drupal/beeswax
```

Finally, clear Drupal's cache (via the UI, or `drush cache:rebuild`).

### Custom components

Beeswax uses [single-directory components](https://www.drupal.org/docs/develop/theming-drupal/using-single-directory-components) and comes with a variety of commonly used components. You can add new components and modify existing ones, but be sure to rebuild the CSS when you make changes.

## Building CSS

Beeswax uses [Tailwind](https://tailwindcss.com) to simplify styling by using classes to compose designs directly in the markup.

If you want to customize the Tailwind-generated CSS, install the development tooling dependencies by running `npm install` in your theme's directory.

If you modify CSS files or classes in a Twig template, run `npm run build` to rebuild the CSS. For development, you can use `npm run dev` to watch for changes and automatically rebuild the CSS.

## Code Formatting

Beeswax uses [Prettier](https://prettier.io) to automatically format code for consistency. The project is configured with plugins for Tailwind CSS and Twig templates.

For the best experience, [set up Prettier in your editor](https://prettier.io/docs/editors) to automatically format files on save.

Run `npm run format` to format all files in your theme, or `npm run format:check` to check if files are formatted correctly without making changes.

**Note**: Some files are excluded from formatting via `.prettierignore`, such as Drupal's `html.html.twig` template, which contains placeholder tokens that break Prettier's HTML parsing.

## Component JavaScript

`lib/component.js` has two classes you can use to nicely encapsulate your component JS without pasting all the `Drupal.behaviors.componentName` boilerplate into every file. The steps are:

1. Extend the `ComponentInstance` class to a new class with the code for your component.
2. Create a new instance of the `ComponentType` class to automatically activate all the component instances on that page.

For example, here's a stub of `accordion.js`:

```js
import { ComponentType, ComponentInstance } from "../../lib/component.js";

// Make a new class with the code for our component.
//
// In every method of this class, `this.el` is an HTMLElement object of
// the component container, whose selector you provide below. You don't
// have an array of elements that you have to `.forEach()` over yourself;
// the ComponentType class handles all that for you.
class Accordion extends ComponentInstance {
  // Every subclass must have an `init` method to activate the component.
  init() {
    this.el.querySelector(".accordion--content").classList.toggle("visible");
    this.el.addClass("js");
  }

  // You may also implement a `remove()` method to clean up when a component is
  // about to be removed from the document. This will be invoked during the
  // `detach()` method of the Drupal behavior.

  // You can create as many other methods as you want; in all of them,
  // `this.el` represents the single instance of the component. Any other
  // properties you add to `this` will be isolated to that one instance
  // as well.
}

// Now we instantiate ComponentType to find the component elements and run
// our script.
new ComponentType(
  // First argument: The subclass of ComponentInstance we just created above.
  Accordion,
  // Second argument: A camel-case unique ID for the behavior (and for `once()`
  // if applicable).
  "accordion",
  // Third argument: A selector for `querySelectorAll()`. All matching elements
  // on the page get their own instance of the subclass you created, each of
  // which has `this.el` pointing to one of those matches.
  ".accordion",
);
```

This is all the code required to be in each component. The ComponentType instance handles finding the elements, running them through `once` if available, and adding them to `Drupal.behaviors`.

All the objects created this way will be stored in a global variable so you can do stuff with them later. Since the `namespace` variable at the top of component.js is `beeswaxComponents`, you would find the Accordion's ComponentType instance at `window.beeswaxComponents.accordion`.

Furthermore, `window.beeswaxComponents.accordion.instances` is an array of all the ComponentInstance objects, and `window.beeswaxComponents.accordion.elements` is an array of all the component container elements.

## Sign-in cards

Log in, password reset and log out are drawn as one framed card, centred on wide
screens; the breadcrumb and title above it are narrowed to the same column. Core's
forms get the class `beeswax-auth-form` from the theme. A form of your own (a
sign-up, a two-factor code, a captcha step) opts in by adding it:

```php
$form['#attributes']['class'][] = 'beeswax-auth-form';
```

The card is `--bw-auth-width` wide (default `30rem`); redefine it on `:root` to change it.

## Modules that send administrators to the admin theme

`tfa` (two-factor setup), `scheduler` (the scheduled-content list on a person's page) and `webform` (a person's
submissions) each draw some of a person's own pages in the admin theme for administrators. To keep them in Beeswax
for everyone, add a theme negotiator to your site (a service tagged `theme_negotiator` with a priority above
scheduler's 10) that returns the default theme for those route names, and clear `_admin_route` on the `tfa.*`
routes in a route subscriber so the header and footer blocks show. The hivelog.eu site does this in its
`hvg_landing` module (`UserPagesThemeNegotiator`, `UserPagesRouteSubscriber`).
