<?php

namespace Drupal\localgov_campaigns;

use Drupal\Core\Entity\EntityTypeManagerInterface;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Drupal\localgov_campaigns\Plugin\Block\CampaignsHierarchyTrait;
use Drupal\node\NodeInterface;
use Drupal\Core\DependencyInjection\ContainerInjectionInterface;

/**
 * Class Campaign.
 *
 * Provide methods for interacting with campaign entities.
 */
class Campaign implements ContainerInjectionInterface {
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
  public static function create(ContainerInterface $container) {
    return new static(
      $container->get('entity_type.manager')
    );
  }

  /**
   * Initialise new campaign instance.
   *
   * @param \Drupal\Core\Entity\EntityTypeManagerInterface $entity_type_manager
   *   The entity type manager service.
   */
  public function __construct(EntityTypeManagerInterface $entity_type_manager) {
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
  public function getCampaign($node) {
    $entity = NULL;

    if ($node instanceof NodeInterface) {
      if ($node->bundle() === 'localgov_campaigns_overview') {
        $entity = $node;
      } elseif ($id = $this->getRootId($node)) {
        $entity = $this->entityTypeManager->getStorage('node')->load($id);
      }
    }

    return $entity;
  }
}
