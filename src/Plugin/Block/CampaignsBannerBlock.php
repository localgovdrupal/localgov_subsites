<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

/**
 * Class CampaignBannerBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "localgov_campaign_banner",
 *   admin_label = "Campaign banner"
 * )
 */
class CampaignsBannerBlock extends CampaignsAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($campaign = $this->getCampaign($this->node)) {
      $build[] = [
        '#theme' => 'campaign_banner',
        '#tag' => $campaign->label(),
        '#heading' => $this->node->label(),
        '#image' => $this->getCampaignBanner(),
      ];
    }

    return $build;
  }

}
