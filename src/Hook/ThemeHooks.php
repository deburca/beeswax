<?php

declare(strict_types=1);

namespace Drupal\beeswax\Hook;

use Drupal\Component\Utility\NestedArray;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Breadcrumb\ChainBreadcrumbBuilderInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Controller\TitleResolverInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Extension\ModuleExtensionList;
use Drupal\Core\Extension\ModuleHandlerInterface;
use Drupal\Core\Extension\ThemeExtensionList;
use Drupal\Core\Extension\ThemeSettingsProvider;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Hook\Attribute\Hook;
use Drupal\Core\Menu\MenuLinkTreeInterface;
use Drupal\Core\Menu\MenuTreeParameters;
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
  use StringTranslationTrait;

  /**
   * Core's sign-in forms, drawn as a card (see formAlter()).
   */
  private const AUTH_FORMS = [
    'user_login_form',
    'user_register_form',
    'user_pass',
    'user_pass_reset',
    'user_logout_confirm',
    // Two-factor authentication (tfa module): the code entry, the setup
    // steps, switching it off, and the overview of what is set up.
    'tfa_login_form',
    'tfa_entry_form',
    'tfa_setup',
    'tfa_disable',
    'tfa_base_overview',
    // A person's own account: edit and cancel (wider, see WIDE_AUTH_FORMS).
    'user_form',
    'user_cancel_form',
  ];

  /**
   * Cards that carry a long form, drawn at the account width.
   */
  private const WIDE_AUTH_FORMS = [
    'user_form',
    'user_cancel_form',
  ];

  /**
   * The Drupal root.
   */
  private static ?string $appRoot = NULL;

  /**
   * Canvas template lookups already made this request, by bundle.
   *
   * @var array<string, bool>
   */
  private array $canvasTemplates = [];

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
    private readonly MenuLinkTreeInterface $menuLinkTree,
    private readonly ConfigFactoryInterface $configFactory,
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
      '#title' => $this->t('Color scheme'),
      '#default_value' => $this->themeSettings->getSetting('scheme'),
      '#options' => [
        'light' => $this->t('Light'),
        'dark' => $this->t('Dark'),
        'auto' => $this->t('Match the device (light or dark)'),
      ],
    ];
    $form['footer_contact'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer: contact'),
      '#default_value' => $this->themeSettings->getSetting('footer_contact'),
      '#description' => $this->t('An email address or a web address (https://…). Shown as a "Contact" link in the footer; leave empty for none.'),
    ];
    $form['footer_app_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer: app link'),
      '#default_value' => $this->themeSettings->getSetting('footer_app_url'),
      '#description' => $this->t('A web address (https://…), such as the app\'s App Store page. Shown as an app link in the footer (see the label below); leave empty for none.'),
    ];
    $form['footer_app_label'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer: app link label'),
      '#default_value' => $this->themeSettings->getSetting('footer_app_label'),
      '#description' => $this->t('The words of the app link, such as "Get the Vinculum app". Leave empty for "Get the app".'),
    ];
    $form['footer_menu'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer: menu'),
      '#default_value' => $this->themeSettings->getSetting('footer_menu'),
      '#description' => $this->t('The machine name of a menu (such as <code>footer</code>) to list at the left of the footer\'s small print. Remove that menu from the footer component, or its links show twice. Leave empty for none.'),
    ];
    $form['footer_copyright'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer: show "© year site name"'),
      '#default_value' => $this->themeSettings->getSetting('footer_copyright'),
    ];
    $form['footer_versions'] = [
      '#type' => 'checkbox',
      '#title' => $this->t('Footer: show the HiveLog and Beeswax versions'),
      '#default_value' => $this->themeSettings->getSetting('footer_versions') ?? TRUE,
    ];
    $form['footer_credit_name'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer: credit name'),
      '#default_value' => $this->themeSettings->getSetting('footer_credit_name'),
      '#description' => $this->t('Shows "Made with ♥ by <em>name</em> with a sprinkle of ✦". Leave empty for no credit line.'),
    ];
    $form['footer_credit_url'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Footer: credit link'),
      '#default_value' => $this->themeSettings->getSetting('footer_credit_url'),
      '#description' => $this->t('A web address (https://…) the credit name links to; leave empty for no link.'),
    ];
    // A description, not a message: a message is queued again every time the
    // form is built, so it piled up when the form was rebuilt after a
    // validation error.
    $form['beeswax_customizing'] = [
      '#type' => 'item',
      '#weight' => -100,
      '#markup' => $this->t("See <code>@path</code> to learn how to customize Beeswax's fonts, colors, and components.", [
        '@path' => $this->themeList->getPath('beeswax') . '/CUSTOMIZING.md',
      ]),
    ];
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
   * Marks the sign-in forms so they are drawn as one framed, centred card.
   *
   * A site's own form (a demo sign-up, a two-factor code, a captcha step)
   * opts in by adding the same class in its own form.
   *
   * @see src/hivelog.css "Sign-in cards"
   */
  #[Hook('form_alter')]
  public function formAlter(array &$form, FormStateInterface $form_state, string $form_id): void {
    if (in_array($form_id, self::AUTH_FORMS, TRUE)) {
      $form['#attributes']['class'][] = 'beeswax-auth-form';
      if (in_array($form_id, self::WIDE_AUTH_FORMS, TRUE)) {
        $form['#attributes']['class'][] = 'beeswax-auth-form--wide';
      }
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
    // Preload only the theme's own fonts: a site that copied fonts.css to the
    // web root may use a different set, and a preload the page never uses
    // costs a request and triggers a browser warning.
    $variables['preload_fonts'] = !file_exists(self::$appRoot . '/fonts.css');

    // The `hivelog-page` <body> class that src/hivelog.css scopes to is
    // added by the module itself (hivelog_preprocess_html(), hivelog
    // >= 1.8.3) — no theme-side copy needed.
  }

  /**
   * Implements template_preprocess_page().
   */
  #[Hook('preprocess_page')]
  public function preprocessPage(array &$variables): void {
    $metadata = new CacheableMetadata();
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

    $variables['footer_meta'] = $this->footerMeta($metadata);

    $route_name = $this->routeMatch->getRouteName();
    if ($route_name === 'entity.canvas_page.canonical' || str_starts_with($this->routeMatch->getRouteObject()?->getPath() ?? '', '/canvas/')) {
      $variables['rendered_by_canvas'] = TRUE;
    }
    elseif ($route_name === 'entity.node.canonical' && $this->moduleHandler->moduleExists('canvas')) {
      $node = $this->routeMatch->getParameter('node');
      $storage = $this->entityTypeManager->getStorage('content_template');
      $variables['rendered_by_canvas'] = $node instanceof NodeInterface && $this->hasCanvasTemplate($node->getType());
      // The answer changes when a content template is added, changed or
      // removed.
      $metadata->addCacheTags($storage->getEntityType()->getListCacheTags());
    }
    else {
      $variables['rendered_by_canvas'] = FALSE;
    }

    $metadata->applyTo($variables['page']);
  }

  /**
   * Whether an enabled full-view Canvas content template exists for a bundle.
   *
   * Memoised for the request, so a page that asks twice runs one query.
   */
  private function hasCanvasTemplate(string $bundle): bool {
    return $this->canvasTemplates[$bundle] ??= (bool) $this->entityTypeManager->getStorage('content_template')
      ->getQuery()
      ->accessCheck(FALSE)
      ->count()
      ->condition('content_entity_type_id', 'node')
      ->condition('content_entity_type_bundle', $bundle)
      ->condition('content_entity_type_view_mode', 'full')
      ->condition('status', TRUE)
      ->execute();
  }

  /**
   * The footer's small print, modelled on the shh footer.
   *
   * Left: a menu (the `footer_menu` setting) and the Contact and app links.
   * Right: "© year site name", an optional credit line and the versions
   * running. Each part shows only when it is set; with none set the footer
   * shows the versions alone, as it did before.
   *
   * Contact and app link come from the theme settings (an address with no
   * scheme is read as an email address; anything that is neither an email nor
   * an http(s) URL is dropped). The versions are the installed HiveLog
   * module's and this theme's, so a bug report can name them.
   *
   * @return array<string, mixed>
   *   `menu` (a render array or NULL), `links` (list of text and url),
   *   `copyright`, `credit` (name and optional url, or NULL), `versions` and
   *   `visible` (whether any of them is set).
   */
  private function footerMeta(CacheableMetadata $metadata): array {
    // Everything below comes from these settings and the site name, and the
    // year changes on 1 January.
    $metadata->addCacheTags(['config:system.site', 'config:beeswax.settings', 'config:system.theme.global']);
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
      $label = trim((string) $this->themeSettings->getSetting('footer_app_label'));
      $links[] = ['text' => $label !== '' ? $label : $this->t('Get the app'), 'url' => $app];
    }

    // Shown unless a site switches it off (a setting stored before this one
    // existed reads as NULL, which keeps the line).
    $versions = [];
    if ($this->themeSettings->getSetting('footer_versions') ?? TRUE) {
      if ($this->moduleHandler->moduleExists('hivelog')) {
        $version = $this->moduleList->getExtensionInfo('hivelog')['version'] ?? '';
        $versions[] = 'HiveLog' . ($version ? ' ' . $version : '');
      }
      $theme_version = $this->themeList->getExtensionInfo('beeswax')['version'] ?? '';
      $versions[] = 'Beeswax' . ($theme_version ? ' ' . $theme_version : '');
    }

    $site_name = (string) $this->configFactory->get('system.site')->get('name');
    if ($this->themeSettings->getSetting('footer_copyright')) {
      // Expire at the turn of the year so the copyright year is never stale.
      $now = new \DateTimeImmutable();
      $metadata->setCacheMaxAge(max(1, $now->modify('first day of January next year midnight')->getTimestamp() - $now->getTimestamp()));
    }
    $credit_name = trim((string) $this->themeSettings->getSetting('footer_credit_name'));

    $meta = [
      'menu' => $this->footerMenu(),
      'links' => $links,
      'copyright' => $this->themeSettings->getSetting('footer_copyright') ? '© ' . date('Y') . ' ' . $site_name : '',
      'credit' => $credit_name === '' ? NULL : [
        'name' => $credit_name,
        'url' => $this->webUrl(trim((string) $this->themeSettings->getSetting('footer_credit_url'))),
      ],
      'versions' => implode(' · ', $versions),
    ];
    // Whether there is any small print to draw at all.
    $meta['visible'] = (bool) array_filter($meta);
    return $meta;
  }

  /**
   * The footer menu named in the theme settings, access-checked, one level.
   */
  private function footerMenu(): ?array {
    $name = trim((string) $this->themeSettings->getSetting('footer_menu'));
    if ($name === '') {
      return NULL;
    }
    $tree = $this->menuLinkTree->load($name, (new MenuTreeParameters())->setMaxDepth(1)->onlyEnabledLinks());
    $tree = $this->menuLinkTree->transform($tree, [
      ['callable' => 'menu.default_tree_manipulators:checkAccess'],
      ['callable' => 'menu.default_tree_manipulators:generateIndexAndSort'],
    ]);
    if (!$tree) {
      return NULL;
    }
    return $this->menuLinkTree->build($tree);
  }

  /**
   * Returns the URL if it is an http(s) address, otherwise NULL.
   */
  private function webUrl(string $value): ?string {
    return preg_match('#^https?://[^\s]+$#i', $value) ? $value : NULL;
  }
}
