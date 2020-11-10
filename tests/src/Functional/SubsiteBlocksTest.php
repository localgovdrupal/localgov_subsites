<?php

namespace Drupal\Tests\localgov_subsites\Functional;

use Drupal\file\Entity\File;
use Drupal\media\Entity\Media;
use Drupal\node\NodeInterface;
use Drupal\Tests\BrowserTestBase;
use Drupal\Tests\TestFileCreationTrait;
use Drupal\Tests\node\Traits\NodeCreationTrait;

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
  public static $modules = [
    'localgov_subsites',
  ];

  /**
   * {@inheritdoc}
   */
  protected $defaultTheme = 'classy';

  /**
   * A user with the 'administer blocks' permission.
   *
   * @var \Drupal\user\UserInterface
   */
  protected $adminUser;

  /**
   * {@inheritdoc}
   */
  protected function setUp() {
    parent::setUp();

    $type = $this->container->get('entity_type.manager')->getStorage('node_type')
      ->create([
        'type' => 'article',
        'name' => 'Article',
      ]);
    $type->save();
    $this->container->get('router.builder')->rebuild();

    $this->adminUser = $this->drupalCreateUser(['administer blocks', 'edit any localgov_subsites_overview content']);
  }

  /**
   * Test banner block.
   */
  public function testSubsiteBannerBlock() {
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_subsite_banner');
    $this->drupalLogout();

    // Create a media image.
    $image = current($this->getTestFiles('image'));
    $file = File::create([
      'uri' => $image->uri,
      'status' => FILE_STATUS_PERMANENT,
    ]);
    $file->save();
    $media_image = Media::create([
      'bundle' => 'image',
      'field_media_image' => ['target_id' => $file->id()],
    ]);
    $media_image->save();

    // Create some nodes.
    $overview_title = $this->randomMachineName(8);
    $overview = $this->createNode([
      'title' => $overview_title,
      'type' => 'localgov_subsites_overview',
      'localgov_subsites_banner_image' => ['target_id' => $media_image->id()],
      'status' => NodeInterface::PUBLISHED,
    ]);
    $page_title = $this->randomMachineName(8);
    $page = $this->createNode([
      'title' => $page_title,
      'type' => 'localgov_subsites_page',
      'localgov_subsites_parent' => $overview->id(),
      'status' => NodeInterface::PUBLISHED,
    ]);
    $article = $this->createNode([
      'title' => 'Test article',
      'type' => 'article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Test subsite overview.
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-banner');
    $this->assertSession()->responseContains('<h1>' . $overview_title . '</h1>');
    $this->assertSession()->responseContains($image->filename);

    // Test subsite page.
    $this->drupalGet($page->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-banner');
    $this->assertSession()->responseContains('<h1>' . $page_title . '</h1>');
    $this->assertSession()->responseContains('<h2>' . $page_title . '</h2>');
    $this->assertSession()->responseContains($image->filename);

    // Test article.
    $this->drupalGet($article->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-banner');
    $this->assertSession()->responseNotContains($image->filename);
  }

  /**
   * Test navigation block.
   */
  public function testSubsiteNavigationBlock() {
    $this->drupalLogin($this->adminUser);
    $this->drupalPlaceBlock('localgov_subsite_navigation');
    $this->drupalLogout();

    // Create some nodes.
    $overview_title = $this->randomMachineName(8);
    $overview = $this->createNode([
      'title' => $overview_title,
      'type' => 'localgov_subsites_overview',
      'status' => NodeInterface::PUBLISHED,
    ]);
    $page1_title = $this->randomMachineName(8);
    $page1 = $this->createNode([
      'title' => $page1_title,
      'type' => 'localgov_subsites_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_parent' => ['target_id' => $overview->id()],
    ]);
    $page2_title = $this->randomMachineName(8);
    $page2 = $this->createNode([
      'title' => $page2_title,
      'type' => 'localgov_subsites_page',
      'status' => NodeInterface::PUBLISHED,
      'localgov_subsites_parent' => ['target_id' => $overview->id()],
    ]);
    $article = $this->createNode([
      'title' => 'Test article',
      'type' => 'article',
      'status' => NodeInterface::PUBLISHED,
    ]);

    // Test subsite overview.
    $xpath = '//ul[@class="navigation-links"]/li';
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-navigation');
    $results = $this->xpath($xpath);
    $this->assertEquals(3, count($results));
    $this->assertStringContainsString($overview_title, $results[0]->getText());
    $this->assertStringNotContainsString($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertStringContainsString($page1_title, $results[1]->getText());
    $this->assertStringContainsString($page1->toUrl()->toString(), $results[1]->getHtml());
    $this->assertStringContainsString($page2_title, $results[2]->getText());
    $this->assertStringContainsString($page2->toUrl()->toString(), $results[2]->getHtml());

    // Test subsite page.
    $this->drupalGet($page1->toUrl()->toString());
    $this->assertSession()->responseContains('block-localgov-subsite-navigation');
    $results = $this->xpath($xpath);
    $this->assertEquals(3, count($results));
    $this->assertStringContainsString($overview_title, $results[0]->getText());
    $this->assertStringContainsString($overview->toUrl()->toString(), $results[0]->getHtml());
    $this->assertStringContainsString($page1_title, $results[1]->getText());
    $this->assertStringNotContainsString($page1->toUrl()->toString(), $results[1]->getHtml());
    $this->assertStringContainsString($page2_title, $results[2]->getText());
    $this->assertStringContainsString($page2->toUrl()->toString(), $results[2]->getHtml());

    // Test article.
    $this->drupalGet($article->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-navigation');
    $this->assertSession()->pageTextNotContains($overview_title);
    $this->assertSession()->pageTextNotContains($page1_title);
    $this->assertSession()->pageTextNotContains($page2_title);

    // Test hide sidebar field.
    $overview->set('localgov_subsites_hide_menu', ['value' => 1]);
    $overview->save();
    $this->drupalGet($overview->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-navigation');
    $this->drupalGet($page1->toUrl()->toString());
    $this->assertSession()->responseNotContains('block-localgov-subsite-navigation');
  }

}
