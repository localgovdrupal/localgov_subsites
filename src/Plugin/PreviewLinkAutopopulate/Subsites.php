<?php

namespace Drupal\localgov_subsites\Plugin\PreviewLinkAutopopulate;

use Drupal\localgov_subsites\Plugin\Block\SubsitesHierarchyTrait;
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

  use SubsitesHierarchyTrait;

  /**
   * {@inheritdoc}
   */
  public function getPreviewEntities(): array {
    assert($this->entity instanceof NodeInterface);
    return $this->getFlattenedSubsiteHierarchy($this->entity);
  }

}
