<?php

namespace Drupal\Tests\localgov_subsites\Functional;

use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\Traits\Core\CronRunTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Tests\system\Functional\Menu\AssertBreadcrumbTrait;
use Drupal\node\NodeInterface;

/**
 * Tests pages working together with LocalGov: pathauto, services, search.
 *
 * @group localgov_directories
 */
class LocalgovIntegrationTest extends BrowserTestBase {

  use NodeCreationTrait;
  use AssertBreadcrumbTrait;
  use CronRunTrait;

  /**
   * {@inheritdoc}
   */
  protected $profile = 'testing';

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'stark';

  /**
   * A user with permission to bypass content access checks.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * The node storage.
   *
   * @var \Drupal\node\NodeStorageInterface
   */
  protected $nodeStorage;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'localgov_core',
    'localgov_services_landing',
    'localgov_services_sublanding',
    'localgov_services_navigation',
    'localgov_subsites',
    'localgov_search',
    'localgov_search_db',
  ];

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();
    $this->drupalPlaceBlock('system_breadcrumb_block');
    $this->adminUser = $this->drupalCreateUser([
      'bypass node access',
      'administer nodes',
    ]);
    $this->nodeStorage = $this->container->get('entity_type.manager')->getStorage('node');
  }

  /**
   * LocalGov Search integration.
   */
  public function testLocalgovSearch() {

    // Confirm the localgov_subsites_overview nodes work with database search.
    $title = 'Subsite overview 1';
    $overview_summary = 'Science is the search for truth, that is the effort to understand the world: it involves the rejection of bias, of dogma, of revelation, but not the rejection of morality.';
    $this->createNode([
      'title' => $title,
      'type' => 'localgov_subsites_overview',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_summary' => $overview_summary,
    ]);
    $this->cronRun();
    $this->drupalGet('search', ['query' => ['s' => 'bias dogma revelation']]);
    $this->assertSession()->pageTextContains($title);
    $this->assertSession()->responseContains('<strong>bias</strong>');
    $this->assertSession()->responseContains('<strong>dogma</strong>');
    $this->assertSession()->responseContains('<strong>revelation</strong>');

    // Confirm the localgov_subsites_page nodes work with database search.
    $page_summary = "Time isn't the main thing. It's the only thing.";
    $this->createNode([
      'title' => 'Subsite subsite page',
      'type' => 'localgov_subsites_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_summary' => $page_summary,
    ]);
    $this->cronRun();
    $this->drupalGet('search', ['query' => ['s' => 'time main only']]);
    $this->assertSession()->pageTextContains('Subsite subsite page');
    $this->assertSession()->responseContains('<strong>time</strong>');
    $this->assertSession()->responseContains('<strong>main</strong>');
    $this->assertSession()->responseContains('<strong>only</strong>');
  }

}
