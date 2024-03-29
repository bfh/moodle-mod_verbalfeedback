<?php

// autoload_static.php @generated by Composer

namespace Composer\Autoload;

class ComposerStaticInite7295e73671fedf6799e4b530315e6b4
{
    public static $prefixLengthsPsr4 = array (
        'G' => 
        array (
            'Goat1000\\SVGGraph\\' => 18,
        ),
    );

    public static $prefixDirsPsr4 = array (
        'Goat1000\\SVGGraph\\' => 
        array (
            0 => __DIR__ . '/..' . '/goat1000/svggraph',
        ),
    );

    public static $classMap = array (
        'Composer\\InstalledVersions' => __DIR__ . '/..' . '/composer/InstalledVersions.php',
        'Dallgoot\\Yaml' => __DIR__ . '/..' . '/dallgoot/yaml/sources/Yaml.php',
        'Dallgoot\\Yaml\\Builder' => __DIR__ . '/..' . '/dallgoot/yaml/sources/Builder.php',
        'Dallgoot\\Yaml\\Compact' => __DIR__ . '/..' . '/dallgoot/yaml/sources/types/Compact.php',
        'Dallgoot\\Yaml\\Dumper' => __DIR__ . '/..' . '/dallgoot/yaml/sources/Dumper.php',
        'Dallgoot\\Yaml\\DumperHandlers' => __DIR__ . '/..' . '/dallgoot/yaml/sources/DumperHandlers.php',
        'Dallgoot\\Yaml\\Loader' => __DIR__ . '/..' . '/dallgoot/yaml/sources/Loader.php',
        'Dallgoot\\Yaml\\NodeFactory' => __DIR__ . '/..' . '/dallgoot/yaml/sources/NodeFactory.php',
        'Dallgoot\\Yaml\\NodeList' => __DIR__ . '/..' . '/dallgoot/yaml/sources/NodeList.php',
        'Dallgoot\\Yaml\\Nodes\\Actions' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/abstract/Actions.php',
        'Dallgoot\\Yaml\\Nodes\\Anchor' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Anchor.php',
        'Dallgoot\\Yaml\\Nodes\\Blank' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Blank.php',
        'Dallgoot\\Yaml\\Nodes\\Comment' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Comment.php',
        'Dallgoot\\Yaml\\Nodes\\CompactMapping' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/CompactMapping.php',
        'Dallgoot\\Yaml\\Nodes\\CompactSequence' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/CompactSequence.php',
        'Dallgoot\\Yaml\\Nodes\\Directive' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Directive.php',
        'Dallgoot\\Yaml\\Nodes\\DocEnd' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/DocEnd.php',
        'Dallgoot\\Yaml\\Nodes\\DocStart' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/DocStart.php',
        'Dallgoot\\Yaml\\Nodes\\Item' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Item.php',
        'Dallgoot\\Yaml\\Nodes\\JSON' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/JSON.php',
        'Dallgoot\\Yaml\\Nodes\\Key' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Key.php',
        'Dallgoot\\Yaml\\Nodes\\Literal' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Literal.php',
        'Dallgoot\\Yaml\\Nodes\\LiteralFolded' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/LiteralFolded.php',
        'Dallgoot\\Yaml\\Nodes\\Literals' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/abstract/Literals.php',
        'Dallgoot\\Yaml\\Nodes\\NodeGeneric' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/abstract/NodeGeneric.php',
        'Dallgoot\\Yaml\\Nodes\\Partial' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Partial.php',
        'Dallgoot\\Yaml\\Nodes\\Quoted' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Quoted.php',
        'Dallgoot\\Yaml\\Nodes\\Root' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Root.php',
        'Dallgoot\\Yaml\\Nodes\\Scalar' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Scalar.php',
        'Dallgoot\\Yaml\\Nodes\\SetKey' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/SetKey.php',
        'Dallgoot\\Yaml\\Nodes\\SetValue' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/SetValue.php',
        'Dallgoot\\Yaml\\Nodes\\Tag' => __DIR__ . '/..' . '/dallgoot/yaml/sources/nodes/Tag.php',
        'Dallgoot\\Yaml\\Regex' => __DIR__ . '/..' . '/dallgoot/yaml/sources/Regex.php',
        'Dallgoot\\Yaml\\TagFactory' => __DIR__ . '/..' . '/dallgoot/yaml/sources/tag/TagFactory.php',
        'Dallgoot\\Yaml\\Tag\\CoreSchema' => __DIR__ . '/..' . '/dallgoot/yaml/sources/tag/CoreSchema.php',
        'Dallgoot\\Yaml\\Tag\\SchemaInterface' => __DIR__ . '/..' . '/dallgoot/yaml/sources/tag/SchemaInterface.php',
        'Dallgoot\\Yaml\\Tag\\SymfonySchema' => __DIR__ . '/..' . '/dallgoot/yaml/sources/tag/SymfonySchema.php',
        'Dallgoot\\Yaml\\Tagged' => __DIR__ . '/..' . '/dallgoot/yaml/sources/types/Tagged.php',
        'Dallgoot\\Yaml\\YamlObject' => __DIR__ . '/..' . '/dallgoot/yaml/sources/types/YamlObject.php',
        'Dallgoot\\Yaml\\YamlProperties' => __DIR__ . '/..' . '/dallgoot/yaml/sources/YamlProperties.php',
    );

    public static function getInitializer(ClassLoader $loader)
    {
        return \Closure::bind(function () use ($loader) {
            $loader->prefixLengthsPsr4 = ComposerStaticInite7295e73671fedf6799e4b530315e6b4::$prefixLengthsPsr4;
            $loader->prefixDirsPsr4 = ComposerStaticInite7295e73671fedf6799e4b530315e6b4::$prefixDirsPsr4;
            $loader->classMap = ComposerStaticInite7295e73671fedf6799e4b530315e6b4::$classMap;

        }, null, ClassLoader::class);
    }
}
