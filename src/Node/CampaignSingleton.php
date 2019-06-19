<?php

namespace Drupal\bhcc_campaign\Node;

use Drupal\bhcc_helper\Node\NodeBase;
use Drupal\node\Entity\Node;

/**
 * Class CampaignSingleton
 *
 * @package Drupal\bhcc_campaign\Node
 */
class CampaignSingleton extends NodeBase {

  /**
   * Get parent node.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\Node|null
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getParent() {
    if (!$this->get('field_campaign')->isEmpty()) {
      return Node::load($this->get('field_campaign')->first()->getValue()['target_id']);
    }

    return false;
  }
}
