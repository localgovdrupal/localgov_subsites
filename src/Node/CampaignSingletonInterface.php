<?php

namespace Drupal\bhcc_campaign\Node;

/**
 * Interface CampaignSingleton.
 *
 * @package Drupal\bhcc_campaign\Node
 */
interface CampaignSingletonInterface {

  /**
   * Get parent node.
   *
   * @return bool|\Drupal\Core\Entity\EntityInterface|\Drupal\node\Entity\NodeInterface|null
   *   Parent campign node, or FALSE if no parent.
   *
   * @throws \Drupal\Core\TypedData\Exception\MissingDataException
   */
  public function getParent();

}
