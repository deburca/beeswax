# Tests

`tests/src/Kernel/FooterMetaTest.php` covers the footer small print that `ThemeHooks` builds (the app label, the versions switch, the safe-URL check, and the cache metadata with the New Year expiry).

Kernel tests need a Drupal codebase with `drupal/core-dev`, so CI does not run them. To run them, make the theme available as `web/themes/contrib/beeswax` (or `custom`) of a Drupal project that has the `cva` module and PHPUnit, then from that project's root:

```bash
SIMPLETEST_DB=sqlite://localhost/:memory: SIMPLETEST_BASE_URL=https://example.test BROWSERTEST_OUTPUT_DIRECTORY=/tmp vendor/bin/phpunit -c web/core --group beeswax web/themes/contrib/beeswax/tests
```

The PHP coding standard (`phpcs --standard=Drupal,DrupalPractice --extensions=php src tests`) runs in CI for `src`.
