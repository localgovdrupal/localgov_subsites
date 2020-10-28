<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\CacheableMetadata;
use Drupal\Core\Entity\EntityInterface;
use Drupal\Core\Session\AccountInterface;
use Drupal\Core\Template\Attribute;

/**
 * Class CampaignNavigationBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "localgov_campaign_navigation",
 *   admin_label = "Campaign navigation",
 *   context_definitions = {
 *     "node" = @ContextDefinition(
 *       "entity:node",
 *       label = @Translation("Current node"),
 *       constraints = {
 *         "Bundle" = {
 *           "localgov_campaigns_overview",
 *           "localgov_campaigns_page"
 *         },
 *       }
 *     )
 *   }
 * )
 */
class CampaignsNavigationBlock extends CampaignsAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];
    $items = [];

    $campaign_entity = $this->getContextValue('node');
    $cache = (new CacheableMetadata())->addCacheableDependency($campaign_entity);
    $storage = $this->getNestedSetStorage('localgov_campaigns');
    $node = $this->getNestedSetNodeKeyFactory()->fromEntity($campaign_entity);
    if ($ancestors = $storage->findAncestors($node)) {
      $tree = $storage->findDescendants($ancestors[0]->getNodeKey());
      array_unshift($tree, $ancestors[0]);
      $mapper = \Drupal::service('entity_hierarchy.entity_tree_node_mapper');
      $entities = $mapper->loadAndAccessCheckEntitysForTreeNodes('node', $tree, $cache);
      $items = $this->nestTree($tree, $ancestors, $entities);
      $campaign_id = $entities[$ancestors[0]]->id();
    }
    elseif ($campaign_entity->bundle('localgov_campaigns_overview')) {
      // Campaign overview page with no children.
      // Still show an block with no children.
      // Cache metadata already has this entity.
      $items = [$this->formatItem($campaign_entity, TRUE)];
      $campaign_id = $campaign_entity->id();
    }

    if ($items) {
      $build[] = [
        '#theme' => 'campaign_navigation',
        '#menu_name' => 'campaign_navigation:' . $campaign_id,
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
        }
        // At the moment we're seeing old revisions in the tree.
        // Seems to be issues that are fixed in queue. @todo
        // https://www.drupal.org/project/issues/entity_hierarchy?text=revisions&status=All
        if (!empty($entities[$node])) {
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
    $campaign = $this->getCampaign();
    if (!is_null($campaign) &&
      $campaign->hasField('localgov_campaigns_hide_menu') &&
      $campaign->localgov_campaigns_hide_menu->value == 1
    ) {
      return AccessResult::neutral();
    }

    return parent::blockAccess($account);
  }

}
