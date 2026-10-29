<?php

declare(strict_types=1);

namespace Drupal\beeswax\Hook;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Messenger\MessengerTrait;
use Drupal\Core\StringTranslation\StringTranslationTrait;
use Drupal\beeswax\RenderCallbacks;
use Drupal\Core\Routing\RouteMatchInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\HttpFoundation\RequestStack;

/**
 * Contains hook implementations for Beeswax.
 */
final class ThemeHooks {

  use MessengerTrait;
  use StringTranslationTrait;

  /**
   * The Drupal root.
   */
  private static ?string $appRoot = NULL;

  public function __construct(
    private readonly ThemeSettingsProvider $themeSettings,
    private readonly RequestStack $requestStack,
    private readonly ThemeExtensionList $themeList,
    private readonly EntityTypeManagerInterface $entityTypeManager,
    private readonly RouteMatchInterface $routeMatch,
    private readonly TitleResolverInterface $titleResolver,
    private readonly ChainBreadcrumbBuilderInterface $breadcrumb,
    private readonly ModuleHandlerInterface $moduleHandler,
    private readonly ModuleExtensionList $moduleList,
    #[Autowire(param: 'app.root')] string $appRoot,
  ) {
    self::$appRoot ??= $appRoot;
  }

  /**
   * Implements hook_element_info_alter().
   */
  #[Hook('element_info_alter')]
  public function alterElementInfo(array &$info): void {
    $info['component']['#pre_render'][] = [RenderCallbacks::class, 'preRenderComponent'];
  }

  /**
   * Implements hook_library_info_alter().
   */
  #[Hook('library_info_alter')]
  public function alterLibraryInfo(array &$libraries, string $extension): void {
    $override = static function (string $name, string $replacement) use (&$libraries): void {
      $old_parents = ['global', 'css', 'theme', $name];
      $new_parents = [...array_slice($old_parents, 0, -1), $replacement];
      $css_settings = NestedArray::getValue($libraries, $old_parents);
      NestedArray::setValue($libraries, $new_parents, $css_settings);
      NestedArray::unsetValue($libraries, $old_parents);
    };
    if ($extension === 'beeswax') {
      if (file_exists(self::$appRoot . '/theme.css')) {
        $override('src/theme.css', '/theme.css');
      }
      if (file_exists(self::$appRoot . '/fonts.css')) {
        $override('src/fonts.css', '/fonts.css');
      }
    }
  }

  /**
   * Implements hook_form_FORM_ID_alter().
   */
  #[Hook('form_system_theme_settings_alter')]
  public function themeSettingsFormAlter(array &$form): void {
    $form['scheme'] = [
      '#type' => 'radios',
      '#title' => t('Color scheme'),
      '#default_value' => $this->themeSettings->getSetting('scheme'),
      '#options' => [
        'light' => t('Light'),
        'dark' => t('Dark'),
        'auto' => t('Match the device (light or dark)'),
      ],
    ];
    $form['footer_contact'] = [
      '#type' => 'textfield',
      '#title' => t('Footer: contact'),
      '#default_value' => $this->themeSettings->getSetting('footer_contact'),
      '#description' => t('An email address or a web address (https://…). Shown as a "Contact" link in the footer; leave empty for none.'),
    ];
    $form['footer_app_url'] = [
      '#type' => 'textfield',
      '#title' => t('Footer: app link'),
      '#default_value' => $this->themeSettings->getSetting('footer_app_url'),
      '#description' => t('A web address (https://…), such as the app\'s App Store page. Shown as a "Get the Vinculum app" link in the footer; leave empty for none.'),
    ];
    $message = $this->t("See <code>@path</code> to learn how to customize Beeswax's fonts, colors, and components.", [
      '@path' => $this->themeList->getPath('beeswax') . '/CUSTOMIZING.md',
    ]);
    $this->messenger()->addMessage($message, 'info');
  }

  /**
   * Implements template_preprocess_image_widget().
   */
  #[Hook('preprocess_image_widget')]
  public function preprocessImageWidget(array &$variables): void {
    $data = &$variables['data'];

    // This prevents image widget templates from rendering preview container
    // HTML to users that do not have permission to access these previews.
    // @todo revisit in https://drupal.org/node/953034
    // @todo revisit in https://drupal.org/node/3114318
    if (isset($data['preview']['#access']) && $data['preview']['#access'] === FALSE) {
      unset($data['preview']);
    }
  }

  /**
   * Implements template_preprocess_html().
   */
  #[Hook('preprocess_html')]
  public function preprocessHtml(array &$variables): void {
    $variables['scheme'] = $this->themeSettings->getSetting('scheme');
    // Get the theme base path for font preloading.
    $variables['beeswax_path'] = $this->requestStack->getCurrentRequest()->getBasePath() . '/' . $this->themeList->getPath('beeswax');

    // The `hivelog-page` <body> class that src/hivelog.css scopes to is
    // added by the module itself (hivelog_preprocess_html(), hivelog
    // >= 1.8.3) — no theme-side copy needed.
  }

  /**
   * Implements template_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables): void {
    // @see \Drupal\Core\Block\Plugin\Block\PageTitleBlock::build()
    $variables['title'] = [
      '#type' => 'page_title',
      '#title' => $variables['page']['#title'] ?? $this->titleResolver->getTitle(
        $this->requestStack->getCurrentRequest(),
        $this->routeMatch->getRouteObject(),
      ),
    ];

    // @see \Drupal\system\Plugin\Block\SystemBreadcrumbBlock::build()
    $variables['breadcrumb'] = $this->breadcrumb->build($this->routeMatch)
      ->toRenderable();

    $variables['footer_meta'] = $this->footerMeta();

    $route_name = $this->routeMatch->getRouteName();
    if ($route_name === 'entity.canvas_page.canonical' || str_starts_with($this->routeMatch->getRouteObject()?->getPath() ?? '', '/canvas/')) {
      $variables['rendered_by_canvas'] = TRUE;
    }
    elseif ($route_name === 'entity.node.canonical' && $this->moduleHandler->moduleExists('canvas')) {
      $node = $this->routeMatch->getParameter('node');
      assert($node instanceof NodeInterface);

      $variables['rendered_by_canvas'] = (bool) $this->entityTypeManager->getStorage('content_template')
        ->getQuery()
        ->accessCheck(FALSE)
        ->count()
        ->condition('content_entity_type_id', 'node')
        ->condition('content_entity_type_bundle', $node->getType())
        ->condition('content_entity_type_view_mode', 'full')
        ->condition('status', TRUE)
        ->execute();
    }
    else {
      $variables['rendered_by_canvas'] = FALSE;
    }
  }

  /**
   * The footer's small print: contact, app link and the versions running.
   *
   * Contact and app link come from the theme settings and show only when set
   * (an address with no scheme is read as an email address; anything that is
   * neither an email nor an http(s) URL is dropped). The versions are the
   * installed HiveLog module's and this theme's, so a bug report can name them.
   *
   * @return array{links: list<array{text: \Drupal\Core\StringTranslation\TranslatableMarkup, url: string}>, versions: string}
   *   Empty when there is nothing to show.
   */
  private function footerMeta(): array {
    $links = [];
    $contact = trim((string) $this->themeSettings->getSetting('footer_contact'));
    if ($contact !== '') {
      $url = filter_var($contact, FILTER_VALIDATE_EMAIL) ? 'mailto:' . $contact : $this->webUrl($contact);
      if ($url) {
        $links[] = ['text' => $this->t('Contact'), 'url' => $url];
      }
    }
    $app = $this->webUrl(trim((string) $this->themeSettings->getSetting('footer_app_url')));
    if ($app) {
      $links[] = ['text' => $this->t('Get the Vinculum app'), 'url' => $app];
    }

    $versions = [];
    if ($this->moduleHandler->moduleExists('hivelog')) {
      $version = $this->moduleList->getExtensionInfo('hivelog')['version'] ?? '';
      $versions[] = 'HiveLog' . ($version ? ' ' . $version : '');
    }
    $theme_version = $this->themeList->getExtensionInfo('beeswax')['version'] ?? '';
    $versions[] = 'Beeswax' . ($theme_version ? ' ' . $theme_version : '');

    return ['links' => $links, 'versions' => implode(' · ', $versions)];
  }

  /**
   * Returns the URL if it is an http(s) address, otherwise NULL.
   */
  private function webUrl(string $value): ?string {
    return preg_match('#^https?://[^\s]+$#i', $value) ? $value : NULL;
  }


}
