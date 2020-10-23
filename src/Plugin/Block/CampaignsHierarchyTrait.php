<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory;
use Drupal\entity_hierarchy\Storage\NestedSetStorageFactory;
use Drupal\node\NodeInterface;

/**
 * Trait providing hiearchy of campaigns.
 *
 * Expect to generalize to provide same methods for all localgov sections.
 * Hence abstract the methods to get ancestors, root and tree and enable
 * developing further more easily.
 */
trait CampaignsHierarchyTrait {

  /**
   * Nested set node key factory.
   *
   * @var \Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory
   */
  protected $nestedSetNodeKeyFactory;

  /**
   * Nested set storage factory.
   *
   * @var \Drupal\entity_hierarchy\Storage\NestedSetStorageFactory
   */
  protected $nestedSetStorageFactory;

  /**
   * Get nested set storage factory service.
   *
   * @return \Drupal\entity_hierarchy\Storage\NestedSetStorageFactory
   *   Nested set storage factory service.
   */
  public function getNestedSetStorageFactory(): NestedSetStorageFactory {
    if (!isset($this->nestedSetStorageFactory)) {
      $this->nestedSetStorageFactory = \Drupal::service('entity_hierarchy.nested_set_storage_factory');
    }

    return $this->nestedSetStorageFactory;
  }

  /**
   * Set nested set storage factory service.
   *
   * @param \Drupal\entity_hierarchy\Storage\NestedSetStorageFactory $storage_factory
   *   Nested set storage factory service.
   *
   * @return $this
   */
  public function setNestedSetStorageFactory(NestedSetStorageFactory $storage_factory): self {
    $this->nestedSetStorageFactory = $storage_factory;
    return $this;
  }

  /**
   * Get nested set node key factory service.
   *
   * @return \Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory
   *   Nested set node key factory.
   */
  public function getNestedSetNodeKeyFactory(): NestedSetNodeKeyFactory {
    if (!isset($this->nestedSetNodeKeyFactory)) {
      $this->nestedSetNodeKeyFactory = \Drupal::service('entity_hierarchy.nested_set_node_factory');
    }

    return $this->nestedSetNodeKeyFactory;
  }

  /**
   * Set nested set node key factory service.
   *
   * @param \Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory $node_key_factory
   *   Nested set storage factory service.
   *
   * @return $this
   */
  public function setNestedSetNodeKeyFactory(NestedSetNodeKeyFactory $node_key_factory): self {
    $this->nestedSetNodeKeyFactory = $node_key_factory;
    return $this;
  }

  /**
   * Get the entity_id of the ultimate parent drupal entity.
   *
   * @param Drupal\Core\Entity\EntityInterface $entity
   *   Entity to find root ancestor of.
   *
   * @return null|int
   *   Node id of overview page.
   */
  protected function getRootId(EntityInterface $entity): ?int {
    if ($entity instanceof NodeInterface &&
      in_array($entity->bundle(), ['localgov_campaigns_overview', 'localgov_campaigns_page'])
    ) {
      $storage = $this->getNestedSetStorageFactory()->get('localgov_campaigns_parent', 'node');
      if ($root_node = $storage->findRoot($this->getNestedSetNodeKeyFactory()->fromEntity($entity))) {
        return $root_node->getId();
      }
    }
  }

}
