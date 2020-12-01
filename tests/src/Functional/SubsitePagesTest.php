<?php

namespace Drupal\Tests\localgov_subsites\Functional;

use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;

/**
 * Tests LocalGov Subsite pages work together.
 *
 * @group localgov_subsites
 */
class SubsitePagesTest extends BrowserTestBase {

  use NodeCreationTrait;
  use AssertBreadcrumbTrait;


  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_theme';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

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
    'localgov_subsites',
    'localgov_subsites_paragraphs',
    'entity_hierarchy',
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
      'reorder entity_hierarchy children',
      'create localgov_subsites_page content',
      'create localgov_subsites_overview content',

    ]);
    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * Verifies basic functionality with all modules.
   */
  public function testSubsiteFields() {
    $this->drupalLogin($this->adminUser);

    // Check overview fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_subsites_overview/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('localgov_subsites_banner');
    $this->assertSession()->pageTextContains('localgov_subsites_content');
    $this->assertSession()->pageTextContains('localgov_subsites_hide_menu');
    $this->assertSession()->pageTextContains('localgov_subsites_summary');
    $this->assertSession()->pageTextContains('localgov_subsites_theme');

    // Check page fields.
    $this->drupalGet('/admin/structure/types/manage/localgov_subsites_page/fields');
    $this->assertSession()->statusCodeEquals(200);
    $this->assertSession()->pageTextContains('localgov_subsites_content');
    $this->assertSession()->pageTextContains('localgov_subsites_topic');
    $this->assertSession()->pageTextContains('localgov_subsites_parent');
    $this->assertSession()->pageTextContains('localgov_subsites_summary');

    // Check fieldgroup tabs on node/add form.
    $this->drupalGet('/node/add/localgov_subsites_overview');
    $this->assertSession()->pageTextContains('Description');
    $this->assertSession()->pageTextContains('Banner and colour theme');
    $this->assertSession()->pageTextContains('Page builder');
    $this->drupalGet('/node/add/localgov_subsites_page');
    $this->assertSession()->pageTextContains('Description');
    $this->assertSession()->pageTextContains('Banner');
    $this->assertSession()->pageTextContains('Page builder');
  }

  /**
   * Pathauto and breadcrumbs.
   */
  public function testSubsitePaths() {
    $overview = $this->createNode([
      'title' => 'Overview 1',
      'type' => 'localgov_subsites_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $this->createNode([
      'title' => 'Page 1',
      'type' => 'localgov_subsites_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_parent' => ['target_id' => $overview->id()],
    ]);

    $this->drupalGet('overview-1/page-1');
    $this->assertText('Page 1');
    $trail = ['' => 'Home'];
    $trail += ['overview-1' => 'Overview 1'];
    //$this->assertBreadcrumb(NULL, $trail);
  }

}