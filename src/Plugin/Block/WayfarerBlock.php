<?php

namespace Drupal\wayfarer\Plugin\Block;

use Drupal\Core\Block\BlockBase;
use Drupal\Core\Block\BlockPluginInterface;
use Drupal\Core\Form\FormStateInterface;
use Drupal\Core\Link;
use Drupal\Core\Url;

/**
 * Provides a 'Travel Alert' Block.
 *
 * @Block(
 *   id = "wayfarer_block",
 *   admin_label = @Translation("Travel Alert"),
 * )
 */
class WayfarerBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // Get the travel destination from Drupal config and set a reasonable default.
    if (!empty($config['wayfarer_place'])) {
      $place = $config['wayfarer_place'];
    }
    else {
      $place = $this->t('my home office.');
    }

    // Get the arrival date from Drupal config and set a reasonable default.
    if (!empty($config['wayfarer_date1'])) {
      $date1 = $config['wayfarer_date1'];
    }
    else {
      $date1 = $this->t('now');
    }

    // Get the departure date from Drupal config and set a reasonable default.
    if (!empty($config['wayfarer_date2'])) {
      $date2 = $config['wayfarer_date2'];
    }
    else {
      $date2 = $this->t('the foreseeable future');
    }
    // @todo Display dates in more human-readable form than YYYY-MM-DD.  

    // Get schedule link from Drupal config and set a reasonable default.
    $external_link = $this->t('Contact me');
    if (!empty($config['wayfarer_schedule_link'])) {
      $schedule_link = $config['wayfarer_schedule_link'];
      $url = Url::fromUri($schedule_link);
      $external_link = $this->t('Click ') . \Drupal\Core\Link::fromTextAndUrl('here', $url)->toString();
     }

    // Build block display.
    $build = array(
      '#markup' => $this->t(
        'I\'m going to be in @place from @date1 until @date2. @external_link to schedule a face-to-face meeting while I\'m in town.',
        array (
          '@place' => $place,
          '@date1' => $date1,
          '@date2' => $date2,
          '@external_link' => $external_link,
        )
      )
    );

    // @todo Add link to contact form for users without a scheduling system.
   
    return $build;
  }

  /**
   * {@inheritdoc}
   */
  public function blockForm($form, FormStateInterface $form_state) {
    $form = parent::blockForm($form, $form_state);

    $config = $this->getConfiguration();

    // Create location form field.
    $form['wayfarer_block_place'] = array (
      '#type' => 'textfield',
      '#title' => $this->t('Where'),
      '#default_value' => isset($config['wayfarer_place']) ? $config['wayfarer_place'] : '',
      '#description' => $this->t('Where are you traveling?'),
      '#required' => TRUE,
    );

    // Create arrival date form field.
    $form['wayfarer_block_date1'] = array (
      '#type' => 'date',
      '#title' => $this->t('Arrival date'),
      '#default_value' => isset($config['wayfarer_date1']) ? $config['wayfarer_date1'] : '',
      '#description' => 'When are you arriving?',
      '#required' => TRUE,

    );

    // Create departure date form field.
    $form['wayfarer_block_date2'] = array (
      '#type' => 'date',
      '#title' => $this->t('Departure date'),
      '#default_value' => isset($config['wayfarer_date2']) ? $config['wayfarer_date2'] : '',
      '#description' => $this->t('When are you leaving?'),
      '#required' => TRUE,
    );
    // @todo Expire/hide the block when date2 has passed.

    $form['wayfarer_block_schedule_link'] = array (
      '#type' => 'textfield',
      '#title' => $this->t('Schedule link'),
      '#default_value' => isset($config['wayfarer_schedule_link']) ? $config['wayfarer_schedule_link'] : '',
      '#description' => $this->t('Put in a link to the scheduling service you use.'),
    );    
    return $form;
  }

 /**
   * {@inheritdoc}
   */
  public function blockValidate($form, FormStateInterface $form_state) {
    // Check if date2 is after date1.
    $date1 = $form_state->getValue('wayfarer_block_date1');
    $date2 = $form_state->getValue('wayfarer_block_date2');
    if ($date2 < $date1) {
      $form_state->setErrorByName('date', $this->t('The second date must be later than the first, you time traveler.'));
    }
  }

 /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('wayfarer_place', $form_state->getValue('wayfarer_block_place'));
    $this->setConfigurationValue('wayfarer_date1', $form_state->getValue('wayfarer_block_date1'));
    $this->setConfigurationValue('wayfarer_date2', $form_state->getValue('wayfarer_block_date2'));
    $this->setConfigurationValue('wayfarer_schedule_link', $form_state->getValue('wayfarer_block_schedule_link'));
  }
}
