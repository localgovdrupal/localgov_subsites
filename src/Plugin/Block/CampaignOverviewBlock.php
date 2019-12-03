<?php

namespace Drupal\bhcc_campaign\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\Core\Cache\Cache;

/**
 * Class CampaignOverviewBlock.
 *
 * @package Drupal\bhcc_campaign\Plugin\Block
 *
 * @Block(
 *   id = "campaign_overview_banner",
 *   admin_label = "Campaign overview banner"
 * )
 */
class CampaignOverviewBlock extends CampaignBlockBase {

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
   * @param \Drupal\node\Entity\Node $node
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
        '#theme' => 'campaign_overview_banner',
        '#heading' => $this->getCampaignTitle($campaign),
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
