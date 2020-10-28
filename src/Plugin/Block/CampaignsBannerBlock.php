<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

/**
 * Class CampaignBannerBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "localgov_campaign_banner",
 *   admin_label = "Campaign banner",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current node"))
 *   }
 * )
 */
class CampaignsBannerBlock extends CampaignsAbstractBlockBase {

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

    if ($this->getCampaign($this->node)) {
      if ($banner = $this->getCampaignBanner()) {
        $viewBuilder = $this->entityTypeManager->getViewBuilder('paragraph');
        $build = $viewBuilder->view($banner);
      }
    }

    return $build;
  }

}
