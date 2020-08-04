<?php

namespace Drupal\Tests\localgov_campaigns\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;

/**
 * Tests LocalGov Campaign pages work together.
 *
 * @group localgov_campaigns
 */
class CampaignPagesTest extends BrowserTestBase {

  use NodeCreationTrait;
  use AssertBreadcrumbTrait;

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
    'localgov_campaigns_paragraphs',
    'field_ui',
    'pathauto',
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
    $this->assertSession()->pageTextContains('localgov_campaigns_summary');
    $this->assertSession()->pageTextContains('localgov_campaigns_banner');
    $this->assertSession()->pageTextContains('localgov_campaigns_pages');
    $this->assertSession()->pageTextContains('localgov_campaigns_hide_menu');
    $this->assertSession()->pageTextContains('localgov_campaigns_content');
    $this->assertSession()->pageTextContains('localgov_campaigns_colour_accent');
    $this->assertSession()->pageTextContains('localgov_campaigns_colour_grad');

    // Check page fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_campaigns_page/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('localgov_campaigns_summary');
    $this->assertSession()->pageTextContains('localgov_campaigns_parent');
    $this->assertSession()->pageTextContains('localgov_campaigns_content');
    $this->assertSession()->pageTextContains('localgov_campaigns_topic');

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

  /**
   * Pathauto and breadcrumbs.
   */
  public function testCampaignPaths() {
    $overview = $this->createNode([
      'title' => 'Overview 1',
      'type' => 'localgov_campaigns_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->createNode([
      'title' => 'Page 1',
      'type' => 'localgov_campaigns_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_campaigns_parent' => ['target_id' => $overview->id()],
    ]);

    $this->drupalGet('overview-1/page-1');
    $this->assertText('Page 1');
    $trail = ['' => 'Home'];
    $trail += ['overview-1' => 'Overview 1'];
    $this->assertBreadcrumb(NULL, $trail);
  }

}
