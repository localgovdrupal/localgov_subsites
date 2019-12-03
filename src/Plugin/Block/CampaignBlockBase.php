<?php

namespace Drupal\bhcc_campaign\Plugin\Block;

use Drupal\bhcc_campaign\Node\CampaignMasterInterface;
use Drupal\bhcc_campaign\Node\CampaignSingletonInterface;
use Drupal\node\NodeInterface;
use Drupal\Core\Block\BlockBase;

/**
 * Abstract Class CampaignBlockBase.
 *
 * Provide common block functions for campaigns.
 */
abstract class CampaignBlockBase extends BlockBase {

  /**
   * Load campaign.
   *
   * @param \Drupal\node\NodeInterface $node
   *   Campaign node to check (either a campaign overview page or sub page).
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\NodeInterface|mixed|null
   *   Node object of Campaign overview page.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  protected function getCampaign(NodeInterface $node) {
    if ($node instanceof CampaignSingletonInterface) {
      $node = $node->getParent();
    }
    return $node instanceof CampaignMasterInterface ? $node : NULL;
  }

  /**
   * Get campaign title.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignMasterInterface $campaign
   *   Campaign overview node.
   *
   * @return string
   *   Campaign overview node title.
   */
  protected function getCampaignTitle(CampaignMasterInterface $campaign) {
    return $campaign->label();
  }

  /**
   * Get Campaign Banner.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignMasterInterface $campaign
   *   Campaign overview node.
   *
   * @return string|null
   *   Stream wrapper url of Campaign overview field_banner,
   *   or NULL if no image field set.
   */
  protected function getCampaignBanner(CampaignMasterInterface $campaign) {

    $campaignImage = $campaign->get('field_banner')->entity;
    $campaignImageURL = !empty($campaignImage) ? $campaignImage->uri->value : NULL;

    return $campaignImageURL;
  }

}
