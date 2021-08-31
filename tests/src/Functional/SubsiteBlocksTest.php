<?php

namespace Drupal\Tests\localgov_subsites\Functional;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\NodeInterface;
use Drupal\paragraphs\Entity\Paragraph;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;
use Drupal\Core\Database\Database;

/**
 * Tests user blocks.
 *
 * @group localgov_subsites
 */
class SubsiteBlocksTest extends BrowserTestBase {

  use NodeCreationTrait;
  use TestFileCreationTrait;

  /**
   * Modules to enable.
   *
   * @var array
   */
  protected static $modules = [
    'localgov_subsites',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'localgov_theme';

  /**
   * {@inheritdoc}
   */
  protected $profile = 'localgov';

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp(): void {
    parent::setUp();

    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();

    $this->adminUser = $this->drupalCreateUser(
      [
        'administer blocks',
        'create localgov_subsites_overview content',
        'edit any localgov_subsites_overview content',
      ]
    );
  }

  /**
   * Test banner block.
   */
  public function testSubsiteBannerBlock() {

    // If we're testing with sqlite, entity_hierarchy will break.
    // See https://github.com/localgovdrupal/localgov_subsites/pull/8#issuecomment-740668968
    $connection = Database::getConnection()->getConnectionOptions();
    if ($connection['driver'] === 'sqlite') {
      return;
    }

    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_subsite_banner', ['region' => 'content']);
    $this->drupalPlaceBlock('localgov_powered_by_block', ['region' => 'content']);
    $this->drupalLogout();

    // Create an image file entity.
    $image = current($this->getTestFiles('image'));
    $file = File::create([
      'uri' => $image->uri,
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $file->save();

    // Create an image media entity, referencing the file entity.
    $media_image = Media::create([
      'bundle' => 'image',
      'field_media_image' => ['target_id' => $file->id()],
    ]);
    $media_image->save();

    // Create a localgov_banner_secondary paragraph entity, referencing the
    // image media entity.
    $banner_paragraph = Paragraph::create([
      'type' => 'localgov_banner_secondary',
      'localgov_image' => ['target_id' => $media_image->id()],
    ]);
    $banner_paragraph->save();

    // Create some nodes.
    $subsite_overview_title = $this->randomMachineName(8);
    $subsite_overview = $this->createNode([
      'title' => $subsite_overview_title,
      'type' => 'localgov_subsites_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    // $subsite_overview->save();
    $subsite_overview->localgov_subsites_banner->appendItem($banner_paragraph);
    $subsite_overview->save();

    // Create a localgov_banner_secondary paragraph entity, referencing the
    // image media entity.
    $banner_paragraph_2 = Paragraph::create([
      'type' => 'localgov_banner_secondary',
      'localgov_image' => ['target_id' => $media_image->id()],
    ]);
    $banner_paragraph_2->save();
    $subsite_page_title = $this->randomMachineName(8);
    $subsite_page = $this->createNode([
      'title' => $subsite_page_title,
      'type' => 'localgov_subsites_page',
      'localgov_subsites_parent' => $subsite_overview->id(),
      'status' => NodeInterface::PUBLISHED,
    ]);
    $subsite_page->localgov_subsites_banner->appendItem($banner_paragraph_2);
    $subsite_page->save();

    $article_title = $this->randomMachineName(8);
    $article = $this->createNode([
      'title' =>
      $article_title,
      'type' => 'article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Test subsite overview.
    $this->drupalGet($subsite_overview->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-banner');
    $this->assertSession()->responseContains($subsite_overview_title);
    $this->assertSession()->responseContains($image->filename);

    // // Test subsite page.
    $this->drupalGet($subsite_page->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-banner');
    $this->assertSession()->responseContains($subsite_page_title);
    $this->assertSession()->responseContains($image->filename);

    // Test article node does NOT show the subsite block or image.
    $this->drupalGet($article->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-banner');
    $this->assertSession()->responseNotContains($image->filename);
    $this->assertSession()->pageTextContains($article_title);
  }

  /**
   * Test navigation block.
   */
  public function testSubsiteNavigationBlock() {

    // If we're testing with sqlite, entity_hierarchy will break.
    // See https://github.com/localgovdrupal/localgov_subsites/pull/8#issuecomment-740668968
    $connection = Database::getConnection()->getConnectionOptions();
    if ($connection['driver'] === 'sqlite') {
      return;
    }

    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_subsite_navigation');
    $this->drupalLogout();

    // Create some nodes.
    $subsite_overview_title = $this->randomMachineName(8);
    $subsite_overview = $this->createNode([
      'title' => $subsite_overview_title,
      'type' => 'localgov_subsites_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $subsite_page1_title = $this->randomMachineName(8);
    $subsite_page1 = $this->createNode([
      'title' => $subsite_page1_title,
      'type' => 'localgov_subsites_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_parent' => ['target_id' => $subsite_overview->id()],
    ]);
    $subsite_page2_title = $this->randomMachineName(8);
    $this->createNode([
      'title' => $subsite_page2_title,
      'type' => 'localgov_subsites_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_parent' => ['target_id' => $subsite_overview->id()],
    ]);
    $article_title = $this->randomMachineName(8);
    $article = $this->createNode([
      'title' =>
      $article_title,
      'type' => 'article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Test menu items on the overview page.
    $this->drupalGet($subsite_overview->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-navigation');
    $this->assertSession()->responseContains($subsite_page1_title);
    $this->assertSession()->responseContains($subsite_page2_title);
    $this->assertSession()->pageTextContains($subsite_overview_title);

    // Test article.
    $this->drupalGet($article->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-navigation');
    $this->assertSession()->pageTextNotContains($subsite_overview_title);
    $this->assertSession()->pageTextNotContains($subsite_page1_title);
    $this->assertSession()->pageTextNotContains($subsite_page2_title);
    $this->assertSession()->pageTextContains($article_title);

    // Test the ability to hide the navigation menu.
    $subsite_overview->set('localgov_subsites_hide_menu', ['value' => 1]);
    $subsite_overview->save();
    $this->drupalGet($subsite_overview->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-navigation');
    $this->drupalGet($subsite_page1->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-navigation');
  }

}
