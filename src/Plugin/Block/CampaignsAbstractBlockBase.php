<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Session\AccountInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract Class CampaignBlockBase.
 *
 * Provide common block functions for campaigns.
 */
abstract class CampaignsAbstractBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

  use CampaignsHierarchyTrait;

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
   * Get campaign.
   *
   * @return null|\Drupal\node\NodeInterface
   *   Node object of Campaign overview page.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getCampaign() {
    $entity = NULL;
    if ($this->node = $this->getContextValue('node')) {
      if ($this->node->bundle() == 'localgov_campaigns_overview') {
        $entity = $this->node;
      }
      else {
        $id = $this->getRootId($this->node);
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
  protected function getCampaignBanner() {
    $this->node = $this->getContextValue('node');
    if ($this->node->hasField('localgov_campaigns_banner')) {
      return $this->node->localgov_campaigns_banner->entity;
    }

    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $this->node = $this->getContextValue('node');
    if ($this->node and
      ($this->node->bundle() == 'localgov_campaigns_overview' or $this->node->bundle() == 'localgov_campaigns_page')
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
