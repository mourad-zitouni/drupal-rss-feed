<?php

namespace Drupal\latest_articles\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Provides a 'Latest Articles' block.
 *
 * @Block(
 *   id = "latest_articles_block",
 *   admin_label = @Translation("Latest Articles"),
 *   category = @Translation("Custom"),
 * )
 */
class LatestArticlesBlock extends BlockBase implements ContainerFactoryPluginInterface {

  /**
   * The entity type manager.
   *
   * @var \Drupal\Core\Entity\EntityTypeManagerInterface
   */
  protected $entityTypeManager;

  /**
   * Constructs a new LatestArticlesBlock.
   *
   * @param array $configuration
   *   A configuration array containing information about the plugin instance.
   * @param string $plugin_id
   *   The plugin ID for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);
    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * {@inheritdoc}
   */
  public static function create(ContainerInterface $container, array $configuration, $plugin_id, $plugin_definition) {
    return new static(
      $configuration,
      $plugin_id,
      $plugin_definition,
      $container->get('entity_type.manager')
    );
  }

  /**
   * {@inheritdoc}
   */
  public function defaultConfiguration() {
    return [
      'num_articles' => 5,
      'cache_lifetime' => 3600,
    ];
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form['num_articles'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Number of Articles to Display'),
      '#default_value' => $this->configuration['num_articles'],
      '#required' => TRUE,
    ];

    $form['cache_lifetime'] = [
      '#type' => 'textfield',
      '#title' => $this->t('Cache Lifetime (in seconds)'),
      '#default_value' => $this->configuration['cache_lifetime'],
      '#required' => TRUE,
    ];

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->configuration['num_articles'] = $form_state->getValue('num_articles');
    $this->configuration['cache_lifetime'] = $form_state->getValue('cache_lifetime');
  }

  /**
   * {@inheritdoc}
   */
  public function build() {
    $number_of_articles = $this->configuration['num_articles'];
    $cache_max_age = $this->configuration['cache_lifetime'];

    // Load latest articles
    $nodes = $this->entityTypeManager
      ->getStorage('node')
      ->getQuery()
      ->condition('status', 1)
      ->condition('type', 'article')
      ->range(0, $number_of_articles)
      ->sort('created', 'DESC')
      ->accessCheck(TRUE)
      ->execute();

      $list = [];
      foreach ($nodes as $nid) {
        $node = $this->entityTypeManager->getStorage('node')->load($nid);
        $list[] = [
          '#markup' => $node->toLink()->toString(),
        ];
      }
  
      return [
        '#theme' => 'item_list',
        '#items' => $list,
        '#cache' => [
          'max-age' => $cache_max_age,
        ],
      ];
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheMaxAge() {
    return $this->configuration['cache_lifetime'];
  }
}
