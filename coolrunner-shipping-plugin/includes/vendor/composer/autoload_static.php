<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInitea49e27124c63430df307af617ab7a1e
{
    public static $prefixLengthsPsr4 = array (
        's' => 
        array (
            'setasign\\Fpdi\\' => 14,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'setasign\\Fpdi\\' => 
        array (
            0 => __DIR__ . '/..' . '/setasign/fpdi/src',
        ),
    );

    public static $classMap = array (
        'Clegginabox\\PDFMerger\\PDFMerger' => __DIR__ . '/..' . '/clegginabox/pdf-merger/src/PDFMerger/PDFMerger.php',
        'FPDF' => __DIR__ . '/..' . '/setasign/fpdf/fpdf.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInitea49e27124c63430df307af617ab7a1e::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInitea49e27124c63430df307af617ab7a1e::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInitea49e27124c63430df307af617ab7a1e::$classMap;

        }, null, ClassLoader::class);
    }
}
