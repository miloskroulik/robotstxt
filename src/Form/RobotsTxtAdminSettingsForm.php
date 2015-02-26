<?php

/**
 * @file
 * Contains \Drupal\robotstxt\Form\RobotsTxtAdminSettingsForm.
 */

namespace Drupal\robotstxt\Form;

use Drupal\Core\Form\FormBase;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Component\Utility\String;

/**
 * Configure robotstxt settings for this site.
 */
class RobotsTxtAdminSettingsForm extends FormBase {

  /**
   * {@inheritdoc}
   */
  public function getFormId() {
    return 'robotstxt_admin_settings';
  }

  /**
   * {@inheritdoc}
   */
  protected function getEditableConfigNames() {
    return ['robotstxt.settings'];
  }

  /**
   * {@inheritdoc}
   */
  public function buildForm(array $form, FormStateInterface $form_state) {
    $config = $this->config('robotstxt.settings');

    $form['robotstxt_content'] = array(
      '#type' => 'textarea',
      '#title' => t('Contents of robots.txt'),
      '#default_value' => $config->get('content'),
      '#cols' => 60,
      '#rows' => 20,
    );

    $form['actions']['submit'] = array(
      '#type' => 'submit',
      '#value' => t('Save'),
    );

    return $form;
  }

  /**
   * {@inheritdoc}
   */
  public function submitForm(array &$form, FormStateInterface $form_state) {
    \Drupal::config('robotstxt.settings')
      ->set('content', $form_state->getValue('robotstxt_content'))
      ->save();
  }

}
