<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

/**
 * Class CampaignOverviewBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "localgov_campaign_overview_banner",
 *   admin_label = "Campaign overview banner"
 * )
 */
class CampaignsOverviewBlock extends CampaignsAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::requestStack()->getCurrentRequest()->get('node');
    $build = [];

    if ($campaign = $this->getCampaign($node)) {
      $build[] = [
        '#theme' => 'campaign_overview_banner',
        '#heading' => $campaign->getTitle(),
        '#image' => $this->getCampaignBanner($campaign),
      ];
    }

    return $build;
  }

}
