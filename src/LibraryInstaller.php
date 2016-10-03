<?php

/*
 * This file is part of the Speedwork package.
 *
 * (c) Sankar <sankar.suda@gmail.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code
 */

namespace Speedwork\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller as BaseLibraryInstaller;
use Composer\IO\IOInterface;
use Composer\Package\PackageInterface;
use Composer\Repository\InstalledRepositoryInterface;

class LibraryInstaller extends BaseLibraryInstaller
{
    protected $filesystem;

    /**
     * {@inheritdoc}
     */
    public function __construct(IOInterface $io, Composer $composer, $type = 'library')
    {
        parent::__construct($io, $composer, $type);

        $this->filesystem = new Filesystem();
    }

    public function getInstallPath(PackageInterface $package)
    {
        $type = $package->getType();

        if ($this->supports($type)) {
            $directory = $this->getDirectory($type, $package);
            if (!empty($directory)) {
                return $directory;
            }
        }

        $names = $package->getNames();

        if ($this->composer->getPackage()) {
            $extra = $this->composer->getPackage()->getExtra();
            if (!empty($extra['installer-paths'])) {
                foreach ($extra['installer-paths'] as $path => $packageNames) {
                    foreach ($packageNames as $packageName) {
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
     * {@inheritdoc}
     */
    public function supports($packageType)
    {
        $allow = [
            'speedwork-component',
            'speedwork-module',
            'speedwork-widget',
            'speedwork-theme',
            'speedwork-template',
            'speedwork-package',
        ];

        // Explicitly state support of "component" packages.
        return in_array($packageType, $allow);
    }

    /**
     * Retrieves the Installer's provided directory.
     */
    public function getDirectory($type, PackageInterface $package)
    {
        $type = str_replace('speedwork-', '', $type);
        if ($type == 'package') {
            return null;
        }

        // Parse the pretty name for the vendor and package name.
        $name = $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
            unset($vendor);
        }

        // Allow the package to define its own name.
        $extra = $package->getExtra();
        if (isset($extra['name'])) {
            $name = $extra['name'];
        }

        if ($type == 'template' || $type == 'theme') {
            $directory = 'public'.DIRECTORY_SEPARATOR.'themes';
        } else {
            $directory = 'app'.DIRECTORY_SEPARATOR.ucfirst($type).'s';
            $name      = ucfirst($name);
        }

        if (strpos($name, '-') !== false) {
            list($type, $name) = explode('-', $name);
        }

        if (isset($extra['directory'])) {
            $directory = $extra['directory'];
        } else {
            $config    = $this->composer->getConfig();
            $directory = $config->has('directory') ? $config->get('directory') : $directory;
        }

        $basePath = ($this->vendorDir ? $this->vendorDir.'/' : '');

        return $basePath.$directory.'/'.$name;
    }

    /**
     * Move the assets of a package.
     *
     * @param \Composer\Package\PackageInterface $package
     *
     * @return bool
     */
    protected function installAssets(PackageInterface $package, $force = true, $remove = false)
    {
        $extra = $package->getExtra();

        if (!isset($extra['assets']) || !is_array($extra['assets'])) {
            return;
        }

        $assets = $extra['assets'];

        // Parse the pretty name for the vendor and package name.
        $name = $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
            unset($vendor);
        }

        if (isset($extra['name'])) {
            $name = $extra['name'];
        }

        $name = strtolower($name);
        $type = $package->getType();
        $type = str_replace('speedwork-', '', $type);

        $basePath  = ($this->vendorDir ? $this->vendorDir.'/../' : '');
        $assetsDir = realpath($basePath.'public/assets/').'/';
        $assetsDir .= isset($extra['assets-dir']) ? $extra['assets-dir'] : ':type/:name';

        $replace = [
            'type'    => $type,
            'name'    => $name,
            'package' => $name,
        ];

        foreach ($replace as $key => $value) {
            $assetsDir = str_replace(':'.$key, $value, $assetsDir);
        }

        $assetsDir .= DIRECTORY_SEPARATOR;
        $assetsDir = strtolower($assetsDir);

        $path = $this->getPackageBasePath($package).DIRECTORY_SEPARATOR;

        foreach ($assets as $from => $to) {
            $to   = $assetsDir.$to;
            $from = $path.$from;

            if ($remove) {
                $this->removeFiles($from, $to);
            } else {
                if ($force) {
                    $this->removeFiles($from, $to);
                }
                $this->copyFiles($from, $to);
            }
        }
    }

    protected function copyFiles($from, $to)
    {
        if (is_file($from)) {
            $this->filesystem->copy($from, $to);
        } elseif (is_dir($from)) {
            $this->filesystem->copy($from, $to);
        } elseif ($files = glob($from)) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $this->copyFiles($file, $to.basename($file));
                } else {
                    $this->copyFiles($file, $to.basename($file));
                }
            }
        }
    }

    protected function removeFiles($from, $to)
    {
        if (is_file($from)) {
            if (file_exists($to)) {
                $this->filesystem->remove($to);
            }
        } elseif (is_dir($from)) {
            if (is_dir($to)) {
                $this->filesystem->remove($to);
            }
        } elseif ($files = glob($from)) {
            foreach ($files as $file) {
                if (is_dir($file)) {
                    $this->removeFiles($file, $to.basename($file));
                } else {
                    $this->removeFiles($file, $to.basename($file));
                }
            }
        }
    }

    /**
     * {@inheritdoc}
     */
    public function install(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        parent::install($repo, $package);
        $this->installAssets($package);
    }

    /**
     * {@inheritdoc}
     */
    public function update(InstalledRepositoryInterface $repo, PackageInterface $initial, PackageInterface $target)
    {
        parent::update($repo, $initial, $target);
        $this->installAssets($target);
    }

    /**
     * {@inheritdoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->installAssets($package, true, true);
        parent::uninstall($repo, $package);
    }
}
