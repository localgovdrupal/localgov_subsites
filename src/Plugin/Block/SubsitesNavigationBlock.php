<?php

namespace Drupal\localgov_subsites\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\Attribute;

/**
 * Class SubsiteNavigationBlock.
 *
 * @package Drupal\localgov_subsites\Plugin\Block
 *
 * @Block(
 *   id = "localgov_subsite_navigation",
 *   admin_label = "Subsite navigation",
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node"),
 *       constraints = {
 *         "Bundle" = {
 *           "localgov_subsites_overview",
 *           "localgov_subsites_page"
 *         },
 *       }
 *     )
 *   }
 * )
 */
class SubsitesNavigationBlock extends SubsitesAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $items = [];

    $subsite_entity = $this->getContextValue('node');
    $cache = (new CacheableMetadata())->addCacheableDependency($subsite_entity);
    $storage = $this->getNestedSetStorage('localgov_subsites');
    $node = $this->getNestedSetNodeKeyFactory()->fromEntity($subsite_entity);
    if ($ancestors = $storage->findAncestors($node)) {
      $tree = $storage->findDescendants($ancestors[0]->getNodeKey());
      array_unshift($tree, $ancestors[0]);
      $mapper = \Drupal::service('entity_hierarchy.entity_tree_node_mapper');
      $entities = $mapper->loadAndAccessCheckEntitysForTreeNodes('node', $tree, $cache);
      $items = $this->nestTree($tree, $ancestors, $entities);
      $subsite_id = $entities[$ancestors[0]]->id();
    }
    elseif ($subsite_entity->bundle('localgov_subsites_overview')) {
      // Subsite overview page with no children.
      // Still show an block with no children.
      // Cache metadata already has this entity.
      $items = [$this->formatItem($subsite_entity, TRUE)];
      $subsite_id = $subsite_entity->id();
    }

    if ($items) {
      $build[] = [
        '#theme' => 'subsite_navigation',
        '#menu_name' => 'subsite_navigation:' . $subsite_id,
        '#items' => $items,
      ];
    }

    $cache->applyTo($build);
    return $build;
  }

  protected function nestTree($tree, $ancestors, $entities, &$index = 0, $depth = 0) {
    $items = $item = [];
    do {
      $node = $tree[$index];
      if ($node->getDepth() > $depth) {
        $item['below'] = $this->nestTree($tree, $ancestors, $entities, $index, $depth + 1);
      }
      elseif ($node->getDepth() == $depth) {
        if (!empty($item)) {
          $items[] = $item;
          $item = [];
        }
        if ($entities->contains($node) && $entities[$node]->isDefaultRevision()) {
          $item = $this->formatItem(
            $entities[$node],
            in_array($node, $ancestors)
          );
        }
      }
    } while (isset($tree[$index + 1]) && ($tree[$index + 1]->getDepth() >= $depth) && ++$index);
    if (!empty($item)) {
      $items[] = $item;
    }
    return $items;
  }

  protected function formatItem(EntityInterface $entity, $in_active_trail) {
    $link = [];
    $link['title'] = $entity->label();
    $link['url'] = $entity->toUrl();
    $link['url']->setOption('set_active_class', TRUE);
    $link['in_active_trail'] = $in_active_trail;
    $link['attributes'] = new Attribute();

    return $link;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $subsite = $this->getSubsite();
    if (!is_null($subsite) &&
      $subsite->hasField('localgov_subsites_hide_menu') &&
      $subsite->localgov_subsites_hide_menu->value == 1
    ) {
      return AccessResult::neutral();
    }

    return parent::blockAccess($account);
  }

}
