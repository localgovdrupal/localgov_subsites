<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

/**
 * Class CampaignOverviewBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "localgov_campaign_overview_banner",
 *   admin_label = "Campaign overview banner",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node", label = @Translation("Current node"))
 *   }
 * )
 */
class CampaignsOverviewBlock extends CampaignsAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($campaign = $this->getCampaign()) {
      $build[] = [
        '#theme' => 'campaign_overview_banner',
        '#heading' => $campaign->getTitle(),
        '#image' => $this->getCampaignBanner($campaign),
      ];
    }

    return $build;
  }

}
