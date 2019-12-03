<?php

namespace Drupal\bhcc_campaign\Node;

/**
 * Interface CampaignMaster.
 *
 * @package Drupal\bhcc_campaign\Node
 */
interface CampaignMasterInterface {

  /**
   * Add a new child page.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignSingletonInterface $child
   *   Child campaign page.
   *
   * @return $this
   */
  public function addChild(CampaignSingletonInterface $child);

  /**
   * Remove child page.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignSingletonInterface $child
   *   Child campaign page.
   *
   * @return $this
   */
  public function removeChild(CampaignSingletonInterface $child);

  /**
   * Check if the guide already contains a page.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignSingletonInterface $child
   *   Child campaign page? (Should we be checking overview instead?).
   *
   * @return bool
   *   Campign has child result.
   */
  public function hasChild(CampaignSingletonInterface $child);

}
