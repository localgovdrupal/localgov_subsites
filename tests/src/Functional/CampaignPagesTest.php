<?php

namespace Drupal\Tests\localgov_campaigns\Functional;

use Drupal\Tests\BrowserTestBase;

/**
 * Tests LocalGov Campaign pages work together.
 *
 * @group localgov_campaigns
 */
class CampaignPagesTest extends BrowserTestBase {

  /**
   * Test breadcrumbs in the Standard profile.
   *
   * @var string
   */
  protected $profile = 'standard';

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_campaigns',
    'field_ui',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
      'administer node fields',
    ]);
    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * Verifies basic functionality with all modules.
   */
  public function testCampaignFields() {
    $this->drupalLogin($this->adminUser);

    // Check overview fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_campaigns_overview/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Summary');
    $this->assertSession()->pageTextContains('Banner');
    $this->assertSession()->pageTextContains('Banner colour');
    $this->assertSession()->pageTextContains('Campaign pages');
    $this->assertSession()->pageTextContains('Full width overview');
    $this->assertSession()->pageTextContains('Page builder');
    $this->assertSession()->pageTextContains('Select colourway accent');
    $this->assertSession()->pageTextContains('Select colourway gradient');

    // Check page fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_campaigns_page/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('Summary');
    $this->assertSession()->pageTextContains('Campaign');
    $this->assertSession()->pageTextContains('Page builder');
    $this->assertSession()->pageTextContains('Topic term');

    // Check edit tabs.
    $this->drupalGet('/node/add/localgov_campaigns_overview');
    $this->assertSession()->pageTextContains('Description');
    $this->assertSession()->pageTextContains('Banner image and colours');
    $this->assertSession()->pageTextContains('Page builder');
    $this->assertSession()->pageTextContains('Child pages');
    $this->drupalGet('/node/add/localgov_campaigns_page');
    $this->assertSession()->pageTextContains('Description');
    $this->assertSession()->pageTextContains('Page builder');
  }

}
