<?php

namespace Drupal\Tests\bhcc_campaign\Unit;

use Drupal\bhcc_campaign\Plugin\Block\CampaignOverviewBlock;

/**
 * Unit tests for the CampaignBannerBlock class.
 *
 * @coversDefaultClass Drupal\bhcc_campaign\Plugin\Block\CampaignOverviewBlock
 * @group bhcc
 */
class CampaignOverviewBlockTest extends CampaignUnitTestCase {

  /**
   * Tests for CampaignOverviewBlock::getBlockBuild().
   *
   * We provide a campaign overview and expect the defined build array retuned.
   */
  public function testGetBlockBuild() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode10;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignOverviewBlock::class, 'getBlockBuild');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $expected = [
      '#theme' => 'campaign_overview_banner',
      '#heading' => 'Campaign Overview 10',
      '#image' => 'public://campaign_banner_10.jpg',
    ];

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignOverviewBlock::getBlockBuild().
   *
   * We provide a campaign overview with no image
   * and expect the defined build array (with image as NULL) retuned.
   */
  public function testGetBlockBuildNoImage() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode200;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignOverviewBlock::class, 'getBlockBuild');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $expected = [
      '#theme' => 'campaign_overview_banner',
      '#heading' => 'Campaign Overview 200',
      '#image' => NULL,
    ];

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignOverviewBlock::getBlockBuild().
   *
   * We provide the a non campaign node
   * and expect to get an empty array returned.
   */
  public function testGetBlockBuildWrongNodeType() {

    // Node to test.
    $wrongNode = $this->genericNode;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignOverviewBlock::class, 'getBlockBuild');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $wrongNode);

    $expected = [];

    $this->assertEquals($expected, $result);
  }

  /**
   * Prepares mock objects.
   */
  public function setUp() {

    $this->testTarget = new CampaignOverviewBlock($configuration = [], $plugin_id = 'none', $plugin_definition = ['provider' => 'nobody']);

    // Mock node objects.
    $campaignOverviewNids = [10, 200];
    foreach ($campaignOverviewNids as $nid) {
      $nodeKey = 'campaignOverviewNode' . $nid;
      $this->$nodeKey = $this->mockNodeObject($nid, 'campaign_overview');
    }

    // Mock a generic non campaign node.
    $this->genericNode = $this->mockGenericNonCampaignNode();

    // Mock image fields.
    $this->mockBannerImageFields();
  }

}
