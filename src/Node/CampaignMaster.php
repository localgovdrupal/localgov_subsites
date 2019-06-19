<?php

namespace Drupal\bhcc_campaign\Node;

use Drupal\bhcc_helper\Node\NodeBase;

/**
 * Class CampaignMaster
 *
 * @package Drupal\bhcc_campaign\Node
 */
class CampaignMaster extends NodeBase {

  /**
   * Add a new child page.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignSingleton $child
   *
   * @return $this
   */
  public function addChild(CampaignSingleton $child) {
    if (!$this->hasChild($child)) {
      $this->set('field_campaign_pages', $this->get('field_campaign_pages')->getValue() + ['target_id' => $child->id()]);
    }

    return $this;
  }

  /**
   * Remove child page.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignSingleton $child
   *
   * @return $this
   */
  public function removeChild(CampaignSingleton $child) {
    if ($this->hasChild($child)) {
      $children = $this->get('field_campaign_pages')->getValue();

      foreach ($children as $delta => $child_info) {
        if ($child_info['target_id'] == $child->id()) {
          unset($children[$delta]);
        }
      }

      $this->set('field_campaign_pages', $children);
    }

    return $this;
  }

  /**
   * Check if the guide already contains a page.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignSingleton $child
   *
   * @return bool
   */
  public function hasChild(CampaignSingleton $child) {
    foreach ($this->get('field_campaign_pages')->getValue() as $item) {
      if ($item['target_id'] == $child->id()) { return true; }
    }

    return false;
  }
}
