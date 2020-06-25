<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\localgov_campaigns\Node\CampaignMasterInterface;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;

/**
 * Class CampaignNavigationBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "campaign_navigation_block",
 *   admin_label = "Campaign navigation",
 *   context_definitions = {
 *     "node" = @ContextDefinition("entity:node")
 *   }
 * )
 */
class CampaignNavigationBlock extends CampaignBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    $node = $this->getContextValue('node');
    if ($campaign = $this->getCampaign($node)) {
      $links = $this->formatLinks($campaign, $node);

      if ($links) {
        $build[] = [
          '#theme' => 'campaign_navigation',
          '#heading' => $campaign->label(),
          '#parentURL' => $campaign->toUrl()->toString(),
          '#links' => $links,
        ];
      }
    }

    return $build;
  }

  /**
   * Format links for the campaign navigation theme.
   *
   * @param \Drupal\bhcc_campaign\Node\CampaignMasterInterface $campaign
   *   Node object of campaign overview page.
   * @param \Drupal\node\NodeInterface $currentNode
   *   Current page node.
   *
   * @return array
   *   Menu links for build.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function formatLinks(CampaignMasterInterface $campaign, NodeInterface $currentNode) {

    if ($currentNode instanceof NodeInterface) {
      $currentNid = $currentNode->id();
    }

    $links = [];

    if ($currentNid == $campaign->id()) {
      $links[] = [
        'title' => $campaign->label(),
        'url' => $campaign->toUrl(),
        'class' => 'is-active',
      ];
    }

    else {
      $links[] = [
        'title' => $campaign->label(),
        'url' => $campaign->toUrl(),
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
          'class' => 'is-active',
        ];
      }

      else {
        $links[] = [
          'title' => $node->label(),
          'url' => $node->toUrl(),
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

    $node = $this->getContextValue('node');
    $overview = $this->getCampaign($node);
    $campaign_nodes = $this->listOverviewAndPages($overview);
    $campaign_cache_tags = $this->prepareCacheTagsForCampaign($campaign_nodes);

    return Cache::mergeTags(parent::getCacheTags(), $campaign_cache_tags);
  }

  /**
   * All cache tags for *a* Campaign.
   *
   * List cache tags for the given Campaign Overview and its child Campaign
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
   * List of Campaign Overview and its child Campaign pages.
   *
   * @return array
   *   List of nodes.
   */
  protected function listOverviewAndPages(NodeInterface $overview): array {

    $page_refs = $overview->field_campaign_pages;
    $page_nodes = array_map(function (EntityReferenceItem $ref) {
      return $ref->entity;
    }, iterator_to_array($page_refs));

    // Weed out the references to deleted nodes.
    $existing_pages = array_filter($page_nodes);

    $related_campaign_nodes = $existing_pages;
    $related_campaign_nodes[] = $overview;
    $related_campaign_nodes_w_sequential_keys = array_values($related_campaign_nodes);

    return $related_campaign_nodes_w_sequential_keys;
  }

}
