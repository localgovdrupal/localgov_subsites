<?php

namespace Drupal\Tests\localgov_campaigns\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\Entity\Node;

/**
 * Kernel tests to check field_campaign_pages field gets updated correctly.
 *
 * @group localgov_campaigns
 */
class CampaignPagesTest extends KernelTestBase {

  use NodeCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'entity_reference',
    'entity_reference_revisions',
    'field',
    'field_group',
    'file',
    'filter',
    'image',
    'layout_discovery',
    'layout_paragraphs',
    'link',
    'menu_ui',
    'node',
    'options',
    'paragraphs',
    'path',
    'system',
    'taxonomy',
    'text',
    'token',
    'user',
    'localgov_core',
    'localgov_campaigns',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();

    $this->installEntitySchema('user');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);
    $this->installConfig([
      'filter',
      'system',
      'node',
      'localgov_campaigns',
    ]);
  }

  /**
   * Test adding pages to overview.
   */
  public function testUpdateCampaignPages() {

    // Create a campaign overview.
    $overview = $this->createNode([
      'title' => 'Overview Page',
      'type' => 'localgov_campaigns_overview',
    ]);
    $this->assertEmpty($overview->field_campaign_pages);

    // Check campaign page reference gets added to overview on node creation.
    $page1 = $this->createNode([
      'title' => 'Page 1',
      'type' => 'localgov_campaigns_page',
      'field_campaign' => ['target_id' => $overview->id()],
    ]);
    $this->assertEquals($overview->id(), $page1->field_campaign->entity->id());
    $overview = Node::load($overview->id());
    $pages = $overview->field_campaign_pages->getValue();
    $this->assertEquals(1, count($pages));
    $this->assertEquals($page1->id(), $pages[0]['target_id']);

    // Check campaign page reference gets added to overview on node update.
    $page2 = $this->createNode([
      'title' => 'Page 2',
      'type' => 'localgov_campaigns_page',
    ]);
    $page2->set('field_campaign', ['target_id' => $overview->id()]);
    $page2->save();
    $this->assertEquals($overview->id(), $page2->field_campaign->entity->id());
    $overview = Node::load($overview->id());
    $pages = $overview->field_campaign_pages->getValue();
    $this->assertEquals(2, count($pages));
    $this->assertEquals($page2->id(), $pages[1]['target_id']);

    // Check campaign pages get removed from overview if campaign changes.
    $new_overview = $this->createNode([
      'title' => 'Another Overview Page',
      'type' => 'localgov_campaigns_overview',
    ]);
    $page2->set('field_campaign', ['target_id' => $new_overview->id()]);
    $page2->save();
    $new_overview = Node::load($new_overview->id());
    $this->assertEquals($new_overview->id(), $page2->field_campaign->entity->id());
    $overview = Node::load($overview->id());
    $pages = $overview->field_campaign_pages->getValue();
    $this->assertEquals(1, count($pages));
    $this->assertEquals($page1->id(), $pages[0]['target_id']);

    // Check campaign pages get deleted from overview page.
    // Trying to delete $page1 here throws a TransactionNoActiveException
    // exception so calling the node delete hook here directly.
    localgov_campaigns_node_delete($page1);
    $overview = Node::load($overview->id());
    $this->assertEmpty($overview->field_campaign_pages);
  }

}
