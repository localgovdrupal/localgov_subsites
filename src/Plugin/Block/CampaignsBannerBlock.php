<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Cache\Cache;

/**
 * Class CampaignBannerBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "campaign_banner",
 *   admin_label = "Campaign banner"
 * )
 */
class CampaignsBannerBlock extends CampaignsAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $node = \Drupal::requestStack()->getCurrentRequest()->get('node');
    $build = [];

    if ($campaign = $this->getCampaign($node)) {
      $build[] = [
        '#theme' => 'campaign_banner',
        '#tag' => $campaign->label(),
        '#heading' => $node->label(),
        '#image' => $this->getCampaignBanner($campaign),
      ];
    }

    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
