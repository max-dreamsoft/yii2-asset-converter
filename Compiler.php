<?php

namespace dreamsoft\assetConverter;

use Yii;
use Leafo\ScssPhp\Compiler as ParentCompiler;

/**
 * Class Scss
 * @package dreamsoft\assetConverter
 * @author Andrey Izman <izmanw@gmail.com>
 */


/**
 * The scss compiler and parser.
 *
 * Converting SCSS to CSS is a three stage process. The incoming file is parsed
 * by `Parser` into a syntax tree, then it is compiled into another tree
 * representing the CSS structure by `Compiler`. The CSS tree is fed into a
 * formatter, like `Formatter` which then outputs CSS as a string.
 *
 * During the first compile, all values are *reduced*, which means that their
 * types are brought to the lowest form before being dump as strings. This
 * handles math equations, variable dereferences, and the like.
 *
 * The `compile` function of `Compiler` is the entry point.
 *
 * In summary:
 *
 * The `Compiler` class creates an instance of the parser, feeds it SCSS code,
 * then transforms the resulting tree to a CSS tree. This class also holds the
 * evaluation context, such as all available mixins and variables at any given
 * time.
 *
 * The `Parser` class is only concerned with parsing its input.
 *
 * The `Formatter` takes a CSS tree, and dumps it to a formatted string,
 * handling things like indentation.
 */

/**
 * SCSS compiler
 *
 * @author Leaf Corcoran <leafot@gmail.com>
 */
class Compiler extends ParentCompiler
{
    /**
     * @inheritdoc
     */
    public function findImport($url)
    {

        $urls = [];

        // for "normal" scss imports (ignore vanilla css and external requests)
        if (!preg_match('/\.css$|^https?:\/\//', $url)) {
            // try both normal and the _partial filename
            $urls = [$url, preg_replace('/[^@\/]+$/', '_\0', $url)];
            foreach ($urls as $key=>$val) {
                if (preg_match('#(@[^/]+)(?:/|$)#', $val, $matches)) {
                    if(!empty($matches[1])){
                        unset($matches[0]);
                        foreach ($matches as $match) {
                            $this->addImportPath(Yii::getAlias($match));
                            $urls[$key] = trim(str_replace($match,'',$val),'/');
                        }
                    }
                }
            }
        }

        foreach ($this->importPaths as $dir) {
            if (is_string($dir)) {
                // check urls for normal import paths
                foreach ($urls as $full) {
                    $full = $dir
                        . (!empty($dir) && substr($dir, -1) !== '/' ? '/' : '')
                        . $full;

                    if ($this->fileExists($file = $full . '.scss') ||
                        $this->fileExists($file = $full)
                    ) {
                        return $file;
                    }
                }
            } elseif (is_callable($dir)) {
                // check custom callback for import path
                $file = call_user_func($dir, $url);

                if ($file !== null) {
                    return $file;
                }
            }
        }

        return null;
    }
}