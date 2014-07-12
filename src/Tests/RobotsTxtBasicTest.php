<?php

/**
 * @file
 * Definition of Drupal\robotstxt\Tests\RobotsTxtBasicTest.
 */

namespace Drupal\robotstxt\Tests;

use Drupal\simpletest\WebTestBase;

/**
 * Tests basic functionality of configured robots.txt files.
 *
 * @group Robots.txt
 */
class RobotsTxtBasicTest extends WebTestBase {

  /**
   * Modules to enable.
   *
   * @var array
   */
  public static $modules = array('robotstxt', 'node');

  /**
   * Checks that an administrator can view the configuration page.
   */
  public function testRobotsTxtAdminAccess() {
    // Create user.
    $admin_user = $this->drupalCreateUser(array('administer robots.txt'));
    $this->drupalLogin($admin_user);
    $this->drupalGet('admin/config/search/robotstxt');

    $this->assertFieldById('edit-robotstxt-content', NULL, 'The textarea for configuring robots.txt is shown.');

  }

  /**
   * Checks that a non-administrative user cannot use the configuration page.
   */
  public function testRobotsTxtUserNoAccess() {
    // Create user.
    $auth_user = $this->drupalCreateUser(array('access content'));
    $this->drupalLogin($auth_user);
    $this->drupalGet('admin/config/search/robotstxt');

    $this->assertNoFieldById('edit-robotstxt-content', NULL, 'The textarea for configuring robots.txt is not shown for users without appropriate permissions.');
  }

  /**
   * Test that the robots.txt path delivers content with an appropriate header.
   */
  public function testRobotsTxtPath() {
    if ($this->robotsTxtFileExists()) {
      return;
    }

    $this->drupalGet('robots.txt');
    $this->assertResponse(200, 'No local robots.txt file was detected, and an anonymous user is delivered content at the /robots.txt path.');
    $header = $this->drupalGetHeader('Content-Type');
    // Note: the header may have charset appended.
    $this->assertIdentical(strpos($header, 'text/plain'), 0, 'The robots.txt file was served with header Content-Type: text/plain');
  }

  /**
   * Checks that a configured robots.txt file is delivered as configured.
   */
  public function testRobotsTxtConfigureRobotsTxt() {
    if ($this->robotsTxtFileExists()) {
      return;
    }

    // Create user.
    $admin_user = $this->drupalCreateUser(array('administer robots.txt'));
    $this->drupalLogin($admin_user);
    $this->drupalGet('admin/config/search/robotstxt');

    $test_string = $this->randomName();
    $this->drupalPostForm('admin/config/search/robotstxt', array('robotstxt_content' => $test_string), t('Save'));

    $this->drupalLogout();
    $this->drupalGet('robots.txt');
    $this->assertResponse(200, 'No local robots.txt file was detected, and an anonymous user is delivered content at the /robots.txt path.');
    $header = $this->drupalGetHeader('Content-Type');
    // Note: the header may have charset appended.
    $this->assertIdentical(strpos($header, 'text/plain'), 0, 'The robots.txt file was served with header Content-Type: text/plain');
    $content = $this->drupalGetContent();
    $this->assertTrue($content == $test_string, sprintf('Test string [%s] is displayed in the configured robots.txt file [%s].', $test_string, $content));
  }

  /**
   * Checks whether a local robots.txt file exists.
   *
   * @return bool
   *   Returns TRUE if a local robots.txt file is found.
   */
  protected function robotsTxtFileExists() {
    $exists = file_exists(DRUPAL_ROOT . '/robots.txt');
    if ($exists) {
      $this->error(sprintf('Unable to proceed with configured robots.txt tests: A local file already exists at %s, so the menu override in this module will never run.', DRUPAL_ROOT . '/robots.txt'));
    }
    return $exists;
  }

}
