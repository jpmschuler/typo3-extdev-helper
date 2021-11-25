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
    echo " => adding overrides to package.json...";
    mergeJson('package');
    echo "done!" . PHP_EOL;
    echo " => removing old git cached files..." . PHP_EOL;
    exec('git rm --cached .php-cs');
    exec('git rm --cached .php-cs.xml');
    exec('git rm --cached .phpstan.constants.php');
    exec('git rm --cached .php-cs-fixer.php');
    exec('git rm --cached .gitlab-ci-codequality.yml');
    exec('git rm --cached phpstan.neon');
    exec('git rm --cached rector.php');
    exec('git rm --cached typoscript-lint.yml');
    echo "done!" . PHP_EOL;
    echo " => running composer up..." . PHP_EOL;
    exec('composer up -W && composer normalize && composer config');
    echo "done!" . PHP_EOL;

    echo " => setting default QA values..." . PHP_EOL;
    exec('composer config extra.phpstan.level 2> /dev/null || composer config extra.phpstan.level 5');
    exec('composer config extra.rector.typo3version 2> /dev/null || composer config extra.rector.typo3version 11');
    echo "done!" . PHP_EOL;

    echo " => running npm up...";
    exec('npm up -W');
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

})();
