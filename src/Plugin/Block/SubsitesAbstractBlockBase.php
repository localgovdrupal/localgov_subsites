<?php

namespace Drupal\localgov_subsites\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract Class SubsiteBlockBase.
 *
 * Provide common block functions for subsites.
 */
abstract class SubsitesAbstractBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  use SubsitesHierarchyTrait;

  /**
   * Node being displayed.
   *
   * @var \Drupal\node\NodeInterface
   */
  protected $node;

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
   * Initialise new content block instance.
   *
   * @param array $configuration
   *   The plugin configuration.
   * @param string $plugin_id
   *   The plugin_id for the plugin instance.
   * @param mixed $plugin_definition
   *   The plugin implementation definition.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->entityTypeManager = $entity_type_manager;
  }

  /**
   * Get subsite.
   *
   * @return null|\Drupal\node\NodeInterface
   *   Node object of Subsite overview page.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getSubsite() {
    $entity = NULL;
    if ($this->node = $this->getContextValue('node')) {
      if ($this->node->bundle() == 'localgov_subsites_overview') {
        $entity = $this->node;
      }
      elseif ($id = $this->getRootId($this->node)) {
        $entity = $this->entityTypeManager->getStorage('node')->load($id);
      }
    }

    return $entity;
  }

  /**
   * Fetches the referenced hero paragraph entity.
   *
   * @return object|null
   *   The hero paragraph entity or NULL.
   */
  protected function getSubsiteBanner() {
    $this->node = $this->getContextValue('node');
    if ($this->node->hasField('localgov_subsites_banner')) {
      return $this->node->localgov_subsites_banner->entity;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $this->node = $this->getContextValue('node');
    if ($this->node and
      ($this->node->bundle() == 'localgov_subsites_overview' or $this->node->bundle() == 'localgov_subsites_page')
    ) {
      return AccessResult::allowed();
    }
    return AccessResult::neutral();
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
