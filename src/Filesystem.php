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

use Composer\Util\Filesystem as OriginalFilesystem;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;

/**
 * This class just completes the default `Composer\Util\Filesystem` with a `copy` method.
 *
 * @author  piwi <me@e-piwi.fr>
 */
class Filesystem extends OriginalFilesystem
{
    /**
     * Exact same code as `copyThenRemove()` method but without removing.
     *
     * @see \Composer\Util\Filesystem::copyThenRemove()
     */
    public function copy($source, $target)
    {
        if (!is_dir($source)) {
            $this->ensureDirectoryExists(dirname($target));
            copy($source, $target);

            return;
        }

        $it = new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS);
        $ri = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::SELF_FIRST);
        $this->ensureDirectoryExists($target);

        foreach ($ri as $file) {
            $targetPath = $target.DIRECTORY_SEPARATOR.$ri->getSubPathName();
            if ($file->isDir()) {
                $this->ensureDirectoryExists($targetPath);
            } else {
                copy($file->getPathname(), $targetPath);
            }
        }
    }
}
