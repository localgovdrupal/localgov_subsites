<?php

namespace Drupal\Tests\localgov_campaigns_paragraphs\Functional;

use Drupal\Tests\paragraphs\Functional\Classic\ParagraphsTestBase;

/**
 * Tests the configuration of localgov_paragraphs.
 */
class CampaignsParagraphsAdministrationTest extends ParagraphsTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = [
    'localgov_campaigns_paragraphs',
  ];

  /**
   * Tests the LocalGovDrupal core paragraph types.
   */
  public function testCampaignParagraphsTypes() {
    $this->loginAsAdmin([
      'administer paragraphs types',
    ]);

    // Check paragraph types installed.
    $this->drupalGet('/admin/structure/paragraphs_type');
    $this->assertSession()->pageTextContains('localgov_accordion');
    $this->assertSession()->pageTextContains('localgov_accordion_pane');
    $this->assertSession()->pageTextContains('localgov_box_link');
    $this->assertSession()->pageTextContains('localgov_call_out_box');
    $this->assertSession()->pageTextContains('localgov_documents');
    $this->assertSession()->pageTextContains('localgov_fact_box');
    $this->assertSession()->pageTextContains('localgov_link_and_summary');
    $this->assertSession()->pageTextContains('localgov_quote');
    $this->assertSession()->pageTextContains('localgov_table');
    $this->assertSession()->pageTextContains('localgov_video');

    // Check 'Accordion' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_accordion/fields');
    $this->assertSession()->pageTextContains('localgov_title');
    $this->assertSession()->pageTextContains('localgov_heading_level');
    $this->assertSession()->pageTextContains('localgov_paragraphs');

    // Check 'Accordion pane' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_accordion_pane/fields');
    $this->assertSession()->pageTextContains('localgov_title');
    $this->assertSession()->pageTextContains('localgov_heading_level');
    $this->assertSession()->pageTextContains('localgov_body_text');

    // Check 'Box link' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_box_link/fields');
    $this->assertSession()->pageTextContains('localgov_image');
    $this->assertSession()->pageTextContains('localgov_link');

    // Check 'Call out box' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_call_out_box/fields');
    $this->assertSession()->pageTextContains('localgov_background_image');
    $this->assertSession()->pageTextContains('localgov_body_text');
    $this->assertSession()->pageTextContains('localgov_button');
    $this->assertSession()->pageTextContains('localgov_header_text');

    // Check 'Documents' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_documents/fields');
    $this->assertSession()->pageTextContains('localgov_documents');

    // Check 'Fact box' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_fact_box/fields');
    $this->assertSession()->pageTextContains('localgov_above_text');
    $this->assertSession()->pageTextContains('localgov_background');
    $this->assertSession()->pageTextContains('localgov_below_text');
    $this->assertSession()->pageTextContains('localgov_fact');

    // Check 'Key contacts' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_key_contacts/fields');
    $this->assertSession()->pageTextContains('localgov_title');
    $this->assertSession()->pageTextContains('localgov_heading_level');
    $this->assertSession()->pageTextContains('localgov_paragraphs');

    // Check 'Key contact item' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_key_contact_item/fields');
    $this->assertSession()->pageTextContains('localgov_title');
    $this->assertSession()->pageTextContains('localgov_heading_level');
    $this->assertSession()->pageTextContains('localgov_colour_theme');
    $this->assertSession()->pageTextContains('localgov_image');
    $this->assertSession()->pageTextContains('localgov_link');
    $this->assertSession()->pageTextContains('localgov_contact_email');

    // Check 'Link and summary' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_link_and_summary/fields');
    $this->assertSession()->pageTextContains('localgov_summary');
    $this->assertSession()->pageTextContains('localgov_link');

    // Check 'Quote' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_quote/fields');
    $this->assertSession()->pageTextContains('localgov_author');
    $this->assertSession()->pageTextContains('localgov_text_plain');

    // Check 'Table' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_table/fields');
    $this->assertSession()->pageTextContains('localgov_table');
    $this->assertSession()->pageTextContains('localgov_table_theme');

    // Check 'Video' fields.
    $this->drupalGet('/admin/structure/paragraphs_type/localgov_video/fields');
    $this->assertSession()->pageTextContains('localgov_video');
  }

}
