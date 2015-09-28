<?php

namespace Speedwork\Installer;

use Composer\Package\PackageInterface;
use Composer\Installer\PearInstaller as BasePearInstaller;

class PearInstaller extends BasePearInstaller
{
  public function getInstallPath(PackageInterface $package)
  {
    
    $type = $package->getType();

    if ($this->supports($type)) {
        return $this->getDirectory($type, $package);
    }

    $names = $package->getNames();

    if ($this->composer->getPackage()) 
    {
      $extra = $this->composer->getPackage()->getExtra();
      if(!empty($extra['installer-paths']))
      {
        foreach($extra['installer-paths'] as $path => $packageNames)
        {
          foreach($packageNames as $packageName)
          {
            if (in_array(strtolower($packageName), $names)) {
              return $path;
            }
          }
        }
      }
    }

    /*
     * In case, the user didn't provide a custom path
     * use the default one, by calling the parent::getInstallPath function
     */
    return parent::getInstallPath($package);
  }

     /**
     * {@inheritDoc}
     */
    public function supports($packageType)
    {
        $allow = [
            'speedwork-component',
            'speedwork-module',
            'speedwork-widget',
            'speedwork-helper',
            'speedwork-theme',
            'speedwork-template',
            'speedwork-framework'
        ];

        // Explicitly state support of "component" packages.
        return in_array($packageType, $allow);
    }

    /**
     * Retrieves the Installer's provided directory.
     */
    public function getDirectory($type, PackageInterface $package)
    {
              // Parse the pretty name for the vendor and package name.
        $name = $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        }

        // Allow the package to define its own name.
        $extra =  $package->getExtra();
        $package = isset($extra['name']) ? $extra['name'] : array();
        if (isset($package['name'])) {
            $name = $package['name'];
        }

        $type = ltrim($type,'speedwork-');

        if($type == 'template' || $type == 'theme') {
            $directory = 'public/templates';
        }else{
            $directory = 'system/'.$type.'s';
        }

        if ($type == 'framework') {
          return './';
        }

        $config = $this->composer->getConfig();
        $directory = $config->has('directory') ? $config->get('directory') : $directory;

        return $directory.DIRECTORY_SEPARATOR.$name;
    }
}