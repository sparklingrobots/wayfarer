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
class wayfarerBlock extends BlockBase implements BlockPluginInterface {

  /**
   * {@inheritdoc}
   */
  public function build() {
    $config = $this->getConfiguration();

    // Get the travel destination from Drupal config and set a reasonable default.
    if (!empty($config['place'])) {
      $place = $config['place'];
    }
    else {
      $place = $this->t('my home office.');
    }

    // Get the arrival date from Drupal config and set a reasonable default.
    if (!empty($config['date1'])) {
      $date1 = $config['date1'];
    }
    else {
      $date1 = $this->t('now');
    }

    // Get the departure date from Drupal config and set a reasonable default.
    if (!empty($config['date2'])) {
      $date2 = $config['date2'];
    }
    else {
      $date2 = $this->t('the foreseeable future');
    }
    // @todo Display dates in more human-readable form than YYYY-MM-DD.  

    // Get schedule link from Drupal config and set a reasonable default.
    if (!empty($config['schedule_link'])) {
      $schedule_link = $config['schedule_link'];
      $url = Url::fromUri($schedule_link);
      $external_link = \Drupal\Core\Link::fromTextAndUrl('here', $url)->toString();
     
      // Build block display when there is a schedule link available.     
      $build = array(
        '#markup' => $this->t(
          'I\'m going to be in @place from @date1 until @date2. Click @external_link to schedule a face-to-face meeting while I\'m in town.', 
          array (
            '@place' => $place,
            '@date1' => $date1,
            '@date2' => $date2,
            '@external_link' => $external_link, 
          )
        )
      );
    }
    
    // Build block display when there is no schedule link available. 
    else {
      $build = array(
        '#markup' => $this->t(
          'I\'m going to be in @place from @date1 until @date2. Contact me to schedule a face-to-face meeting while I\'m in town.', 
          array (
            '@place' => $place,
            '@date1' => $date1,
            '@date2' =>$date2,
          )
        )
      );
    }   
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
      '#default_value' => isset($config['place']) ? $config['place'] : '',
      '#description' => $this->t('Where are you traveling?'),
    );

    // Create arrival date form field.
    $form['wayfarer_block_date1'] = array (
      '#type' => 'date',
      '#title' => $this->t('Arrival date'),
      '#default_value' => isset($config['date1']) ? $config['date1'] : '',
      '#description' => 'When are you arriving?',
    );

    // Create departure date form field.
    $form['wayfarer_block_date2'] = array (
      '#type' => 'date',
      '#title' => $this->t('Departure date'),
      '#default_value' => isset($config['date2']) ? $config['date2'] : '',
      '#description' => 'When are you leaving?',
    );
    // @todo Validate dates to ensure they are in the future, and that date2 is after date1.
    // @todo Expire/hide the block when date2 has passed.

    $form['wayfarer_block_schedule_link'] = array (
      '#type' => 'textfield',
      '#title' => $this->t('Schedule link'),
      '#default_value' => isset($config['schedule_link']) ? $config['schedule_link'] : '',
      '#description' => $this->t('Put in a link to the scheduling service you use.'),
    );    
    return $form;
  }

 /**
   * {@inheritdoc}
   */
  public function blockSubmit($form, FormStateInterface $form_state) {
    $this->setConfigurationValue('place', $form_state->getValue('wayfarer_block_place'));
    $this->setConfigurationValue('date1', $form_state->getValue('wayfarer_block_date1'));
    $this->setConfigurationValue('date2', $form_state->getValue('wayfarer_block_date2'));
    $this->setConfigurationValue('schedule_link', $form_state->getValue('wayfarer_block_schedule_link'));
  }
}
