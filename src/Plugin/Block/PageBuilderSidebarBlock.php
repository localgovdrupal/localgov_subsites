<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Class PageBuilderSidebarBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "page_builder_sidebar",
 *   admin_label = "Page builder sidebar"
 * )
 */
class PageBuilderSidebarBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    /** @var \Drupal\node\NodeInterface $node */
    if ($node = \Drupal::requestStack()->getCurrentRequest()->get('node')) {
      if ($node->hasField('field_sidebar') && !$node->get('field_sidebar')->isEmpty()) {
        foreach ($node->get('field_sidebar')->getValue() as $block) {
          $paragraph =  \Drupal::entityTypeManager()
            ->getStorage('paragraph')
            ->load($block['target_id']);

          $build[] = entity_view($paragraph, 'default');
        }
      }
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}
