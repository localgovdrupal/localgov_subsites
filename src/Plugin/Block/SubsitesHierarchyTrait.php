<?php

namespace Drupal\localgov_subsites\Plugin\Block;

use Drupal\Core\Entity\EntityInterface;
use Drupal\entity_hierarchy\Storage\NestedSetNodeKeyFactory;
use Drupal\entity_hierarchy\Storage\NestedSetStorage;
use Drupal\entity_hierarchy\Storage\NestedSetStorageFactory;
use Drupal\node\NodeInterface;

/**
 * Trait providing hiearchy of subsites.
 *
 * Expect to generalize to provide same methods for all localgov sections.
 * Hence abstract the methods to get ancestors, root and tree and enable
 * developing further more easily.
 */
trait SubsitesHierarchyTrait {

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
   * Get the configured nested set storage for subsites.
   *
   * @param string $section
   *   The section of the site for the hiearchy.
   *
   * @return \Drupal\entity_hierarchy\Storage\NestedSetStorage
   *   Nested set storage for localgov_subsites_parent.
   */
  protected function getNestedSetStorage(string $section): NestedSetStorage {
    $lookup = ['localgov_subsites' => 'localgov_subsites_parent'];
    return $this->getNestedSetStorageFactory()->get($lookup[$section], 'node');
  }

  /**
   * Get the entity_id of the ultimate parent drupal entity.
   *
   * @param \Drupal\Core\Entity\EntityInterface $entity
   *   Entity to find root ancestor of.
   *
   * @return null|int
   *   Node id of overview page.
   */
  protected function getRootId(EntityInterface $entity): ?int {
    if ($entity instanceof NodeInterface &&
      in_array($entity->bundle(), [
        'localgov_subsites_overview',
        'localgov_subsites_page',
      ], TRUE)
      && !is_null($entity->id())
    ) {
      if ($root_node = $this->getNestedSetStorage('localgov_subsites')->findRoot($this->getNestedSetNodeKeyFactory()->fromEntity($entity))) {
        return $root_node->getId();
      }
    }

    return NULL;
  }

  /**
   * Get flattened list of nodes in subsite hierarchy.
   *
   * This does not do any access checks so unpublished nodes may be returned. If
   * this becomes a requirement then it should be extended to include this.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Any entity in the hierarchy.
   *
   * @return array
   *   Flattened list of nodes in subsite hierarchy.
   */
  public function getFlattenedSubsiteHierarchy(NodeInterface $node): array {
    $nodes = [];

    $storage = $this->getNestedSetStorage('localgov_subsites');
    $node_key = $this->getNestedSetNodeKeyFactory()->fromEntity($node);
    if ($ancestors = $storage->findAncestors($node_key)) {
      $tree = $storage->findDescendants($ancestors[0]->getNodeKey());
      array_unshift($tree, $ancestors[0]);
      $mapper = \Drupal::service('entity_hierarchy.entity_tree_node_mapper');
      $ancestor_entities = $mapper->loadEntitiesForTreeNodesWithoutAccessChecks('node', $tree);
      foreach ($ancestor_entities as $ancestor_entity) {
        if (!$ancestor_entities->contains($ancestor_entity)) {
          // Doesn't exist or is access hidden.
          continue;
        }
        $entity = $ancestor_entities->offsetGet($ancestor_entity);
        if ($entity instanceof NodeInterface) {
          $nodes[] = $entity;
        }
      }
    }

    return $nodes;
  }

}
