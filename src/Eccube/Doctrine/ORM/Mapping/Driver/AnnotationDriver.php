<?php

/*
 * This file is part of EC-CUBE
 *
 * Copyright(c) EC-CUBE CO.,LTD. All Rights Reserved.
 *
 * http://www.ec-cube.co.jp/
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Eccube\Doctrine\ORM\Mapping\Driver;

use Doctrine\Persistence\Mapping\MappingException;

class AnnotationDriver extends \Doctrine\ORM\Mapping\Driver\AnnotationDriver
{
    protected $trait_proxies_directory;

    public function setTraitProxiesDirectory($dir)
    {
        $this->trait_proxies_directory = $dir;
    }

    /**
     * {@inheritdoc}
     */
    public function getAllClassNames()
    {
        if ($this->classNames !== null) {
            return $this->classNames;
        }

        if ($this->paths === []) {
            throw MappingException::pathRequiredForDriver(static::class);
        }

        $classes = [];
        $includedFiles = [];

        foreach ($this->paths as $path) {
            if (!is_dir($path)) {
                throw MappingException::fileMappingDriversRequireConfiguredDirectoryPath($path);
            }

            $iterator = new \RegexIterator(
                new \RecursiveIteratorIterator(
                    new \RecursiveDirectoryIterator($path, \FilesystemIterator::SKIP_DOTS),
                    \RecursiveIteratorIterator::LEAVES_ONLY
                ),
                '/^.+'.preg_quote($this->fileExtension).'$/i',
                \RecursiveRegexIterator::GET_MATCH
            );

            foreach ($iterator as $file) {
                $sourceFile = $file[0];

                if (!preg_match('(^phar:)i', $sourceFile)) {
                    $sourceFile = realpath($sourceFile);
                }

                foreach ($this->excludePaths as $excludePath) {
                    $exclude = str_replace('\\', '/', realpath($excludePath));
                    $current = str_replace('\\', '/', $sourceFile);

                    if (strpos($current, $exclude) !== false) {
                        continue 2;
                    }
                }
                $projectDir = realpath(__DIR__.'/../../../../../../');
                if ('\\' === DIRECTORY_SEPARATOR) {
                    $path = str_replace('\\', '/', $path);
                    $this->trait_proxies_directory = str_replace('\\', '/', $this->trait_proxies_directory);
                    $sourceFile = str_replace('\\', '/', $sourceFile);
                    $projectDir = str_replace('\\', '/', $projectDir);
                }
                // Replace /path/to/ec-cube to proxies path
                $proxyFile = str_replace($projectDir, $this->trait_proxies_directory, $path).'/'.basename($sourceFile);
                if (file_exists($proxyFile)) {
                    // 通常はプロキシを優先してロードする（プラグインの EntityExtension を含んだ拡張クラス）。
                    // ただし src またはプロキシのいずれかから同名クラスが既にロード済みの場合、
                    // どちらを require しても二重定義で Fatal error になる。
                    // ロード済みなら何も require せず、ReflectionClass で実際のロード元を追跡に使う。
                    $fqcn = $this->resolveFqcnFromEntitySourceFile($sourceFile);
                    if ($fqcn !== null && class_exists($fqcn, false)) {
                        $sourceFile = (new \ReflectionClass($fqcn))->getFileName();
                    } else {
                        require_once $proxyFile;
                        $sourceFile = $proxyFile;
                    }
                } else {
                    require_once $sourceFile;
                }

                $includedFiles[] = realpath($sourceFile);
            }
        }

        $declared = get_declared_classes();

        foreach ($declared as $className) {
            $rc = new \ReflectionClass($className);
            $sourceFile = $rc->getFileName();
            if (in_array($sourceFile, $includedFiles) && !$this->isTransient($className)) {
                $classes[] = $className;
            }
        }

        $this->classNames = $classes;

        return $classes;
    }

    /**
     * エンティティ PHP ファイルパスから FQCN を推定する（プロキシ二重 require 判定用）.
     */
    private function resolveFqcnFromEntitySourceFile(string $sourceFile): ?string
    {
        $normalized = str_replace('\\', '/', $sourceFile);

        if (preg_match('#/src/Eccube/Entity/(.+)\.php$#', $normalized, $m)) {
            return 'Eccube\\Entity\\'.str_replace('/', '\\', $m[1]);
        }
        if (preg_match('#/app/Customize/Entity/(.+)\.php$#', $normalized, $m)) {
            return 'Customize\\Entity\\'.str_replace('/', '\\', $m[1]);
        }
        if (preg_match('#/app/Plugin/([^/]+)/Entity/(.+)\.php$#', $normalized, $m)) {
            return 'Plugin\\'.$m[1].'\\Entity\\'.str_replace('/', '\\', $m[2]);
        }

        return null;
    }
}
