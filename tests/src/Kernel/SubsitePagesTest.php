<?php

namespace Drupal\Tests\localgov_subsites\Kernel;

use Drupal\KernelTests\KernelTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\node\Entity\Node;

/**
 * Kernel tests to check localgov_subsites_pages field gets updated correctly.
 *
 * @group localgov_subsites
 */
class SubsitePagesTest extends KernelTestBase {

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
    'media',
    'media_library',
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
    'views',
    'localgov_core',
    'localgov_subsites',
  ];

  /**
   * {@inheritdoc}
   */
  public function setUp() {
    parent::setup();

    $this->installEntitySchema('user');
    $this->installEntitySchema('media');
    $this->installEntitySchema('node');
    $this->installEntitySchema('file');
    $this->installSchema('node', ['node_access']);
    $this->installSchema('file', ['file_usage']);
    $this->installConfig([
      'filter',
      'system',
      'node',
      'localgov_subsites',
    ]);
  }

  /**
   * Test adding pages to overview.
   */
  public function testUpdateSubsitePages() {

    // Create a subsite overview.
    $overview = $this->createNode([
      'title' => 'Overview Page',
      'type' => 'localgov_subsites_overview',
    ]);
    $this->assertEmpty($overview->localgov_subsites_pages);

    // Check subsite page reference gets added to overview on node creation.
    $page1 = $this->createNode([
      'title' => 'Page 1',
      'type' => 'localgov_subsites_page',
      'localgov_subsites_parent' => ['target_id' => $overview->id()],
    ]);
    $this->assertEquals($overview->id(), $page1->localgov_subsites_parent->entity->id());
    $overview = Node::load($overview->id());
    $pages = $overview->localgov_subsites_pages->getValue();
    $this->assertEquals(1, count($pages));
    $this->assertEquals($page1->id(), $pages[0]['target_id']);

    // Check subsite page reference gets added to overview on node update.
    $page2 = $this->createNode([
      'title' => 'Page 2',
      'type' => 'localgov_subsites_page',
    ]);
    $page2->set('localgov_subsites_parent', ['target_id' => $overview->id()]);
    $page2->save();
    $this->assertEquals($overview->id(), $page2->localgov_subsites_parent->entity->id());
    $overview = Node::load($overview->id());
    $pages = $overview->localgov_subsites_pages->getValue();
    $this->assertEquals(2, count($pages));
    $this->assertEquals($page2->id(), $pages[1]['target_id']);

    // Check subsite pages get removed from overview if subsite changes.
    $new_overview = $this->createNode([
      'title' => 'Another Overview Page',
      'type' => 'localgov_subsites_overview',
    ]);
    $page2->set('localgov_subsites_parent', ['target_id' => $new_overview->id()]);
    $page2->save();
    $new_overview = Node::load($new_overview->id());
    $this->assertEquals($new_overview->id(), $page2->localgov_subsites_parent->entity->id());
    $overview = Node::load($overview->id());
    $pages = $overview->localgov_subsites_pages->getValue();
    $this->assertEquals(1, count($pages));
    $this->assertEquals($page1->id(), $pages[0]['target_id']);

    // Check subsite pages get deleted from overview page.
    // Trying to delete $page1 here throws a TransactionNoActiveException
    // exception so calling the node delete hook here directly.
    localgov_subsites_node_delete($page1);
    $overview = Node::load($overview->id());
    $this->assertEmpty($overview->localgov_subsites_pages);
  }

}
