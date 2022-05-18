<?php

/**
 * @file
 * Drush integration for vanilla_selectbox.
 */

use Symfony\Component\Filesystem\Filesystem;

/**
 * The Vanilla SelectBox plugin URI.
 */
define('VANILLA_SELECTBOX_DOWNLOAD_URI', 'https://github.com/PhilippeMarcMeyer/vanillaSelectBox/archive/refs/heads/master.zip');

/**
 * Implements hook_drush_command().
 */
function vanilla_selectbox_lib_drush_command() {
  $items = [];

  // The key in the $items array is the name of the command.
  // No bootstrap.
  $items['vanilla-selectbox-plugin'] = [
    'callback' => 'drush_vanilla_selectbox_lib_plugin',
    'description' => dt('Download and install the Vanilla SelectBox plugin.'),
    'bootstrap' => DRUSH_BOOTSTRAP_NONE,
    'arguments' => [
      'path' => dt('Optional. A path where to install the Vanilla SelectBox plugin. If omitted Drush will use the default location.'),
    ],
    'aliases' => ['vanilla-selectbox-plugin'],
  ];

  return $items;
}

/**
 * Implements hook_drush_help().
 */
function vanilla_selectbox_lib_drush_help($section) {
  switch ($section) {
    case 'drush:vanilla-selectbox-plugin':
      return dt('Download and install the Vanilla SelectBox plugin from https://github.com/PhilippeMarcMeyer/vanillaSelectBox, default location is /libraries.');
  }
}

/**
 * Command to download the Vanilla SelectBox plugin.
 */
function drush_vanilla_selectbox_lib_plugin() {
  $args = func_get_args();
  if (!empty($args[0])) {
    $path = $args[0];
  }
  else {
    $path = 'libraries';
  }

  // Create the path if it does not exist.
  if (!is_dir($path)) {
    drush_op('mkdir', $path);
    \Drupal::logger(dt('Directory @path was created', ['@path' => $path]))->notice('notice');

  }

  // Set the directory to the download location.
  $olddir = getcwd();
  chdir($path);

  // Download the zip archive.
  if ($filepath = drush_download_file(VANILLA_SELECTBOX_DOWNLOAD_URI)) {
    $filename = basename($filepath);
    $dirname = basename($filepath, '.zip');

    // Remove any existing Vanilla SelectBox plugin directory.
    if (is_dir($dirname) || is_dir('vanilla_selectbox')) {
      Filesystem::remove($dirname, TRUE);
      Filesystem::remove('vanilla_selectbox', TRUE);
      \Drupal::logger(dt('A existing Vanilla SelectBox plugin was deleted from @path', ['@path' => $path]))->notice('notice');
    }

    // Decompress the zip archive.
    drush_tarball_extract($filename, $dirname);

    // Change the directory name to "vanilla_selectbox" if needed.
    if ($dirname != 'vanilla_selectbox') {
      drush_move_dir($dirname, 'vanilla_selectbox', TRUE);
      $dirname = 'vanilla_selectbox';
    }
  }

  if (is_dir($dirname)) {
    \Drupal::logger(dt('Vanilla SelectBox plugin has been installed in @path', ['@path' => $path]))->info('success');
  }
  else {
    \Drupal::logger(dt('Drush was unable to install the Vanilla SelectBox plugin to @path', ['@path' => $path]))->error('error');
  }

  // Set working directory back to the previous working directory.
  chdir($olddir);
}
