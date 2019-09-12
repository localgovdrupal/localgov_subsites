<?php

namespace Drupal\bhcc_campaign\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;

/**
 * Class CampaignOverviewBlock
 *
 * @package Drupal\bhcc_campaign\Plugin\Block
 *
 * @Block(
 *   id = "campaign_overview_banner",
 *   admin_label = "Campaign overview banner"
 * )
 */
class CampaignOverviewBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $nodeID = \Drupal::requestStack()->getCurrentRequest()->get('node');

    $nodeTitle = $nodeID->label();

    $nodeImageURL = file_create_url($nodeID->get('field_banner')->entity->uri->value);

    $build[] = [
      '#theme' => 'campaign_overview_banner',
      '#heading' => $nodeTitle,
      '#image' => $nodeImageURL
    ];

    return $build;
  }
  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}
