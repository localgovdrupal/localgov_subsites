<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Abstract Class CampaignBlockBase.
 *
 * Provide common block functions for campaigns.
 */
abstract class CampaignsAbstractBlockBase extends BlockBase {

  /**
   * Load campaign.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Campaign node to check (either a campaign overview or campaign page).
   *
   * @return null|\Drupal\node\NodeInterface
   *   Node object of Campaign overview page.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getCampaign(NodeInterface $node) {
    if ($node->bundle() == 'localgov_campaigns_page' and $node->field_campaign->entity) {
      $node = $node->field_campaign->entity;
    }
    return $node->bundle() == 'localgov_campaigns_overview' ? $node : NULL;
  }

  /**
   * Get Campaign Banner.
   *
   * @param Drupal\node\NodeInterface $node
   *   Campaign overview node.
   *
   * @return string|null
   *   Stream wrapper url of Campaign overview field_banner,
   *   or NULL if no image field set.
   */
  protected function getCampaignBanner(NodeInterface $node) {
    if ($node->get('field_banner')->entity) {
      $image = $node->get('field_banner')->entity;
      $image_url = !empty($image) ? $image->uri->value : NULL;

      return $image_url;
    }

    return NULL;
  }

}
