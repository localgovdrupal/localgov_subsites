<?php

namespace Drupal\bhcc_campaign\Plugin\Block;

use Drupal\bhcc_campaign\Node\CampaignMaster;
use Drupal\bhcc_campaign\Node\CampaignSingleton;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\node\Entity\Node;

/**
 * Class CampaignNavigationBlock
 *
 * @package Drupal\bhcc_campaign\Plugin\Block
 *
 * @Block(
 *   id = "campaign_navigation_block",
 *   admin_label = "Campaign navigation"
 * )
 */
class CampaignNavigationBlock extends BlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($campaign = $this->getCampaign()) {
      $links = $this->formatLinks($campaign);

      if ($links) {
        $build[] = [
          '#theme' => 'campaign_navigation',
          '#heading' => $campaign->label(),
          '#parentURL' => $campaign->toUrl()->toString(),
          '#links' => $links
        ];
      }
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
   * Format links for the campaign navigation theme.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignMaster $campaign
   *
   * @return array
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  private function formatLinks(CampaignMaster $campaign) {

    // Get current node and store nid as variable currentNid
    $currentNode = \Drupal::routeMatch()->getParameter('node');
    if ($currentNode instanceof \Drupal\node\NodeInterface) {
      $currentNid = $currentNode->id();
    }

    $links = [];

    if ($currentNid == $campaign->id()) {
      $links[] = [
        'title' => $campaign->label(),
        'url' => $campaign->toUrl(),
        'class' => 'is-active'
      ];
    }

    else {
      $links[] = [
        'title' => $campaign->label(),
        'url' => $campaign->toUrl()
      ];        
    }

    foreach ($campaign->get('field_campaign_pages')->getValue() as $node_data) {
      $node = Node::load($node_data['target_id']);

      $campaignNid = $node->id();

      if ($currentNid == $campaignNid) {
        $links[] = [
          'title' => $node->label(),
          'url' => $node->toUrl(),
          'class' => 'is-active'
        ];
      }

      else {
        $links[] = [
          'title' => $node->label(),
          'url' => $node->toUrl()
        ];        
      }


    }

    return $links;
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheContexts() {
    return Cache::mergeContexts(parent::getCacheContexts(), array('route'));
  }
}
