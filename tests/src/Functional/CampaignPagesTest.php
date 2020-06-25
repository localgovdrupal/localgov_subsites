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
    $this->drupalGet('/admin/structure/types/manage/localgov_campaigns_overview/fields');
    $this->assertSession()->statusCodeEquals(200);

    $this->drupalGet('/admin/structure/types/manage/localgov_campaigns_page/fields');
    $this->assertSession()->statusCodeEquals(200);
  }

}
