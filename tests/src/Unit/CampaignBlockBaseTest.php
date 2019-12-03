<?php

namespace Drupal\Tests\bhcc_campaign\Unit;

use Drupal\bhcc_campaign\Plugin\Block\CampaignBlockBase;
use Drupal\bhcc_campaign\Node\CampaignMaster;

/**
 * Unit tests for the CampaignBlockBase abstract class.
 *
 * @coversDefaultClass Drupal\bhcc_campaign\Plugin\Block\CampaignBlockBase
 * @group bhcc
 */
class CampaignBlockBaseTest extends CampaignUnitTestCase {

  /**
   * Tests for CampaignBlockBase::getCampaign().
   *
   * We provide a campaign overview and expect that its returned
   * as its the campaign.
   */
  public function testGetCampaign() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode10;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBlockBase::class, 'getCampaign');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);
    $resultNid = $result->id();

    // Expected result.
    $expectedNid = 10;

    // Assert the campaign node we get is node '10'.
    $this->assertEquals($expectedNid, $resultNid);

    // Assert the returned node is of type CampaignMaster.
    $this->assertInstanceOf(CampaignMaster::class, $result);
  }

  /**
   * Tests for CampaignBlockBase::getCampaign().
   *
   * We provide a campaign child page,
   * and expect the parent campaign overview node to be returned.
   */
  public function testGetCampaignForChildPage() {

    // Node to test.
    $campaignNode = $this->campaignPageNode20;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBlockBase::class, 'getCampaign');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);
    $resultNid = $result->id();

    // Expected result - should be the parent campaign.
    $expectedNid = $this->campaignOverviewNode10->id();

    // Assert the campaign node we get is node '10'.
    $this->assertEquals($expectedNid, $resultNid);

    // Assert the returned node is of type CampaignMaster.
    $this->assertInstanceOf(CampaignMaster::class, $result);
  }

  /**
   * Tests for CampaignBlockBase::getCampaign().
   *
   * We provide a generic non campaign node.
   * and expect null to be returned.
   */
  public function testGetCampaignForNonCampaignNode() {

    // Node to test.
    $wrongNode = $this->genericNode;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBlockBase::class, 'getCampaign');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $wrongNode);

    // Assert returned NULL (this is not a campaign).
    $this->assertNull($result);
  }

  /**
   * Tests for CampaignBlockBase:getCampaignTitle().
   *
   * We provide a Campaign Overview and expect to get its node title returned.
   */
  public function testGetCampaignTitle() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode10;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBlockBase::class, 'getCampaignTitle');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $expected = 'Campaign Overview 10';

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignBlockBase::getCampaignBanner().
   *
   * We provide a campaign overview and expect the defined
   * file stream wrapper to be returned.
   */
  public function testGetCampaignBanner() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode10;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBlockBase::class, 'getCampaignBanner');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $expected = 'public://campaign_banner_10.jpg';

    $this->assertEquals($expected, $result);
  }

  /**
   * Tests for CampaignBlockBase::getCampaignBanner().
   *
   * We provide a campaign overview without an image
   * and expect NULL to be returned.
   */
  public function testGetCampaignBannerNoImage() {

    // Node to test.
    $campaignNode = $this->campaignOverviewNode200;

    // Turn protected method into public method.
    $method = new \ReflectionMethod(CampaignBlockBase::class, 'getCampaignBanner');
    $method->setAccessible(TRUE);
    $result = $method->invoke($this->testTarget, $campaignNode);

    $this->assertNull($result);
  }

  /**
   * Prepares mock objects.
   */
  public function setUp() {

    $this->testTarget = $this->getMockForAbstractClass(
      '\Drupal\bhcc_campaign\Plugin\Block\CampaignBlockBase', [
        $configuration = [],
        $plugin_id = 'none',
        $plugin_definition = ['provider' => 'nobody'],
      ]
    );

    // Mock node objects.
    $campaignOverviewNids = [10, 200];
    foreach ($campaignOverviewNids as $nid) {
      $nodeKey = 'campaignOverviewNode' . $nid;
      $this->$nodeKey = $this->mockNodeObject($nid, 'campaign_overview');
    }

    // Only need 1 child campaign node.
    $this->campaignPageNode20 = $this->mockNodeObject(20, 'campaign_page');
    $this->mockCampaignParent($this->campaignPageNode20, $this->campaignOverviewNode10);

    // Get a generic node to fail getting a campaign.
    $this->genericNode = $this->mockGenericNonCampaignNode();

    // Mock campaign image fields.
    $this->mockBannerImageFields();
  }

}
