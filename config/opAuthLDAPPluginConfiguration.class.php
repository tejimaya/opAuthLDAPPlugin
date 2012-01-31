<?php

/**
 * opAuthLDAPPluginConfiguration
 *
 * @package    opAuthLDAPPlugin
 * @subpackage config
 * @author     Shouta Kashiwagi <kashiwagi@tejimaya.com>
 */
class opAuthLDAPPluginConfiguration extends sfPluginConfiguration
{
  public function initialize()
  {
    sfToolkit::addIncludePath(dirname(__FILE__).'/../lib/vendor');
  }
}
?>
