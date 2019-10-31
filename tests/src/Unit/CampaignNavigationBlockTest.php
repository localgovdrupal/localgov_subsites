<?php

namespace Drupal\Tests\bhcc_campaign\Unit;

use Drupal\bhcc_campaign\Plugin\Block\CampaignNavigationBlock;
use Drupal\Core\Field\EntityReferenceFieldItemList;
use Drupal\Core\Field\Plugin\Field\FieldType\EntityReferenceItem;
use Drupal\node\Entity\Node;
use Drupal\Tests\UnitTestCase;

/**
 * Unit tests for the CampaignNavigationBlock class.
 *
 * @coversDefaultClass Drupal\bhcc_campaign\Plugin\Block\CampaignNavigationBlock
 * @group bhcc
 */
class CampaignNavigationBlockTest extends UnitTestCase {

  /**
   * Tests for CampaignNavigationBlock::prepareCacheTagsForCampaign().
   *
   * We provide some nodes and expect cache tags based on their nid.
   */
  public function testPrepareCacheTagsForCampaign() {

    $campaign_nodes = [
      $this->campaignPageNode20,
      $this->campaignPageNode30,
      $this->campaignHomepageNode10,
    ];

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'PrepareCacheTagsForCampaign');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaign_nodes);

    // The cache tags are always sorted.
    $expected_cache_tags = ['node:10', 'node:20', 'node:30'];

    $this->assertEquals($expected_cache_tags, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::prepareCacheTagsForCampaign().
   *
   * Prepare cache tag for one node only.
   */
  public function testPrepareCacheTagsForCampaignWithNoChild() {

    $campaign_nodes = [$this->campaignHomepageNode100];

    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'PrepareCacheTagsForCampaign');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaign_nodes);

    $expected_cache_tags = ['node:100'];
    $this->assertEquals($expected_cache_tags, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::listHomepageAndPages().
   *
   * The Campaign homepage has some child Campaign pages.
   */
  public function testListHomepageAndPages() {

    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'listHomepageAndPages');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $this->campaignHomepageNode10);

    $expected_campaign_nodes = [
      $this->campaignPageNode20,
      $this->campaignPageNode30,
      $this->campaignHomepageNode10,
    ];
    $this->assertEquals($expected_campaign_nodes, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::listHomepageAndPages().
   *
   * The Campaign homepage has no child Campaign page.
   */
  public function testListHomepageWithNoChildPage() {

    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'listHomepageAndPages');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $this->campaignHomepageNode100);

    $expected_campaign_nodes = [$this->campaignHomepageNode100];
    $this->assertEquals($expected_campaign_nodes, $result);
  }

  /**
   * Prepares mock objects.
   */
  public function setUp() {

    $this->testTarget = new CampaignNavigationBlock($configuration = [], $plugin_id = 'none', $plugin_definition = ['provider' => 'nobody']);

    // Campaign homepage page nodes.
    $this->campaignHomepageNode10 = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->campaignHomepageNode10->expects($this->any())
      ->method('getCacheTags')
      ->willReturn(['node:10']);

    $this->campaignHomepageNode100 = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->campaignHomepageNode100->expects($this->any())
      ->method('getCacheTags')
      ->willReturn(['node:100']);

    // Campaign page nodes.
    $this->campaignPageNode20 = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->campaignPageNode20->expects($this->any())
      ->method('getCacheTags')
      ->willReturn(['node:20']);

    $this->campaignPageNode30 = $this->getMockBuilder(Node::class)
      ->disableOriginalConstructor()
      ->getMock();
    $this->campaignPageNode30->expects($this->any())
      ->method('getCacheTags')
      ->willReturn(['node:30']);

    // Add child Campaign pages to Campaign homepage 10.
    $mockEntityRefItemForNode20 = $this->getMockBuilder(EntityReferenceItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEntityRefItemForNode20->expects($this->any())
      ->method('__isset')
      ->with('entity')
      ->willReturn(TRUE);
    $mockEntityRefItemForNode20->expects($this->any())
      ->method('__get')
      ->with('entity')
      ->willReturn($this->campaignPageNode20);

    $mockEntityRefItemForNode30 = $this->getMockBuilder(EntityReferenceItem::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEntityRefItemForNode30->expects($this->any())
      ->method('__isset')
      ->with('entity')
      ->willReturn(TRUE);
    $mockEntityRefItemForNode30->expects($this->any())
      ->method('__get')
      ->with('entity')
      ->willReturn($this->campaignPageNode30);

    $mockEntityRefList = $this->getMockBuilder(EntityReferenceFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEntityRefList->expects($this->any())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([$mockEntityRefItemForNode20, $mockEntityRefItemForNode30]));
    $this->campaignHomepageNode10->expects($this->any())
      ->method('__get')
      ->with('field_campaign_pages')
      ->willReturn($mockEntityRefList);

    // Campaign homepage 100 has no child Campaign page.
    $mockEmptyEntityRefList = $this->getMockBuilder(EntityReferenceFieldItemList::class)
      ->disableOriginalConstructor()
      ->getMock();
    $mockEmptyEntityRefList->expects($this->any())
      ->method('getIterator')
      ->willReturn(new \ArrayIterator([]));
    $this->campaignHomepageNode100->expects($this->any())
      ->method('__get')
      ->with('field_campaign_pages')
      ->willReturn($mockEmptyEntityRefList);
  }

  /**
   * This is what we are testing.
   *
   * @var Drupal\bhcc_campaign\Plugin\Block\CampaignNavigationBlock
   */
  protected $testTarget;

  /**
   * Campaign homepage node.
   *
   * @var Drupal\node\NodeInterface
   */
  protected $campaignHomepageNode10;
  protected $campaignHomepageNode100;

  /**
   * Campaign page nodes.
   *
   * @var Drupal\node\NodeInterface
   */
  protected $campaignPageNode20;
  protected $campaignPageNode30;

}
