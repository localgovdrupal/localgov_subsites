<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\node\NodeInterface;
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
class CampaignBannerBlock extends CampaignBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {

    $build = [];
    $node = \Drupal::requestStack()->getCurrentRequest()->get('node');
    $build[] = $this->getBlockBuild($node);
    return $build;
  }

  /**
   * Get Block Build array.
   *
   * @param Drupal\node\NodeInterface $node
   *   Current Node.
   *
   * @return array
   *   Block Render array.
   */
  protected function getBlockBuild(NodeInterface $node) {

    $blockBuild = [];

    if ($campaign = $this->getCampaign($node)) {

      $campaignImageURL = $this->getCampaignBanner($campaign);

      $blockBuild = [
        '#theme' => 'campaign_banner',
        '#tag' => $this->getCampaignTitle($campaign),
        '#heading' => $node->label(),
        '#image' => $campaignImageURL,
      ];
    }

    return $blockBuild;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

}
