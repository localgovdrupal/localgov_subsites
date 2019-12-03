<?php

namespace Drupal\Tests\bhcc_campaign\Unit;

use Drupal\Core\Url;
use Drupal\bhcc_campaign\Plugin\Block\CampaignNavigationBlock;

/**
 * Unit tests for the CampaignNavigationBlock class.
 *
 * @coversDefaultClass Drupal\bhcc_campaign\Plugin\Block\CampaignNavigationBlock
 * @group bhcc
 */
class CampaignNavigationBlockTest extends CampaignUnitTestCase {

  /**
   * Tests for CampaignNavigationBlock::formatLinks()
   *
   * We provide the campaign overview node for both parameters and expect to
   * get the links for the navigation block returned with the
   * campaign overview set as active.
   */
  public function testFormatLinks() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode10;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'formatLinks');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode, $campaignNode);

    $expected = [
      [
        'title' => 'Campaign Overview 10',
        'url' => Url::fromUri('entity:node/10'),
        'class' => 'is-active',
      ],
      [
        'title' => 'Campaign Child Page 20',
        'url' => Url::fromUri('entity:node/20'),
      ],
      [
        'title' => 'Campaign Child Page 30',
        'url' => Url::fromUri('entity:node/30'),
      ],
    ];

    // Assert it matches the expected.
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::formatLinks()
   *
   * We provide the campaign overview node with a campaign child page node
   * and expect to get the links for the navigation block returned with the
   * campaign childpage set as active.
   */
  public function testFormatLinksForChildCampaignPage() {

    // Node to test.
    $campaignNode = $this->campaignPageNode20;
    $campaignOverview = $this->campaignOverviewNode10;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'formatLinks');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignOverview, $campaignNode);

    $expected = [
      [
        'title' => 'Campaign Overview 10',
        'url' => Url::fromUri('entity:node/10'),
      ],
      [
        'title' => 'Campaign Child Page 20',
        'url' => Url::fromUri('entity:node/20'),
        'class' => 'is-active',
      ],
      [
        'title' => 'Campaign Child Page 30',
        'url' => Url::fromUri('entity:node/30'),
      ],
    ];

    // Assert it matches the expected.
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::formatLinks()
   *
   * We provide a campaign overview with no child pages,
   * and expect to only get a link to itself.
   */
  public function testFormatLinksEmptyCampaign() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode100;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'formatLinks');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode, $campaignNode);

    // Note that really the url will be an object.
    $expected = [
      [
        'title' => 'Campaign Overview 100',
        'url' => Url::fromUri('entity:node/100'),
        'class' => 'is-active',
      ],
    ];

    // Assert it matches the expected.
    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::prepareCacheTagsForCampaign().
   *
   * We provide some nodes and expect cache tags based on their nid.
   */
  public function testPrepareCacheTagsForCampaign() {

    $campaign_nodes = [
      $this->campaignPageNode20,
      $this->campaignPageNode30,
      $this->campaignOverviewNode10,
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

    $campaign_nodes = [$this->campaignOverviewNode100];

    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'PrepareCacheTagsForCampaign');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaign_nodes);

    $expected_cache_tags = ['node:100'];
    $this->assertEquals($expected_cache_tags, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::listOverviewAndPages().
   *
   * The Campaign Overview has some child Campaign pages.
   */
  public function testListOverviewAndPages() {

    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'listOverviewAndPages');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $this->campaignOverviewNode10);

    $expected_campaign_nodes = [
      $this->campaignPageNode20,
      $this->campaignPageNode30,
      $this->campaignOverviewNode10,
    ];
    $this->assertEquals($expected_campaign_nodes, $result);
  }

  /**
   * Tests for CampaignNavigationBlock::listOverviewAndPages().
   *
   * The Campaign Overview has no child Campaign page.
   */
  public function testListOverviewWithNoChildPage() {

    $method = new \ReflectionMethod(CampaignNavigationBlock::class, 'listOverviewAndPages');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $this->campaignOverviewNode100);

    $expected_campaign_nodes = [$this->campaignOverviewNode100];
    $this->assertEquals($expected_campaign_nodes, $result);
  }

  /**
   * Prepares mock objects.
   */
  public function setUp() {

    $this->testTarget = new CampaignNavigationBlock($configuration = [], $plugin_id = 'none', $plugin_definition = ['provider' => 'nobody']);

    // Mock node objects.
    $campaignOverviewNids = [10, 100];
    foreach ($campaignOverviewNids as $nid) {
      $nodeKey = 'campaignOverviewNode' . $nid;
      $this->$nodeKey = $this->mockNodeObject($nid, 'campaign_overview');
    }
    $campaignPageNids = [20, 30];
    foreach ($campaignPageNids as $nid) {
      $nodeKey = 'campaignPageNode' . $nid;
      $this->$nodeKey = $this->mockNodeObject($nid, 'campaign_page');
      $this->mockCampaignParent($this->$nodeKey, $this->campaignOverviewNode10);
    }

    // Mock the entity reference fields.
    $this->mockEntityRefFields();

    // Mock Drupal provided entity services.
    $this->mockDrupalEntityServices();
  }

}
