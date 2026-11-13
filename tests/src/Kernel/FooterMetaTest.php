<?php

declare(strict_types=1);

namespace Drupal\Tests\beeswax\Kernel;

use Drupal\beeswax\Hook\ThemeHooks;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\KernelTests\KernelTestBase;
use PHPUnit\Framework\Attributes\Group;
use PHPUnit\Framework\Attributes\RunTestsInSeparateProcesses;

/**
 * Tests the footer small print that ThemeHooks builds for page.html.twig.
 */
#[Group('beeswax')]
#[RunTestsInSeparateProcesses]
final class FooterMetaTest extends KernelTestBase {

  /**
   * {@inheritdoc}
   */
  protected static $modules = ['system', 'cva'];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->installConfig(['system']);
    $this->container->get('theme_installer')->install(['beeswax']);
    $this->config('system.theme')->set('default', 'beeswax')->save();
    $this->container->get('theme.manager')->setActiveTheme(
      $this->container->get('theme.initialization')->initTheme('beeswax'),
    );
  }

  /**
   * Sets theme settings on the Beeswax theme.
   *
   * @param array<string, mixed> $settings
   *   The settings to store.
   */
  private function settings(array $settings): void {
    $config = $this->config('beeswax.settings');
    foreach ($settings as $key => $value) {
      $config->set($key, $value);
    }
    $config->save();
  }

  /**
   * Calls the private footerMeta() the way preprocessPage() does.
   *
   * @return array{0: array<string, mixed>, 1: \Drupal\Core\Cache\CacheableMetadata}
   *   The footer data and the cache metadata it added.
   */
  private function footerMeta(): array {
    $hooks = $this->container->get('class_resolver')->getInstanceFromDefinition(ThemeHooks::class);
    $metadata = new CacheableMetadata();
    $method = new \ReflectionMethod($hooks, 'footerMeta');
    return [$method->invoke($hooks, $metadata), $metadata];
  }

  /**
   * A site with nothing set still shows the versions line.
   */
  public function testVersionsShownByDefault(): void {
    [$meta] = $this->footerMeta();
    $this->assertTrue($meta['visible']);
    $this->assertStringContainsString('Beeswax', $meta['versions']);
    $this->assertSame([], $meta['links']);
    $this->assertNull($meta['credit']);
  }

  /**
   * With the versions switched off and nothing else set, nothing is drawn.
   */
  public function testNothingVisibleWhenVersionsOffAndNothingSet(): void {
    $this->settings(['footer_versions' => FALSE]);
    [$meta] = $this->footerMeta();
    $this->assertFalse($meta['visible']);
    $this->assertSame('', $meta['versions']);
  }

  /**
   * The app link reads "Get the app" until the site words it.
   */
  public function testAppLinkLabel(): void {
    $this->settings(['footer_app_url' => 'https://example.com/app']);
    [$meta] = $this->footerMeta();
    $this->assertSame('Get the app', (string) $meta['links'][0]['text']);

    $this->settings(['footer_app_label' => 'Get the Vinculum app']);
    [$meta] = $this->footerMeta();
    $this->assertSame('Get the Vinculum app', (string) $meta['links'][0]['text']);
  }

  /**
   * An app address that is not http(s) is dropped.
   */
  public function testUnsafeAppUrlIsDropped(): void {
    $this->settings(['footer_app_url' => 'javascript:alert(1)']);
    [$meta] = $this->footerMeta();
    $this->assertSame([], $meta['links']);
  }

  /**
   * The footer declares the config it reads and expires at New Year.
   */
  public function testCacheMetadata(): void {
    $this->settings(['footer_copyright' => TRUE]);
    [$meta, $metadata] = $this->footerMeta();
    $this->assertStringContainsString((string) date('Y'), $meta['copyright']);
    $this->assertContains('config:system.site', $metadata->getCacheTags());
    $this->assertContains('config:beeswax.settings', $metadata->getCacheTags());
    $max_age = $metadata->getCacheMaxAge();
    $this->assertGreaterThan(0, $max_age);
    $this->assertLessThanOrEqual(366 * 86400, $max_age);
  }

}
