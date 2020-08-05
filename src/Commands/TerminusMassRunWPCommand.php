<?php

namespace Pantheon\TerminusMassRun\Commands;

use Pantheon\Terminus\Exceptions\TerminusProcessException;
use Pantheon\Terminus\Site\SiteAwareInterface;
use Pantheon\Terminus\Commands\Remote\WPCommand;
use Pantheon\TerminusMassRun\Traits\TerminusMassRunTrait;

class TerminusMassRunWPCommand extends WPCommand implements SiteAwareInterface {

  use TerminusMassRunTrait;

  /**
   * Mass run of WP CLI commands.
   *
   * @authorize
   *
   * @command remote:mass:wp
   * @aliases mass-wp
   *
   * @param array $cmd The WP CLI command to run on sites.
   * @param array $options
   * @return string Command output
   *
   * @option env The Pantheon environments to target.
   * @option upstream UUID of a Pantheon Upstream to filter by.
   *
   * @usage terminus site:list --format=list | terminus remote:mass:wp --env=<env> -- cache flush Clear cache on all sites.
   */
  public function runCommand(array $cmd, array $options = ['env' => 'dev', 'upstream' => '', 'progress' => false]) {
    $output = '';
    $sites = $this->filterFrameworks($this->getAllSites($options['upstream']), ['wordpress', 'wordpress_network']);

    foreach ($sites as $site) {
      try {
        $output .= $this->wpCommand("{$site->getName()}.{$options['env']}", $cmd, $options);
      }
      catch (TerminusProcessException $e) {
        // If the command doesn't run, we want to skip it and continue to run
        // the rest of the scripts.
        $this->log()->error('WP CLI command for {site_name} could not be run.', [
          'site_name' => $site->getName(),
        ]);
        continue;
      }
    }

    return $output;
  }

}
