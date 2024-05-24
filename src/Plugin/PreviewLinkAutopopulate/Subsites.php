<?php

namespace Drupal\localgov_subsites\Plugin\PreviewLinkAutopopulate;

use Drupal\node\NodeInterface;
use Drupal\preview_link\PreviewLinkAutopopulatePluginBase;

/**
 * Auto-populate subsite preview links.
 *
 * @PreviewLinkAutopopulate(
 *   id = "localgov_subsites",
 *   label = @Translation("Add all the pages for this subsite"),
 *   description = @Translation("Add subsite overview and page nodes to preview link."),
 *   supported_entities = {
 *     "node" = {
 *       "localgov_subsites_overview",
 *       "localgov_subsites_page",
 *     }
 *   },
 * )
 */
class Subsites extends PreviewLinkAutopopulatePluginBase {

  /**
   * {@inheritdoc}
   */
  public function getPreviewEntities(): array {
    $nodes = [];

    // Find subsite overview.
    $node = $this->getEntity();
    if ($node->bundle() == 'localgov_subsites_overview') {
      $overview = $node;
    }
    elseif ($node->bundle() == 'localgov_subsites_page') {
      $overview = $node->get('localgov_subsites_parent')->entity;
    }
    $nodes[] = $overview;

    // Find subsite pages.
    $pages = $this->entityTypeManager->getStorage('node')
      ->loadByProperties([
        'type' => 'localgov_subsites_page',
        'localgov_subsites_parent' => $overview->id(),
      ]);
    foreach ($pages as $page) {
      if ($page instanceof NodeInterface && $page->access('view')) {
        $nodes[] = $page;
      }
    }

    return $nodes;
  }

}
