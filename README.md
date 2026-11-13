# Beeswax Theme

Beeswax is a Drupal theme with a bee and honey identity (a pine and beeswax palette, Fraunces and IBM Plex type, light and dark). It was forked from Mercury and is built with Tailwind CSS and single directory components.

What it provides:

- **A skin for [HiveLog](https://www.drupal.org/project/hivelog)** (`src/hivelog.css`): HiveLog's dashboard, lists, forms and status colours drawn in the Beeswax palette. Beeswax does not need HiveLog; without it that stylesheet has nothing to match.
- **Account pages:** sign-in, registration, password reset, two-factor and the account edit pages as one framed card.
- **A footer line:** optional contact, app link, menu, copyright, credit and the versions running.
- **A honeycomb background** behind HiveLog, account and sign-in pages.
- **Components** (`components/`) for [Drupal Canvas](/project/canvas) and plain Twig use. Canvas is optional: the HiveLog and account pages do not use it.

## Customization

See [CUSTOMIZING.md](CUSTOMIZING.md) for detailed instructions on how to customize this theme, including fonts, colors, components, and JavaScript.

## Known issues

Canvas's code components are currently not compatible with Tailwind-based themes like Beeswax, and creating a code component will break Beeswax's styling. This will be fixed in [#3549628], but for now, here's how to work around it:

1. In Canvas's in-browser code editor, open the Global CSS tab.
2. Paste the contents of your custom theme's `theme.css` into the code editor. It must be at the top.
3. Paste the contents of your custom theme's `main.css` into the code editor, removing all the `@import` statements at the top first. It must come _after_ the contents of `theme.css`.
4. Save the global CSS.

## Getting help

If you have trouble or questions, please [visit the issue queue](https://www.drupal.org/project/issues/beeswax?categories=All) or find us on [Drupal Slack](https://www.drupal.org/community/contributor-guide/reference-information/talk/tools/slack), in the `#drupal-cms-support` channel.

## Roadmap

Beeswax is under active development. Planned improvements include more components, better customization options, and [Storybook support](https://www.drupal.org/project/beeswax/issues/3562711). If you want to contribute to Beeswax, check out the `#drupal-cms-development` channel in Drupal Slack.
