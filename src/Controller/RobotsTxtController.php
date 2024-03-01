<?php

namespace Drupal\robotstxt\Controller;

use Drupal\Core\Controller\ControllerBase;
use Drupal\Core\Render\HtmlResponse;
use Drupal\Core\Render\RenderContext;
use Drupal\Core\Render\RendererInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;
use Drupal\Core\Config\ConfigFactoryInterface;
use Drupal\Core\Extension\ModuleHandlerInterface;

/**
 * Provides output robots.txt output.
 */
class RobotsTxtController extends ControllerBase implements ContainerInjectionInterface {

  /**
   * The module handler service.
   *
   * @var \Drupal\Core\Extension\ModuleHandlerInterface
   */
  protected $moduleHandler;

  /**
   * RobotsTxt module 'robotstxt.settings' configuration.
   *
   * @var \Drupal\Core\Config\ImmutableConfig
   */
  protected $moduleConfig;

  /**
   * The core renderer service.
   *
   * @var \Drupal\Core\Render\RendererInterface
   */
  protected $renderer;

  /**
   * Constructs a RobotsTxtController object.
   *
   * @param \Drupal\Core\Config\ConfigFactoryInterface $config
   *   Configuration object factory.
   * @param \Drupal\Core\Extension\ModuleHandlerInterface $module_handler
   *   The module handler service.
   * @param \Drupal\Core\Render\RendererInterface $renderer
   *   The core renderer service.
   */
  public function __construct(ConfigFactoryInterface $config, ModuleHandlerInterface $module_handler, RendererInterface $renderer) {
    $this->moduleConfig = $config->get('robotstxt.settings');
    $this->moduleHandler = $module_handler;
    $this->renderer = $renderer;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('config.factory'),
      $container->get('module_handler'),
      $container->get('renderer')
    );
  }

  /**
   * Serves the configured robots.txt file.
   *
   * @return \Symfony\Component\HttpFoundation\Response
   *   The robots.txt file as a response object with 'text/plain' content type.
   */
  public function content() {
    $context = new RenderContext();
    $response = $this->renderer->executeInRenderContext($context, function () {
      $content = [];
      $content[] = $this->moduleConfig->get('content');

      // Hook other modules for adding additional lines.
      if ($additions = $this->moduleHandler->invokeAll('robotstxt')) {
        $content = array_merge($content, $additions);
      }
      // Trim any extra whitespace and filter out empty strings.
      $content = array_map('trim', $content);
      $content = array_filter($content);
      $content = implode("\n", $content);

      // Treat the content as an render array in order to populate the $context
      // object.
      $build = ['#plain_text' => $content];
      $this->renderer->render($build);

      return new HtmlResponse($build, Response::HTTP_OK, ['content-type' => 'text/plain']);
    });

    // If there is metadata left on the context, apply it on the response.
    if (!$context->isEmpty()) {
      $metadata = $context->pop();
      $metadata->addCacheTags(['robotstxt']);
      $meta_data->addCacheContexts(['url.site']);
      $response->addCacheableDependency($metadata);
      $response->addAttachments($metadata->getAttachments());
    }

    return $response;
  }

}
