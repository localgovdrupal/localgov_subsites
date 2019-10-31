<?php

namespace Drupal\bhcc_campaign\Plugin\Block;

use Drupal\bhcc_campaign\Node\CampaignMaster;
use Drupal\bhcc_campaign\Node\CampaignSingleton;
use Drupal\Core\Block\BlockBase;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\node\Entity\Node;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\node\NodeInterface;

/**
 * Class CampaignNavigationBlock
 *
 * @package Drupal\bhcc_campaign\Plugin\Block
 *
 * @Block(
 *   id = "campaign_navigation_block",
 *   admin_label = "Campaign navigation",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node")
 *   }
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
    $node = $this->getContextValue('node');

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
    $currentNode = $this->getContextValue('node');

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
      if (is_null($node)) {
        continue;
      }

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
    return Cache::mergeContexts(parent::getCacheContexts(), ['route']);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {

    $homepage = $this->getCampaign();
    $campaign_nodes = $this->listHomepageAndPages($homepage);
    $campaign_cache_tags = $this->prepareCacheTagsForCampaign($campaign_nodes);

    return Cache::mergeTags(parent::getCacheTags(), $campaign_cache_tags);
  }

  /**
   * All cache tags for *a* Campaign.
   *
   * List cache tags for the given Campaign homepage and its child Campaign
   * pages.
   *
   * @param array $campaign_nodes
   *   List of nodes.
   *
   * @return array
   *   List of strings.
   */
  protected function prepareCacheTagsForCampaign(array $campaign_nodes): array {

    $list_of_tag_collections = array_map(function (CacheableDependencyInterface $cacheable): array {
      return $cacheable->getCacheTags();
    }, $campaign_nodes);

    $merged_tags = array_reduce($list_of_tag_collections, [Cache::class, 'mergeTags'], $initial = []);
    return $merged_tags;
  }

  /**
   * List of Campaign homepage and its child Campaign pages.
   *
   * @return array
   *   List of nodes.
   */
  protected function listHomepageAndPages(NodeInterface $homepage): array {

    $page_refs = $homepage->field_campaign_pages;
    $page_nodes = array_map(function (EntityReferenceItem $ref) {
      return $ref->entity;
    }, iterator_to_array($page_refs));

    // Weed out the references to deleted nodes.
    $existing_pages = array_filter($page_nodes);

    $related_campaign_nodes = $existing_pages;
    $related_campaign_nodes[] = $homepage;
    $related_campaign_nodes_w_sequential_keys = array_values($related_campaign_nodes);

    return $related_campaign_nodes_w_sequential_keys;
  }

}
