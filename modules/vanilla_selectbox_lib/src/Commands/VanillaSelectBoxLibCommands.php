<?php

namespace Drupal\vanilla_selectbox_lib\Commands;

use Drush\Commands\DrushCommands;
use Drush\Drush;
use Symfony\Component\Filesystem\Filesystem;
use Psr\Log\LogLevel;

/**
 * The Vanilla SelectBox plugin URI.
 */
define('VANILLA_SELECTBOX_DOWNLOAD_URI', 'https://github.com/PhilippeMarcMeyer/vanillaSelectBox/archive/refs/heads/master.zip');

/**
 * A Drush commandfile.
 *
 * In addition to this file, you need a drush.services.yml
 * in root of your module, and a composer.json file that provides the name
 * of the services file to use.
 *
 * See these files for an example of injecting Drupal services:
 *   - http://cgit.drupalcode.org/devel/tree/src/Commands/DevelCommands.php
 *   - http://cgit.drupalcode.org/devel/tree/drush.services.yml
 */
class VanillaSelectBoxLibCommands extends DrushCommands {

  /**
   * Download and install the Vanilla SelectBox plugin.
   *
   * @param string $path
   *   Optional. A path where to install the Vanilla SelectBox plugin. If omitted Drush
   *   will use the default location.
   *
   * @command vanilla_selectbox:plugin
   * @aliases vanilla_selectbox_plugin,vanilla-selectbox-plugin
   *
   * @throws \Exception
   */
  public function plugin($path = '') {
    if (empty($path)) {
      $path = 'libraries';
    }

    // Create the path if it does not exist.
    if (!is_dir($path)) {
      drush_op('mkdir', $path);
      $this->drushLog(dt('Directory @path was created', ['@path' => $path]), 'notice');
    }

    // Set the directory to the download location.
    $olddir = getcwd();
    chdir($path);

    // Download the zip archive.
    if ($filepath = $this->drushDownloadFile(CHOSEN_DOWNLOAD_URI)) {
      $filename = basename($filepath);
      $dirname = basename($filepath, '.zip');

      // Remove any existing Vanilla SelectBox plugin directory.
      if (is_dir($dirname) || is_dir('vanilla_selectbox')) {
        Filesystem::remove($dirname, TRUE);
        Filesystem::remove('vanilla_selectbox', TRUE);
        $this->drushLog(dt('A existing Vanilla SelectBox plugin was deleted from @path', ['@path' => $path]), 'notice');
      }

      // Decompress the zip archive.
      $this->drushTarballExtract($filename, $dirname);

      // Change the directory name to "vanilla_selectbox" if needed.
      if ('vanilla_selectbox' !== $dirname) {
        $this->drushMoveDir($dirname, 'vanilla_selectbox');
        $dirname = 'vanilla_selectbox';
      }

      unlink($filename);
    }

    if (is_dir($dirname)) {
      $this->drushLog(dt('Vanilla SelectBox plugin has been installed in @path', ['@path' => $path]), 'success');
    }
    else {
      $this->drushLog(dt('Drush was unable to install the Vanilla SelectBox plugin to @path', ['@path' => $path]), 'error');
    }

    // Set working directory back to the previous working directory.
    chdir($olddir);
  }

  /**
   * Logs with an arbitrary level.
   *
   * @param string $message
   *   The log message.
   * @param mixed $type
   *   The log type.
   */
  public function drushLog($message, $type = LogLevel::INFO) {
    $this->logger()->log($type, $message);
  }

  /**
   * @param string $url
   *   The download url.
   * @param mixed $destination
   *   The destination path.
   * @return bool|string
   *   The destination file.
   * @throws \Exception
   */
  public function drushDownloadFile($url, $destination = FALSE) {
    // Generate destination if omitted.
    if (!$destination) {
      $file = basename(current(explode('?', $url, 2)));
      $destination = getcwd() . '/' . basename($file);
    }

    // Copied from: \Drush\Commands\SyncViaHttpCommands::downloadFile.
    static $use_wget;
    if ($use_wget === NULL) {
      $process = Drush::process(['which', 'wget']);
      $process->run();
      $use_wget = $process->isSuccessful();
    }

    $destination_tmp = drush_tempnam('download_file');
    if ($use_wget) {
      $args = ['wget', '-q', '--timeout=30', '-O', $destination_tmp, $url];
    }
    else {
      $args = ['curl', '-s', '-L', '--connect-timeout', '30', '-o', $destination_tmp, $url];
    }
    $process = Drush::process($args);
    $process->mustRun();

    if (!drush_file_not_empty($destination_tmp) && $file = @file_get_contents($url)) {
      @file_put_contents($destination_tmp, $file);
    }
    if (!drush_file_not_empty($destination_tmp)) {
      // Download failed.
      throw new \Exception(dt("The URL !url could not be downloaded.", ['!url' => $url]));
    }
    if ($destination) {
      $fs = new Filesystem();
      $fs->rename($destination_tmp, $destination, TRUE);
      return $destination;
    }
    return $destination_tmp;
  }

  /**
   * @param string $src
   *   The origin filename or directory.
   * @param string $dest
   *   The new filename or directory.
   * @return bool
   */
  public function drushMoveDir($src, $dest) {
    $fs = new Filesystem();
    $fs->rename($src, $dest, TRUE);
    return TRUE;
  }

  /**
   * Create a directory.
   *
   * @param string $path
   *   The make directory path.
   *
   * @return bool
   */
  public function drushMkdir($path) {
    $fs = new Filesystem();
    $fs->mkdir($path);
    return TRUE;
  }

  /**
   * Extracts a tarball.
   *
   * @param string $path
   *   The filename or directory.
   * @param bool $destination
   *   The destination path.
   *
   * @return mixed
   *
   * @throws \Exception
   */
  public function drushTarballExtract($path, $destination = FALSE) {
    $this->drushMkdir($destination);
    $cwd = getcwd();
    if (preg_match('/\.tgz$/', $path)) {
      drush_op('chdir', dirname($path));
      $process = Drush::process(['tar', '-xvzf', $path, '-C', $destination]);
      $process->run();
      $return = $process->isSuccessful();
      drush_op('chdir', $cwd);

      if (!$return) {
        throw new \Exception(dt('Unable to extract !filename.' . PHP_EOL . implode(PHP_EOL, $process->getOutput()), ['!filename' => $path]));
      }
    }
    else {
      drush_op('chdir', dirname($path));
      $process = Drush::process(['unzip', $path, '-d', $destination]);
      $process->run();
      $return = $process->isSuccessful();
      drush_op('chdir', $cwd);

      if (!$return) {
        throw new \Exception(dt('Unable to extract !filename.' . PHP_EOL . implode(PHP_EOL, $process->getOutput()), ['!filename' => $path]));
      }
    }

    return $return;
  }

}
