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

    $nodeID = \Drupal::requestStack()->getCurrentRequest()->get('node');

    $nodeTitle = $nodeID->label();

    if ($campaign = $this->getCampaign()) {

      $campaignImage = file_create_url($campaign->get('field_banner')->entity->uri->value);

      $build[] = [
        '#theme' => 'campaign_banner',
        '#tag' => $campaign->label(),
        '#heading' => $nodeTitle,
        '#image' => $campaignImage
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
