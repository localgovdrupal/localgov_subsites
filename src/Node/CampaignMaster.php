<?php

namespace Drupal\localgov_campaigns\Node;

use Drupal\bhcc_helper\Node\NodeBase;

/**
 * Class CampaignMaster.
 *
 * @package Drupal\bhcc_campaign\Node
 */
class CampaignMaster extends NodeBase implements CampaignMasterInterface {

  /**
   * {@inheritdoc}
   */
  public function addChild(CampaignSingletonInterface $child) {
    if (!$this->hasChild($child)) {
      $this->set('field_campaign_pages', $this->get('field_campaign_pages')->getValue() + ['target_id' => $child->id()]);
    }

    return $this;
  }

  /**
   * {@inheritdoc}
   */
  public function removeChild(CampaignSingletonInterface $child) {
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
   * {@inheritdoc}
   */
  public function hasChild(CampaignSingletonInterface $child) {
    foreach ($this->get('field_campaign_pages')->getValue() as $item) {
      if ($item['target_id'] == $child->id()) {
        return TRUE;
      }
    }

    return FALSE;
  }

}
