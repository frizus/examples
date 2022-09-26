<?php

namespace App\TargetDomains\Libraries;

use DiDom\Document;
use DiDom\Element;
use Illuminate\Support\Str;

class Clean
{
    /**
     * @param Element[] $elements
     * @param $keepNbsp
     * @param Document $container
     * @return Element[]
     */
    protected static function htmlElementHelper($elements, $keepNbsp, $container)
    {
        foreach ($elements as $element) {
            $node = $element->getNode();
            $class = get_class($node);
            if ($class == 'DOMText') {
                $cleanData = static::string($node->data, false, $keepNbsp);
                if (strlen($cleanData)) {
                    $cleanChild = $container->createTextNode($cleanData);
                    $element->replace($cleanChild, false);
                    unset($element);
                } else {
                    $element->remove();
                }
            } elseif (in_array($class, ['DOMComment', 'DOMCdataSection'])) {
                $element->remove();
            } elseif ($class == 'DOMElement') {
                $origCaseTag = $element->tag;
                $tag = Str::lower($origCaseTag);

                // @see https://www.w3schools.com/tags/ref_byfunc.asp
                if (in_array(
                    $tag,
                    [
                        'script', 'style', 'noscript', 'meta', 'link',
                        'title', 'input', 'textarea', 'button',
                        'select', 'optgroup', 'option', 'datalist',
                        'output', 'frame', 'frameset', 'noframes', 'iframe',
                        'canvas', 'svg', 'audio', 'head', 'base', 'basefont',
                        'applet', 'embed', 'object', 'param', '!doctype',
                        'hr', 'template',

                        'picture', 'img', 'source',
                    ]
                )
                ) {
                    $element->remove();
                    continue;
                }

                if (in_array($tag, ['cite', 'mark', 'time', 'label', 'a'])) {
                    $tag = $origCaseTag = 'span';
                    $span = $container->createElement($tag);
                    $span->appendChild($element->children());
                    $element->replace($span, false);
                    $element = $span;
                } elseif (
                    in_array($tag, ['header', 'footer', 'main', 'section', 'article', 'aside', 'details', 'dialog', 'summary', 'data']) ||
                    !in_array(
                        $tag,
                        [
                            'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                            'p', 'br', 'acronym', 'abbr', 'b',
                            'bdi', 'bdo', 'big', 'blockquote',
                            'center', 'del', 'em', 'font', 'i',
                            'ins', 'kbd', 'meter', 'pre', 'progress',
                            'q', 'rp', 'rt', 'ruby', 's', 'samp',
                            'small', 'strike', 'strong', 'sub',
                            'sup', 'tt', 'u', 'var', 'wbr', 'img',
                            'fieldset', 'map', 'area', 'figcaption',
                            'figure', 'picture', 'source', 'a', 'ul',
                            'ol', 'li', 'dir', 'dl', 'dt', 'dd',
                            'table', 'caption', 'th', 'tr', 'td',
                            'thead', 'tbody', 'tfoot', 'col', 'colgroup',
                            'div', 'span'
                        ]
                    )
                ) {
                    $tag = $origCaseTag = 'div';
                    $div = $container->createElement($tag);
                    $div->appendChild($element->children());
                    $element->replace($div, false);
                    $element = $div;
                }

                foreach ($element->attributes() as $name => $value) {
                    $lowerName = Str::lower($name);
                    if ($lowerName !== $name) {
                        $element->removeAttribute($name);
                        $element->setAttribute($lowerName, $value);
                    }
                }

                if (in_array(
                    $tag,
                    [
                        'span', 'h1', 'h2', 'h3', 'h4', 'h5', 'h6',
                        'p', 'br', 'bdi', 'bdo', 'big', 'blockquote',
                        'center', 'del', 'em', 'font', 'i', 'ins',
                        'kbd', 'pre', 'q', 'rp', 'rt', 'ruby', 's', 'samp',
                        'small', 'strike', 'strong', 'sub', 'sup', 'tt', 'u',
                        'var', 'wbr', 'fieldset', 'figcaption', 'figure',
                        'picture', 'ul', 'ol', 'li', 'dir', 'dl', 'dt', 'dd',
                        'div', 'span'
                    ]
                )) {
                    $exclusions = in_array($tag, ['span', 'kdb', 'samp', 'var']) ? ['title'] : [];
                    $element->removeAllAttributes($exclusions);
                //} elseif ($tag == 'a') {
                //    $element->removeAllAttributes(['href']);
                //    $element->setAttribute('target', '_blank');
                //    $element->setAttribute('rel', 'nofollow noopener');
                //} elseif ($tag == 'img') {
                //    $element->removeAllAttributes(['alt', 'title', 'height', 'ismap', 'sizes', 'src', 'srcset', 'usemap', 'width']);
                //    $element->setAttribute('loading', 'lazy');
                //    $element->setAttribute('referrerpolicy', 'no-referrer');
                } elseif ($tag == 'table') {
                    $element->removeAllAttributes(['height', 'width']);
                    $element->setAttribute('class', 'table table-bordered');
                } elseif (in_array($tag, ['thead', 'tbody', 'tfoot', 'tr'])) {
                    $element->removeAllAttributes();
                } elseif ($tag == 'td' || $tag == 'th') {
                    $element->removeAllAttributes(['height', 'width', 'colspan', 'rowspan', 'nowrap']);
                }

                foreach ($element->attributes() as $name => $value) {
                    $lowerName = Str::lower($name);
                    if (
                        in_array($lowerName, ['id', 'class', 'style', 'content', 'role', 'lang']) ||
                        (strpos($lowerName, 'item') === 0) ||
                        (strpos($lowerName, 'aria-') === 0) ||
                        (strpos($lowerName, 'data-') === 0)
                    ) {
                        $element->removeAttribute($name);
                    }
                }

                if ($origCaseTag !== $tag) {
                    $obj = $container->createElement($tag, null, $element->attributes());
                    $obj->appendChild($element->children());
                    $element->replace($obj, false);
                    $element = $obj;
                }

                static::htmlElementHelper($element->children(), $keepNbsp, $container);

                if (!$element->hasChildren()) {
                    if (in_array($tag, ['h1', 'h2', 'h3', 'h4', 'h5', 'h6', 'div', 'p', 'span', 'font', 'small', 'big'])) {
                        $element->remove();
                    }
                }
            }
        }

        return $elements;
    }

    public static function uglyInsideHtmlBeautifier($html, $root = 'root')
    {
        $startWord = '<' . $root . '>';
        $startPos = mb_strpos($html, $startWord) + mb_strlen($startWord);
        $endPos = mb_strrpos($html, '</' . $root . '>');
        $length = $endPos - $startPos;
        $html = mb_substr($html, $startPos, $length);
        $chunks = explode("\n", $html);
        foreach ($chunks as &$chunk) {
            if (strpos($chunk, '  ') === 0) {
                $chunk = substr($chunk, 2);
            }
        }
        unset($chunk);
        $html = trim(implode("\n", $chunks));

        return $html;
    }

    /**
     * @param Element[] $element
     * @param bool $keepNbsp
     * @return mixed
     */
    public static function html($element, $keepNbsp = true)
    {
        if (is_null($element)) {
            return '';
        }
        if (is_scalar($element)) {
            $element = (string)$element;
            if ($element === '') {
                return '';
            }
        }
        $container = new Document(null, false, 'UTF-8', Document::TYPE_XML);
        $container->preserveWhiteSpace(false);
        if (is_string($element)) {
            $container->loadXml('<root></root>');
            $root = $container->toElement();
            $root->setInnerHtml($element);
        } else {
            $container->loadXml('<root></root>');
            $root = $container->toElement();
            $element = $root->appendChild($element);
        }

        static::htmlElementHelper($root->children(), $keepNbsp, $container);

        if (!$root->hasChildren()) {
            $html = '';
        } else {
            $html = static::uglyInsideHtmlBeautifier($root->toDocument()->format()->xml());
            unset($root, $element, $container);
        }

        return $html;
    }

    public static function string($string, $truncate = true, $keepNbsp = false, $keepNewLines = false)
    {
        static $truncateLength;
        if (!isset($truncateLength)) {
            $truncateLength = config('scrapper.truncate_length');
        }

        $string = @(string)$string;

        if (!$keepNbsp) {
            $string = str_replace('&nbsp;', ' ', $string);
        }


        $string = preg_replace('~(*BSR_ANYCRLF)\R~', "\n", $string);
        if (!$keepNewLines) {
            $string = preg_replace("#[\ \s\t\n]+#u", " ", $string);
        } else {
            $string = preg_replace(
                [
                    "#[\ \t ]+#u",
                    "#\n #",
                    "#\n{2,}#"
                ],
                [
                    " ",
                    "\n",
                    "\n"
                ],
                $string
            );
        }

        $string = trim($string);

        if ($truncate) {
            if (mb_strlen($string) > $truncateLength) {
                $string = mb_substr($string, 0, $truncateLength);
            }
        }

        return $string;
    }

    public static function property($string)
    {
        $haveStartingDash = strpos($string, '-') === 0;
        $haveStartingSpace = strpos($string, ' ') === 1;
        $haveEndingDash = strrpos($string, '-') === (strlen($string) - 1);
        $haveEndingSpace = strpos($string, ' ') === (strlen($string) - 2);

        $string = str_replace(
            [
                '.',
                '->',
                '[',
                ']',
            ],
            '-',
            @(string)$string
        );

        if (strpos($string, '--') !== false) {
            $string = preg_replace('#-{2,}#', '-', $string);
        }

        $string = trim($string, '- ');

        if ($haveStartingDash) {
            if ($haveStartingSpace) {
                $string = ' ' . $string;
            }
            $string = '-' . $string;
        }
        if ($haveEndingDash) {
            if ($haveEndingSpace) {
                $string .= ' ';
            }
            $string .= '-';
        }

        return $string;
    }
}
