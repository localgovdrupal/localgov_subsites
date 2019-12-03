<?php

namespace Drupal\Tests\bhcc_campaign\Unit;

use Drupal\bhcc_campaign\Plugin\Block\CampaignBannerBlock;

/**
 * Unit tests for the CampaignBannerBlock class.
 *
 * @coversDefaultClass Drupal\bhcc_campaign\Plugin\Block\CampaignBannerBlock
 * @group bhcc
 */
class CampaignBannerBlockTest extends CampaignUnitTestCase {

  /**
   * Tests for CampaignBannerBlock::getBlockBuild().
   *
   * We provide a campaign child page
   * and expect the defined build array returned.
   */
  public function testGetBlockBuild() {

    // Node to test.
    $campaignNode = $this->campaignPageNode20;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBannerBlock::class, 'getBlockBuild');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $expected = [
      '#theme' => 'campaign_banner',
      '#tag' => 'Campaign Overview 10',
      '#heading' => 'Campaign Child Page 20',
      '#image' => 'public://campaign_banner_10.jpg',
    ];

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignBannerBlock::getBlockBuild().
   *
   * We provide a campaign child page attached to a campaign with no image
   * and expect the defined build array returned. (no image present)
   */
  public function testGetBlockBuildNoBanner() {

    // Node to test.
    $campaignNode = $this->campaignPageNode220;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBannerBlock::class, 'getBlockBuild');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $expected = [
      '#theme' => 'campaign_banner',
      '#tag' => 'Campaign Overview 200',
      '#heading' => 'Campaign Child Page 220',
      '#image' => NULL,
    ];

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignBannerBlock::getBlockBuild().
   *
   * We provide a generic node which is not part of a campaign
   * and expect an empty value back (the block should not render).
   */
  public function testGetBlockBuildNotCampaignNode() {

    // Node to test.
    $wrongNode = $this->genericNode;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBannerBlock::class, 'getBlockBuild');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $wrongNode);

    $this->assertEmpty($result);
  }

  /**
   * Prepares mock objects.
   */
  public function setUp() {

    $this->testTarget = new CampaignBannerBlock($configuration = [], $plugin_id = 'none', $plugin_definition = ['provider' => 'nobody']);

    // Mock node objects.
    $campaignOverviewNids = [10, 200];
    foreach ($campaignOverviewNids as $nid) {
      $nodeKey = 'campaignOverviewNode' . $nid;
      $this->$nodeKey = $this->mockNodeObject($nid, 'campaign_overview');
    }

    // Mock Campaign child page for node 10 (with image banner)
    $this->campaignPageNode20 = $this->mockNodeObject(20, 'campaign_page');
    $this->mockCampaignParent($this->campaignPageNode20, $this->campaignOverviewNode10);

    // Mock Campaign child page for node 220 (no image banner)
    $this->campaignPageNode220 = $this->mockNodeObject(220, 'campaign_page');
    $this->mockCampaignParent($this->campaignPageNode220, $this->campaignOverviewNode200);

    // Mock a generic non campaign node.
    $this->genericNode = $this->mockGenericNonCampaignNode();

    // Mock image fields.
    $this->mockBannerImageFields();
  }

}
