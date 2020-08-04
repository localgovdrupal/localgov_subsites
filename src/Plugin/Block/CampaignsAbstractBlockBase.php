<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Entity\EntityTypeManagerInterface;
use Drupal\Core\Plugin\ContainerFactoryPluginInterface;
use Drupal\Core\Routing\CurrentRouteMatch;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;

/**
 * Abstract Class CampaignBlockBase.
 *
 * Provide common block functions for campaigns.
 */
abstract class CampaignsAbstractBlockBase extends BlockBase implements ContainerFactoryPluginInterface {

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
      $container->get('current_route_match'),
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
   * @param \Drupal\Core\Routing\CurrentRouteMatch $route_match
   *   The route match service.
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(array $configuration, $plugin_id, $plugin_definition, CurrentRouteMatch $route_match, EntityTypeManagerInterface $entity_type_manager) {
    parent::__construct($configuration, $plugin_id, $plugin_definition);

    $this->routeMatch = $route_match;
    $this->entityTypeManager = $entity_type_manager;
    if ($this->routeMatch->getParameter('node')) {
      $this->node = $this->routeMatch->getParameter('node');
      if (!$this->node instanceof NodeInterface) {
        $node_storage = $this->entityTypeManager->getStorage('node');
        $this->node = $node_storage->load($this->node);
      }
    }
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
    if ($this->node instanceof NodeInterface) {
      if ($this->node->bundle() == 'localgov_campaigns_page' and $this->node->localgov_campaigns_parent->entity) {
        return $this->node->localgov_campaigns_parent->entity;
      }
      return $this->node->bundle() == 'localgov_campaigns_overview' ? $this->node : NULL;
    }
    return NULL;
  }

  /**
   * Get Campaign Banner.
   *
   * @return string|null
   *   Stream wrapper url of Campaign overview localgov_campaigns_banner,
   *   or NULL if no image field set.
   */
  protected function getCampaignBanner() {
    $campaign = $this->getCampaign();
    if ($campaign->get('localgov_campaigns_banner_image')->entity) {
      $file_storage = $this->entityTypeManager->getStorage('file');
      $fid = $campaign->get('localgov_campaigns_banner_image')->entity->field_media_image[0]->getValue()['target_id'];
      $file = $file_storage->load($fid);
      if (!is_null($file)) {
        return $file->createFileUrl();
      }
    }
    return NULL;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
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
