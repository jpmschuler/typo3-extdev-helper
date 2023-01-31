<?php
declare(strict_types=1);
(static function () {
    function recursive_unset_value(&$array, $unwantedValue)
    {
        foreach ($array as $key => &$value) {
            if ($value === $unwantedValue) {
                unset($array[$key]);
            }
            if (is_array($value)) {
                recursive_unset_value($value, $unwantedValue);
            }
        }
    }

    function autoload()
    {
        if (file_exists($vendorAutoLoadFile = dirname(__DIR__) . '/.Build/vendor/autoload.php')) {
            // Console is root package, thus vendor folder is .Build/vendor
            $classLoader = require $vendorAutoLoadFile;
        } elseif (file_exists($vendorAutoLoadFile = dirname(dirname(dirname(__DIR__))) . '/autoload.php')) {
            // Console is a dependency, thus located in vendor/<vendorname>/<packagename>
            $classLoader = require $vendorAutoLoadFile;
        } else {
            echo 'Could not find autoload.php file. TYPO3 Console needs to be installed with composer' . PHP_EOL;
            exit(1);
        }
    }

    function mergeJson($jsonName): bool
    {
        $jsonPretty = new \Camspiers\JsonPretty\JsonPretty;
        $extensionComposerPath = './' . $jsonName . '.json';
        $extensionComposer = json_decode(file_get_contents($extensionComposerPath), true);
        if (!is_array($extensionComposer)) {
            throw new \Exception('error parsing ' . $extensionComposerPath, 1633043705);
        }
        $overrideComposerPath = __DIR__ . '/' . $jsonName . '.override.json';
        $overrideComposer = json_decode(file_get_contents($overrideComposerPath), true);
        if (!is_array($overrideComposer)) {
            throw new \Exception('error parsing ' . $overrideComposerPath, 1633043715);
        }
        $resultComposer = array_replace_recursive($extensionComposer, $overrideComposer);
        recursive_unset_value($resultComposer, null);

        $result = $jsonPretty->prettify($resultComposer);
        file_put_contents($extensionComposerPath, $result);
        return true;
    }

    autoload();
    echo "Initializing typo3-extdev-helper:" . PHP_EOL;
    echo " => adding overrides to composer.json...";
    mergeJson('composer');
    echo "done!" . PHP_EOL;
    echo " => overriding composer.json..." . PHP_EOL;
    exec('composer config --unset version');
    exec('composer config bin-dir .Build/bin');
    exec('composer config vendor-dir .Build/vendor');
    exec('composer config extra.typo3/cms.app-dir .Build');
    exec('composer config extra.typo3/cms.web-dir .Build/public');
    exec('composer config sort-packages true');
    echo "done!" . PHP_EOL;
    echo " => setting default codequality settings..." . PHP_EOL;
    exec('composer config extra.codequality.phpstan-level 2> /dev/null || composer config extra.codequality.phpstan-level 5');
    exec('composer config extra.codequality.typo3-deprecations 2> /dev/null || composer config extra.codequality.typo3-deprecations 11');
    echo "done!" . PHP_EOL;
    echo " => cleaning composer.json...";
    exec('.Build/bin/rector process composer.json || true');
    echo "done!" . PHP_EOL;

    echo " => adding default folders if not existing..." . PHP_EOL;
    if (!file_exists('./Classes')) {
        if (!mkdir('./Classes') && !is_dir('./Classes')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', './Classes'));
        }
        touch('./Classes/.keep');
    }
    if (!file_exists('./Configuration')) {
        if (!mkdir('./Configuration') && !is_dir('./Configuration')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', './Configuration'));
        }
        touch('./Configuration/.keep');
    }

    if (!file_exists('./Tests/Unit')) {
        if (!mkdir('./Tests/Unit', 0777, true) && !is_dir('./Tests/Unit')) {
            throw new \RuntimeException(sprintf('Directory "%s" was not created', './Tests/Unit'));
        }
        touch('./Tests/Unit/.keep');
    }
    echo "done!" . PHP_EOL;
    echo " => running composer up..." . PHP_EOL;
    exec('composer up -W && composer normalize && composer config');
    echo "done!" . PHP_EOL;


})();
