<?php

namespace Drupal\localgov_campaigns\Plugin\Block;

use Drupal\Core\Access\AccessResult;
use Drupal\Core\Cache\Cache;
use Drupal\Core\Cache\CacheableDependencyInterface;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\Core\Session\AccountInterface;
use Drupal\node\NodeInterface;
use Drupal\node\Entity\Node;

/**
 * Class CampaignNavigationBlock.
 *
 * @package Drupal\localgov_campaigns\Plugin\Block
 *
 * @Block(
 *   id = "localgov_campaign_navigation",
 *   admin_label = "Campaign navigation",
 * )
 */
class CampaignsNavigationBlock extends CampaignsAbstractBlockBase {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $build = [];

    if ($campaign = $this->getCampaign()) {
      $links = $this->formatLinks($campaign, $this->node);

      if ($links) {
        $build[] = [
          '#theme' => 'campaign_navigation',
          '#heading' => $campaign->label(),
          '#parent_url' => $campaign->toUrl()->toString(),
          '#links' => $links,
        ];
      }
    }

    return $build;
  }

  /**
   * Format links for the campaign navigation theme.
   *
   * @param \Drupal\node\NodeInterface $campaign
   *   Node object of campaign overview page.
   * @param \Drupal\node\NodeInterface $currentNode
   *   Current page node.
   *
   * @return array
   *   Menu links for build.
   *
   * @throws \Drupal\Core\Entity\EntityMalformedException
   */
  protected function formatLinks(NodeInterface $campaign, NodeInterface $currentNode) {
    $links = [];

    if ($currentNode instanceof NodeInterface) {
      $currentNid = $currentNode->id();
    }
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

    $campaign_pages = $campaign->get('field_campaign_pages')->getValue();
    $campaign_pages_count = $campaign->get('field_campaign_pages')->count();

    if ($campaign_pages_count > 0) {
      foreach ($campaign_pages as $node_data) {
        if (isset($node_data['target_id'])) {
          $node = Node::load($node_data['target_id']);
          if (is_null($node)) {
            continue;
          }
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
    }

    return $links;
  }

  /**
   * {@inheritdoc}
   */
  protected function blockAccess(AccountInterface $account) {
    $campaign = $this->getCampaign();
    if (!is_null($campaign) &&
      $campaign->hasField('field_hide_sidebar') &&
      $campaign->field_hide_sidebar->value == 1
    ) {
      return AccessResult::neutral();
    }

    return parent::blockAccess($account);
  }

  /**
   * {@inheritdoc}
   */
  public function getCacheTags() {
    $campaign_nodes = $this->listOverviewAndPages();
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
    $merged_tags = array_reduce($list_of_tag_collections, [Cache::class, 'mergeTags'], []);
    return $merged_tags;
  }

  /**
   * List of Campaign Overview and its child Campaign pages.
   *
   * @return array
   *   List of nodes.
   */
  protected function listOverviewAndPages(): array {
    $overview = $this->getCampaign();
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
