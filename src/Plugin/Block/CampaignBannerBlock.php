<?php

namespace Drupal\bhcc_campaign\Plugin\Block;

use Drupal\bhcc_campaign\Node\CampaignSingleton;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Class CampaignBannerBlock
 *
 * @package Drupal\bhcc_campaign\Plugin\Block
 *
 * @Block(
 *   id = "campaign_banner",
 *   admin_label = "Campaign banner"
 * )
 */
class CampaignBannerBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($campaign = $this->getCampaign()) {
      $build[] = [
        '#theme' => 'campaign_banner',
        '#tag' => 'Community',
        '#heading' => $campaign->label()
      ];
    }

    return $build;
  }

  /**
   * Load campaign.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\Node|mixed|null
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  private function getCampaign() {
    $node = \Drupal::requestStack()->getCurrentRequest()->get('node');

    if ($node instanceof CampaignSingleton) {
      $node = $node->getParent();
    }

    return $node;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}
