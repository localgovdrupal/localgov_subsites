<?php

namespace Drupal\localgov_subsites\Plugin\Block;

/**
 * Class SubsiteOverviewBlock.
 *
 * @package Drupal\localgov_subsites\Plugin\Block
 *
 * @Block(
 *   id = "localgov_subsite_overview_banner",
 *   admin_label = "Subsite overview banner",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current node"))
 *   }
 * )
 */
class SubsitesOverviewBlock extends SubsitesAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($subsite = $this->getSubsite()) {
      $build[] = [
        '#theme' => 'subsite_overview_banner',
        '#heading' => $subsite->getTitle(),
        '#image' => $this->getSubsiteBanner($subsite),
      ];
    }

    return $build;
  }

}
