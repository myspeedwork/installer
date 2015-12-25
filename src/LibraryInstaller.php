<?php

namespace Speedwork\Installer;

use Composer\Composer;
use Composer\Installer\LibraryInstaller as BaseLibraryInstaller;
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
            return $this->getDirectory($type, $package);
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
            'speedwork-helper',
            'speedwork-theme',
            'speedwork-template',
            'speedwork-framework',
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
        $extra = $package->getExtra();
        if (isset($extra['name'])) {
            $name = $extra['name'];
        }

        $type = ltrim($type, 'speedwork-');

        if ($type == 'template' || $type == 'theme') {
            $directory = 'public/templates';
        } else {
            $directory = 'system/'.$type.'s';
        }

        if ($type == 'framework') {
            $directory = 'speedwork';
        }

        if ($extra['directory']) {
            $directory = $extra['directory'];
        } else {
            $config    = $this->composer->getConfig();
            $directory = $config->has('directory') ? $config->get('directory') : $directory;
        }

        return $directory.DIRECTORY_SEPARATOR.$name;
    }

    /**
     * Move the assets of a package.
     *
     * @param \Composer\Package\PackageInterface $package
     *
     * @return bool
     */
    protected function installAssets(PackageInterface $package, $remove = false)
    {
        $extra  = $package->getExtra();
        $assets = $extra['assets'];

        if (empty($assets)) {
            return;
        }

        // Parse the pretty name for the vendor and package name.
        $name = $prettyName = $package->getPrettyName();
        if (strpos($prettyName, '/') !== false) {
            list($vendor, $name) = explode('/', $prettyName);
        }

        if (isset($extra['name'])) {
            $name = $extra['name'];
        }

        $type = $package->getType();
        $type = ltrim($type, 'speedwork-');

        $assetsDir = $extra['assets-dir'] ?: 'public/assets/:type/:name';

        $replace = [
            'type'    => $type.'s',
            'name'    => $name,
            'package' => $name,
        ];

        foreach ($replace as $key => $value) {
            $assetsDir = str_replace(':'.$key, $value, $assetsDir);
        }

        $path = $this->getPackageBasePath($package);

        if ($remove) {
            foreach ($assets as $asset) {
                $target = rtrim($assetsDir.$asset['target'], '/');

                if (file_exists($from)) {
                    $this->filesystem->remove($target);
                }
            }

            return true;
        }

        foreach ($assets as $asset) {
            $from   = $path.'/'.$asset['name'];
            $target = rtrim($assetsDir.$asset['target'], '/');

            if (file_exists($from)) {
                $this->filesystem->copy($from, $target);
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
        $this->installAssets($initial, true);
        parent::update($repo, $initial, $target);
        $this->installAssets($target);
    }
    /**
     * {@inheritdoc}
     */
    public function uninstall(InstalledRepositoryInterface $repo, PackageInterface $package)
    {
        $this->installAssets($package, true);
        parent::uninstall($repo, $package);
    }
}
