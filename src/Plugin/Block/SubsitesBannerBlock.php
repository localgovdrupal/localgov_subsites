<?php

namespace Drupal\localgov_subsites\Plugin\Block;

/**
 * Class SubsiteBannerBlock.
 *
 * @package Drupal\localgov_subsites\Plugin\Block
 *
 * @Block(
 *   id = "localgov_subsite_banner",
 *   admin_label = "Subsite banner",
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
class SubsitesBannerBlock extends SubsitesAbstractBlockBase {

  /**
   * The entity view builder interface.
   *
   * @var \Drupal\Core\Entity\EntityViewBuilderInterface
   */
  private $viewBuilder;

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($this->getSubsite($this->node)) {
      if ($banner = $this->getSubsiteBanner()) {
        $viewBuilder = $this->entityTypeManager->getViewBuilder('paragraph');
        $build = $viewBuilder->view($banner);
      }
    }

    return $build;
  }

}
